@extends('layouts.workspace')
@section('title', __('Locations'))
@section('workspace')
@php
    $titles = ['province' => 'Provinces', 'district' => 'Districts', 'area' => 'Areas'];
    $singular = ['province' => 'province', 'district' => 'district', 'area' => 'area'];
    $crumbs = [[__('Platform'), route('platform.dashboard')], [__('Locations'), route('platform.locations')]];
    if ($province) $crumbs[] = [loc_name($province), route('platform.locations', ['province' => $province->id])];
    if ($district) $crumbs[] = [loc_name($district)];
@endphp
<x-page-header :title="__($titles[$level]).($district ? ' · '.loc_name($district) : ($province ? ' · '.loc_name($province) : ''))" icon="map-location-dot" :crumbs="$crumbs" :subtitle="__('The places offices choose from when they enter a property. Each has a name in English, Dari and Pashto.')">
    @if ($district)<a class="btn btn-outline-soft" href="{{ route('platform.locations', ['province' => $province->id]) }}">{{ __('Back to districts') }}</a>@elseif ($province)<a class="btn btn-outline-soft" href="{{ route('platform.locations') }}">{{ __('Back to provinces') }}</a>@endif
    <button class="btn btn-primary" type="button" data-modal-open="place-create"><x-icon name="plus" class="me-1" /> {{ __('Add '.$singular[$level]) }}</button>
</x-page-header>

<div class="card">
    <div class="card-body">
        @if ($rows->isEmpty())
            <x-workspace.empty icon="map-location-dot" :title="__('Nothing here yet.')" :description="__('Add the first one with the button above.')" />
        @else
        <table class="table table-hover w-100 align-middle">
            <thead><tr><th>English</th><th>دری</th><th>پښتو</th>@if ($level !== 'area')<th>{{ __('Code') }}</th><th>{{ $level === 'province' ? __('Districts') : __('Areas') }}</th>@endif<th class="text-end">{{ __('Actions') }}</th></tr></thead>
            <tbody>
            @foreach ($rows as $row)
                @php $inside = $level === 'province' ? route('platform.locations', ['province' => $row->id]) : ($level === 'district' ? route('platform.locations', ['district' => $row->id]) : null); @endphp
                <tr>
                    <td>@if ($inside)<a class="fw-bold text-heading text-decoration-none" href="{{ $inside }}">{{ $row->name_en }}</a>@else<strong class="text-heading">{{ $row->name_en }}</strong>@endif</td>
                    <td dir="rtl">{{ $row->name_fa }}</td>
                    <td dir="rtl">{{ $row->name_ps }}</td>
                    @if ($level !== 'area')<td><span class="badge-code" dir="ltr">{{ $row->code ?: '—' }}</span></td><td>{{ number_format($level === 'province' ? $row->districts_count : $row->areas_count) }}</td>@endif
                    <td class="text-end text-nowrap">
                        <div class="rh-actions">
                            @if ($inside)<a class="btn btn-sm btn-soft-primary" href="{{ $inside }}">{{ __('View') }}</a>@endif
                            <button class="btn btn-sm btn-light-secondary" type="button" title="{{ __('Edit') }}" data-modal="place-edit" data-action="{{ route('platform.locations.update', [$level, $row->id]) }}" data-edit="{{ json_encode(['name_en' => $row->name_en, 'name_fa' => $row->name_fa, 'name_ps' => $row->name_ps, 'code' => $row->code]) }}"><x-icon name="pen" /></button>
                            <form method="post" action="{{ route('platform.locations.destroy', [$level, $row->id]) }}" data-confirm="{{ __('Delete this place?') }}" data-confirm-text="{{ $row->name_en }} · {{ __('Everything inside it is deleted too, and properties there lose this place.') }}" data-confirm-ok="{{ __('Delete') }}">@csrf @method('DELETE')<button class="btn btn-sm btn-soft-danger" title="{{ __('Delete') }}"><x-icon name="trash" /></button></form>
                        </div>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
        @endif
    </div>
</div>
@endsection
@push('modals')
@foreach (['place-create' => [route('platform.locations.store'), 'Add '.$singular[$level], false], 'place-edit' => ['#', 'Edit place', true]] as $id => [$action, $title, $editing])
<x-workspace.drawer :id="$id" :title="__($title)" :description="__('Write the name in all three languages.')" icon="map-location-dot">
<form method="post" action="{{ $action }}">@csrf @if ($editing) @method('PUT') @else
    <input type="hidden" name="level" value="{{ $level }}">
    @if ($level === 'district')<input type="hidden" name="province_id" value="{{ $province->id }}">@endif
    @if ($level === 'area')<input type="hidden" name="district_id" value="{{ $district->id }}">@endif
@endif
<div class="modal-body"><div class="row g-3">
    <div class="col-12"><label class="form-label">English <span class="req">*</span></label><input class="form-control" name="name_en" dir="ltr" required></div>
    <div class="col-12 col-md-6"><label class="form-label">دری <span class="req">*</span></label><input class="form-control" name="name_fa" dir="rtl" required></div>
    <div class="col-12 col-md-6"><label class="form-label">پښتو <span class="req">*</span></label><input class="form-control" name="name_ps" dir="rtl" required></div>
    @if ($level !== 'area')<div class="col-12 col-md-6"><label class="form-label">{{ __('Code') }} @if ($level === 'province')<span class="req">*</span>@endif</label><input class="form-control" name="code" dir="ltr" maxlength="{{ $level === 'province' ? 10 : 30 }}" @if ($level === 'province') required @endif></div>@endif
</div></div>
<div class="modal-footer"><button class="btn btn-light-secondary" type="button" data-bs-dismiss="modal">{{ __('Cancel') }}</button><button class="btn btn-primary" type="submit"><x-icon name="check" class="me-1" /> {{ __('Save') }}</button></div>
</form>
</x-workspace.drawer>
@endforeach
@endpush
@push('scripts')
@include('platform._edit-script')
@endpush
