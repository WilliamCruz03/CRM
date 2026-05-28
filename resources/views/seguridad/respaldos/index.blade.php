@extends('layouts.app')

@section('title', 'Respaldos de Base de Datos')

@section('content')
<div class="container-fluid">
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h3 class="card-title mb-0">
                <i class="bi bi-database"></i> Respaldos de Base de Datos
            </h3>
            <button type="button" class="btn btn-primary" id="btnGenerarRespaldo">
                <i class="bi bi-plus-circle"></i> Generar Nuevo Respaldo
            </button>
        </div>
        <div class="card-body">
            <div class="alert alert-info">
                <i class="bi bi-info-circle"></i>
                Los respaldos se generan en formato ZIP y contienen ambas bases de datos (CRM y Ventas).
                <br>
                <small>Los respaldos antiguos se eliminan automáticamente después de 7 días.</small>
            </div>

            <div id="loadingIndicator" class="text-center my-4" style="display: none;">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Generando respaldo...</span>
                </div>
                <p class="mt-2">Generando respaldo, esto puede tomar varios minutos...</p>
            </div>

            <div class="table-responsive">
                <table class="table table-bordered table-striped" id="backupsTable">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Nombre del Archivo</th>
                            <th>Fecha</th>
                            <th>Tamaño</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($backups as $index => $backup)
                        <tr>
                            <td style="text-align: center">{{ $index + 1 }}</td>
                            <td>{{ $backup['filename'] }}</td>
                            <td style="text-align: center">{{ $backup['date'] }}</td>
                            <td style="text-align: right">{{ $backup['size'] }}</td>
                            <td style="text-align: center">
                                <a href="{{ route('seguridad.respaldos.download', ['filename' => $backup['filename']]) }}" 
                                   class="btn btn-success btn-sm" title="Descargar">
                                    <i class="bi bi-download"></i>
                                </a>
                                <button type="button" class="btn btn-danger btn-sm" 
                                        onclick="eliminarRespaldo('{{ $backup['filename'] }}')" title="Eliminar">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center">No hay respaldos disponibles. Genere el primer respaldo.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.getElementById('btnGenerarRespaldo').addEventListener('click', function() {
        const loadingIndicator = document.getElementById('loadingIndicator');
        const btn = this;
        
        btn.disabled = true;
        loadingIndicator.style.display = 'block';
        
        fetch('{{ route("seguridad.respaldos.store") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                if (window.mostrarToast) {
                    window.mostrarToast('Respaldo generado correctamente', 'success');
                }
                setTimeout(() => location.reload(), 2000);
            } else {
                if (window.mostrarToast) {
                    window.mostrarToast(data.message || 'Error al generar respaldo', 'danger');
                }
                btn.disabled = false;
                loadingIndicator.style.display = 'none';
            }
        })
        .catch(error => {
            console.error('Error:', error);
            if (window.mostrarToast) {
                window.mostrarToast('Error de conexión', 'danger');
            }
            btn.disabled = false;
            loadingIndicator.style.display = 'none';
        });
    });

    function eliminarRespaldo(filename) {
        if (confirm('¿Está seguro de eliminar este respaldo? Esta acción no se puede deshacer.')) {
            fetch(`{{ route("seguridad.respaldos.destroy", '') }}/${filename}`, {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    if (window.mostrarToast) {
                        window.mostrarToast('Respaldo eliminado correctamente', 'success');
                    }
                    setTimeout(() => location.reload(), 1000);
                } else {
                    if (window.mostrarToast) {
                        window.mostrarToast(data.message || 'Error al eliminar', 'danger');
                    }
                }
            })
            .catch(error => {
                console.error('Error:', error);
                if (window.mostrarToast) {
                    window.mostrarToast('Error de conexión', 'danger');
                }
            });
        }
    }
</script>
@endpush
@endsection