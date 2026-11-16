{{--
    A form in a pop-up of the template. Opened by any button with data-modal-open="{id}".
    The slot holds the <form> with its own .modal-body and .modal-footer.
--}}
@props(['id', 'title', 'kicker' => null, 'description' => null, 'wide' => false, 'icon' => 'plus'])
<div class="modal fade" id="{{ $id }}" tabindex="-1" aria-hidden="true" aria-labelledby="{{ $id }}-title">
    <div class="modal-dialog {{ $wide ? 'modal-lg' : '' }}">
        <div class="modal-content">
            <div class="modal-header">
                <div class="set-head">
                    <span class="set-icon"><x-icon :name="$icon" /></span>
                    <div>
                        <h5 class="modal-title" id="{{ $id }}-title">{{ $title }}</h5>
                        @if ($description)<small>{{ $description }}</small>@elseif ($kicker)<small>{{ $kicker }}</small>@endif
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __('Close') }}"></button>
            </div>
            {{ $slot }}
        </div>
    </div>
</div>
