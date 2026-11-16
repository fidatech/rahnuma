<?php

namespace App\Support;

use App\Models\{Agency, Lead, PromotionRequest, Property, Subscription, User};

/**
 * The alerts of the top bar. They are read from the records each time, so nothing has to be
 * marked as done: an alert that asks for an action stays until the action is taken.
 *
 * Each alert: icon, tone, title, text, url, at (a time, or null) and "open" (true while it
 * still needs someone to act; the others count as new until the list has been opened).
 */
class Notifications
{
    public static function for(?User $user): array
    {
        if (! $user) return [];

        try {
            if ($user->isPlatformAdmin()) return self::admin();
            if ($user->agency_id) return self::office($user);
        } catch (\Throwable) {
            // a missing table before "php artisan migrate" must not break every page
        }

        return [];
    }

    private static function admin(): array
    {
        $items = [];

        foreach (PromotionRequest::withoutGlobalScopes()->with(['agency:id,name', 'property:id,code'])->where('status', 'pending')->oldest()->limit(8)->get() as $ask) {
            $items[] = self::item('bullhorn', 'warning', __('Sponsor request'),
                trim(($ask->agency?->name ?: '').' · '.($ask->property?->code ?: '').' · '.__(':count days', ['count' => $ask->days])),
                route('platform.marketplace'), $ask->created_at, true);
        }

        $ending = Subscription::withoutGlobalScopes()->with('agency:id,name')->where('status', 'active')->whereNotNull('ends_at')
            ->whereBetween('ends_at', [now(), now()->addDays(7)])->orderBy('ends_at')->limit(5)->get();
        foreach ($ending as $sub) {
            $items[] = self::item('receipt', 'danger', __('Subscription ends soon'),
                ($sub->agency?->name ?: '').' · '.$sub->ends_at->format('Y-m-d'), route('platform.subscriptions', ['status' => 'expiring']), null, true);
        }

        $unpaid = Subscription::withoutGlobalScopes()->whereNull('paid_at')->where('status', '!=', 'cancelled')->count();
        if ($unpaid) {
            $items[] = self::item('sack-dollar', 'info', __('Unpaid subscriptions'), __(':count subscriptions wait for payment', ['count' => $unpaid]),
                route('platform.subscriptions', ['status' => 'unpaid']), null, true);
        }

        $trials = Agency::whereNotNull('trial_ends_at')->whereBetween('trial_ends_at', [now(), now()->addDays(7)])->count();
        if ($trials) {
            $items[] = self::item('clock', 'info', __('Trials end soon'), __(':count offices finish their trial this week', ['count' => $trials]),
                route('platform.dashboard'), null, true);
        }

        foreach (Agency::where('created_at', '>=', now()->subDays(3))->latest()->limit(3)->get(['id', 'name', 'slug', 'created_at']) as $agency) {
            $items[] = self::item('building-user', 'success', __('New office'), $agency->name, route('platform.agencies.show', $agency), $agency->created_at, false);
        }

        return $items;
    }

    private static function office(User $user): array
    {
        $items = [];
        $agencyId = (int) $user->agency_id;

        // what the administrator decided about the requests of this office
        $decided = PromotionRequest::withoutGlobalScopes()->with('property:id,code,title')->where('agency_id', $agencyId)
            ->whereIn('status', ['approved', 'rejected'])->where('reviewed_at', '>=', now()->subDays(14))->latest('reviewed_at')->limit(6)->get();
        foreach ($decided as $ask) {
            $ok = $ask->status === 'approved';
            $items[] = self::item($ok ? 'circle-check' : 'circle-xmark', $ok ? 'success' : 'danger',
                $ok ? __('Sponsor request approved') : __('Sponsor request rejected'),
                trim(($ask->property?->code ?: '').' · '.($ask->admin_note ?: $ask->property?->title)), route('tenant.promotions.index'), $ask->reviewed_at, false);
        }

        // the subscription or the trial of the office is about to end
        $sub = Subscription::withoutGlobalScopes()->where('agency_id', $agencyId)->where('status', 'active')
            ->where(fn ($q) => $q->whereNull('ends_at')->orWhere('ends_at', '>', now()))->orderByDesc('ends_at')->first();
        $agency = $user->agency;
        if ($sub && $sub->ends_at && $sub->ends_at->lte(now()->addDays(7))) {
            $items[] = self::item('receipt', 'danger', __('Your subscription ends soon'), $sub->ends_at->format('Y-m-d'), route('tenant.dashboard'), null, true);
        } elseif (! $sub && $agency?->trial_ends_at && $agency->trial_ends_at->isFuture() && $agency->trial_ends_at->lte(now()->addDays(7))) {
            $items[] = self::item('clock', 'warning', __('Your trial ends soon'), $agency->trial_ends_at->format('Y-m-d'), route('tenant.dashboard'), null, true);
        }

        // a paid placement of the office is in its last days
        $placements = Property::withoutGlobalScopes()->where('agency_id', $agencyId)->where('is_sponsored', 1)->whereNotNull('sponsored_until')
            ->whereBetween('sponsored_until', [now(), now()->addDays(3)])->limit(4)->get(['id', 'code', 'sponsored_until']);
        foreach ($placements as $property) {
            $items[] = self::item('bullhorn', 'warning', __('Sponsored placement ends soon'), $property->code.' · '.$property->sponsored_until->format('Y-m-d'),
                route('tenant.promotions.index', ['property' => $property->id]), null, true);
        }

        $leads = Lead::withoutGlobalScopes()->where('agency_id', $agencyId)->where('status', 'new')->count();
        if ($leads) {
            $items[] = self::item('filter', 'info', __('New leads'), __(':count leads wait for a first contact', ['count' => $leads]),
                route('tenant.resource.index', 'leads'), null, true);
        }

        return $items;
    }

    private static function item(string $icon, string $tone, string $title, string $text, string $url, $at, bool $open): array
    {
        return ['icon' => $icon, 'tone' => $tone, 'title' => $title, 'text' => trim($text, ' ·'), 'url' => $url, 'at' => $at?->toIso8601String(), 'open' => $open];
    }
}
