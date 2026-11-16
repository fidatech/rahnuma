<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\{PromotionRequest, Property};
use App\Support\PromotionPrices;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

/** The office asks for a sponsored or featured placement, pays, and waits for the administrator. */
class PromotionRequestController extends Controller
{
    public function index(Request $r)
    {
        return view('tenant.promotions', [
            'properties' => Property::whereIn('visibility', ['public', 'featured'])->whereIn('status', ['available', 'active'])->orderBy('title')->get(['id', 'title', 'code', 'is_sponsored', 'sponsored_until', 'visibility', 'featured_until']),
            'requests' => PromotionRequest::with('property:id,title,code')->latest()->paginate(15),
            'prices' => PromotionPrices::all(),
            'currency' => PromotionPrices::currency(),
            'instructions' => PromotionPrices::instructions(),
            'chosen' => (int) $r->query('property'),
        ]);
    }

    public function store(Request $r)
    {
        $d = $r->validate([
            'property_id' => 'required|integer',
            'kind' => ['required', Rule::in(PromotionRequest::KINDS)],
            'days' => ['required', Rule::in(PromotionRequest::DAYS)],
            'payment_method' => ['required', Rule::in(PromotionRequest::METHODS)],
            'payment_reference' => 'nullable|max:120',
            'receipt' => 'nullable|file|mimes:jpg,jpeg,png,webp,pdf|max:4096',
            'notes' => 'nullable|max:500',
        ]);
        // proof of the payment: the number of the transfer, or a picture of the receipt
        if (empty($d['payment_reference']) && ! $r->hasFile('receipt')) {
            return back()->withErrors(['payment_reference' => __('Give the transaction number or upload the receipt.')])->withInput();
        }

        // only a listing of this office that is on the marketplace can be promoted
        $property = Property::whereIn('visibility', ['public', 'featured'])->whereIn('status', ['available', 'active'])->find($d['property_id']);
        if (! $property) {
            return back()->withErrors(['property_id' => __('Publish the property on the marketplace first.')])->withInput();
        }
        if (PromotionRequest::where('property_id', $property->id)->where('kind', $d['kind'])->where('status', 'pending')->exists()) {
            return back()->withErrors(['property_id' => __('A request for this property is already waiting for approval.')])->withInput();
        }

        PromotionRequest::create([
            'agency_id' => $r->user()->agency_id, 'property_id' => $property->id, 'kind' => $d['kind'], 'days' => (int) $d['days'],
            'amount' => PromotionPrices::price($d['kind'], (int) $d['days']), 'currency' => PromotionPrices::currency(),
            'payment_method' => $d['payment_method'], 'payment_reference' => $d['payment_reference'] ?? null,
            'receipt_path' => $r->hasFile('receipt') ? $r->file('receipt')->store('receipts', 'local') : null,
            'notes' => $d['notes'] ?? null, 'status' => 'pending', 'requested_by' => $r->user()->id,
        ]);

        return back()->with('success', __('Request sent. The placement starts when the administrator confirms your payment.'));
    }

    /** The office may withdraw a request the administrator has not looked at yet. */
    public function destroy(PromotionRequest $promotionRequest)
    {
        abort_unless((int) $promotionRequest->agency_id === (int) auth()->user()->agency_id, 404);
        abort_unless($promotionRequest->status === 'pending', 403);
        if ($promotionRequest->receipt_path) Storage::disk('local')->delete($promotionRequest->receipt_path);
        $promotionRequest->delete();

        return back()->with('success', __('Request withdrawn.'));
    }

    public function receipt(PromotionRequest $promotionRequest)
    {
        abort_unless((int) $promotionRequest->agency_id === (int) auth()->user()->agency_id, 404);
        abort_unless($promotionRequest->receipt_path && Storage::disk('local')->exists($promotionRequest->receipt_path), 404);

        return Storage::disk('local')->response($promotionRequest->receipt_path);
    }
}
