@extends('layouts.app')

@section('title', 'Respaldos de Base de Datos')

@section('content')
<div class="container-fluid">
    <div class="page-header">
        <h3><i class="bi bi-database-check"></i> Gestión de Respaldos</h3>
        <p class="text-muted">Administra las copias de seguridad de las bases de datos del sistema</p>
    </div>

    @php
        // Usar el helper puede() para verificar permisos
        $puedeVer = auth()->user()->puede('seguridad', 'respaldos', 'ver');
        $puedeCrear = auth()->user()->puede('seguridad', 'respaldos', 'crear');
        $puedeDescargar = auth()->user()->puede('seguridad', 'respaldos', 'editar');
        $puedeEliminar = auth()->user()->puede('seguridad', 'respaldos', 'eliminar');
    @endphp

    @if($puedeVer)
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h3 class="card-title mb-0">
                <i class="bi bi-database"></i> Respaldos de Base de Datos
            </h3>
            @if($puedeCrear)
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalSeleccionBD">
                <i class="bi bi-plus-circle"></i> Generar Nuevo Respaldo
            </button>
            @endif
        </div>
        <div class="card-body">
            <div class="alert alert-info">
                <i class="bi bi-info-circle"></i>
                Los respaldos se generan en formato .bak y se guardan en el servidor.
                <!--
                <br>
                <small>Al descargar, se abrirá un diálogo para elegir dónde guardar el archivo.</small>
                -->
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
                                @if($puedeDescargar)
                                <button type="button" class="btn btn-success btn-sm" 
                                        onclick="descargarRespaldo('{{ $backup['filename'] }}')" 
                                        title="Descargar">
                                    <i class="bi bi-download"></i>
                                </button>
                                @endif
                                @if($puedeEliminar)
                                <button type="button" class="btn btn-danger btn-sm" 
                                        onclick="confirmarEliminar('respaldo', '{{ $backup['filename'] }}', '{{ $backup['filename'] }}')" 
                                        title="Eliminar">
                                    <i class="bi bi-trash"></i>
                                </button>
                                @endif
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
    @else
    <div class="alert alert-warning">
        <i class="bi bi-exclamation-triangle"></i> No tienes permiso para acceder a este módulo.
    </div>
    @endif
</div>
@include('seguridad.respaldos.partials.seleccionar_bd')

@push('scripts')
<script>
    // Función para descargar respaldo con diálogo "Guardar como" nativo (para navegadores compatibles)
    async function descargarRespaldo(filename) {
        // Mostrar toast de preparacion
        if (window.mostrarToast) {
            window.mostrarToast('Iniciando descarga...', 'warning');
        }
        
        // Deshabilitar el boton para evitar clicks multiples
        const buttons = document.querySelectorAll(`[onclick*="descargarRespaldo('${filename}')"]`);
        buttons.forEach(btn => {
            btn.disabled = true;
            const icon = btn.querySelector('i');
            if (icon) {
                icon.className = 'bi bi-hourglass-split fa-spin';
            }
        });
        
        try {
            // Crear un enlace invisible y hacer clic en él
            const link = document.createElement('a');
            link.href = `/seguridad/respaldos/download/${filename}`;
            link.download = filename;
            link.style.display = 'none';
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
            
            if (window.mostrarToast) {
                window.mostrarToast('Descarga iniciada', 'success');
            }
        } catch (error) {
            console.error('Error al descargar:', error);
            if (window.mostrarToast) {
                window.mostrarToast('Error al iniciar la descarga', 'danger');
            }
        } finally {
            // Restaurar botones
            buttons.forEach(btn => {
                btn.disabled = false;
                const icon = btn.querySelector('i');
                if (icon) {
                    icon.className = 'bi bi-download';
                }
            });
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

    // Función para limpiar el estado del modal y loading
    function limpiarEstadoRespaldo() {
        // Ocultar loading
        const loadingIndicator = document.getElementById('loadingIndicator');
        if (loadingIndicator) loadingIndicator.style.display = 'none';
        
        // Rehabilitar botón
        const btnConfirmar = document.getElementById('btnConfirmarRespaldo');
        if (btnConfirmar) btnConfirmar.disabled = false;
        
        // ============================================
        // LIMPIAR MODAL - SIN ELIMINAR DEL DOM
        // ============================================
        
        // 1. Obtener el modal
        const modalElement = document.getElementById('modalSeleccionBD');
        if (modalElement) {
            // Obtener la instancia de Bootstrap
            let modal = null;
            try {
                modal = bootstrap.Modal.getInstance(modalElement);
            } catch (e) {
                // Ignorar
            }
            
            if (modal) {
                // Si existe instancia, ocultar correctamente (sin destruir)
                modal.hide();
            } else {
                // Si no hay instancia, ocultar manualmente
                modalElement.classList.remove('show');
                modalElement.style.display = 'none';
            }
        }
        
        // 2. Eliminar SOLO los backdrops (no el modal)
        const backdrops = document.querySelectorAll('.modal-backdrop');
        backdrops.forEach(backdrop => backdrop.remove());
        
        // 3. Limpiar el body
        document.body.classList.remove('modal-open');
        document.body.style.overflow = '';
        document.body.style.paddingRight = '';
        document.body.style.position = '';
    }
    
    // Evento para confirmar respaldo
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
                
                // Cerrar modal (solo ocultar, no destruir)
                const modalElement = document.getElementById('modalSeleccionBD');
                const modal = bootstrap.Modal.getInstance(modalElement);
                if (modal) {
                    modal.hide();
                } else {
                    modalElement.classList.remove('show');
                    modalElement.style.display = 'none';
                    document.querySelectorAll('.modal-backdrop').forEach(el => el.remove());
                    document.body.classList.remove('modal-open');
                }
                
                // Mostrar loading
                const loadingIndicator = document.getElementById('loadingIndicator');
                if (loadingIndicator) loadingIndicator.style.display = 'block';
                
                // Deshabilitar el botón
                btnConfirmar.disabled = true;
                
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
                    if (loadingIndicator) loadingIndicator.style.display = 'none';
                    
                    if (data.success) {
                        if (window.mostrarToast) window.mostrarToast(data.message, 'success');
                        setTimeout(() => location.reload(), 2000);
                    } else {
                        if (window.mostrarToast) window.mostrarToast(data.message || 'Error al generar respaldos', 'danger');
                        btnConfirmar.disabled = false;
                        limpiarEstadoRespaldo();
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    limpiarEstadoRespaldo();
                    if (window.mostrarToast) {
                        window.mostrarToast('Error de conexión al generar respaldos', 'danger');
                    }
                });
            });
        }
    });
</script>
@endpush
@endsection