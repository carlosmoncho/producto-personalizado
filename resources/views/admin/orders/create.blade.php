@extends('layouts.admin')

@section('title', 'Crear Pedido')

@section('content')
<!-- Header con Breadcrumb -->
<div class="d-flex justify-content-between align-items-start mb-4">
    <div>
        <!-- Breadcrumb -->
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item">
                    <a href="{{ route('admin.dashboard') }}">
                        <i class="bi bi-house-fill"></i> Dashboard
                    </a>
                </li>
                <li class="breadcrumb-item">
                    <a href="{{ route('admin.orders.index') }}">Pedidos</a>
                </li>
                <li class="breadcrumb-item active" aria-current="page">Crear Pedido</li>
            </ol>
        </nav>
        
        <!-- Título y descripción -->
        <div class="d-flex align-items-center">
            <div class="icon-square bg-primary text-white rounded me-3">
                <i class="bi bi-cart-plus-fill"></i>
            </div>
            <div>
                <h2 class="mb-0">Crear Nuevo Pedido</h2>
                <small class="text-muted">Complete el formulario para registrar un nuevo pedido</small>
            </div>
        </div>
    </div>
</div>

<form method="POST" action="{{ route('admin.orders.store') }}" id="createOrderForm">
    @csrf
    
    <div class="row">
        <!-- Columna Izquierda: Información del Cliente -->
        <div class="col-lg-4">
            <!-- Selección de Cliente -->
            <div class="card shadow-sm mb-4 border-0">
                <div class="card-header bg-golden border-bottom-0 py-3">
                    <div class="d-flex align-items-center">
                        <div class="icon-square bg-white rounded me-3" style="color: var(--primary-color);">
                            <i class="bi bi-person-fill"></i>
                        </div>
                        <div>
                            <h5 class="mb-0">Información del Cliente</h5>
                            <small>Seleccione o agregue un cliente</small>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Selector de Cliente Existente -->
                    <div class="mb-3">
                        <label class="form-label">Cliente</label>
                        <select class="form-select" id="customer_selector" name="customer_id">
                            <option value="">-- Nuevo Cliente --</option>
                            @foreach(\App\Models\Customer::orderBy('name')->get() as $customer)
                                <option value="{{ $customer->id }}" 
                                        data-email="{{ $customer->email }}"
                                        data-phone="{{ $customer->phone }}"
                                        data-address="{{ $customer->address }}"
                                        {{ old('customer_id') == $customer->id ? 'selected' : '' }}>
                                    {{ $customer->name }} - {{ $customer->email }}
                                </option>
                            @endforeach
                        </select>
                        <small class="text-muted">Seleccione un cliente existente o deje vacío para crear uno nuevo</small>
                    </div>

                    <hr>

                    <!-- Campos del Cliente -->
                    <div id="customer_fields">
                        <div class="mb-3">
                            <label for="customer_name" class="form-label">Nombre <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('customer_name') is-invalid @enderror" 
                                   id="customer_name" name="customer_name" value="{{ old('customer_name') }}" required>
                            @error('customer_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="customer_email" class="form-label">Email <span class="text-danger">*</span></label>
                            <input type="email" class="form-control @error('customer_email') is-invalid @enderror" 
                                   id="customer_email" name="customer_email" value="{{ old('customer_email') }}" required>
                            @error('customer_email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="customer_phone" class="form-label">Teléfono <span class="text-danger">*</span></label>
                            <input type="tel" class="form-control @error('customer_phone') is-invalid @enderror" 
                                   id="customer_phone" name="customer_phone" value="{{ old('customer_phone') }}" required>
                            @error('customer_phone')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="customer_address" class="form-label">Dirección <span class="text-danger">*</span></label>
                            <textarea class="form-control @error('customer_address') is-invalid @enderror" 
                                      id="customer_address" name="customer_address" rows="3" required>{{ old('customer_address') }}</textarea>
                            @error('customer_address')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            <!-- Notas del Pedido -->
            <div class="card shadow-sm border-0">
                <div class="card-header bg-golden border-bottom-0 py-3">
                    <div class="d-flex align-items-center">
                        <div class="icon-square bg-white rounded me-3" style="color: var(--primary-color);">
                            <i class="bi bi-chat-text-fill"></i>
                        </div>
                        <div>
                            <h5 class="mb-0">Notas del Pedido</h5>
                            <small>Información adicional</small>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="mb-0">
                        <textarea class="form-control @error('notes') is-invalid @enderror" 
                                  id="notes" name="notes" rows="4" placeholder="Agregue cualquier nota o instrucción especial...">{{ old('notes') }}</textarea>
                        @error('notes')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>
        </div>

        <!-- Columna Derecha: Productos -->
        <div class="col-lg-8">
            <!-- Selección de Productos -->
            <div class="card shadow-sm mb-4 border-0">
                <div class="card-header bg-golden border-bottom-0 py-3">
                    <div class="d-flex align-items-center justify-content-between">
                        <div class="d-flex align-items-center">
                            <div class="icon-square bg-white rounded me-3" style="color: var(--primary-color);">
                                <i class="bi bi-box-seam-fill"></i>
                            </div>
                            <div>
                                <h5 class="mb-0">Productos del Pedido</h5>
                                <small>Agregue productos y configure cantidades</small>
                            </div>
                        </div>
                        <button type="button" class="btn btn-primary btn-sm" id="addProductBtn">
                            <i class="bi bi-plus-circle me-1"></i>Agregar Producto
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Buscador de Productos -->
                    <div class="row mb-3">
                        <div class="col-md-12">
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-search"></i></span>
                                <input type="text" class="form-control" id="product_search" 
                                       placeholder="Buscar productos por nombre, SKU o descripción...">
                            </div>
                            <div id="product_search_results" class="list-group mt-2" style="display: none; max-height: 300px; overflow-y: auto;"></div>
                        </div>
                    </div>

                    <!-- Lista de Productos Agregados -->
                    <div id="products_list">
                        <div class="table-responsive">
                            <table class="table table-hover" id="products_table">
                                <thead>
                                    <tr>
                                        <th width="40%">Producto</th>
                                        <th width="15%">Cantidad</th>
                                        <th width="15%">Precio Unit.</th>
                                        <th width="15%">Subtotal</th>
                                        <th width="15%">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody id="products_tbody">
                                    <!-- Los productos se agregarán dinámicamente aquí -->
                                </tbody>
                                <tfoot>
                                    <tr class="table-light">
                                        <td colspan="3" class="text-end"><strong>Total:</strong></td>
                                        <td><strong id="order_total">€0.00</strong></td>
                                        <td></td>
                                    </tr>
                                </tfoot>
                            </table>
                            <div id="no_products_message" class="text-center py-4 text-muted">
                                <i class="bi bi-inbox display-4"></i>
                                <p class="mt-2">No se han agregado productos al pedido</p>
                                <p><small>Use el buscador arriba para agregar productos</small></p>
                            </div>
                        </div>
                    </div>

                    <!-- Campos ocultos para los productos -->
                    <div id="products_inputs"></div>
                </div>
            </div>

            <!-- Resumen y Acciones -->
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-8">
                            <div class="alert alert-info mb-0">
                                <i class="bi bi-info-circle me-2"></i>
                                <strong>Información:</strong> El pedido se creará con estado "Pendiente". Podrá cambiar el estado y agregar más detalles después de crearlo.
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-success" id="submit_btn" disabled>
                                    <i class="bi bi-check-circle me-2"></i>Crear Pedido
                                </button>
                                <a href="{{ route('admin.orders.index') }}" class="btn btn-outline-secondary">
                                    <i class="bi bi-arrow-left me-2"></i>Cancelar
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>

<!-- Template para producto en la lista -->
<template id="product_row_template">
    <tr data-product-id="">
        <td>
            <div class="d-flex align-items-center">
                <img src="" alt="" class="img-thumbnail me-2" style="width: 50px; height: 50px; object-fit: cover;">
                <div>
                    <strong class="product-name"></strong><br>
                    <small class="text-muted product-sku"></small>
                </div>
            </div>
        </td>
        <td>
            <input type="number" class="form-control quantity-input" min="1" value="1" style="width: 80px;">
        </td>
        <td>
            <span class="price-display">€0.00</span>
        </td>
        <td>
            <span class="subtotal-display">€0.00</span>
        </td>
        <td>
            <button type="button" class="btn btn-outline-danger btn-sm remove-product">
                <i class="bi bi-trash"></i>
            </button>
        </td>
    </tr>
</template>

@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Variables globales
    let products = [];
    let productPrices = {};
    
    // Elementos del DOM
    const customerSelector = document.getElementById('customer_selector');
    const customerFields = document.getElementById('customer_fields');
    const productSearch = document.getElementById('product_search');
    const searchResults = document.getElementById('product_search_results');
    const productsTable = document.getElementById('products_table');
    const productsTbody = document.getElementById('products_tbody');
    const noProductsMessage = document.getElementById('no_products_message');
    const orderTotal = document.getElementById('order_total');
    const submitBtn = document.getElementById('submit_btn');
    const productsInputs = document.getElementById('products_inputs');
    
    // Manejo del selector de cliente
    customerSelector.addEventListener('change', function() {
        if (this.value) {
            // Cliente existente seleccionado
            const option = this.options[this.selectedIndex];
            document.getElementById('customer_name').value = option.text.split(' - ')[0];
            document.getElementById('customer_email').value = option.dataset.email;
            document.getElementById('customer_phone').value = option.dataset.phone || '';
            document.getElementById('customer_address').value = option.dataset.address || '';
            
            // Hacer campos de solo lectura
            customerFields.querySelectorAll('input, textarea').forEach(field => {
                field.readOnly = true;
                field.classList.add('bg-light');
            });
        } else {
            // Nuevo cliente
            customerFields.querySelectorAll('input, textarea').forEach(field => {
                field.readOnly = false;
                field.classList.remove('bg-light');
                if (field.id !== 'customer_address') {
                    field.value = '';
                }
            });
            document.getElementById('customer_address').value = '';
        }
    });
    
    // Búsqueda de productos
    let searchTimeout;
    productSearch.addEventListener('input', function() {
        clearTimeout(searchTimeout);
        const searchTerm = this.value.trim();
        
        if (searchTerm.length >= 2) {
            searchTimeout = setTimeout(() => {
                searchProducts(searchTerm);
            }, 300);
        } else {
            searchResults.style.display = 'none';
            searchResults.innerHTML = '';
        }
    });
    
    // Productos disponibles precargados
    const availableProducts = [
        @foreach(\App\Models\Product::where('active', true)->with('pricing')->get() as $product)
        {
            id: {{ $product->id }},
            name: "{{ addslashes($product->name) }}",
            sku: "{{ $product->sku }}",
            description: "{{ addslashes(Str::limit($product->description, 100)) }}",
            price: {{ $product->pricing->count() > 0 ? $product->pricing->min('unit_price') : 10 }},
            image: "{{ $product->getFirstImageUrl() }}"
        },
        @endforeach
    ];
    
    // Función para buscar productos
    function searchProducts(term) {
        const results = availableProducts.filter(product => {
            const searchTerm = term.toLowerCase();
            return product.name.toLowerCase().includes(searchTerm) ||
                   product.sku.toLowerCase().includes(searchTerm) ||
                   product.description.toLowerCase().includes(searchTerm);
        });
        
        displaySearchResults(results.slice(0, 10)); // Limitar a 10 resultados
    }
    
    // Mostrar resultados de búsqueda
    function displaySearchResults(products) {
        searchResults.innerHTML = '';
        
        if (products.length === 0) {
            searchResults.innerHTML = '<div class="list-group-item">No se encontraron productos</div>';
        } else {
            products.forEach(product => {
                const item = document.createElement('a');
                item.href = '#';
                item.className = 'list-group-item list-group-item-action';
                item.innerHTML = `
                    <div class="d-flex align-items-center">
                        <img src="${product.image || '/storage/default-product.png'}" 
                             alt="${product.name}" 
                             class="img-thumbnail me-2" 
                             style="width: 40px; height: 40px; object-fit: cover;">
                        <div>
                            <strong>${product.name}</strong><br>
                            <small class="text-muted">SKU: ${product.sku} - Precio: €${product.price || '10.00'}</small>
                        </div>
                    </div>
                `;
                
                item.addEventListener('click', function(e) {
                    e.preventDefault();
                    addProduct(product);
                    productSearch.value = '';
                    searchResults.style.display = 'none';
                });
                
                searchResults.appendChild(item);
            });
        }
        
        searchResults.style.display = 'block';
    }
    
    // Agregar producto a la lista
    function addProduct(product) {
        console.log('Agregando producto:', product); // Debug
        
        // Verificar si el producto ya está en la lista
        if (products.find(p => p.id === product.id)) {
            alert('Este producto ya está en la lista');
            return;
        }
        
        products.push(product);
        productPrices[product.id] = product.price || 10;
        
        // Crear fila manualmente para mayor control
        const tr = document.createElement('tr');
        tr.dataset.productId = product.id;
        tr.innerHTML = `
            <td>
                <div class="d-flex align-items-center">
                    <img src="${product.image || '/images/no-image.png'}" alt="${product.name}" 
                         class="img-thumbnail me-2" style="width: 50px; height: 50px; object-fit: cover;">
                    <div>
                        <strong>${product.name}</strong><br>
                        <small class="text-muted">SKU: ${product.sku}</small>
                    </div>
                </div>
            </td>
            <td>
                <input type="number" class="form-control quantity-input" min="1" value="1" style="width: 80px;">
            </td>
            <td>
                <span class="price-display">€${(product.price || 10).toFixed(2)}</span>
            </td>
            <td>
                <span class="subtotal-display">€${(product.price || 10).toFixed(2)}</span>
            </td>
            <td>
                <button type="button" class="btn btn-outline-danger btn-sm remove-product">
                    <i class="bi bi-trash"></i>
                </button>
            </td>
        `;
        
        // Event listeners
        const quantityInput = tr.querySelector('.quantity-input');
        quantityInput.addEventListener('input', function() {
            console.log('Cantidad cambiada:', this.value); // Debug
            updateSubtotal(product.id);
            updateTotal();
            updateHiddenInputs();
        });
        
        const removeBtn = tr.querySelector('.remove-product');
        removeBtn.addEventListener('click', function() {
            console.log('Removiendo producto:', product.id); // Debug
            removeProduct(product.id);
        });
        
        productsTbody.appendChild(tr);
        
        // Mostrar tabla y ocultar mensaje
        productsTable.style.display = 'table';
        noProductsMessage.style.display = 'none';
        
        updateTotal();
        updateSubmitButton();
        updateHiddenInputs();
        
        console.log('Productos actuales:', products); // Debug
        console.log('Productos en DOM:', document.querySelectorAll('#products_tbody tr').length); // Debug
    }
    
    // Eliminar producto
    function removeProduct(productId) {
        const row = document.querySelector(`tr[data-product-id="${productId}"]`);
        if (row) {
            row.remove();
        }
        
        products = products.filter(p => p.id !== productId);
        delete productPrices[productId];
        
        if (products.length === 0) {
            productsTable.style.display = 'none';
            noProductsMessage.style.display = 'block';
        }
        
        updateTotal();
        updateSubmitButton();
        updateHiddenInputs();
    }
    
    // Actualizar subtotal de un producto
    function updateSubtotal(productId) {
        const row = document.querySelector(`tr[data-product-id="${productId}"]`);
        if (row) {
            const quantity = parseInt(row.querySelector('.quantity-input').value) || 1;
            const price = productPrices[productId] || 10;
            const subtotal = quantity * price;
            row.querySelector('.subtotal-display').textContent = '€' + subtotal.toFixed(2);
        }
    }
    
    // Actualizar total del pedido
    function updateTotal() {
        let total = 0;
        document.querySelectorAll('#products_tbody tr').forEach(row => {
            const subtotalText = row.querySelector('.subtotal-display').textContent;
            const subtotal = parseFloat(subtotalText.replace('€', ''));
            total += subtotal;
        });
        orderTotal.textContent = '€' + total.toFixed(2);
    }
    
    // Actualizar estado del botón submit
    function updateSubmitButton() {
        submitBtn.disabled = products.length === 0;
    }
    
    // Actualizar campos ocultos con los productos
    function updateHiddenInputs() {
        console.log('Actualizando campos ocultos...'); // Debug
        productsInputs.innerHTML = '';
        
        products.forEach((product, index) => {
            const row = document.querySelector(`tr[data-product-id="${product.id}"]`);
            const quantity = row ? row.querySelector('.quantity-input').value : 1;
            const price = productPrices[product.id] || 10;
            
            console.log(`Producto ${index}:`, {id: product.id, quantity, price}); // Debug
            
            // Crear inputs de forma más explícita
            const idInput = document.createElement('input');
            idInput.type = 'hidden';
            idInput.name = `products[${index}][id]`;
            idInput.value = product.id;
            productsInputs.appendChild(idInput);
            
            const quantityInputHidden = document.createElement('input');
            quantityInputHidden.type = 'hidden';
            quantityInputHidden.name = `products[${index}][quantity]`;
            quantityInputHidden.value = quantity;
            productsInputs.appendChild(quantityInputHidden);
            
            const priceInput = document.createElement('input');
            priceInput.type = 'hidden';
            priceInput.name = `products[${index}][price]`;
            priceInput.value = price;
            productsInputs.appendChild(priceInput);
        });
        
        console.log('Campos ocultos creados:', productsInputs.children.length); // Debug
    }
    
    // Validación del formulario antes de enviar
    document.getElementById('createOrderForm').addEventListener('submit', function(e) {
        if (products.length === 0) {
            e.preventDefault();
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Debe agregar al menos un producto al pedido'
            });
            return false;
        }
        
        // Actualizar campos ocultos antes de enviar
        updateHiddenInputs();
    });
    
    // Botón de agregar producto manual
    document.getElementById('addProductBtn').addEventListener('click', function() {
        productSearch.focus();
        productSearch.select();
    });
    
    
    // Ocultar resultados de búsqueda al hacer clic fuera
    document.addEventListener('click', function(e) {
        if (!productSearch.contains(e.target) && !searchResults.contains(e.target)) {
            searchResults.style.display = 'none';
        }
    });
});
</script>
@endpush