@extends('layouts.admin')

@section('title', 'Imágenes por Atributo - ' . $product->name)

@section('content')
<div class="container-fluid">
    <x-admin.breadcrumb :items="$breadcrumbs" />

    <div class="row mb-4">
        <div class="col-12 d-flex justify-content-between align-items-center">
            <h1 class="h3">Imágenes por Atributo</h1>
            <div>
                <a href="{{ route('admin.products.show', $product) }}" class="btn btn-secondary">
                    <i class="bi bi-arrow-left"></i> Volver al Producto
                </a>
            </div>
        </div>
    </div>

    <div class="alert alert-info mb-4">
        <i class="bi bi-info-circle me-2"></i>
        Asigna imágenes específicas a cada atributo del producto. Cuando el cliente seleccione un atributo en el configurador, se mostrarán las imágenes asociadas a ese atributo.
    </div>

    @if($attributesByGroup->isEmpty())
        <div class="alert alert-warning">
            <i class="bi bi-exclamation-triangle me-2"></i>
            Este producto no tiene atributos asignados. Primero <a href="{{ route('admin.products.edit', $product) }}">edita el producto</a> y asigna atributos del configurador.
        </div>
    @else
        @foreach($attributesByGroup as $groupName => $attributes)
            <x-admin.card title="{{ $groupName }}" class="mb-4">
                <div class="row">
                    @foreach($attributes as $attributeValue)
                        <div class="col-md-6 col-lg-4 mb-4">
                            <div class="card h-100 attribute-image-card" data-attribute-value-id="{{ $attributeValue->id }}">
                                <div class="card-header bg-light d-flex justify-content-between align-items-center">
                                    <span class="fw-bold">
                                        @if($attributeValue->productAttribute->type === 'color')
                                            <span class="color-preview me-2" style="background-color: {{ $attributeValue->productAttribute->hex_code ?? '#ccc' }}; display: inline-block; width: 20px; height: 20px; border-radius: 50%; vertical-align: middle; border: 1px solid #ddd;"></span>
                                        @endif
                                        {{ $attributeValue->productAttribute->display_name ?? $attributeValue->productAttribute->name }}
                                    </span>
                                    <span class="badge bg-secondary images-count">
                                        {{ count($attributeValue->images ?? []) }} img
                                    </span>
                                </div>
                                <div class="card-body">
                                    <!-- Galería de imágenes existentes -->
                                    <div class="images-gallery mb-3" data-attribute-value-id="{{ $attributeValue->id }}">
                                        @if(!empty($attributeValue->images))
                                            <div class="row g-2">
                                                @foreach($attributeValue->images as $index => $image)
                                                    <div class="col-4 image-item" data-index="{{ $index }}">
                                                        <div class="position-relative">
                                                            <img src="{{ Storage::disk(config('filesystems.default', 'public'))->url($image) }}"
                                                                 alt="Imagen {{ $index + 1 }}"
                                                                 class="img-fluid rounded border"
                                                                 style="aspect-ratio: 1; object-fit: cover; width: 100%;"
                                                                 onerror="this.src='/api/storage/{{ $image }}'">
                                                            <button type="button"
                                                                    class="btn btn-danger btn-sm position-absolute top-0 end-0 delete-image-btn"
                                                                    data-product-slug="{{ $product->slug }}"
                                                                    data-attribute-value-id="{{ $attributeValue->id }}"
                                                                    data-image-index="{{ $index }}"
                                                                    style="transform: translate(25%, -25%); padding: 0.125rem 0.375rem; font-size: 0.75rem;">
                                                                <i class="bi bi-x"></i>
                                                            </button>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        @else
                                            <div class="text-center text-muted py-3 no-images-message">
                                                <i class="bi bi-image fs-1 opacity-25 d-block mb-2"></i>
                                                <small>Sin imágenes asignadas</small>
                                            </div>
                                        @endif
                                    </div>

                                    <!-- Formulario para subir nuevas imágenes -->
                                    <form class="upload-form"
                                          data-product-slug="{{ $product->slug }}"
                                          data-attribute-value-id="{{ $attributeValue->id }}">
                                        @csrf
                                        <div class="input-group">
                                            <input type="file"
                                                   class="form-control form-control-sm"
                                                   name="images[]"
                                                   multiple
                                                   accept="image/*">
                                            <button type="submit" class="btn btn-primary btn-sm">
                                                <i class="bi bi-upload"></i>
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </x-admin.card>
        @endforeach
    @endif
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Manejar subida de imágenes
    document.querySelectorAll('.upload-form').forEach(form => {
        form.addEventListener('submit', async function(e) {
            e.preventDefault();

            const productSlug = this.dataset.productSlug;
            const attributeValueId = this.dataset.attributeValueId;
            const fileInput = this.querySelector('input[type="file"]');
            const submitBtn = this.querySelector('button[type="submit"]');

            if (!fileInput.files.length) {
                alert('Selecciona al menos una imagen');
                return;
            }

            const formData = new FormData();
            for (let file of fileInput.files) {
                formData.append('images[]', file);
            }
            formData.append('_token', '{{ csrf_token() }}');

            // Deshabilitar botón mientras se sube
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';

            try {
                const response = await fetch(`/admin/products/${productSlug}/attribute-images/${attributeValueId}`, {
                    method: 'POST',
                    body: formData
                });

                const data = await response.json();

                if (data.success) {
                    // Recargar la galería
                    updateGallery(attributeValueId, data.images);
                    fileInput.value = '';
                } else {
                    alert(data.message || 'Error al subir las imágenes');
                }
            } catch (error) {
                console.error('Error:', error);
                alert('Error al subir las imágenes');
            } finally {
                submitBtn.disabled = false;
                submitBtn.innerHTML = '<i class="bi bi-upload"></i>';
            }
        });
    });

    // Manejar eliminación de imágenes
    document.addEventListener('click', async function(e) {
        if (e.target.closest('.delete-image-btn')) {
            const btn = e.target.closest('.delete-image-btn');
            const productSlug = btn.dataset.productSlug;
            const attributeValueId = btn.dataset.attributeValueId;
            const imageIndex = btn.dataset.imageIndex;

            if (!confirm('¿Eliminar esta imagen?')) {
                return;
            }

            try {
                const response = await fetch(`/admin/products/${productSlug}/attribute-images/${attributeValueId}/${imageIndex}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    }
                });

                const data = await response.json();

                if (data.success) {
                    updateGallery(attributeValueId, data.images);
                } else {
                    alert(data.message || 'Error al eliminar la imagen');
                }
            } catch (error) {
                console.error('Error:', error);
                alert('Error al eliminar la imagen');
            }
        }
    });

    // Función para actualizar la galería de imágenes
    function updateGallery(attributeValueId, images) {
        const gallery = document.querySelector(`.images-gallery[data-attribute-value-id="${attributeValueId}"]`);
        const card = document.querySelector(`.attribute-image-card[data-attribute-value-id="${attributeValueId}"]`);
        const countBadge = card.querySelector('.images-count');
        const productSlug = card.querySelector('.upload-form').dataset.productSlug;

        // Actualizar contador
        countBadge.textContent = `${images.length} img`;

        if (images.length === 0) {
            gallery.innerHTML = `
                <div class="text-center text-muted py-3 no-images-message">
                    <i class="bi bi-image fs-1 opacity-25 d-block mb-2"></i>
                    <small>Sin imágenes asignadas</small>
                </div>
            `;
        } else {
            let html = '<div class="row g-2">';
            images.forEach((imageUrl, index) => {
                html += `
                    <div class="col-4 image-item" data-index="${index}">
                        <div class="position-relative">
                            <img src="${imageUrl}"
                                 alt="Imagen ${index + 1}"
                                 class="img-fluid rounded border"
                                 style="aspect-ratio: 1; object-fit: cover; width: 100%;">
                            <button type="button"
                                    class="btn btn-danger btn-sm position-absolute top-0 end-0 delete-image-btn"
                                    data-product-slug="${productSlug}"
                                    data-attribute-value-id="${attributeValueId}"
                                    data-image-index="${index}"
                                    style="transform: translate(25%, -25%); padding: 0.125rem 0.375rem; font-size: 0.75rem;">
                                <i class="bi bi-x"></i>
                            </button>
                        </div>
                    </div>
                `;
            });
            html += '</div>';
            gallery.innerHTML = html;
        }
    }
});
</script>
@endpush
