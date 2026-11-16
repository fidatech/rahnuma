<?php

namespace App\Services;

use App\Models\{Agency, PlatformEntry, Promotion, Property};
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\{Cache, DB};

/**
 * Paid placements on the marketplace. Only the administrator gives them: the office pays the
 * platform, the placement runs for a number of days, and the fee is written to money in.
 */
class PromotionService
{
    /** Start or extend a placement. $target is the property, or the office for "featured_office". */
    public function start(string $kind, Property|Agency $target, int $days, float $amount, string $currency, ?string $notes = null): Promotion
    {
        return DB::transaction(function () use ($kind, $target, $days, $amount, $currency, $notes) {
            $now = now();
            $current = match ($kind) {
                'sponsored' => $target->is_sponsored ? $target->sponsored_until : null,
                'featured' => $target->visibility === 'featured' ? $target->featured_until : null,
                default => $target->marketplace_featured ? $target->marketplace_featured_until : null,
            };
            // days bought while a placement is still running are added to its end
            $from = $current instanceof Carbon && $current->isFuture() ? $current->copy() : $now->copy();
            $until = $from->copy()->addDays($days);

            match ($kind) {
                'sponsored' => $target->update(['is_sponsored' => true, 'sponsored_until' => $until]),
                'featured' => $target->update(['visibility' => 'featured', 'featured_at' => $target->featured_at ?: $now, 'featured_until' => $until, 'published_at' => $target->published_at ?: $now]),
                default => $target->update(['marketplace_featured' => true, 'marketplace_featured_until' => $until]),
            };

            $agencyId = $target instanceof Agency ? $target->id : $target->agency_id;
            $entry = null;
            if ($amount > 0) {
                $entry = PlatformEntry::create([
                    'direction' => 'in', 'category' => $kind === 'sponsored' ? 'sponsorship' : 'featuring',
                    'amount' => $amount, 'currency' => $currency, 'entry_date' => $now->toDateString(), 'agency_id' => $agencyId,
                    'reference' => $target instanceof Property ? $target->code : null, 'notes' => $notes, 'created_by' => auth()->id(),
                ]);
            }

            $promotion = Promotion::create([
                'kind' => $kind, 'agency_id' => $agencyId, 'property_id' => $target instanceof Property ? $target->id : null,
                'starts_at' => $from, 'ends_at' => $until, 'amount' => $amount, 'currency' => $currency,
                'platform_entry_id' => $entry?->id, 'notes' => $notes, 'created_by' => auth()->id(),
            ]);
            $this->refresh();

            return $promotion;
        });
    }

    /** End a placement today. The fee already received stays in money in. */
    public function stop(string $kind, Property|Agency $target): void
    {
        match ($kind) {
            'sponsored' => $target->update(['is_sponsored' => false, 'sponsored_until' => null]),
            'featured' => $target->update(['visibility' => 'public', 'featured_at' => null, 'featured_until' => null]),
            default => $target->update(['marketplace_featured' => false, 'marketplace_featured_until' => null]),
        };

        Promotion::where('kind', $kind)->whereNull('stopped_at')->where('ends_at', '>', now())
            ->when($target instanceof Property, fn ($q) => $q->where('property_id', $target->id), fn ($q) => $q->where('agency_id', $target->id)->whereNull('property_id'))
            ->update(['stopped_at' => now()]);
        $this->refresh();
    }

    /** Take down the placements whose days have run out. Cheap enough to call when a page is built. */
    public function expire(): void
    {
        $now = now();
        $changed = Property::withoutGlobalScopes()->where('is_sponsored', 1)->whereNotNull('sponsored_until')->where('sponsored_until', '<', $now)
            ->update(['is_sponsored' => false, 'sponsored_until' => null]);
        $changed += Property::withoutGlobalScopes()->where('visibility', 'featured')->whereNotNull('featured_until')->where('featured_until', '<', $now)
            ->update(['visibility' => 'public', 'featured_at' => null, 'featured_until' => null]);
        $changed += Agency::where('marketplace_featured', 1)->whereNotNull('marketplace_featured_until')->where('marketplace_featured_until', '<', $now)
            ->update(['marketplace_featured' => false, 'marketplace_featured_until' => null]);

        if ($changed) $this->refresh();
    }

    private function refresh(): void
    {
        Cache::forget('rahnuma:marketplace:home:v9');
    }
}
