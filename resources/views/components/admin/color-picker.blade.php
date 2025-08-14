@props([
    'name' => 'color',
    'value' => '#000000',
    'label' => 'Color',
    'required' => false
])

<x-admin.form-group :label="$label" :name="$name" :required="$required">
    <div class="input-group">
        <input type="color" 
               class="form-control form-control-color" 
               id="{{ $name }}_picker" 
               value="{{ old($name, $value) }}"
               {{ $attributes }}>
        <input type="text" 
               class="form-control @error($name) is-invalid @enderror" 
               id="{{ $name }}_hex" 
               name="{{ $name }}"
               value="{{ old($name, $value) }}" 
               pattern="^#[0-9A-Fa-f]{6}$" 
               placeholder="#000000"
               @if($required) required @endif>
    </div>
</x-admin.form-group>

@once
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Sync color pickers with hex inputs
    document.querySelectorAll('input[type="color"]').forEach(picker => {
        const hexInput = document.getElementById(picker.id.replace('_picker', '_hex'));
        if (hexInput) {
            picker.addEventListener('input', () => {
                hexInput.value = picker.value.toUpperCase();
            });
            
            hexInput.addEventListener('input', () => {
                if (hexInput.value.match(/^#[0-9A-F]{6}$/i)) {
                    picker.value = hexInput.value;
                }
            });
        }
    });
});
</script>
@endpush
@endonce