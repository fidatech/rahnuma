<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\{Account, Customer, Owner, Property, Province, Transaction, User};
use App\Services\AccountingService;
use Illuminate\Http\Request;
use Illuminate\Support\{Facades\Cache, Str};

class ResourceController extends Controller
{
    private array $resources = [
        'owners' => [\App\Models\Owner::class, ['name'=>'text','phone'=>'text','whatsapp'=>'text','tazkira_no'=>'text','email'=>'email','address'=>'text','notes'=>'textarea']],
        'customers' => [\App\Models\Customer::class, ['name'=>'text','phone'=>'text','whatsapp'=>'text','tazkira_no'=>'text','email'=>'email','address'=>'text','source'=>'text','notes'=>'textarea']],
        'leads' => [\App\Models\Lead::class, ['customer_id'=>'number','property_id'=>'number','source'=>'text','status'=>'text','assigned_user_id'=>'number','next_follow_up_at'=>'datetime-local','notes'=>'textarea']],
        'visits' => [\App\Models\Visit::class, ['customer_id'=>'number','property_id'=>'number','assigned_user_id'=>'number','scheduled_at'=>'datetime-local','status'=>'text','outcome'=>'text','rejection_reason'=>'text','notes'=>'textarea']],
        'payments' => [\App\Models\Payment::class, ['transaction_id'=>'number','customer_id'=>'number','owner_id'=>'number','direction'=>'text','category'=>'text','amount'=>'number','currency'=>'text','exchange_rate_to_afn'=>'number','method'=>'text','reference_no'=>'text','paid_at'=>'datetime-local','notes'=>'textarea']],
        'expenses' => [\App\Models\Expense::class, ['category'=>'text','amount'=>'number','currency'=>'text','exchange_rate_to_afn'=>'number','method'=>'text','reference_no'=>'text','expense_date'=>'date','description'=>'textarea']],
    ];

    private function cfg(string $resource): array
    {
        abort_unless(isset($this->resources[$resource]), 404);
        return $this->resources[$resource];
    }

    public function index(Request $request, string $resource)
    {
        [$model, $fields] = $this->cfg($resource);
        $query = $model::query();

        if ($resource === 'leads' || $resource === 'visits') {
            $query->with([
                'customer:id,name,phone',
                'property:id,code,title',
                'assignedUser:id,name',
            ]);
        } elseif ($resource === 'payments') {
            $query->with([
                'customer:id,name,phone',
                'owner:id,name,phone',
                'transaction:id,reference_no',
            ]);
        }

        if ($request->filled('q')) {
            $term = trim((string) $request->q);
            $searchable = match ($resource) {
                'owners', 'customers' => ['name','phone','whatsapp','tazkira_no','email','address'],
                'leads' => ['source','status','notes'],
                'visits' => ['status','outcome','rejection_reason','notes'],
                'payments' => ['category','currency','method','reference_no','notes'],
                'expenses' => ['category','currency','method','reference_no','description'],
                default => [],
            };
            $query->where(function ($q) use ($searchable, $term) {
                foreach ($searchable as $column) $q->orWhere($column, 'like', '%'.$term.'%');
            });
        }

        if ($request->filled('status') && in_array($resource, ['leads','visits'], true)) {
            $query->where('status', $request->status);
        }

        $items = $query->latest()->paginate(20)->withQueryString();
        $lookupUrls = [
            'customer_id' => route('tenant.lookup', 'customers'),
            'owner_id' => route('tenant.lookup', 'owners'),
            'property_id' => route('tenant.lookup', 'properties'),
            'transaction_id' => route('tenant.lookup', 'transactions'),
            'assigned_user_id' => route('tenant.lookup', 'users'),
        ];

        $selectOptions = $this->selectOptions($resource);
        $provinces = Cache::remember('rahnuma:provinces:list', 3600, fn () => Province::orderBy('name_en')->get(['id','name_en','name_fa']));
        $stats = $this->stats($resource, $model);

        return view('tenant.resource', compact('resource','fields','items','lookupUrls','selectOptions','provinces','stats'));
    }

    public function quick(string $resource, int $id)
    {
        [$model] = $this->cfg($resource);
        $item = $model::findOrFail($id);

        if ($resource === 'leads' || $resource === 'visits') {
            $item->load(['customer:id,name,phone,whatsapp','property:id,code,title,status,transaction_type','assignedUser:id,name']);
        } elseif ($resource === 'payments') {
            $item->load(['customer:id,name,phone','owner:id,name,phone','transaction:id,reference_no,transaction_type']);
        }

        return view('tenant.resource-quick', compact('resource', 'item'));
    }

