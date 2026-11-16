@php
    $erpText = [
        'cancel' => __('Cancel'),
        'confirmOk' => __('Yes'),
        'confirmTitle' => __('Are you sure?'),
        'noResults' => __('No results'),
    ];
    $rahnumaText = [
        'close' => __('Close'),
        'quickView' => __('Quick view'),
        'previewFailed' => __('Could not load this preview. Open the full record instead.'),
        'couldNotSave' => __('Could not save this record.'),
        'networkError' => __('Network or server error. Please try again.'),
        'saved' => __('Saved and selected.'),
        'selectOption' => __('Select an option'),
    ];
@endphp
<div id="rahnuma-modals">@stack('modals')</div>
<div id="rahnuma-page-scripts" hidden>@stack('scripts')</div>
<div class="spa-progress" id="rahnuma-spa-progress" aria-hidden="true"><span></span></div>
<script>window.ERP_TEXT = {{ Illuminate\Support\Js::from($erpText) }}; window.RAHNUMA_TEXT = {{ Illuminate\Support\Js::from($rahnumaText) }};</script>
<script defer src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>
<script defer src="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/js/select2.min.js"></script>
<script defer src="{{ asset_v('js/app.js') }}"></script>
<script defer src="{{ asset_v('assets/rahnuma-smart.js') }}"></script>
<script defer src="{{ asset_v('assets/rahnuma-spa.js') }}"></script>
