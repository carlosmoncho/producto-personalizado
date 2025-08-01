@extends('layouts.admin')

@section('title', 'Productos')

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
            <h1 class="h3">Productos</h1>
            <a href="{{ route('admin.products.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-circle"></i> Nuevo Producto
            </a>
        </div>
    </div>

    <!-- Mensajes de éxito/error -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Filtros -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.products.index') }}" class="row g-3">
                <div class="col-md-3">
                    <label for="search" class="form-label">Buscar</label>
                    <input type="text" class="form-control" id="search" name="search" 
                           value="{{ request('search') }}" placeholder="Nombre, SKU o descripción">
                </div>
                
                <div class="col-md-3">
                    <label for="category_id" class="form-label">Categoría</label>
                    <select class="form-select" id="category_id" name="category_id">
                        <option value="">Todas las categorías</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}" {{ request('category_id') == $category->id ? 'selected' : '' }}>
                                {{ $category->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                
                <div class="col-md-3">
                    <label for="subcategory_id" class="form-label">Subcategoría</label>
                    <select class="form-select" id="subcategory_id" name="subcategory_id">
                        <option value="">Todas las subcategorías</option>
                        @foreach($subcategories as $subcategory)
                            <option value="{{ $subcategory->id }}" {{ request('subcategory_id') == $subcategory->id ? 'selected' : '' }}>
                                {{ $subcategory->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                
                <div class="col-md-2">
                    <label for="status" class="form-label">Estado</label>
                    <select class="form-select" id="status" name="status">
                        <option value="">Todos</option>
                        <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Activos</option>
                        <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactivos</option>
                    </select>
                </div>
                
                <div class="col-md-1 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-search"></i> Filtrar
                    </button>
                </div>
            </form>
            
            @if(request()->hasAny(['search', 'category_id', 'subcategory_id', 'status']))
                <div class="mt-2">
                    <a href="{{ route('admin.products.index') }}" class="btn btn-sm btn-secondary">
                        <i class="bi bi-x-circle"></i> Limpiar filtros
                    </a>
                </div>
            @endif
        </div>
    </div>

    <!-- Tabla de productos -->
    <div class="card">
        <div class="card-body">
            @if($products->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Imagen</th>
                                <th>Nombre / SKU</th>
                                <th>Categoría</th>
                                <th>Colores</th>
                                <th>Tallas</th>
                                <th>Precio desde</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($products as $product)
                                <tr>
                                    <td>
                                        @if($product->images && count($product->images) > 0)
                                            <img src="{{ $product->getFirstImageUrl() }}" 
                                                 alt="{{ $product->name }}" 
                                                 class="img-thumbnail" 
                                                 style="width: 50px; height: 50px; object-fit: cover;">
                                        @else
                                            <div class="bg-light rounded d-flex align-items-center justify-content-center" 
                                                 style="width: 50px; height: 50px;">
                                                <i class="bi bi-image text-muted"></i>
                                            </div>
                                        @endif
                                    </td>
                                    <td>
                                        <strong>{{ $product->name }}</strong><br>
                                        <small class="text-muted">SKU: {{ $product->sku }}</small>
                                    </td>
                                    <td>
                                        {{ $product->category->name }}<br>
                                        <small class="text-muted">{{ $product->subcategory->name }}</small>
                                    </td>
                                    <td>
                                        @if($product->colors && count($product->colors) > 0)
                                            <span class="badge bg-info">{{ count($product->colors) }} colores</span>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($product->sizes && count($product->sizes) > 0)
                                            <span class="badge bg-secondary">{{ count($product->sizes) }} tallas</span>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($product->pricing->count() > 0)
                                            €{{ number_format($product->pricing->min('unit_price'), 2) }}
                                        @else
                                            <span class="text-muted">Sin precio</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($product->active)
                                            <span class="badge bg-success">Activo</span>
                                        @else
                                            <span class="badge bg-danger">Inactivo</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <a href="{{ route('admin.products.show', $product) }}" 
                                               class="btn btn-sm btn-info" 
                                               title="Ver">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                            <a href="{{ route('admin.products.edit', $product) }}" 
                                               class="btn btn-sm btn-warning" 
                                               title="Editar">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            <form action="{{ route('admin.products.destroy', $product) }}" 
                                                  method="POST" 
                                                  class="d-inline"
                                                  onsubmit="return confirm('¿Estás seguro de que quieres eliminar este producto?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" 
                                                        class="btn btn-sm btn-danger" 
                                                        title="Eliminar">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Paginación -->
                <div class="d-flex justify-content-center mt-4">
                    {{ $products->withQueryString()->links() }}
                </div>
            @else
                <div class="text-center py-5">
                    <i class="bi bi-box-seam display-1 text-muted"></i>
                    <p class="mt-3 text-muted">
                        @if(request()->hasAny(['search', 'category_id', 'subcategory_id', 'status']))
                            No se encontraron productos con los filtros aplicados.
                        @else
                            No hay productos registrados.
                        @endif
                    </p>
                    <a href="{{ route('admin.products.create') }}" class="btn btn-primary mt-2">
                        <i class="bi bi-plus-circle"></i> Crear Primer Producto
                    </a>
                </div>
            @endif
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Filtrar subcategorías según la categoría seleccionada
    const categorySelect = document.getElementById('category_id');
    const subcategorySelect = document.getElementById('subcategory_id');
    const allSubcategories = Array.from(subcategorySelect.options);
    
    categorySelect.addEventListener('change', function() {
        const selectedCategoryId = this.value;
        const currentSubcategoryId = subcategorySelect.value;
        
        // Limpiar subcategorías
        subcategorySelect.innerHTML = '<option value="">Todas las subcategorías</option>';
        
        if (selectedCategoryId) {
            // Filtrar subcategorías de la categoría seleccionada
            @foreach($subcategories as $subcategory)
                if ('{{ $subcategory->category_id }}' === selectedCategoryId) {
                    const option = new Option('{{ $subcategory->name }}', '{{ $subcategory->id }}');
                    if ('{{ $subcategory->id }}' === currentSubcategoryId) {
                        option.selected = true;
                    }
                    subcategorySelect.add(option);
                }
            @endforeach
        } else {
            // Mostrar todas las subcategorías
            @foreach($subcategories as $subcategory)
                const option = new Option('{{ $subcategory->name }}', '{{ $subcategory->id }}');
                if ('{{ $subcategory->id }}' === currentSubcategoryId) {
                    option.selected = true;
                }
                subcategorySelect.add(option);
            @endforeach
        }
    });
    
    // Ejecutar al cargar la página si hay una categoría seleccionada
    if (categorySelect.value) {
        categorySelect.dispatchEvent(new Event('change'));
    }
});
</script>
@endpush
@endsection