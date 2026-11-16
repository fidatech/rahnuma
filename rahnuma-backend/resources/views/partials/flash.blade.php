{{-- Messages set with ->with('success', '...') and validation errors are shown as toasts of the template. --}}
<div id="rahnuma-flashes" hidden>
    @foreach (['success', 'warning', 'danger', 'info'] as $type)
        @if (session($type))
            <div data-toast="{{ $type }}">{{ session($type) }}</div>
        @endif
    @endforeach
    @if ($errors->any())
        <div data-toast="danger">{{ implode(' · ', $errors->all()) }}</div>
    @endif
</div>
