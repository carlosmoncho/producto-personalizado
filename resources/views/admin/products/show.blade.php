@extends('layouts.admin')

@section('title', $product->name)

@section('content')
<div class="container-fluid">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            @foreach($breadcrumbs as $breadcrumb)
                @if($loop->last)
                    <li class="breadcrumb-item active">{{ $breadcrumb['name'] }}</li>
                @else
                    <li class="breadcrumb-item"><a href="{{ $breadcrumb['url'] }}">{{ $breadcrumb['name'] }}</a></li>
                @endif
            @endforeach
        </ol>
    </nav>

    <div class="row mb-4">
        <div class="col-12 d-flex justify-content-between align-items-center">
            <h1 class="h3">{{ $product->name }}</h1>
            <div>
                <a href="{{ route('admin.products.edit', $product) }}" class="btn btn-primary">
                    <i class="bi bi-pencil"></i> Editar
                </a>
                <a href="{{ route('admin.products.index') }}" class="btn btn-secondary">
                    <i class="bi bi-arrow-left"></i> Volver
                </a>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Columna principal -->
        <div class="col-lg-8">
            <!-- Información básica -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Información Básica</h5>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-3">
                            <strong>SKU:</strong>
                        </div>
                        <div class="col-md-9">
                            <code>{{ $product->sku }}</code>
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-3">
                            <strong>Slug:</strong>
                        </div>
                        <div class="col-md-9">
                            {{ $product->slug }}
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-3">
                            <strong>Estado:</strong>
                        </div>
                        <div class="col-md-9">
                            @if($product->active)
                                <span class="badge bg-success">Activo</span>
                            @else
                                <span class="badge bg-danger">Inactivo</span>
                            @endif
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-3">
                            <strong>Categoría:</strong>
                        </div>
                        <div class="col-md-9">
                            {{ $product->category->name }} / {{ $product->subcategory->name }}
                        </div>
                    </div>

                    @if($product->description)
                        <div class="row">
                            <div class="col-md-3">
                                <strong>Descripción:</strong>
                            </div>
                            <div class="col-md-9">
                                {{ $product->description }}
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Especificaciones -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Especificaciones</h5>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <strong>Material:</strong>
                        </div>
                        <div class="col-md-8">
                            {{ $product->material }}
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-4">
                            <strong>Sistema de Impresión:</strong>
                        </div>
                        <div class="col-md-8">
                            {{ $product->printing_system }}
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-4">
                            <strong>Número de Caras:</strong>
                        </div>
                        <div class="col-md-8">
                            {{ $product->face_count }}
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4">
                            <strong>Colores de Impresión:</strong>
                        </div>
                        <div class="col-md-8">
                            {{ $product->print_colors_count }} colores
                        </div>
                    </div>
                </div>
            </div>

            <!-- Colores y Tallas -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Colores y Tallas</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <strong>Colores Disponibles:</strong>
                        <div class="mt-2">
                            @php
                                $availableColors = \App\Models\AvailableColor::whereIn('name', $product->colors ?? [])->get();
                            @endphp
                            @foreach($availableColors as $color)
                                <span class="badge me-2 mb-2" 
                                      style="background-color: {{ $color->hex_code }}; color: {{ $color->hex_code == '#FFFFFF' ? '#000' : '#FFF' }}">
                                    {{ $color->name }}
                                </span>
                            @endforeach
                        </div>
                    </div>

                    <div class="mb-3">
                        <strong>Colores de Impresión:</strong>
                        <div class="mt-2">
                            @php
                                $printColors = \App\Models\AvailablePrintColor::whereIn('name', $product->print_colors ?? [])->get();
                            @endphp
                            @foreach($printColors as $color)
                                <span class="badge me-2 mb-2" 
                                      style="background-color: {{ $color->hex_code }}; color: {{ $color->hex_code == '#FFFFFF' ? '#000' : '#FFF' }}">
                                    {{ $color->name }}
                                </span>
                            @endforeach
                        </div>
                    </div>

                    <div>
                        <strong>Tallas Disponibles:</strong>
                        <div class="mt-2">
                            @foreach($product->sizes ?? [] as $size)
                                <span class="badge bg-secondary me-2">{{ $size }}</span>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

            <!-- Precios -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Tabla de Precios</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Cantidad</th>
                                    <th>Precio Total</th>
                                    <th>Precio Unitario</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($product->pricing->sortBy('quantity_from') as $price)
                                    <tr>
                                        <td>{{ $price->quantity_from }} - {{ $price->quantity_to }}</td>
                                        <td>€{{ number_format($price->price, 2) }}</td>
                                        <td>€{{ number_format($price->unit_price, 2) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Columna lateral -->
        <div class="col-lg-4">
            <!-- Imágenes -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Imágenes</h5>
                </div>
                <div class="card-body">
                    @if($product->images && count($product->images) > 0)
                        <div id="productCarousel" class="carousel slide" data-bs-ride="carousel">
                            <div class="carousel-inner">
                                @foreach($product->getImagesUrls() as $index => $imageUrl)
                                    <div class="carousel-item {{ $index === 0 ? 'active' : '' }}">
                                        <img src="{{ $imageUrl }}" class="d-block w-100" alt="Imagen {{ $index + 1 }}">
                                    </div>
                                @endforeach
                            </div>
                            @if(count($product->images) > 1)
                                <button class="carousel-control-prev" type="button" data-bs-target="#productCarousel" data-bs-slide="prev">
                                    <span class="carousel-control-prev-icon"></span>
                                </button>
                                <button class="carousel-control-next" type="button" data-bs-target="#productCarousel" data-bs-slide="next">
                                    <span class="carousel-control-next-icon"></span>
                                </button>
                            @endif
                        </div>
                    @else
                        <p class="text-muted text-center">No hay imágenes disponibles</p>
                    @endif
                </div>
            </div>

            <!-- Modelo 3D -->
            @if($product->model_3d_file)
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">Modelo 3D</h5>
                    </div>
                    <div class="card-body">
                        <div class="d-grid">
                            <a href="{{ $product->getModel3dUrl() }}" class="btn btn-primary" download>
                                <i class="bi bi-download"></i> Descargar Modelo 3D
                            </a>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Información adicional -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Información Adicional</h5>
                </div>
                <div class="card-body">
                    <p class="mb-2">
                        <small class="text-muted">Creado:</small><br>
                        {{ $product->created_at->format('d/m/Y H:i') }}
                    </p>
                    <p class="mb-0">
                        <small class="text-muted">Última actualización:</small><br>
                        {{ $product->updated_at->format('d/m/Y H:i') }}
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Acciones -->
    <div class="row mt-4">
        <div class="col-12">
            <hr>
            <div class="d-flex justify-content-between">
                <form action="{{ route('admin.products.destroy', $product) }}" method="POST" 
                      onsubmit="return confirm('¿Estás seguro de que quieres eliminar este producto?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">
                        <i class="bi bi-trash"></i> Eliminar Producto
                    </button>
                </form>
                
                <div>
                    <a href="{{ route('admin.products.edit', $product) }}" class="btn btn-primary">
                        <i class="bi bi-pencil"></i> Editar
                    </a>
                    <a href="{{ route('admin.products.index') }}" class="btn btn-secondary">
                        <i class="bi bi-arrow-left"></i> Volver al Listado
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection