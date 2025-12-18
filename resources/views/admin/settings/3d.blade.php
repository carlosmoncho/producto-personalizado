@extends('layouts.admin')

@section('title', 'Configuración 3D')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3">Configuración 3D</h1>
    </div>

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

    <div class="row">
        <!-- Lista de HDRIs -->
        <div class="col-lg-8">
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-lightbulb me-2"></i>
                        HDRIs Disponibles
                    </h5>
                    @if($activeHdri)
                        <span class="badge bg-success">
                            <i class="bi bi-check-circle me-1"></i>
                            Activo: {{ $activeHdri->name }}
                        </span>
                    @else
                        <span class="badge bg-warning text-dark">
                            <i class="bi bi-exclamation-triangle me-1"></i>
                            Sin HDRI activo
                        </span>
                    @endif
                </div>
                <div class="card-body">
                    @if($hdriFiles->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead>
                                    <tr>
                                        <th>Estado</th>
                                        <th>Nombre</th>
                                        <th>Archivo</th>
                                        <th>Tamaño</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($hdriFiles as $hdri)
                                        <tr class="{{ $hdri->is_active ? 'table-success' : '' }}">
                                            <td>
                                                @if($hdri->is_active)
                                                    <span class="badge bg-success">
                                                        <i class="bi bi-check-circle"></i> Activo
                                                    </span>
                                                @else
                                                    <span class="badge bg-secondary">Inactivo</span>
                                                @endif
                                            </td>
                                            <td>
                                                <strong>{{ $hdri->name }}</strong>
                                            </td>
                                            <td>
                                                <small class="text-muted">{{ $hdri->original_filename }}</small>
                                            </td>
                                            <td>
                                                <small>{{ $hdri->formatted_size }}</small>
                                            </td>
                                            <td>
                                                <div class="btn-group btn-group-sm">
                                                    @if(!$hdri->is_active)
                                                        <form action="{{ route('admin.settings.hdri.activate', $hdri) }}" method="POST" class="d-inline">
                                                            @csrf
                                                            <button type="submit" class="btn btn-outline-success" title="Activar">
                                                                <i class="bi bi-check-lg"></i>
                                                            </button>
                                                        </form>
                                                    @endif
                                                    <form action="{{ route('admin.settings.hdri.delete', $hdri) }}" method="POST" class="d-inline">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-outline-danger"
                                                                onclick="return confirm('¿Eliminar {{ $hdri->name }}?')" title="Eliminar">
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

                        @if($activeHdri)
                            <div class="mt-3">
                                <form action="{{ route('admin.settings.hdri.deactivate') }}" method="POST">
                                    @csrf
                                    <button type="submit" class="btn btn-outline-warning btn-sm">
                                        <i class="bi bi-x-circle me-1"></i>
                                        Desactivar HDRI (usar iluminación básica)
                                    </button>
                                </form>
                            </div>
                        @endif
                    @else
                        <div class="alert alert-info mb-0">
                            <i class="bi bi-info-circle me-2"></i>
                            No hay HDRIs subidos. Sube uno usando el formulario de la derecha.
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Formulario para subir nuevo HDRI -->
        <div class="col-lg-4">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-upload me-2"></i>
                        Subir nuevo HDRI
                    </h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.settings.hdri.upload') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="mb-3">
                            <label for="name" class="form-label">Nombre identificador</label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror"
                                   id="name" name="name" placeholder="Ej: Estudio cálido" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="mb-3">
                            <label for="hdri" class="form-label">Archivo HDRI</label>
                            <input type="file" class="form-control @error('hdri') is-invalid @enderror"
                                   id="hdri" name="hdri" accept=".hdr,.exr" required>
                            @error('hdri')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">Formatos: .hdr, .exr (máx. 50MB)</div>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="bi bi-upload me-1"></i>
                            Subir HDRI
                        </button>
                    </form>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-info-circle me-2"></i>
                        Información
                    </h5>
                </div>
                <div class="card-body">
                    <h6>¿Qué es un HDRI?</h6>
                    <p class="text-muted small">
                        Un HDRI (High Dynamic Range Image) es una imagen panorámica de 360°
                        que ilumina objetos 3D de forma realista.
                    </p>

                    <h6>Recomendaciones</h6>
                    <ul class="text-muted small">
                        <li>Resolución: 2K-4K</li>
                        <li>Tipo: "Studio" o "Interior"</li>
                        <li>Formato: .hdr (más compatible)</li>
                    </ul>

                    <a href="https://polyhaven.com/hdris" target="_blank" class="btn btn-sm btn-outline-secondary w-100">
                        <i class="bi bi-box-arrow-up-right me-1"></i>
                        Descargar HDRIs gratis (Poly Haven)
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
