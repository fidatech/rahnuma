@extends('layouts.workspace')
@section('workspace')
@php($singular=\Illuminate\Support\Str::singular(ucfirst($resource)))
@php($relationMeta=['customer_id'=>['resource'=>'customer','modal'=>'quick-customer-modal','title'=>'Create customer'],'owner_id'=>['resource'=>'owner','modal'=>'quick-owner-modal','title'=>'Create owner'],'property_id'=>['resource'=>'property','modal'=>'quick-property-modal','title'=>'Create property']])
<div class="page-header"><div class="page-header__copy"><div class="eyebrow">Operations register</div><h1>{{ ucwords($resource) }}</h1><p class="page-subtitle">Manage {{ strtolower($resource) }} with human-readable, connected records — never raw database IDs.</p></div></div>
<div class="grid">
    <div class="g8 card">
        <div class="card-header"><div><h2 class="section-title">{{ ucwords($resource) }} register</h2><div class="card-subtitle">{{ number_format($items->total()) }} records</div></div></div>
        <div class="table-wrap"><table><thead><tr>@foreach($fields as $f=>$type)<th>{{ ucwords(str_replace('_',' ',$f)) }}</th>@endforeach<th>Action</th></tr></thead><tbody>
        @forelse($items as $item)<tr>@foreach($fields as $f=>$type)@php($displayValue=$item->$f)@if(isset($lookups[$f]))@php($resolved=collect($lookups[$f])->firstWhere('id',(int)$item->$f))@php($displayValue=$resolved['text']??$item->$f)@elseif(isset($selectOptions[$f]))@php($displayValue=$selectOptions[$f][$item->$f]??$item->$f)@endif<td>{{ \Illuminate\Support\Str::limit((string)$displayValue,35) ?: '—' }}</td>@endforeach<td><form method="post" action="{{ route('tenant.resource.destroy',[$resource,$item->id]) }}">@csrf @method('DELETE')<button class="btn danger sm">Delete</button></form></td></tr>@empty<tr><td colspan="{{ count($fields)+1 }}"><div class="empty-state">No {{ $resource }} have been recorded yet.</div></td></tr>@endforelse
        </tbody></table></div><div class="pagination">{{ $items->links() }}</div>
    </div>
    <div class="g4 card">
        <div class="eyebrow">Smart create</div><h3>Add {{ $singular }}</h3><p class="muted">Select connected records by name. Create missing records instantly with the + button.</p>
        <form method="post" action="{{ route('tenant.resource.store',$resource) }}">@csrf
            @foreach($fields as $f=>$type)
                <div class="field"><label>{{ ucwords(str_replace('_',' ',$f)) }}</label>
                    @if(isset($lookups[$f]))
                        @php($meta=$relationMeta[$f]??null)
                        <div class="{{ $meta?'smart-input-row':'' }}">
                            <select name="{{ $f }}" id="resource-{{ $f }}" @if($meta)data-smart-resource="{{ $meta['resource'] }}"@endif>
                                <option value="">Select {{ strtolower(str_replace('_id','',str_replace('_',' ',$f))) }}</option>
                                @foreach($lookups[$f] as $option)<option value="{{ $option['id'] }}">{{ $option['text'] }}</option>@endforeach
                            </select>
                            @if($meta)<button type="button" class="smart-add" data-modal-open="{{ $meta['modal'] }}" data-quick-target="resource-{{ $f }}" title="{{ $meta['title'] }}">+</button>@endif
                        </div>
                    @elseif(isset($selectOptions[$f]))
                        <select name="{{ $f }}"><option value="">Select</option>@foreach($selectOptions[$f] as $value=>$label)<option value="{{ $value }}">{{ $label }}</option>@endforeach</select>
                    @elseif($type==='textarea')
                        <textarea name="{{ $f }}"></textarea>
                    @else
                        <input type="{{ $type }}" name="{{ $f }}" step="any">
                    @endif
                </div>
            @endforeach
            <button class="btn block">Save {{ strtolower($singular) }}</button>
        </form>
    </div>
</div>
@endsection

@if(in_array('owner_id',array_keys($fields)))
@push('modals')<x-quick-create.owner-modal />@endpush
@endif
@if(in_array('customer_id',array_keys($fields)))
@push('modals')<x-quick-create.customer-modal />@endpush
@endif
@if(in_array('property_id',array_keys($fields)))
@push('modals')<x-quick-create.property-modal :owners="$quickOwners" :provinces="$provinces" />@endpush
@endif
