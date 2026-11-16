<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\{Account, Commission, Customer, Owner, Property, PropertyOwner, Province, RentSchedule, SettaSerial, Transaction};
use App\Services\{AccountingService, CommissionService, VerificationService};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{Cache, DB};
use Illuminate\Validation\ValidationException;

class TransactionController extends Controller
{
    public function index(Request $request)
    {
        $query = Transaction::with('property');
        if ($request->filled('q')) {
            $term = trim((string) $request->q);
            $query->where(function ($q) use ($term) {
                $q->where('reference_no','like','%'.$term.'%')->orWhere('setta_number','like','%'.$term.'%')
                    ->orWhereHas('property', fn ($p) => $p->where('title','like','%'.$term.'%')->orWhere('code','like','%'.$term.'%'));
            });
        }
        if ($request->filled('transaction_type')) $query->where('transaction_type',$request->transaction_type);
        if ($request->filled('status')) $query->where('status',$request->status);

        $agg = Transaction::selectRaw("COUNT(*) as total, SUM(CASE WHEN transaction_type='sale' THEN 1 ELSE 0 END) as sale_count, SUM(CASE WHEN transaction_type='rent' THEN 1 ELSE 0 END) as rent_count, SUM(CASE WHEN transaction_type='bay_wafa' THEN 1 ELSE 0 END) as mortgage_count")->first();
        return view('tenant.transactions.index', [
            'transactions' => $query->latest()->paginate(20)->withQueryString(),
            'stats' => [
                'total' => (int) $agg->total,
                'sale' => (int) $agg->sale_count,
                'rent' => (int) $agg->rent_count,
                'mortgage' => (int) $agg->mortgage_count,
            ],
        ]);
    }

    public function create(Request $request)
    {
        $oldPropertyId = session()->getOldInput('property_id') ?: $request->integer('property_id');
        $oldOwnerId = session()->getOldInput('seller_owner_id');
        $oldCustomerId = session()->getOldInput('buyer_customer_id');
        return view('tenant.transactions.form', [
            'selectedProperty' => $oldPropertyId ? Property::with('owner:id,name')->find($oldPropertyId) : null,
            'selectedOwner' => $oldOwnerId ? Owner::find($oldOwnerId) : null,
            'selectedCustomer' => $oldCustomerId ? Customer::find($oldCustomerId) : null,
            'provinces' => \Illuminate\Support\Facades\Cache::remember('rahnuma:provinces:list', 3600, fn () => Province::orderBy('name_en')->get(['id','name_en','name_fa'])),
        ]);
    }

