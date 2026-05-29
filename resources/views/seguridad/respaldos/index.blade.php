@extends('layouts.app')

@section('title', 'Respaldos de Base de Datos')

@section('content')
<div class="container-fluid">
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h3 class="card-title mb-0">
                <i class="bi bi-database"></i> Respaldos de Base de Datos
            </h3>
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalSeleccionBD">
                <i class="bi bi-plus-circle"></i> Generar Nuevo Respaldo
            </button>
        </div>
        <div class="card-body">
            <div class="alert alert-info">
                <i class="bi bi-info-circle"></i>
                Los respaldos se generan en formato .bak y se guardan en el servidor.
                <br>
                <small>Al descargar, se abrirá un diálogo para elegir dónde guardar el archivo.</small>
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
                            <th>Base de Datos</th>
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
                            <td><span class="badge bg-primary">{{ $backup['database'] }}</span></td>
                            <td>{{ $backup['filename'] }}</td>
                            <td style="text-align: center">{{ $backup['date'] }}</td>
                            <td style="text-align: right">{{ $backup['size'] }}</td>
                            <td style="text-align: center">
                                <button type="button" class="btn btn-success btn-sm" 
                                        onclick="descargarRespaldo('{{ $backup['filename'] }}')" 
                                        title="Descargar">
                                    <i class="bi bi-download"></i>
                                </button>
                                <button type="button" class="btn btn-danger btn-sm" 
                                        onclick="confirmarEliminar('respaldo', '{{ $backup['filename'] }}', '{{ $backup['filename'] }}')" 
                                        title="Eliminar">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center">No hay respaldos disponibles. Genere el primer respaldo.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

@include('seguridad.respaldos.partials.seleccionar_bd')

@push('scripts')
<script>
    // Función para descargar respaldo con diálogo "Guardar como" nativo (para navegadores compatibles)
    async function descargarRespaldo(filename) {
        // Mostrar toast de preparación
        if (window.mostrarToast) {
            window.mostrarToast('Preparando archivo...', 'warning');
        }
        
        try {
            // 1. Obtener el archivo del servidor
            const response = await fetch(`/seguridad/respaldos/download/${filename}`);
            if (!response.ok) {
                throw new Error('Error al obtener el archivo del servidor');
            }
            const blob = await response.blob();
            
            // 2. Verificar si el navegador soporta la API showSaveFilePicker
            if ('showSaveFilePicker' in window) {
                try {
                    // Método moderno: Abre el diálogo "Guardar como" nativo del sistema
                    const fileHandle = await window.showSaveFilePicker({
                        suggestedName: filename,
                        types: [{
                            description: 'Archivo de respaldo',
                            accept: { 'application/octet-stream': ['.bak'] }
                        }]
                    });
                    
                    // Escribir el contenido en el archivo seleccionado
                    const writable = await fileHandle.createWritable();
                    await writable.write(blob);
                    await writable.close();
                    
                    if (window.mostrarToast) {
                        window.mostrarToast(`Respaldo guardado correctamente`, 'success');
                    }
                    return; // Salir, la descarga fue exitosa
                    
                } catch (err) {
                    // Si el usuario cancela el diálogo, no hacemos nada
                    if (err.name === 'AbortError') {
                        if (window.mostrarToast) {
                            window.mostrarToast('Descarga cancelada', 'info');
                        }
                        return;
                    }
                    // Otro error con la API showSaveFilePicker, mostramos y pasamos al fallback
                    console.error('Error con showSaveFilePicker:', err);
                    if (window.mostrarToast) {
                        window.mostrarToast('Usando método alternativo de descarga...', 'warning');
                    }
                }
            }
            
            // 3. Método clásico (FALLBACK) - Para navegadores que no soportan la API showSaveFilePicker
            // o cuando la API showSaveFilePicker falla
            const url = window.URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = filename;
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);
            window.URL.revokeObjectURL(url);
            
            if (window.mostrarToast) {
                window.mostrarToast('Descarga iniciada (método estándar)', 'success');
            }
            
        } catch (error) {
            console.error('Error al descargar:', error);
            if (window.mostrarToast) {
                window.mostrarToast('Error al descargar el archivo', 'danger');
            }
        }
    }

    // Función para eliminar respaldo
    window.ejecutarEliminarRespaldo = function(filename, nombreArchivo) {
        fetch(`/seguridad/respaldos/${filename}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content'),
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Eliminar fila de la tabla
                const fila = document.getElementById(`respaldo-row-${filename.replace(/\./g, '-')}`);
                if (fila) fila.remove();
                if (window.mostrarToast) window.mostrarToast(`Respaldo "${nombreArchivo}" eliminado correctamente`, 'success');
                setTimeout(() => location.reload(), 1000);
            } else {
                const errorMsg = data.message || 'Error al eliminar el respaldo';
                if (window.mostrarToast) window.mostrarToast(errorMsg, 'danger');
            }
        })
        .catch(error => {
            console.error('Error al eliminar:', error);
            if (window.mostrarToast) window.mostrarToast('Error de conexión al eliminar el respaldo', 'danger');
        });
    };

    // Evento para confirmar respaldo (cuando se hace clic en "Generar Respaldos" dentro del modal)
    document.addEventListener('DOMContentLoaded', function() {
        const btnConfirmar = document.getElementById('btnConfirmarRespaldo');
        if (btnConfirmar) {
            btnConfirmar.addEventListener('click', function() {
                const selectedDatabases = [];
                document.querySelectorAll('#listaBasesDatos input[type="checkbox"]:checked').forEach(checkbox => {
                    selectedDatabases.push(checkbox.value);
                });
                
                if (selectedDatabases.length === 0) {
                    if (window.mostrarToast) window.mostrarToast('Debe seleccionar al menos una base de datos', 'warning');
                    return;
                }
                
                // Cerrar modal
                const modalElement = document.getElementById('modalSeleccionBD');
                const modal = bootstrap.Modal.getInstance(modalElement);
                if (modal) modal.hide();
                
                // Mostrar loading
                const loadingIndicator = document.getElementById('loadingIndicator');
                if (loadingIndicator) loadingIndicator.style.display = 'block';
                
                // Enviar petición
                fetch('{{ route("seguridad.respaldos.store") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ databases: selectedDatabases })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        if (window.mostrarToast) window.mostrarToast(data.message, 'success');
                        setTimeout(() => location.reload(), 2000);
                    } else {
                        if (window.mostrarToast) window.mostrarToast(data.message || 'Error al generar respaldos', 'danger');
                        if (loadingIndicator) loadingIndicator.style.display = 'none';
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    if (window.mostrarToast) window.mostrarToast('Error de conexión', 'danger');
                    if (loadingIndicator) loadingIndicator.style.display = 'none';
                });
            });
        }
    });
</script>
@endpush
@endsection