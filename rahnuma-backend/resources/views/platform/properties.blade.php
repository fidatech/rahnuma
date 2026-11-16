@extends('layouts.workspace')
@section('title', __('All properties'))
@section('workspace')
<x-page-header :title="__('All properties')" icon="building" :crumbs="[[__('Platform'), route('platform.dashboard')], [__('All properties')]]" :subtitle="__('Every listing of every office. Hide, publish, feature or delete any of them.')">
    <a class="btn btn-outline-soft" href="{{ route('platform.marketplace') }}"><x-icon name="bullhorn" class="me-1" /> {{ __('Promotions') }}</a>
</x-page-header>

<div class="row g-3 mb-3">
    <div class="col-6 col-xl-3"><x-stat :label="__('All properties')" :value="number_format($counts['all'])" icon="building" tone="blue" /></div>
    <div class="col-6 col-xl-3"><x-stat :label="__('On the marketplace')" :value="number_format($counts['public'])" icon="store" tone="green" :href="route('platform.properties', ['visibility' => 'public'])" /></div>
    <div class="col-6 col-xl-3"><x-stat :label="__('Private')" :value="number_format($counts['private'])" icon="eye-slash" tone="violet" :href="route('platform.properties', ['visibility' => 'private'])" /></div>
    <div class="col-6 col-xl-3"><x-stat :label="__('Sponsored')" :value="number_format($counts['sponsored'])" icon="bullhorn" tone="amber" :href="route('platform.marketplace')" /></div>
</div>

<form class="card" method="get">
    <div class="card-body"><div class="row g-2 align-items-end">
        <div class="col-12 col-lg"><label class="form-label">{{ __('Search') }}</label><div class="input-icon"><x-icon name="magnifying-glass" /><input class="form-control" name="q" value="{{ request('q') }}" placeholder="{{ __('Title or property code') }}"></div></div>
        <div class="col-6 col-lg-3"><label class="form-label">{{ __('Office') }}</label><select class="form-select" name="agency_id" data-placeholder="{{ __('Any office') }}"><option value=""></option>@foreach ($agencies as $a)<option value="{{ $a->id }}" @selected(request('agency_id') == $a->id)>{{ $a->name }}</option>@endforeach</select></div>
        <div class="col-6 col-lg-2"><label class="form-label">{{ __('Visibility') }}</label><select class="form-select" name="visibility" data-placeholder="{{ __('Any') }}"><option value=""></option>@foreach (['private', 'public', 'featured'] as $v)<option value="{{ $v }}" @selected(request('visibility') === $v)>{{ enum_label($v) }}</option>@endforeach</select></div>
        <div class="col-6 col-lg-2"><label class="form-label">{{ __('Deal') }}</label><select class="form-select" name="transaction_type" data-placeholder="{{ __('Any deal') }}"><option value=""></option>@foreach (config('rahnuma.transaction_types') as $t)<option value="{{ $t }}" @selected(request('transaction_type') === $t)>{{ deal_label($t, true) }}</option>@endforeach</select></div>
        <div class="col-6 col-lg-auto d-flex gap-2"><button class="btn btn-primary flex-fill"><x-icon name="magnifying-glass" class="me-1" /> {{ __('Search') }}</button>@if (request()->hasAny(['q', 'agency_id', 'visibility', 'transaction_type', 'status']))<a class="btn btn-outline-soft" href="{{ route('platform.properties') }}">{{ __('Reset') }}</a>@endif</div>
    </div></div>
</form>

<div class="card">
    <div class="card-body">
        @if ($properties->isEmpty())
            <x-workspace.empty icon="building" :title="__('No properties found.')" :description="__('Try another search.')" />
        @else
        <table class="table table-hover w-100 align-middle">
            <thead><tr><th>{{ __('Property') }}</th><th>{{ __('Office') }}</th><th>{{ __('Deal') }}</th><th>{{ __('Price') }}</th><th>{{ __('Status') }}</th><th class="text-end">{{ __('Actions') }}</th></tr></thead>
            <tbody>
            @foreach ($properties as $p)
                @php $img = $p->coverMedia; $amount = $p->transaction_type === 'rent' ? $p->monthly_rent : $p->price; @endphp
                <tr>
                    <td><div class="d-flex align-items-center gap-2"><span class="rh-thumb">@if ($img)<img src="{{ asset('storage/'.($img->thumbnail_path ?: $img->path)) }}" alt="" loading="lazy">@else<x-icon name="building" />@endif</span><div class="min-w-0"><strong class="d-block text-heading text-truncate" style="max-width:280px">{{ $p->title }}</strong><span class="small text-muted"><span dir="ltr">{{ $p->code }}</span> · {{ loc_name($p->province) ?: '—' }}</span></div></div></td>
                    <td>@if ($p->agency)<a href="{{ route('platform.agencies.show', $p->agency) }}">{{ $p->agency->name }}</a>@else—@endif</td>
                    <td>{{ deal_label($p->transaction_type) }}</td>
                    <td dir="ltr" class="text-nowrap">{{ number_format((float) $amount) }} {{ $p->currency }}</td>
                    <td><span class="badge-soft {{ status_tone($p->visibility) }}">{{ enum_label($p->visibility) }}</span> <span class="badge-soft {{ status_tone($p->status) }}">{{ enum_label($p->status) }}</span>@if ($p->is_sponsored) <span class="badge-soft warning"><x-icon name="bullhorn" /></span>@endif</td>
                    <td class="text-end text-nowrap">
                        <div class="rh-actions">
                            @foreach (['private' => ['eye-slash', 'Hide'], 'public' => ['store', 'Publish']] as $v => [$icon, $label])
                                @if ($p->visibility !== $v)<form method="post" action="{{ route('platform.properties.visibility', $p) }}">@csrf<input type="hidden" name="visibility" value="{{ $v }}"><button class="btn btn-sm btn-light-secondary" title="{{ __($label) }}"><x-icon :name="$icon" /> <span>{{ __($label) }}</span></button></form>@endif
                            @endforeach
                            @if (in_array($p->visibility, ['public', 'featured'], true))<a class="btn btn-sm btn-soft-primary" href="{{ route('platform.marketplace', ['q' => $p->code]) }}" title="{{ __('Sell a placement') }}"><x-icon name="bullhorn" /> <span>{{ __('Promote') }}</span></a>@endif
                            <form method="post" action="{{ route('platform.properties.destroy', $p) }}" data-confirm="{{ __('Delete this property?') }}" data-confirm-text="{{ $p->code }} · {{ $p->title }}" data-confirm-ok="{{ __('Delete') }}">@csrf @method('DELETE')<button class="btn btn-sm btn-soft-danger" title="{{ __('Delete') }}"><x-icon name="trash" /></button></form>
                        </div>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
        {{ $properties->links('partials.pagination') }}
        @endif
    </div>
</div>
@endsection
