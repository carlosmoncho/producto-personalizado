@props([
    'label' => null,
    'name' => null,
    'required' => false,
    'error' => null,
    'help' => null,
    'wrapper' => 'mb-3'
])

<div class="{{ $wrapper }}">
    @if($label)
        <label for="{{ $name }}" class="form-label">
            {{ $label }}
            @if($required)
                <span class="text-danger">*</span>
            @endif
        </label>
    @endif
    
    {{ $slot }}
    
    @if($help)
        <small class="text-muted">{{ $help }}</small>
    @endif
    
    @if($error || $errors->has($name))
        <div class="invalid-feedback d-block">
            {{ $error ?? $errors->first($name) }}
        </div>
    @endif
</div>