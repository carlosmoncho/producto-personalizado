@extends('layouts.admin')

@section('title', 'Configurador de Productos')

@push('styles')
<style>
    :root {
        --configurator-primary: #007bff;
        --configurator-success: #28a745;
        --configurator-warning: #ffc107;
        --configurator-danger: #dc3545;
        --configurator-disabled: #6c757d;
    }

    .configurator-container {
        max-width: 1400px;
        margin: 0 auto;
    }

    .configurator-step {
        transition: all 0.3s ease;
        border: 2px solid #e9ecef;
        border-radius: 12px;
        background: white;
        margin-bottom: 2rem;
    }

    .configurator-step.active {
        border-color: var(--configurator-primary);
        box-shadow: 0 4px 12px rgba(0, 123, 255, 0.15);
    }

    .configurator-step.completed {
        border-color: var(--configurator-success);
        background: linear-gradient(135deg, #f8f9ff 0%, #e8f5e8 100%);
    }

    .configurator-step.disabled {
        opacity: 0.6;
        pointer-events: none;
        background: #f8f9fa;
    }

    .step-header {
        padding: 1.5rem;
        border-bottom: 1px solid #e9ecef;
        background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
        border-radius: 12px 12px 0 0;
    }

    .step-number {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: bold;
        color: white;
        background: var(--configurator-disabled);
        margin-right: 1rem;
        transition: all 0.3s ease;
    }

    .configurator-step.active .step-number {
        background: var(--configurator-primary);
        transform: scale(1.1);
    }

    .configurator-step.completed .step-number {
        background: var(--configurator-success);
    }

    .step-content {
        padding: 2rem;
    }

    .attribute-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
        gap: 1rem;
        margin-top: 1rem;
    }

    .attribute-card {
        border: 2px solid #e9ecef;
        border-radius: 12px;
        padding: 1.5rem;
        text-align: center;
        cursor: pointer;
        transition: all 0.3s ease;
        position: relative;
        background: white;
    }

    .attribute-card:hover {
        border-color: var(--configurator-primary);
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0, 123, 255, 0.15);
    }

    .attribute-card.selected {
        border-color: var(--configurator-primary);
        background: linear-gradient(135deg, #e3f2fd 0%, #f0f8ff 100%);
        box-shadow: 0 4px 12px rgba(0, 123, 255, 0.2);
    }

    .attribute-card.disabled {
        opacity: 0.4;
        cursor: not-allowed;
        background: #f8f9fa;
    }

    .attribute-card.not-recommended {
        border-color: var(--configurator-warning);
        background: #fff8e1;
    }

    .color-preview {
        width: 60px;
        height: 60px;
        border-radius: 50%;
        margin: 0 auto 1rem;
        border: 3px solid #fff;
        box-shadow: 0 2px 8px rgba(0,0,0,0.15);
    }

    .ink-preview {
        width: 40px;
        height: 40px;
        border-radius: 8px;
        margin: 0 auto 0.5rem;
        border: 2px solid #fff;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }

    .price-display {
        position: fixed;
        top: 100px;
        right: 30px;
        background: white;
        border-radius: 12px;
        padding: 1.5rem;
        box-shadow: 0 8px 25px rgba(0,0,0,0.15);
        border: 2px solid var(--configurator-primary);
        min-width: 300px;
        z-index: 1000;
    }

    .price-breakdown {
        margin-top: 1rem;
        font-size: 0.9rem;
        color: #6c757d;
    }

    .certification-badges {
        margin-top: 1rem;
    }

    .certification-badge {
        display: inline-block;
        background: var(--configurator-success);
        color: white;
        padding: 0.25rem 0.75rem;
        border-radius: 20px;
        font-size: 0.8rem;
        margin: 0.25rem;
    }

    .progress-bar {
        height: 8px;
        background: #e9ecef;
        border-radius: 4px;
        overflow: hidden;
        margin: 1rem 0;
    }

    .progress-fill {
        height: 100%;
        background: linear-gradient(90deg, var(--configurator-primary), var(--configurator-success));
        width: 0%;
        transition: width 0.5s ease;
    }

    .validation-message {
        background: #f8d7da;
        color: #721c24;
        padding: 1rem;
        border-radius: 8px;
        margin: 1rem 0;
        border-left: 4px solid var(--configurator-danger);
    }

    .success-message {
        background: #d4edda;
        color: #155724;
        padding: 1rem;
        border-radius: 8px;
        margin: 1rem 0;
        border-left: 4px solid var(--configurator-success);
    }

    .quantity-selector {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 1rem;
        margin-top: 1rem;
    }

    .quantity-card {
        border: 2px solid #e9ecef;
        border-radius: 12px;
        padding: 1.5rem;
        text-align: center;
        cursor: pointer;
        transition: all 0.3s ease;
        position: relative;
    }

    .quantity-card.recommended {
        border-color: var(--configurator-success);
        background: linear-gradient(135deg, #f0fff4 0%, #e8f5e8 100%);
    }

    .quantity-card.best-value {
        border-color: var(--configurator-warning);
        background: linear-gradient(135deg, #fffbf0 0%, #fff8e1 100%);
    }

    .quantity-card .badge {
        position: absolute;
        top: -8px;
        right: -8px;
        background: var(--configurator-success);
        color: white;
        padding: 0.25rem 0.5rem;
        border-radius: 12px;
        font-size: 0.7rem;
    }

    .ink-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(120px, 1fr));
        gap: 1rem;
        margin-top: 1rem;
    }

    .ink-card {
        border: 2px solid #e9ecef;
        border-radius: 12px;
        padding: 1rem;
        text-align: center;
        cursor: pointer;
        transition: all 0.3s ease;
        position: relative;
    }

    .ink-card.recommended {
        border-color: var(--configurator-success);
        background: linear-gradient(135deg, #f0fff4 0%, #e8f5e8 100%);
    }

    .loading-spinner {
        display: inline-block;
        width: 20px;
        height: 20px;
        border: 2px solid #f3f3f3;
        border-top: 2px solid var(--configurator-primary);
        border-radius: 50%;
        animation: spin 1s linear infinite;
    }

    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }

    .tooltip-custom {
        position: relative;
        cursor: help;
    }

    .tooltip-custom::after {
        content: attr(data-tooltip);
        position: absolute;
        bottom: 100%;
        left: 50%;
        transform: translateX(-50%);
        background: rgba(0,0,0,0.9);
        color: white;
        padding: 0.5rem;
        border-radius: 4px;
        font-size: 0.8rem;
        white-space: nowrap;
        opacity: 0;
        pointer-events: none;
        transition: opacity 0.3s;
        z-index: 1000;
    }

    .tooltip-custom:hover::after {
        opacity: 1;
    }

    @media (max-width: 768px) {
        .price-display {
            position: static;
            margin: 2rem 0;
            width: 100%;
        }

        .attribute-grid {
            grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
        }

        .step-content {
            padding: 1rem;
        }
    }
</style>
@endpush

@section('content')
<div class="configurator-container">
    <!-- Header -->
    <div class="text-center mb-4">
        <h1 class="display-4 mb-2">Configurador de Productos</h1>
        <p class="lead text-muted">{{ $product->name }}</p>
        <div class="progress-bar">
            <div class="progress-fill" id="progressBar"></div>
        </div>
    </div>

    <!-- Mensajes de estado -->
    <div id="messageContainer"></div>

    <!-- Precio flotante -->
    <div class="price-display" id="priceDisplay" style="display: none;">
        <h5 class="mb-3">Resumen del Pedido</h5>
        <div class="d-flex justify-content-between align-items-center mb-2">
            <span>Precio unitario:</span>
            <strong id="unitPrice">€0.00</strong>
        </div>
        <div class="d-flex justify-content-between align-items-center mb-2">
            <span>Cantidad:</span>
            <strong id="quantityDisplay">0</strong>
        </div>
        <hr>
        <div class="d-flex justify-content-between align-items-center mb-3">
            <span class="h6">Total:</span>
            <strong class="h5 text-primary" id="totalPrice">€0.00</strong>
        </div>
        <div class="price-breakdown" id="priceBreakdown"></div>
        <div class="certification-badges" id="certificationBadges"></div>
        <button class="btn btn-success w-100 mt-3" id="addToCartBtn" style="display: none;">
            <i class="bi bi-cart-plus me-2"></i>Agregar al Carrito
        </button>
    </div>

    <!-- Paso 1: Selección de Color -->
    <div class="configurator-step active" id="step-color" data-step="color">
        <div class="step-header">
            <div class="d-flex align-items-center">
                <div class="step-number">1</div>
                <div>
                    <h4 class="mb-1">Seleccione el Color</h4>
                    <p class="mb-0 text-muted">El color determina las opciones disponibles en los siguientes pasos</p>
                </div>
            </div>
        </div>
        <div class="step-content">
            <div class="attribute-grid" id="colorsGrid">
                @foreach($availableColors as $color)
                    <div class="attribute-card" 
                         data-attribute-id="{{ $color->id }}" 
                         data-attribute-type="color"
                         data-hex="{{ $color->hex_code }}">
                        <div class="color-preview" style="background-color: {{ $color->hex_code }};"></div>
                        <h6 class="mb-1">{{ $color->name }}</h6>
                        <small class="text-muted">{{ $color->value }}</small>
                        @if($color->price_modifier != 0)
                            <div class="badge bg-info mt-2">
                                {{ $color->price_modifier > 0 ? '+' : '' }}€{{ number_format($color->price_modifier, 3) }}
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- Paso 2: Selección de Material -->
    <div class="configurator-step disabled" id="step-material" data-step="material">
        <div class="step-header">
            <div class="d-flex align-items-center">
                <div class="step-number">2</div>
                <div>
                    <h4 class="mb-1">Seleccione el Material</h4>
                    <p class="mb-0 text-muted">Los materiales disponibles dependen del color seleccionado</p>
                </div>
            </div>
        </div>
        <div class="step-content">
            <div class="attribute-grid" id="materialsGrid">
                <!-- Se carga dinámicamente -->
            </div>
        </div>
    </div>

    <!-- Paso 3: Selección de Tamaño -->
    <div class="configurator-step disabled" id="step-size" data-step="size">
        <div class="step-header">
            <div class="d-flex align-items-center">
                <div class="step-number">3</div>
                <div>
                    <h4 class="mb-1">Seleccione el Tamaño</h4>
                    <p class="mb-0 text-muted">Diferentes tamaños pueden tener precios distintos</p>
                </div>
            </div>
        </div>
        <div class="step-content">
            <div class="attribute-grid" id="sizesGrid">
                <!-- Se carga dinámicamente -->
            </div>
        </div>
    </div>

    <!-- Paso 4: Selección de Cantidad -->
    <div class="configurator-step disabled" id="step-quantity" data-step="quantity">
        <div class="step-header">
            <div class="d-flex align-items-center">
                <div class="step-number">4</div>
                <div>
                    <h4 class="mb-1">Seleccione la Cantidad</h4>
                    <p class="mb-0 text-muted">Mayor cantidad, mejor precio unitario</p>
                </div>
            </div>
        </div>
        <div class="step-content">
            <div class="quantity-selector" id="quantitiesGrid">
                <!-- Se carga dinámicamente -->
            </div>
        </div>
    </div>

    <!-- Paso 5: Personalización (Tintas) -->
    <div class="configurator-step disabled" id="step-personalization" data-step="personalization">
        <div class="step-header">
            <div class="d-flex align-items-center">
                <div class="step-number">5</div>
                <div>
                    <h4 class="mb-1">Personalización</h4>
                    <p class="mb-0 text-muted">Seleccione las tintas de impresión</p>
                </div>
            </div>
        </div>
        <div class="step-content">
            <!-- Número de colores -->
            <div class="mb-4">
                <label class="form-label">Número de colores de impresión:</label>
                <div class="btn-group" role="group" id="colorCountSelector">
                    <input type="radio" class="btn-check" name="colorCount" id="colorCount1" value="1">
                    <label class="btn btn-outline-primary" for="colorCount1">1 Color</label>
                    <input type="radio" class="btn-check" name="colorCount" id="colorCount2" value="2">
                    <label class="btn btn-outline-primary" for="colorCount2">2 Colores</label>
                    <input type="radio" class="btn-check" name="colorCount" id="colorCount3" value="3">
                    <label class="btn btn-outline-primary" for="colorCount3">3 Colores</label>
                </div>
            </div>

            <!-- Selector de tintas -->
            <div id="inkSelectorContainer" style="display: none;">
                <label class="form-label">Seleccione las tintas de impresión:</label>
                <div class="ink-grid" id="inksGrid">
                    <!-- Se carga dinámicamente -->
                </div>
                <div class="mt-3">
                    <small class="text-muted">
                        <i class="bi bi-info-circle me-1"></i>
                        Las tintas recomendadas están marcadas con borde verde para mejor contraste.
                    </small>
                </div>
            </div>
        </div>
    </div>

    <!-- Paso 6: Resumen y Finalizar -->
    <div class="configurator-step disabled" id="step-summary" data-step="summary">
        <div class="step-header">
            <div class="d-flex align-items-center">
                <div class="step-number">✓</div>
                <div>
                    <h4 class="mb-1">Resumen de la Configuración</h4>
                    <p class="mb-0 text-muted">Revise su configuración antes de finalizar</p>
                </div>
            </div>
        </div>
        <div class="step-content">
            <div class="row">
                <div class="col-md-8">
                    <div id="configurationSummary">
                        <!-- Se carga dinámicamente -->
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-header">
                            <h6 class="mb-0">Detalles del Pedido</h6>
                        </div>
                        <div class="card-body" id="orderDetails">
                            <!-- Se carga dinámicamente -->
                        </div>
                    </div>
                </div>
            </div>
            <div class="text-center mt-4">
                <button class="btn btn-primary btn-lg" id="finalizeOrderBtn" disabled>
                    <i class="bi bi-check-circle me-2"></i>Finalizar Configuración
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal de carga -->
<div class="modal fade" id="loadingModal" data-bs-backdrop="static" tabindex="-1">
    <div class="modal-dialog modal-sm modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-body text-center p-4">
                <div class="loading-spinner mb-3"></div>
                <p class="mb-0">Actualizando configuración...</p>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
// Variables globales del configurador
window.configuratorData = {
    product: @json($product),
    configuration: @json($configuration),
    currentSelection: @json($configuration->attributes_base ?? []),
    currentPersonalization: @json($configuration->personalization ?? []),
    currentStep: 'color',
    selectedInks: [],
    requiredInkCount: 0,
    apiEndpoints: {
        attributes: '{{ route("admin.api.configurator.attributes") }}',
        recommendedInks: '{{ route("admin.api.configurator.inks.recommended") }}',
        calculatePrice: '{{ route("admin.api.configurator.price.calculate") }}',
        updateConfiguration: '{{ route("admin.api.configurator.configuration.update") }}',
        validateConfiguration: '{{ route("admin.api.configurator.configuration.validate") }}'
    },
    csrfToken: '{{ csrf_token() }}'
};
</script>
<script src="{{ asset('js/configurator.js') }}"></script>
@endpush