    public function store(Request $request, CommissionService $commission, VerificationService $verify, AccountingService $accounting)
    {
        $data = $request->validate([
            'property_id' => 'required|integer',
            'transaction_type' => 'required|in:sale,rent,bay_wafa',
            'setta_number' => 'nullable|max:120',
            'seller_owner_id' => 'nullable|integer',
            'buyer_customer_id' => 'nullable|integer',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'amount' => 'nullable|numeric|min:0',
            'monthly_rent' => 'nullable|numeric|min:0',
            'deposit_amount' => 'nullable|numeric|min:0',
            'currency' => 'required|max:5',
            'exchange_rate_to_afn' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string|max:5000',
        ]);

        $property = Property::with('owner')->findOrFail($data['property_id']);
        $sellerOwnerId = $data['seller_owner_id'] ?: $property->owner_id;
        $buyerCustomerId = $data['buyer_customer_id'] ?? null;

        if (!$sellerOwnerId || !Owner::find($sellerOwnerId)) {
            throw ValidationException::withMessages(['seller_owner_id' => 'Select or create the property owner / landlord before completing this transaction.']);
        }
        if (!$buyerCustomerId || !Customer::find($buyerCustomerId)) {
            throw ValidationException::withMessages(['buyer_customer_id' => 'Select or create the buyer / tenant before completing this transaction.']);
        }

        $data['seller_owner_id'] = $sellerOwnerId;
        $data['buyer_customer_id'] = $buyerCustomerId;

        return DB::transaction(function () use ($data, $property, $commission, $verify, $accounting) {
            if (!$property->owner_id && !empty($data['seller_owner_id'])) {
                $property->update(['owner_id' => $data['seller_owner_id']]);
                PropertyOwner::updateOrCreate(
                    ['property_id' => $property->id, 'owner_id' => $data['seller_owner_id']],
                    ['is_primary' => true, 'ownership_percent' => 100]
                );
                $property->load('owner');
            }
            $agencyId = (int) auth()->user()->agency_id;
            $type = $data['transaction_type'];

            if ($type === 'rent') {
                $data['landlord_owner_id'] = $data['seller_owner_id'];
                $data['tenant_customer_id'] = $data['buyer_customer_id'];
            }

            $base = $type === 'rent' ? ($data['monthly_rent'] ?? 0) : ($data['amount'] ?? 0);
            if ($base <= 0) {
                throw ValidationException::withMessages([
                    $type === 'rent' ? 'monthly_rent' : 'amount' => 'Transaction value must be greater than zero.',
                ]);
            }

            $calculated = $commission->calculate($type, $base);
            $setta = null;

            if (!empty($data['setta_number'])) {
                $setta = SettaSerial::withoutGlobalScopes()
                    ->where('agency_id', $agencyId)
                    ->where('serial_no', $data['setta_number'])
                    ->lockForUpdate()
                    ->first();

                if ($setta && $setta->status !== 'available') {
                    throw ValidationException::withMessages(['setta_number' => 'This tracked Setta serial has already been used or is unavailable.']);
                }
                if (config('rahnuma.government_mode') && !$setta) {
                    throw ValidationException::withMessages(['setta_number' => 'Government mode requires a Setta serial assigned to this Rahnama.']);
                }
            }

            $transaction = Transaction::create($data + [
                'status' => 'completed',
                'reference_no' => 'TX-'.date('Ymd').'-'.random_int(10000, 99999),
                'setta_serial_id' => $setta?->id,
                'commission_total' => $calculated['total'],
                'commission_side_a' => $calculated['side_a'],
                'commission_side_b' => $calculated['side_b'],
                'completed_at' => now(),
                'created_by' => auth()->id(),
                'approved_by' => auth()->id(),
            ]);

            $verify->assign($transaction);

            if ($setta) {
                $setta->update(['status' => 'used', 'used_at' => now(), 'transaction_id' => $transaction->id]);
            }

            foreach ([['party_a', $calculated['side_a']], ['party_b', $calculated['side_b']]] as [$kind, $amount]) {
                Commission::create([
                    'transaction_id' => $transaction->id,
                    'type' => $kind,
                    'amount' => $amount,
                    'currency' => $transaction->currency,
                    'status' => 'due',
                    'due_at' => now()->toDateString(),
                ]);
            }

            if ($type === 'rent' && $transaction->start_date && $transaction->end_date && $transaction->monthly_rent) {
                $date = $transaction->start_date->copy();
                while ($date->lte($transaction->end_date)) {
                    RentSchedule::create([
                        'transaction_id' => $transaction->id,
                        'due_date' => $date->toDateString(),
                        'amount' => $transaction->monthly_rent,
                        'currency' => $transaction->currency,
                        'status' => $date->isPast() ? 'due' : 'upcoming',
                        'paid_amount' => 0,
                    ]);
                    $date->addMonth();
                }
            }

            $property->update(['status' => $type === 'sale' ? 'sold' : ($type === 'rent' ? 'rented' : 'mortgaged')]);
            Cache::forget('rahnuma:marketplace:home:v8');

            $receivable = Account::where('code', '1200')->first();
            $incomeCode = $type === 'sale' ? '4000' : ($type === 'rent' ? '4010' : '4020');
            $income = Account::where('code', $incomeCode)->first();

            if ($receivable && $income && $calculated['total'] > 0) {
                $accounting->post($agencyId, 'Commission receivable for '.$transaction->reference_no, [
                    ['account_id' => $receivable->id, 'debit' => $calculated['total'], 'credit' => 0, 'currency' => $transaction->currency, 'exchange_rate_to_afn' => $transaction->exchange_rate_to_afn],
                    ['account_id' => $income->id, 'debit' => 0, 'credit' => $calculated['total'], 'currency' => $transaction->currency, 'exchange_rate_to_afn' => $transaction->exchange_rate_to_afn],
                ], 'transaction', $transaction->id);
            }

            return redirect()->route('tenant.transactions.show', $transaction)->with('success', 'Transaction completed and verification record assigned.');
        });
    }

    public function show(Transaction $transaction)
    {
        $transaction->load(['property.owner','property.coverMedia','commissions','rentSchedules']);
        return view('tenant.transactions.show', compact('transaction'));
    }

    public function quick(Transaction $transaction)
    {
        $transaction->load(['property.owner','property.coverMedia','commissions']);
        return view('tenant.transactions.quick', compact('transaction'));
    }
}
