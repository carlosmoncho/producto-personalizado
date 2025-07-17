@extends('layouts.admin')

@section('title', 'Editar Campo Personalizado')

@section('content')
<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Editar Campo Personalizado: {{ $customField->name }}</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('admin.custom-fields.update', $customField) }}">
                    @csrf
                    @method('PUT')

                    <div class="row">
                        <div class="col-md-8">
                            <div class="mb-3">
                                <label for="name" class="form-label">Nombre del Campo <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                       id="name" name="name" value="{{ old('name', $customField->name) }}" required>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="field_type" class="form-label">Tipo de Campo <span class="text-danger">*</span></label>
                                <select class="form-select @error('field_type') is-invalid @enderror" 
                                        id="field_type" name="field_type" required>
                                    <option value="">Seleccionar tipo</option>
                                    <option value="text" {{ old('field_type', $customField->field_type) == 'text' ? 'selected' : '' }}>Texto</option>
                                    <option value="number" {{ old('field_type', $customField->field_type) == 'number' ? 'selected' : '' }}>Número</option>
                                    <option value="email" {{ old('field_type', $customField->field_type) == 'email' ? 'selected' : '' }}>Email</option>
                                    <option value="tel" {{ old('field_type', $customField->field_type) == 'tel' ? 'selected' : '' }}>Teléfono</option>
                                    <option value="select" {{ old('field_type', $customField->field_type) == 'select' ? 'selected' : '' }}>Lista Desplegable</option>
                                    <option value="radio" {{ old('field_type', $customField->field_type) == 'radio' ? 'selected' : '' }}>Radio Button</option>
                                    <option value="checkbox" {{ old('field_type', $customField->field_type) == 'checkbox' ? 'selected' : '' }}>Checkbox</option>
                                    <option value="textarea" {{ old('field_type', $customField->field_type) == 'textarea' ? 'selected' : '' }}>Área de Texto</option>
                                    <option value="file" {{ old('field_type', $customField->field_type) == 'file' ? 'selected' : '' }}>Archivo</option>
                                    <option value="date" {{ old('field_type', $customField->field_type) == 'date' ? 'selected' : '' }}>Fecha</option>
                                    <option value="time" {{ old('field_type', $customField->field_type) == 'time' ? 'selected' : '' }}>Hora</option>
                                    <option value="datetime" {{ old('field_type', $customField->field_type) == 'datetime' ? 'selected' : '' }}>Fecha y Hora</option>
                                </select>
                                @error('field_type')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Opciones para select/radio -->
                    <div class="mb-3" id="options-container" style="{{ in_array($customField->field_type, ['select', 'radio']) ? 'display: block;' : 'display: none;' }}">
                        <label class="form-label">Opciones <span class="text-danger">*</span></label>
                        <div id="options-list">
                            @if(old('options') ?? $customField->options)
                                @foreach(old('options') ?? $customField->options as $index => $option)
                                    <div class="input-group mb-2 option-row">
                                        <input type="text" class="form-control" name="options[]" value="{{ $option }}" 
                                               placeholder="Opción">
                                        <button type="button" class="btn btn-outline-danger remove-option">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </div>
                                @endforeach
                            @else
                                <div class="input-group mb-2 option-row">
                                    <input type="text" class="form-control" name="options[]" placeholder="Opción">
                                    <button type="button" class="btn btn-outline-danger remove-option">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                            @endif
                        </div>
                        <button type="button" class="btn btn-outline-primary btn-sm" id="add-option">
                            <i class="bi bi-plus-circle me-1"></i>Agregar Opción
                        </button>
                        @error('options')
                            <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="placeholder" class="form-label">Placeholder</label>
                        <input type="text" class="form-control @error('placeholder') is-invalid @enderror" 
                               id="placeholder" name="placeholder" value="{{ old('placeholder', $customField->placeholder) }}" 
                               placeholder="Texto de ayuda para el usuario">
                        @error('placeholder')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="help_text" class="form-label">Texto de Ayuda</label>
                        <textarea class="form-control @error('help_text') is-invalid @enderror" 
                                  id="help_text" name="help_text" rows="2">{{ old('help_text', $customField->help_text) }}</textarea>
                        @error('help_text')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="row">
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="sort_order" class="form-label">Orden</label>
                                <input type="number" class="form-control @error('sort_order') is-invalid @enderror" 
                                       id="sort_order" name="sort_order" value="{{ old('sort_order', $customField->sort_order) }}" min="0">
                                @error('sort_order')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="mb-3">
                                <div class="form-check mt-4">
                                    <input class="form-check-input" type="checkbox" id="required" name="required" value="1" 
                                           {{ old('required', $customField->required) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="required">
                                        Campo requerido
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="mb-3">
                                <div class="form-check mt-4">
                                    <input class="form-check-input" type="checkbox" id="active" name="active" value="1" 
                                           {{ old('active', $customField->active) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="active">
                                        Campo activo
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-between">
                        <a href="{{ route('admin.custom-fields.index') }}" class="btn btn-secondary">
                            <i class="bi bi-arrow-left me-2"></i>Cancelar
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-circle me-2"></i>Actualizar Campo
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Vista Previa</h5>
            </div>
            <div class="card-body">
                <div id="field-preview">
                    <!-- La vista previa se actualizará con JavaScript -->
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const fieldTypeSelect = document.getElementById('field_type');
    const optionsContainer = document.getElementById('options-container');
    const previewContainer = document.getElementById('field-preview');
    
    // Manejar cambio de tipo de campo
    fieldTypeSelect.addEventListener('change', function() {
        const fieldType = this.value;
        
        // Mostrar/ocultar opciones
        if (fieldType === 'select' || fieldType === 'radio') {
            optionsContainer.style.display = 'block';
        } else {
            optionsContainer.style.display = 'none';
        }
        
        // Actualizar vista previa
        updatePreview();
    });
    
    // Gestión de opciones
    document.getElementById('add-option').addEventListener('click', function() {
        const optionsList = document.getElementById('options-list');
        const newRow = document.createElement('div');
        newRow.className = 'input-group mb-2 option-row';
        newRow.innerHTML = `
            <input type="text" class="form-control" name="options[]" placeholder="Opción">
            <button type="button" class="btn btn-outline-danger remove-option">
                <i class="bi bi-trash"></i>
            </button>
        `;
        optionsList.appendChild(newRow);
        updatePreview();
    });
    
    document.addEventListener('click', function(e) {
        if (e.target.closest('.remove-option')) {
            e.target.closest('.option-row').remove();
            updatePreview();
        }
    });
    
    // Actualizar vista previa cuando cambien los campos
    document.getElementById('name').addEventListener('input', updatePreview);
    document.getElementById('placeholder').addEventListener('input', updatePreview);
    document.getElementById('help_text').addEventListener('input', updatePreview);
    document.getElementById('required').addEventListener('change', updatePreview);
    
    function updatePreview() {
        const fieldType = fieldTypeSelect.value;
        const fieldName = document.getElementById('name').value || 'Campo de ejemplo';
        const placeholder = document.getElementById('placeholder').value;
        const helpText = document.getElementById('help_text').value;
        const required = document.getElementById('required').checked;
        
        let previewHTML = `<label class="form-label">${fieldName}`;
        if (required) previewHTML += ' <span class="text-danger">*</span>';
        previewHTML += '</label>';
        
        switch (fieldType) {
            case 'text':
            case 'email':
            case 'tel':
            case 'number':
                previewHTML += `<input type="${fieldType}" class="form-control" placeholder="${placeholder}">`;
                break;
            case 'textarea':
                previewHTML += `<textarea class="form-control" rows="3" placeholder="${placeholder}"></textarea>`;
                break;
            case 'select':
                previewHTML += '<select class="form-select"><option>Seleccionar...</option>';
                document.querySelectorAll('#options-list input[name="options[]"]').forEach(input => {
                    if (input.value) previewHTML += `<option>${input.value}</option>`;
                });
                previewHTML += '</select>';
                break;
            case 'radio':
                document.querySelectorAll('#options-list input[name="options[]"]').forEach((input, index) => {
                    if (input.value) {
                        previewHTML += `
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="preview_radio" id="radio_${index}">
                                <label class="form-check-label" for="radio_${index}">${input.value}</label>
                            </div>
                        `;
                    }
                });
                break;
            case 'checkbox':
                previewHTML += `
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="preview_checkbox">
                        <label class="form-check-label" for="preview_checkbox">${fieldName}</label>
                    </div>
                `;
                break;
            case 'file':
                previewHTML += '<input type="file" class="form-control">';
                break;
            case 'date':
                previewHTML += '<input type="date" class="form-control">';
                break;
            case 'time':
                previewHTML += '<input type="time" class="form-control">';
                break;
            case 'datetime':
                previewHTML += '<input type="datetime-local" class="form-control">';
                break;
            default:
                previewHTML = '<p class="text-muted">Selecciona un tipo de campo para ver la vista previa</p>';
        }
        
        if (helpText && fieldType) {
            previewHTML += `<div class="form-text">${helpText}</div>`;
        }
        
        previewContainer.innerHTML = previewHTML;
    }
    
    // Inicializar vista previa
    updatePreview();
});
</script>
@endpush
