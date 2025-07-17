@extends('layouts.admin')

@section('title', 'Detalles del Campo Personalizado')

@section('content')
<div class="row">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Información del Campo</h5>
            </div>
            <div class="card-body">
                <h4>{{ $customField->name }}</h4>
                
                <div class="row mb-3">
                    <div class="col-sm-4"><strong>Tipo:</strong></div>
                    <div class="col-sm-8">
                        <span class="badge bg-info">{{ ucfirst($customField->field_type) }}</span>
                    </div>
                </div>

                @if($customField->placeholder)
                    <div class="row mb-3">
                        <div class="col-sm-4"><strong>Placeholder:</strong></div>
                        <div class="col-sm-8">{{ $customField->placeholder }}</div>
                    </div>
                @endif

                @if($customField->help_text)
                    <div class="row mb-3">
                        <div class="col-sm-4"><strong>Texto de Ayuda:</strong></div>
                        <div class="col-sm-8">{{ $customField->help_text }}</div>
                    </div>
                @endif

                <div class="row mb-3">
                    <div class="col-sm-4"><strong>Requerido:</strong></div>
                    <div class="col-sm-8">
                        @if($customField->required)
                            <span class="badge bg-warning">Sí</span>
                        @else
                            <span class="badge bg-secondary">No</span>
                        @endif
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-sm-4"><strong>Estado:</strong></div>
                    <div class="col-sm-8">
                        @if($customField->active)
                            <span class="badge bg-success">Activo</span>
                        @else
                            <span class="badge bg-secondary">Inactivo</span>
                        @endif
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-sm-4"><strong>Orden:</strong></div>
                    <div class="col-sm-8">{{ $customField->sort_order }}</div>
                </div>

                @if($customField->options && count($customField->options) > 0)
                    <div class="row mb-3">
                        <div class="col-sm-4"><strong>Opciones:</strong></div>
                        <div class="col-sm-8">
                            @foreach($customField->options as $option)
                                <span class="badge bg-primary me-1">{{ $option }}</span>
                            @endforeach
                        </div>
                    </div>
                @endif

                <div class="mt-3">
                    <a href="{{ route('admin.custom-fields.edit', $customField) }}" class="btn btn-primary">
                        <i class="bi bi-pencil me-2"></i>Editar
                    </a>
                    <a href="{{ route('admin.custom-fields.index') }}" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left me-2"></i>Volver
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Vista Previa del Campo</h5>
            </div>
            <div class="card-body">
                <label class="form-label">{{ $customField->name }}
                    @if($customField->required)
                        <span class="text-danger">*</span>
                    @endif
                </label>

                @if($customField->field_type == 'text')
                    <input type="text" class="form-control" placeholder="{{ $customField->placeholder }}" disabled>
                @elseif($customField->field_type == 'number')
                    <input type="number" class="form-control" placeholder="{{ $customField->placeholder }}" disabled>
                @elseif($customField->field_type == 'email')
                    <input type="email" class="form-control" placeholder="{{ $customField->placeholder }}" disabled>
                @elseif($customField->field_type == 'tel')
                    <input type="tel" class="form-control" placeholder="{{ $customField->placeholder }}" disabled>
                @elseif($customField->field_type == 'textarea')
                    <textarea class="form-control" rows="3" placeholder="{{ $customField->placeholder }}" disabled></textarea>
                @elseif($customField->field_type == 'select' && $customField->options)
                    <select class="form-select" disabled>
                        <option>Seleccionar...</option>
                        @foreach($customField->options as $option)
                            <option>{{ $option }}</option>
                        @endforeach
                    </select>
                @elseif($customField->field_type == 'radio' && $customField->options)
                    @foreach($customField->options as $index => $option)
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="preview_radio" id="radio_{{ $index }}" disabled>
                            <label class="form-check-label" for="radio_{{ $index }}">{{ $option }}</label>
                        </div>
                    @endforeach
                @elseif($customField->field_type == 'checkbox')
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="preview_checkbox" disabled>
                        <label class="form-check-label" for="preview_checkbox">{{ $customField->name }}</label>
                    </div>
                @elseif($customField->field_type == 'file')
                    <input type="file" class="form-control" disabled>
                @elseif($customField->field_type == 'date')
                    <input type="date" class="form-control" disabled>
                @elseif($customField->field_type == 'time')
                    <input type="time" class="form-control" disabled>
                @elseif($customField->field_type == 'datetime')
                    <input type="datetime-local" class="form-control" disabled>
                @endif

                @if($customField->help_text)
                    <div class="form-text">{{ $customField->help_text }}</div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection