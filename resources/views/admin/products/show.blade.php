@extends('layouts.admin')

@section('title', 'Detalles del Producto')

@section('content')
<div class="row">
    <div class="col-md-5">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Información del Producto</h5>
            </div>
            <div class="card-body">
                <!-- Imágenes del producto -->
                @if($product->getImagesUrls())
                    <div id="productCarousel" class="carousel slide mb-3" data-bs-ride="carousel">
                        <div class="carousel-inner">
                            @foreach($product->getImagesUrls() as $index => $imageUrl)
                                <div class="carousel-item {{ $index == 0 ? 'active' : '' }}">
                                    <img src="{{ $imageUrl }}" class="d-block w-100 rounded" alt="{{ $product->name }}" 
                                         style="height: 300px; object-fit: cover;">
                                </div>
                            @endforeach
                        </div>
                        @if(count($product->getImagesUrls()) > 1)
                            <button class="carousel-control-prev" type="button" data-bs-target="#productCarousel" data-bs-slide="prev">
                                <span class="carousel-control-prev-icon"></span>
                            </button>
                            <button class="carousel-control-next" type="button" data-bs-target="#productCarousel" data-bs-slide="next">
                                <span class="carousel-control-next-icon"></span>
                            </button>
                        @endif
                    </div>
                @else
                    <div class="bg-light rounded d-flex align-items-center justify-content-center mb-3" 
                         style="width: 100%; height: 300px;">
                        <i class="bi bi-image display-4 text-muted"></i>
                    </div>
                @endif

                <h4>{{ $product->name }}</h4>
                <p class="text-muted">{{ $product->description }}</p>
                
                <div class="row text-center mb-3">
                    <div class="col-6">
                        <h6 class="text-primary">{{ $product->category->name }}</h6>
                        <small>Categoría</small>
                    </div>
                    <div class="col-6">
                        <h6 class="text-success">{{ $product->subcategory->name }}</h6>
                        <small>Subcategoría</small>
                    </div>
                </div>

                <div class="mb-3">
                    @if($product->active)
                        <span class="badge bg-success">Activo</span>
                    @else
                        <span class="badge bg-secondary">Inactivo</span>
                    @endif
                </div>

                <div class="d-flex gap-2">
                    <a href="{{ route('admin.products.edit', $product) }}" class="btn btn-primary">
                        <i class="bi bi-pencil me-2"></i>Editar
                    </a>
                    <a href="{{ route('admin.products.index') }}" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left me-2"></i>Volver
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-7">
        <!-- Especificaciones -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Especificaciones</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <strong>SKU:</strong> <code>{{ $product->sku }}</code><br>
                        <strong>Color:</strong> <span class="badge bg-info">{{ $product->color }}</span><br>
                        <strong>Material:</strong> {{ $product->material }}<br>
                        <strong>Sistema de Impresión:</strong> {{ $product->printing_system }}
                    </div>
                    <div class="col-md-6">
                        <strong>Número de Caras:</strong> {{ $product->face_count }}<br>
                        <strong>Colores de Impresión:</strong> {{ $product->print_colors_count }}<br>
                        @if($product->getModel3DUrl())
                            <strong>Modelo 3D:</strong> <a href="{{ $product->getModel3DUrl() }}" target="_blank">Ver archivo</a>
                        @endif
                    </div>
                </div>

                <div class="mt-3">
                    <strong>Tamaños Disponibles:</strong><br>
                    @foreach($product->sizes as $size)
                        <span class="badge bg-secondary me-1">{{ $size }}</span>
                    @endforeach
                </div>

                <div class="mt-3">
                    <strong>Colores de Impresión:</strong><br>
                    @foreach($product->print_colors as $color)
                        <span class="badge bg-primary me-1">{{ $color }}</span>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- Precios -->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Tabla de Precios</h5>
            </div>
            <div class="card-body">
                @if($product->pricing->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Cantidad</th>
                                    <th>Precio Total</th>
                                    <th>Precio Unitario</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($product->pricing->sortBy('quantity_from') as $pricing)
                                    <tr>
                                        <td>{{ $pricing->quantity_from }} - {{ $pricing->quantity_to }}</td>
                                        <td><strong>€{{ number_format($pricing->price, 2) }}</strong></td>
                                        <td>€{{ number_format($pricing->unit_price, 2) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center py-4">
                        <i class="bi bi-currency-euro display-4 text-muted"></i>
                        <h6 class="mt-2">No hay precios configurados</h6>
                        <a href="{{ route('admin.products.edit', $product) }}" class="btn btn-primary btn-sm">
                            <i class="bi bi-plus-circle me-1"></i>Configurar Precios
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