    public function store(Request $request, string $resource, AccountingService $accounting)
    {
        [$model, $fields] = $this->cfg($resource);
        $data = $request->only(array_keys($fields));
        $data = array_filter($data, fn ($v) => $v !== null && $v !== '');

        $this->ensureTenantReferences($data);

        if (in_array($resource, ['expenses','payments'], true)) $data['created_by'] = auth()->id();
        $item = $model::create($data);
        $agencyId = (int) auth()->user()->agency_id;

        if ($resource === 'expenses') {
            $cash = Account::where('code','1000')->first();
            $expense = Account::where('code','5090')->first();
            if ($cash && $expense) {
                $accounting->post($agencyId, 'Expense: '.($item->category ?? 'Other'), [
                    ['account_id'=>$expense->id,'debit'=>$item->amount,'credit'=>0,'currency'=>$item->currency,'exchange_rate_to_afn'=>$item->exchange_rate_to_afn ?: 1],
                    ['account_id'=>$cash->id,'debit'=>0,'credit'=>$item->amount,'currency'=>$item->currency,'exchange_rate_to_afn'=>$item->exchange_rate_to_afn ?: 1],
                ], 'expense', $item->id);
            }
        }

        if ($resource === 'payments' && ($item->direction ?? '') === 'in') {
            $cash = Account::where('code','1000')->first();
            $receivable = Account::where('code','1200')->first();
            if ($cash && $receivable) {
                $accounting->post($agencyId, 'Payment received '.($item->reference_no ?? ''), [
                    ['account_id'=>$cash->id,'debit'=>$item->amount,'credit'=>0,'currency'=>$item->currency,'exchange_rate_to_afn'=>$item->exchange_rate_to_afn ?: 1],
                    ['account_id'=>$receivable->id,'debit'=>0,'credit'=>$item->amount,'currency'=>$item->currency,'exchange_rate_to_afn'=>$item->exchange_rate_to_afn ?: 1],
                ], 'payment', $item->id);
            }
        }

        return back()->with('success', __(':name created.', ['name' => __(Str::headline(Str::singular($resource)))]));
    }

    public function destroy(string $resource, int $id)
    {
        [$model] = $this->cfg($resource);
        $model::findOrFail($id)->delete();
        return back()->with('success', __('Deleted.'));
    }

    private function ensureTenantReferences(array $data): void
    {
        if (!empty($data['customer_id']) && !Customer::find($data['customer_id'])) abort(422, __('Invalid customer.'));
        if (!empty($data['owner_id']) && !Owner::find($data['owner_id'])) abort(422, __('Invalid owner.'));
        if (!empty($data['property_id']) && !Property::find($data['property_id'])) abort(422, __('Invalid property.'));
        if (!empty($data['transaction_id']) && !Transaction::find($data['transaction_id'])) abort(422, __('Invalid transaction.'));
        if (!empty($data['assigned_user_id']) && !User::where('agency_id',auth()->user()->agency_id)->find($data['assigned_user_id'])) abort(422, __('Invalid team member.'));
    }

    private function stats(string $resource, string $model): array
    {
        $base = [
            'total' => $model::count(),
            'month' => $model::where('created_at', '>=', now()->startOfMonth())->count(),
        ];

        if ($resource === 'leads') {
            $base['attention'] = $model::whereIn('status',['new','contacted','negotiating'])->count();
            $base['done'] = $model::where('status','won')->count();
        } elseif ($resource === 'visits') {
            $base['attention'] = $model::where('status','scheduled')->count();
            $base['done'] = $model::where('status','completed')->count();
        } elseif ($resource === 'owners') {
            $base['attention'] = Owner::whereDoesntHave('primaryProperties')->count();
            $base['done'] = Owner::whereHas('primaryProperties')->count();
        } else {
            $base['attention'] = null;
            $base['done'] = null;
        }

        return $base;
    }

    private function selectOptions(string $resource): array
    {
        $common = [
            'currency' => collect(config('rahnuma.currencies'))->mapWithKeys(fn($x)=>[$x=>$x])->all(),
            'method' => ['cash'=>'Cash','bank'=>'Bank','card'=>'Card','hesabpay'=>'HesabPay','other'=>'Other'],
            'source' => ['walk-in'=>'Walk-in','referral'=>'Referral','marketplace'=>'Marketplace','whatsapp'=>'WhatsApp','facebook'=>'Facebook','phone'=>'Phone','other'=>'Other'],
        ];

        if ($resource === 'leads') $common['status'] = ['new'=>'New','contacted'=>'Contacted','property_suggested'=>'Property suggested','visit_scheduled'=>'Visit scheduled','negotiating'=>'Negotiating','won'=>'Won','lost'=>'Lost'];
        if ($resource === 'visits') $common['status'] = ['scheduled'=>'Scheduled','completed'=>'Completed','cancelled'=>'Cancelled','no_show'=>'No-show'];
        if ($resource === 'payments') {
            $common['direction'] = ['in'=>'Money in','out'=>'Money out'];
            $common['category'] = ['commission'=>'Commission','rent'=>'Rent','deposit'=>'Deposit','advance'=>'Advance','refund'=>'Refund','other'=>'Other'];
        }
        if ($resource === 'expenses') $common['category'] = ['office_rent'=>'Office rent','salary'=>'Salary','utilities'=>'Utilities','internet'=>'Internet','marketing'=>'Marketing','transport'=>'Transport','printing'=>'Printing','government_fee'=>'Government fee','other'=>'Other'];

        return $common;
    }
}
