@extends('layouts.admin')

@section('title', 'Detalles de Categoría')

@section('content')
<div class="row">
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Información de la Categoría</h5>
            </div>
            <div class="card-body text-center">
                @if($category->getImageUrl())
                    <img src="{{ $category->getImageUrl() }}" alt="{{ $category->name }}" 
                         class="img-fluid rounded mb-3" style="max-width: 200px;">
                @else
                    <div class="bg-light rounded d-flex align-items-center justify-content-center mb-3" 
                         style="width: 100%; height: 200px;">
                        <i class="bi bi-image display-4 text-muted"></i>
                    </div>
                @endif
                
                <h4>{{ $category->name }}</h4>
                <p class="text-muted">{{ $category->description }}</p>
                
                <div class="row text-center">
                    <div class="col-6">
                        <h5 class="text-primary">{{ $category->subcategories->count() }}</h5>
                        <small>Subcategorías</small>
                    </div>
                    <div class="col-6">
                        <h5 class="text-success">{{ $category->products->count() }}</h5>
                        <small>Productos</small>
                    </div>
                </div>

                <div class="mt-3">
                    @if($category->active)
                        <span class="badge bg-success">Activa</span>
                    @else
                        <span class="badge bg-secondary">Inactiva</span>
                    @endif
                </div>

                <div class="mt-3">
                    <a href="{{ route('admin.categories.edit', $category) }}" class="btn btn-primary">
                        <i class="bi bi-pencil me-2"></i>Editar
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-8">
        <!-- Subcategorías -->
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Subcategorías</h5>
                <a href="{{ route('admin.subcategories.create') }}?category_id={{ $category->id }}" 
                   class="btn btn-sm btn-primary">
                    <i class="bi bi-plus-circle me-1"></i>Nueva Subcategoría
                </a>
            </div>
            <div class="card-body">
                @if($category->subcategories->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Nombre</th>
                                    <th>Productos</th>
                                    <th>Estado</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($category->subcategories as $subcategory)
                                    <tr>
                                        <td>{{ $subcategory->name }}</td>
                                        <td>
                                            <span class="badge bg-primary">{{ $subcategory->products->count() }}</span>
                                        </td>
                                        <td>
                                            @if($subcategory->active)
                                                <span class="badge bg-success">Activa</span>
                                            @else
                                                <span class="badge bg-secondary">Inactiva</span>
                                            @endif
                                        </td>
                                        <td>
                                            <a href="{{ route('admin.subcategories.show', $subcategory) }}" 
                                               class="btn btn-outline-primary btn-sm">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center py-4">
                        <i class="bi bi-folder2 display-4 text-muted"></i>
                        <h6 class="mt-2">No hay subcategorías</h6>
                        <a href="{{ route('admin.subcategories.create') }}?category_id={{ $category->id }}" 
                           class="btn btn-primary btn-sm">
                            <i class="bi bi-plus-circle me-1"></i>Crear Primera Subcategoría
                        </a>
                    </div>
                @endif
            </div>
        </div>

        <!-- Productos -->
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Productos Recientes</h5>
                <a href="{{ route('admin.products.index') }}?category_id={{ $category->id }}" 
                   class="btn btn-sm btn-outline-primary">
                    Ver todos
                </a>
            </div>
            <div class="card-body">
                @if($category->products->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Producto</th>
                                    <th>SKU</th>
                                    <th>Estado</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($category->products->take(5) as $product)
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                @if($product->getFirstImageUrl())
                                                    <img src="{{ $product->getFirstImageUrl() }}" 
                                                         alt="{{ $product->name }}" 
                                                         class="rounded me-2" width="40" height="40" 
                                                         style="object-fit: cover;">
                                                @endif
                                                <div>
                                                    <strong>{{ $product->name }}</strong>
                                                    <br><small class="text-muted">{{ $product->subcategory->name }}</small>
                                                </div>
                                            </div>
                                        </td>
                                        <td><code>{{ $product->sku }}</code></td>
                                        <td>
                                            @if($product->active)
                                                <span class="badge bg-success">Activo</span>
                                            @else
                                                <span class="badge bg-secondary">Inactivo</span>
                                            @endif
                                        </td>
                                        <td>
                                            <a href="{{ route('admin.products.show', $product) }}" 
                                               class="btn btn-outline-primary btn-sm">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center py-4">
                        <i class="bi bi-box display-4 text-muted"></i>
                        <h6 class="mt-2">No hay productos</h6>
                        <a href="{{ route('admin.products.create') }}?category_id={{ $category->id }}" 
                           class="btn btn-primary btn-sm">
                            <i class="bi bi-plus-circle me-1"></i>Crear Primer Producto
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection