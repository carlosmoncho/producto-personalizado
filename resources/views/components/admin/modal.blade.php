@props([
    'id' => 'modal',
    'title' => 'Modal',
    'size' => '',
    'footer' => true,
    'cancelText' => 'Cancelar',
    'submitText' => 'Guardar',
    'submitId' => null
])

<div class="modal fade" id="{{ $id }}" tabindex="-1" aria-labelledby="{{ $id }}Label" aria-hidden="true">
    <div class="modal-dialog {{ $size ? 'modal-' . $size : '' }}">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="{{ $id }}Label">{{ $title }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                {{ $slot }}
            </div>
            @if($footer)
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ $cancelText }}</button>
                    <button type="button" class="btn btn-primary" 
                            @if($submitId) id="{{ $submitId }}" @endif>
                        {{ $submitText }}
                    </button>
                </div>
            @endif
        </div>
    </div>
</div>