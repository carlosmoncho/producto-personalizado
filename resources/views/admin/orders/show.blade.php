@extends('layouts.admin')

@section('title', 'Detalles del Pedido')

@section('content')
<div class="row">
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Información del Pedido</h5>
            </div>
            <div class="card-body">
                <h4>{{ $order->order_number }}</h4>
                
                <div class="mb-3">
                    @php
                        $statusColors = [
                            'pending' => 'warning',
                            'processing' => 'info',
                            'approved' => 'success',
                            'in_production' => 'primary',
                            'shipped' => 'info',
                            'delivered' => 'success',
                            'cancelled' => 'danger'
                        ];
                        $color = $statusColors[$order->status] ?? 'secondary';
                    @endphp
                    <span class="badge bg-{{ $color }} fs-6">{{ $order->status_label }}</span>
                </div>

                <div class="row mb-2">
                    <div class="col-sm-5"><strong>Subtotal:</strong></div>
                    <div class="col-sm-7">€{{ number_format($order->subtotal ?? $order->total_amount, 2) }}</div>
                </div>
                @if($order->tax_amount)
                <div class="row mb-2">
                    <div class="col-sm-5"><strong>IVA ({{ number_format($order->tax_rate ?? 21, 0) }}%):</strong></div>
                    <div class="col-sm-7">€{{ number_format($order->tax_amount, 2) }}</div>
                </div>
                @endif
                <div class="row mb-3">
                    <div class="col-sm-5"><strong>Total:</strong></div>
                    <div class="col-sm-7">
                        <h5 class="text-success mb-0">€{{ number_format($order->total_amount, 2) }}</h5>
                    </div>
                </div>

                <div class="row mb-2">
                    <div class="col-sm-5"><strong>Fecha:</strong></div>
                    <div class="col-sm-7">{{ $order->created_at->format('d/m/Y H:i') }}</div>
                </div>

                @if($order->approved_at)
                    <div class="row mb-2">
                        <div class="col-sm-5"><strong>Aprobado:</strong></div>
                        <div class="col-sm-7">{{ $order->approved_at->format('d/m/Y H:i') }}</div>
                    </div>
                @endif

                @if($order->shipped_at)
                    <div class="row mb-2">
                        <div class="col-sm-5"><strong>Enviado:</strong></div>
                        <div class="col-sm-7">{{ $order->shipped_at->format('d/m/Y H:i') }}</div>
                    </div>
                @endif

                @if($order->delivered_at)
                    <div class="row mb-2">
                        <div class="col-sm-5"><strong>Entregado:</strong></div>
                        <div class="col-sm-7">{{ $order->delivered_at->format('d/m/Y H:i') }}</div>
                    </div>
                @endif

                <div class="mt-4">
                    <h6>Cambiar Estado:</h6>
                    <form method="POST" action="{{ route('admin.orders.update-status', $order) }}">
                        @csrf
                        @method('PATCH')
                        <div class="input-group">
                            <select name="status" class="form-select form-select-sm">
                                <option value="pending" {{ $order->status == 'pending' ? 'selected' : '' }}>Pendiente</option>
                                <option value="processing" {{ $order->status == 'processing' ? 'selected' : '' }}>Procesando</option>
                                <option value="approved" {{ $order->status == 'approved' ? 'selected' : '' }}>Aprobado</option>
                                <option value="in_production" {{ $order->status == 'in_production' ? 'selected' : '' }}>En Producción</option>
                                <option value="shipped" {{ $order->status == 'shipped' ? 'selected' : '' }}>Enviado</option>
                                <option value="delivered" {{ $order->status == 'delivered' ? 'selected' : '' }}>Entregado</option>
                                <option value="cancelled" {{ $order->status == 'cancelled' ? 'selected' : '' }}>Cancelado</option>
                            </select>
                            <button type="submit" class="btn btn-primary btn-sm">
                                <i class="bi bi-check"></i>
                            </button>
                        </div>
                    </form>
                </div>

                <div class="mt-3">
                    <a href="{{ route('admin.orders.edit', $order) }}" class="btn btn-primary btn-sm">
                        <i class="bi bi-pencil me-2"></i>Editar
                    </a>
                    <a href="{{ route('admin.orders.index') }}" class="btn btn-outline-secondary btn-sm">
                        <i class="bi bi-arrow-left me-2"></i>Volver
                    </a>
                </div>
            </div>
        </div>

        <!-- Timeline del pedido -->
        <div class="card mt-3">
            <div class="card-header">
                <h6 class="mb-0">Timeline del Pedido</h6>
            </div>
            <div class="card-body">
                <div class="timeline">
                    <div class="timeline-item">
                        <div class="timeline-marker bg-primary"></div>
                        <div class="timeline-content">
                            <h6 class="timeline-title">Pedido Creado</h6>
                            <p class="timeline-text">{{ $order->created_at->format('d/m/Y H:i') }}</p>
                        </div>
                    </div>

                    @if($order->approved_at)
                        <div class="timeline-item">
                            <div class="timeline-marker bg-success"></div>
                            <div class="timeline-content">
                                <h6 class="timeline-title">Pedido Aprobado</h6>
                                <p class="timeline-text">{{ $order->approved_at->format('d/m/Y H:i') }}</p>
                            </div>
                        </div>
                    @endif

                    @if($order->shipped_at)
                        <div class="timeline-item">
                            <div class="timeline-marker bg-info"></div>
                            <div class="timeline-content">
                                <h6 class="timeline-title">Pedido Enviado</h6>
                                <p class="timeline-text">{{ $order->shipped_at->format('d/m/Y H:i') }}</p>
                            </div>
                        </div>
                    @endif

                    @if($order->delivered_at)
                        <div class="timeline-item">
                            <div class="timeline-marker bg-success"></div>
                            <div class="timeline-content">
                                <h6 class="timeline-title">Pedido Entregado</h6>
                                <p class="timeline-text">{{ $order->delivered_at->format('d/m/Y H:i') }}</p>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Widget de Mensajes -->
        @include('admin.orders.messages._widget')
    </div>

    <div class="col-md-8">
        <!-- Información del cliente -->
        <div class="card mb-3">
            <div class="card-header">
                <h5 class="mb-0">Información del Cliente</h5>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-12">
                        <h6 class="text-primary mb-2"><i class="bi bi-person-fill me-2"></i>Datos de Contacto</h6>
                        <strong>Nombre:</strong>
                        @if($order->customer_id && $order->customer)
                            <a href="{{ route('admin.customers.show', $order->customer) }}" class="text-decoration-none">
                                {{ $order->customer_name }}
                                <i class="bi bi-external-link ms-1"></i>
                            </a>
                        @else
                            {{ $order->customer_name }}
                        @endif
                        <br>
                        <strong>Email:</strong> {{ $order->customer_email }}<br>
                        <strong>Teléfono:</strong> {{ $order->customer_phone }}
                    </div>
                </div>

                <div class="row">
                    @if($order->shipping_address)
                        <div class="col-md-6 mb-3">
                            <h6 class="text-primary mb-2"><i class="bi bi-truck me-2"></i>Dirección de Envío</h6>
                            <div class="border rounded p-3 bg-light">
                                {{ $order->shipping_address }}
                            </div>
                        </div>
                    @endif

                    @if($order->billing_address)
                        <div class="col-md-6 mb-3">
                            <h6 class="text-primary mb-2"><i class="bi bi-receipt me-2"></i>Dirección de Facturación</h6>
                            <div class="border rounded p-3 bg-light">
                                {{ $order->billing_address }}
                            </div>
                        </div>
                    @endif

                    @if(!$order->shipping_address && !$order->billing_address && $order->customer_address)
                        <div class="col-md-12 mb-3">
                            <h6 class="text-primary mb-2"><i class="bi bi-geo-alt-fill me-2"></i>Dirección</h6>
                            <div class="border rounded p-3 bg-light">
                                {{ $order->customer_address }}
                            </div>
                        </div>
                    @endif
                </div>

                @if($order->company_name || $order->nif_cif)
                    <div class="row mt-2">
                        <div class="col-md-12">
                            <h6 class="text-primary mb-2"><i class="bi bi-building me-2"></i>Datos de Facturación</h6>
                            <div class="border rounded p-3 bg-warning bg-opacity-10">
                                @if($order->company_name)
                                    <div class="mb-1">
                                        <strong>Empresa/Autónomo:</strong> {{ $order->company_name }}
                                    </div>
                                @endif
                                @if($order->nif_cif)
                                    <div>
                                        <strong>NIF/CIF:</strong> <span class="badge bg-dark">{{ $order->nif_cif }}</span>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                @endif

                @if($order->customer_id && $order->customer)
                    <div class="mt-3">
                        <a href="{{ route('admin.customers.show', $order->customer) }}" class="btn btn-outline-primary btn-sm">
                            <i class="bi bi-person me-1"></i>Ver Ficha Cliente
                        </a>
                    </div>
                @endif

                @if($order->notes)
                    <div class="mt-3">
                        <h6 class="text-primary"><i class="bi bi-sticky me-2"></i>Notas del Pedido</h6>
                        <div class="alert alert-info mb-0" style="white-space: pre-line;">{{ $order->notes }}</div>
                    </div>
                @endif
            </div>
        </div>

        <!-- Items del pedido -->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Productos del Pedido ({{ $order->items->count() }})</h5>
            </div>
            <div class="card-body">
                @if($order->items->count() > 0)
                    @foreach($order->items as $item)
                        <div class="border rounded p-3 mb-3">
                            <div class="row">
                                <div class="col-md-2">
                                    @if($item->product->getFirstImageUrl())
                                        <img src="{{ $item->product->getFirstImageUrl() }}" 
                                             alt="{{ $item->product->name }}" 
                                             class="img-fluid rounded">
                                    @else
                                        <div class="bg-light rounded d-flex align-items-center justify-content-center" 
                                             style="width: 100%; height: 80px;">
                                            <i class="bi bi-image text-muted"></i>
                                        </div>
                                    @endif
                                </div>
                                <div class="col-md-6">
                                    <h6>
                                        <a href="{{ route('admin.products.show', $item->product) }}" class="text-decoration-none">
                                            {{ $item->product->name }}
                                            <i class="bi bi-external-link ms-1"></i>
                                        </a>
                                    </h6>
                                    <p class="text-muted mb-2">SKU: {{ $item->product->sku }}</p>
                                    <p class="mb-2"><strong><i class="bi bi-box-seam me-1"></i>Cantidad:</strong> {{ number_format($item->quantity) }} uds</p>

                                    @if($item->configuration && is_array($item->configuration))
                                        @php
                                            // Nombres legibles para atributos
                                            $attributeNames = [
                                                'material' => 'Material',
                                                'color' => 'Color del Producto',
                                                'size' => 'Tamaño',
                                                'system' => 'Sistema de Impresión',
                                                'quantity' => 'Cantidad',
                                                'ink' => 'Tintas',
                                                'ink_color' => 'Color de Tinta',
                                                'cliche' => 'Cliché',
                                                'weight' => 'Gramaje',
                                            ];

                                            // Separar configuración en grupos
                                            $generalConfig = [];
                                            $inkColorAttrs = collect([]);
                                            $customInks = $item->custom_inks ?? [];

                                            foreach($item->configuration as $key => $attributeId) {
                                                if (is_array($attributeId)) {
                                                    $attributes = \App\Models\ProductAttribute::whereIn('id', $attributeId)->get();
                                                    if ($key === 'ink_color') {
                                                        $inkColorAttrs = $attributes;
                                                    } else {
                                                        $generalConfig[$key] = $attributes->pluck('name')->implode(', ');
                                                    }
                                                } else {
                                                    $attribute = \App\Models\ProductAttribute::find($attributeId);
                                                    if ($attribute) {
                                                        if ($key === 'ink_color') {
                                                            $inkColorAttrs = collect([$attribute]);
                                                        } elseif ($key === 'color') {
                                                            $generalConfig[$key] = [
                                                                'name' => $attribute->name,
                                                                'hex' => $attribute->hex_code
                                                            ];
                                                        } else {
                                                            $generalConfig[$key] = $attribute->name;
                                                        }
                                                    }
                                                }
                                            }

                                            $hasInkColors = $inkColorAttrs->count() > 0 || count($customInks) > 0;
                                        @endphp

                                        {{-- Configuración General --}}
                                        <div class="mb-2">
                                            <strong><i class="bi bi-sliders me-1"></i>Configuración:</strong>
                                            <div class="mt-1 p-2 bg-light rounded border">
                                                <div class="row row-cols-2 g-2">
                                                    @foreach($generalConfig as $key => $value)
                                                        @if($key !== 'quantity' || !isset($generalConfig['ink']))
                                                            <div class="col">
                                                                <small class="text-muted d-block">{{ $attributeNames[$key] ?? ucfirst(str_replace('_', ' ', $key)) }}</small>
                                                                @if(is_array($value) && isset($value['hex']))
                                                                    <div class="d-flex align-items-center gap-1">
                                                                        <span class="d-inline-block rounded-circle border" style="width: 14px; height: 14px; background-color: {{ $value['hex'] }};"></span>
                                                                        <span class="fw-medium">{{ $value['name'] }}</span>
                                                                    </div>
                                                                @else
                                                                    <span class="fw-medium">{{ $value }}</span>
                                                                @endif
                                                            </div>
                                                        @endif
                                                    @endforeach
                                                </div>
                                            </div>
                                        </div>

                                        {{-- Colores de Tinta (predefinidos + personalizados) --}}
                                        @if($hasInkColors)
                                            <div class="mb-2">
                                                <strong><i class="bi bi-palette-fill me-1"></i>Colores de Tinta:</strong>
                                                <div class="mt-1 p-2 bg-light rounded border">
                                                    <div class="d-flex flex-wrap gap-2">
                                                        {{-- Colores predefinidos --}}
                                                        @foreach($inkColorAttrs as $inkAttr)
                                                            <div class="d-flex align-items-center gap-1 px-2 py-1 bg-white rounded border">
                                                                @if($inkAttr->hex_code)
                                                                    <span class="d-inline-block rounded-circle border border-secondary" style="width: 20px; height: 20px; background-color: {{ $inkAttr->hex_code }};"></span>
                                                                @endif
                                                                <span class="fw-medium">{{ $inkAttr->name }}</span>
                                                                <span class="badge bg-secondary" style="font-size: 0.65rem;">Catálogo</span>
                                                            </div>
                                                        @endforeach

                                                        {{-- Colores personalizados --}}
                                                        @foreach($customInks as $ink)
                                                            <div class="d-flex align-items-center gap-1 px-2 py-1 bg-white rounded border border-warning">
                                                                <span class="d-inline-block rounded-circle border border-dark" style="width: 20px; height: 20px; background-color: {{ $ink['hex'] }};"></span>
                                                                <span class="fw-medium font-monospace" style="font-size: 0.8rem;">{{ strtoupper($ink['hex']) }}</span>
                                                                @if(!empty($ink['name']))
                                                                    <span class="text-muted">({{ $ink['name'] }})</span>
                                                                @endif
                                                                @if(!empty($ink['pantone']))
                                                                    <span class="badge bg-info" style="font-size: 0.65rem;">{{ $ink['pantone'] }}</span>
                                                                @endif
                                                                <span class="badge bg-warning text-dark" style="font-size: 0.65rem;">Personalizado</span>
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                </div>
                                            </div>
                                        @endif
                                    @else
                                        @if($item->selected_size)
                                            <p class="mb-1"><strong>Tamaño:</strong> {{ $item->selected_size }}</p>
                                        @endif
                                        @if($item->selected_color)
                                            <p class="mb-1"><strong>Color:</strong> {{ $item->selected_color }}</p>
                                        @endif

                                        {{-- Tintas personalizadas (fallback para items sin configuración nueva) --}}
                                        @if($item->custom_inks && count($item->custom_inks) > 0)
                                            <div class="mb-2">
                                                <strong><i class="bi bi-palette me-1"></i>Tintas Personalizadas:</strong>
                                                <div class="mt-1 d-flex flex-wrap gap-2">
                                                    @foreach($item->custom_inks as $ink)
                                                        <div class="d-flex align-items-center gap-1 px-2 py-1 bg-light rounded border">
                                                            <span class="d-inline-block rounded-circle border" style="width: 18px; height: 18px; background-color: {{ $ink['hex'] }};"></span>
                                                            <small class="fw-medium">{{ strtoupper($ink['hex']) }}</small>
                                                            @if(!empty($ink['name']))
                                                                <small class="text-muted">({{ $ink['name'] }})</small>
                                                            @endif
                                                        </div>
                                                    @endforeach
                                                </div>
                                            </div>
                                        @elseif($item->has_custom_ink && $item->custom_ink_hex)
                                            <div class="mb-2">
                                                <strong><i class="bi bi-palette me-1"></i>Tinta Personalizada:</strong>
                                                <div class="mt-1 d-flex align-items-center gap-2">
                                                    <span class="d-inline-block rounded-circle border" style="width: 18px; height: 18px; background-color: {{ $item->custom_ink_hex }};"></span>
                                                    <small class="fw-medium">{{ strtoupper($item->custom_ink_hex) }}</small>
                                                    @if($item->custom_ink_name)
                                                        <small class="text-muted">({{ $item->custom_ink_name }})</small>
                                                    @endif
                                                </div>
                                            </div>
                                        @endif
                                    @endif

                                    <div class="mt-2">
                                        <a href="{{ route('admin.products.show', $item->product) }}" class="btn btn-outline-info btn-sm">
                                            <i class="bi bi-box me-1"></i>Ver Producto
                                        </a>
                                    </div>

                                    @if($item->design_comments)
                                        <p class="mb-1 mt-2"><strong>Comentarios:</strong> {{ $item->design_comments }}</p>
                                    @endif
                                </div>
                                <div class="col-md-2 text-center">
                                    @if($item->getDesignImageUrl())
                                        <div class="mb-2">
                                            <img src="{{ $item->getDesignImageUrl() }}"
                                                 alt="Diseño"
                                                 class="img-fluid rounded border"
                                                 style="cursor: pointer; max-height: 120px;"
                                                 data-bs-toggle="modal"
                                                 data-bs-target="#designModal{{ $item->id }}">
                                            <small class="d-block mt-1"><i class="bi bi-image-fill text-primary"></i> Diseño</small>
                                        </div>
                                        <a href="{{ $item->getDesignImageUrl() }}"
                                           download="diseño-{{ $item->product->sku }}.png"
                                           class="btn btn-sm btn-outline-primary">
                                            <i class="bi bi-download me-1"></i>Descargar
                                        </a>

                                        <!-- Modal para ver diseño en grande -->
                                        <div class="modal fade" id="designModal{{ $item->id }}" tabindex="-1">
                                            <div class="modal-dialog modal-lg modal-dialog-centered">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title">Diseño - {{ $item->product->name }}</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                    </div>
                                                    <div class="modal-body text-center">
                                                        <img src="{{ $item->getDesignImageUrl() }}"
                                                             alt="Diseño"
                                                             class="img-fluid"
                                                             style="max-height: 70vh;">
                                                    </div>
                                                    <div class="modal-footer">
                                                        <a href="{{ $item->getDesignImageUrl() }}"
                                                           download="diseño-{{ $item->product->sku }}.png"
                                                           class="btn btn-primary">
                                                            <i class="bi bi-download me-2"></i>Descargar Diseño
                                                        </a>
                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @else
                                        <div class="bg-light rounded d-flex align-items-center justify-content-center"
                                             style="width: 100%; height: 80px;">
                                            <i class="bi bi-image text-muted"></i>
                                        </div>
                                        <small class="d-block mt-1 text-muted">Sin diseño</small>
                                    @endif

                                    @if($item->preview_3d)
                                        <div class="mt-3 mb-2">
                                            <img src="{{ $item->preview_3d }}"
                                                 alt="Preview 3D"
                                                 class="img-fluid rounded border"
                                                 style="cursor: pointer; max-height: 120px;"
                                                 data-bs-toggle="modal"
                                                 data-bs-target="#preview3dModal{{ $item->id }}">
                                            <small class="d-block mt-1"><i class="bi bi-box text-success"></i> Vista 3D</small>
                                        </div>
                                        <a href="{{ $item->preview_3d }}"
                                           download="preview-3d-{{ $item->product->sku }}.png"
                                           class="btn btn-sm btn-outline-success mb-1">
                                            <i class="bi bi-download me-1"></i>Descargar
                                        </a>

                                        @if($item->model_3d_config)
                                            <button type="button" class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#viewer3dModal{{ $item->id }}">
                                                <i class="bi bi-badge-3d me-1"></i>Ver 3D
                                            </button>
                                        @endif

                                        <!-- Modal para ver preview 3D en grande -->
                                        <div class="modal fade" id="preview3dModal{{ $item->id }}" tabindex="-1">
                                            <div class="modal-dialog modal-lg modal-dialog-centered">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title"><i class="bi bi-box me-2"></i>Vista 3D Personalizada - {{ $item->product->name }}</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                    </div>
                                                    <div class="modal-body text-center">
                                                        <img src="{{ $item->preview_3d }}"
                                                             alt="Preview 3D"
                                                             class="img-fluid"
                                                             style="max-height: 70vh;">
                                                    </div>
                                                    <div class="modal-footer">
                                                        <a href="{{ $item->preview_3d }}"
                                                           download="preview-3d-{{ $item->product->sku }}.png"
                                                           class="btn btn-success">
                                                            <i class="bi bi-download me-2"></i>Descargar Preview 3D
                                                        </a>
                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        @if($item->model_3d_config)
                                            <!-- Modal para visor 3D interactivo -->
                                            <div class="modal fade" id="viewer3dModal{{ $item->id }}" tabindex="-1">
                                                <div class="modal-dialog modal-xl modal-dialog-centered">
                                                    <div class="modal-content">
                                                        <div class="modal-header bg-dark text-white">
                                                            <h5 class="modal-title">
                                                                <i class="bi bi-badge-3d me-2"></i>Visor 3D Interactivo - {{ $item->product->name }}
                                                            </h5>
                                                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                                        </div>
                                                        <div class="modal-body p-0">
                                                            @include('admin.partials.three-model-viewer', [
                                                                'modelUrl' => $item->model_3d_config['model_url'] ?? null,
                                                                'colorHex' => $item->model_3d_config['color_hex'] ?? null,
                                                                'logoUrl' => $item->design_image ?? null,
                                                                'logoTransform' => $item->model_3d_config['logo_transform'] ?? null,
                                                                'viewerId' => 'viewer-item-' . $item->id
                                                            ])
                                                        </div>
                                                        <div class="modal-footer bg-light">
                                                            <div class="me-auto text-muted small">
                                                                <i class="bi bi-mouse me-1"></i>Arrastra para rotar |
                                                                <i class="bi bi-zoom-in me-1"></i>Scroll para zoom
                                                            </div>
                                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        @endif
                                    @endif
                                </div>
                                <div class="col-md-2 text-end">
                                    <h6>€{{ number_format($item->unit_price, 2) }}</h6>
                                    <small class="text-muted">por unidad</small>
                                    @if($item->extras && $item->extras > 0)
                                    <div class="mt-1">
                                        <small class="text-info">+€{{ number_format($item->extras, 2) }} extras</small>
                                    </div>
                                    @endif
                                    <hr>
                                    <h5>€{{ number_format($item->total_price, 2) }}</h5>
                                    <small class="text-muted">total</small>
                                </div>
                            </div>

                            @if($item->custom_field_values && count($item->custom_field_values) > 0)
                                <div class="mt-3">
                                    <h6>Campos Personalizados:</h6>
                                    <div class="row">
                                        @foreach($item->custom_field_values as $fieldKey => $fieldValue)
                                            <div class="col-md-6">
                                                <strong>{{ ucfirst(str_replace('_', ' ', $fieldKey)) }}:</strong> {{ $fieldValue }}
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        </div>
                    @endforeach

                    <!-- Total del pedido -->
                    <div class="row">
                        <div class="col-md-8"></div>
                        <div class="col-md-4">
                            <div class="card bg-light">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between mb-2">
                                        <span>Subtotal:</span>
                                        <span>€{{ number_format($order->subtotal ?? $order->total_amount, 2) }}</span>
                                    </div>
                                    @if($order->tax_amount)
                                    <div class="d-flex justify-content-between mb-2">
                                        <span>IVA ({{ number_format($order->tax_rate ?? 21, 0) }}%):</span>
                                        <span>€{{ number_format($order->tax_amount, 2) }}</span>
                                    </div>
                                    @endif
                                    <hr class="my-2">
                                    <div class="d-flex justify-content-between">
                                        <strong>Total:</strong>
                                        <strong class="text-success fs-5">€{{ number_format($order->total_amount, 2) }}</strong>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @else
                    <div class="text-center py-4">
                        <i class="bi bi-cart display-4 text-muted"></i>
                        <h6 class="mt-2">No hay productos en este pedido</h6>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<style>
.timeline {
    position: relative;
    padding-left: 30px;
}

.timeline-item {
    position: relative;
    margin-bottom: 20px;
}

.timeline-marker {
    position: absolute;
    left: -35px;
    top: 5px;
    width: 12px;
    height: 12px;
    border-radius: 50%;
}

.timeline-item::before {
    content: '';
    position: absolute;
    left: -31px;
    top: 18px;
    width: 4px;
    height: calc(100% + 20px);
    background-color: #dee2e6;
}

.timeline-item:last-child::before {
    display: none;
}

.timeline-title {
    font-size: 0.9rem;
    margin-bottom: 0.25rem;
}

.timeline-text {
    font-size: 0.8rem;
    color: #6c757d;
    margin-bottom: 0;
}
</style>
@endsection
