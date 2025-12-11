@extends('layouts.admin')

@section('title', 'Importar/Exportar Datos')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="bi bi-database me-2"></i>Importar/Exportar Datos</h2>
</div>

<div class="row">
    <!-- Export Section -->
    <div class="col-md-6 mb-4">
        <div class="card h-100">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="bi bi-download me-2"></i>Exportar Base de Datos</h5>
            </div>
            <div class="card-body">
                <p class="text-muted">
                    Descarga todos los datos de la base de datos en formato JSON.
                    Puedes usar este archivo para importar los datos en otra instalacion.
                </p>
                <div class="alert alert-info">
                    <i class="bi bi-info-circle me-2"></i>
                    <strong>Nota:</strong> Las imagenes base64 (design_image, preview_3d) se excluyen para reducir el tamano del archivo.
                </div>
                <a href="{{ route('admin.data-import.export') }}" class="btn btn-primary btn-lg w-100">
                    <i class="bi bi-download me-2"></i>Descargar JSON
                </a>
            </div>
        </div>
    </div>

    <!-- Import Section -->
    <div class="col-md-6 mb-4">
        <div class="card h-100">
            <div class="card-header bg-success text-white">
                <h5 class="mb-0"><i class="bi bi-upload me-2"></i>Importar Base de Datos</h5>
            </div>
            <div class="card-body">
                <p class="text-muted">
                    Sube un archivo JSON exportado previamente para importar los datos.
                    <strong class="text-danger">Esto reemplazara TODOS los datos existentes.</strong>
                </p>
                <div class="alert alert-warning">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    <strong>Atencion:</strong> Esta accion eliminara todos los datos actuales y los reemplazara con los del archivo.
                </div>
                <form action="{{ route('admin.data-import.import') }}" method="POST" enctype="multipart/form-data" id="importForm">
                    @csrf
                    <div class="mb-3">
                        <input type="file" class="form-control" name="file" accept=".json,.txt" required>
                    </div>
                    <button type="submit" class="btn btn-success btn-lg w-100" id="importBtn">
                        <i class="bi bi-upload me-2"></i>Importar Datos
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Current Data Summary -->
<div class="card">
    <div class="card-header">
        <h5 class="mb-0"><i class="bi bi-table me-2"></i>Estado Actual de la Base de Datos</h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-sm table-hover">
                <thead>
                    <tr>
                        <th>Tabla</th>
                        <th class="text-end">Registros</th>
                    </tr>
                </thead>
                <tbody>
                    @php $totalRecords = 0; @endphp
                    @foreach($tableCounts as $table => $count)
                        <tr>
                            <td><code>{{ $table }}</code></td>
                            <td class="text-end">
                                @if($count === 'Error')
                                    <span class="badge bg-danger">Error</span>
                                @else
                                    @php $totalRecords += $count; @endphp
                                    <span class="badge {{ $count > 0 ? 'bg-success' : 'bg-secondary' }}">{{ number_format($count) }}</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr class="table-dark">
                        <th>Total</th>
                        <th class="text-end">{{ number_format($totalRecords) }}</th>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>

<!-- Import Results -->
@if(session('results'))
<div class="card mt-4">
    <div class="card-header bg-info text-white">
        <h5 class="mb-0"><i class="bi bi-list-check me-2"></i>Resultados de la Importacion</h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-sm">
                <thead>
                    <tr>
                        <th>Tabla</th>
                        <th>Estado</th>
                        <th>Detalle</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach(session('results') as $table => $result)
                        <tr>
                            <td><code>{{ $table }}</code></td>
                            <td>
                                @if($result['status'] === 'success')
                                    <span class="badge bg-success">OK</span>
                                @else
                                    <span class="badge bg-danger">Error</span>
                                @endif
                            </td>
                            <td>
                                @if($result['status'] === 'success')
                                    {{ $result['count'] }} registros importados
                                @else
                                    {{ $result['message'] ?? 'Error desconocido' }}
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endif
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const importForm = document.getElementById('importForm');
    const importBtn = document.getElementById('importBtn');

    importForm.addEventListener('submit', function(e) {
        e.preventDefault();

        Swal.fire({
            title: 'Confirmar Importacion',
            html: `
                <div class="text-start">
                    <p><strong class="text-danger">Esta accion eliminara TODOS los datos actuales.</strong></p>
                    <p>Se importaran los datos del archivo JSON seleccionado.</p>
                    <p>Esta seguro de continuar?</p>
                </div>
            `,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#28a745',
            cancelButtonColor: '#6c757d',
            confirmButtonText: '<i class="bi bi-check-circle me-2"></i>Si, importar',
            cancelButtonText: '<i class="bi bi-x-circle me-2"></i>Cancelar',
            focusCancel: true
        }).then((result) => {
            if (result.isConfirmed) {
                importBtn.disabled = true;
                importBtn.innerHTML = '<i class="bi bi-hourglass-split me-2"></i>Importando...';
                importForm.submit();
            }
        });
    });
});
</script>
@endpush
