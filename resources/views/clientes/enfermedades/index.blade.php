@extends('layouts.app')

@section('title', 'Patologías - CRM')
@section('page-title', 'Registro de Patologías')

@section('content')
<div class="container-fluid">
    <div class="page-header">
        <h3><i class="bi bi-heart-pulse"></i> Registro de Patologías</h3>
        <p class="text-muted">Gestiona el catálogo de patologías registradas</p>
    </div>

    @php
        $puedeVer = $permisos['ver'] ?? false;
        $puedeCrear = $permisos['crear'] ?? false;
        $puedeEditar = $permisos['editar'] ?? false;
        $puedeEliminar = $permisos['eliminar'] ?? false;
    @endphp

    @if($puedeVer || $puedeCrear)
    <div class="row mb-4">
        <div class="col-md-6">
            @if($puedeVer)
            <div class="search-box">
                <i class="bi bi-search"></i>
                <input type="text" class="form-control" id="buscarPatologia" placeholder="Buscar patología..." autocomplete="off">
                <button type="button" class="bi bi-x-circle-fill" id="limpiarBuscarPatologia" 
                style="position: absolute; right: 15px; top: 50%; transform: translateY(-50%); z-index: 10; color: #6c757d; cursor: pointer; display: none; font-size: 1.2rem; background: none; border: none; padding: 0; line-height: 1;"
                title="Limpiar búsqueda"></button>
            </div>
            @endif
        </div>
        <div class="col-md-6 text-end">
            @if($puedeCrear)
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalNuevaPatologia">
                <i class="bi bi-plus-circle"></i> Nueva Patología
            </button>
            @endif
        </div>
    </div>
    @endif

    @if($puedeVer)
    <div id="patologias-table-container">@include('clientes.enfermedades.partials.tabla')</div>
    @elseif($puedeCrear)
    <div class="card">
        <div class="card-body text-center py-5">
            <i class="bi bi-heart-pulse" style="font-size: 3rem; color: #ccc;"></i>
            <p class="text-muted mt-3">No tienes permiso para ver el listado de patologías, pero puedes crear nuevas.</p>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalNuevaPatologia">
                <i class="bi bi-plus-circle"></i> Crear nueva patología
            </button>
        </div>
    </div>
    @else
    <div class="alert alert-warning">
        <i class="bi bi-exclamation-triangle"></i> No tienes permiso para acceder a este módulo.
    </div>
    @endif
</div>

<!-- Modals -->
@include('clientes.enfermedades.partials.modal-nueva-patologia')
@include('clientes.enfermedades.partials.modal-editar-patologia')
@endsection

@push('scripts')
<script>
// ============================================
// FUNCIONES PARA LA VISTA DE PATOLOGÍAS
// ============================================

let patologiaActualId = null;
let timeoutIdPatologia;

function editarPatologia(id) {
    patologiaActualId = id;
    
    fetch(`/enfermedades/${id}/edit`, {
        headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            document.getElementById('edit_patologia_id').value = data.data.id_patologia;
            document.getElementById('edit_patologia_descripcion').value = data.data.descripcion;
            
            const modal = new bootstrap.Modal(document.getElementById('modalEditarPatologia'));
            modal.show();
        } else {
            if (window.mostrarToast) window.mostrarToast('Error al cargar los datos', 'danger');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        if (window.mostrarToast) window.mostrarToast('Error de conexión', 'danger');
    });
}

let versionPatologias = 0;
window.refrescarListadoPatologias = async function() {
    const contenedor = document.getElementById('patologias-table-container');
    if (!contenedor) return;
    const version = ++versionPatologias;
    const url = new URL(window.location.href);
    const termino = document.getElementById('buscarPatologia').value.trim();
    url.searchParams.set('q', termino);
    if (termino) url.searchParams.delete('page');
    contenedor.setAttribute('aria-busy', 'true');
    try {
        const respuesta = await fetch(url.href, { headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' }, cache: 'no-store' });
        if (!respuesta?.ok) throw new Error('Consulta fallida');
        const datos = await respuesta.json();
        if (typeof datos.html !== 'string') throw new Error('Respuesta inesperada');
        if (version !== versionPatologias) return;
        contenedor.innerHTML = datos.html;
        window.inicializarSelectorPerPage?.('perPageSelect');
    } catch {
        if (version === versionPatologias) window.mostrarToast?.('No se pudo actualizar el listado de patologías.', 'danger');
    } finally {
        if (version === versionPatologias) contenedor.removeAttribute('aria-busy');
    }
};
const buscadorPatologias = document.getElementById('buscarPatologia');
buscadorPatologias?.addEventListener('input', () => {
    clearTimeout(timeoutIdPatologia);
    ++versionPatologias;
    document.getElementById('limpiarBuscarPatologia').style.display = buscadorPatologias.value ? '' : 'none';
    timeoutIdPatologia = setTimeout(window.refrescarListadoPatologias, 300);
});
document.getElementById('limpiarBuscarPatologia')?.addEventListener('click', () => {
    buscadorPatologias.value = '';
    buscadorPatologias.dispatchEvent(new Event('input'));
    buscadorPatologias.focus();
});

document.addEventListener('DOMContentLoaded', function() {
    window.inicializarSelectorPerPage('perPageSelect');
});
</script>
@endpush