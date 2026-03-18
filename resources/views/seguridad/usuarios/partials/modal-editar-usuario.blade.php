<!-- Modal Editar Usuario -->
<div class="modal fade" id="modalEditarUsuario" tabindex="-1" aria-labelledby="modalEditarUsuarioLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalEditarUsuarioLabel">
                    <i class="bi bi-pencil-square"></i> Editar Usuario
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="formEditarUsuario">
                    @csrf
                    @method('PUT')
                    <input type="hidden" id="edit_usuario_id" name="usuario_id">
                    
                    <!-- Datos personales -->
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Nombre <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="edit_Nombre" name="Nombre"
                                    onkeydown="return soloLetras(event)"
                                    oninput="aMayusculas(event)"
                                    required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Ap. Paterno <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="edit_ApPaterno" name="ApPaterno"
                                    onkeydown="return soloLetras(event)"
                                    oninput="aMayusculas(event)"
                                    required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Ap. Materno</label>
                            <input type="text" class="form-control" id="edit_ApMaterno" name="ApMaterno"
                                    onkeydown="return soloLetras(event)"
                                    oninput="aMayusculas(event)">
                        </div>
                    </div>

                    <!-- Datos de cuenta -->
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Usuario <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="edit_usuario" name="usuario" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Nueva Contraseña</label>
                            <input type="password" class="form-control" id="edit_passw" name="passw" placeholder="Dejar vacío para no cambiar">
                        </div>
                    </div>

                    <!-- Contacto -->
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Email / Contacto</label>
                            <input type="email" class="form-control" id="edit_contacto" name="contacto">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Teléfono Móvil</label>
                            <input type="text" class="form-control" id="edit_TelefonoMovil" name="TelefonoMovil"
                                    onkeydown="return soloNumeros(event)">
                        </div>
                    </div>

                    <!-- Dirección -->
                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <label class="form-label">Dirección</label>
                            <input type="text" class="form-control" id="edit_Direccion" name="Direccion">
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Localidad</label>
                            <input type="text" class="form-control" id="edit_Localidad" name="Localidad">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Municipio</label>
                            <input type="text" class="form-control" id="edit_Municipio" name="Municipio">
                        </div>
                    </div>

                    <!-- Datos adicionales -->
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">CURP</label>
                            <input type="text" class="form-control" id="edit_curp" name="curp" maxlength="18"
                                    oninput="aMayusculas(event)">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Fecha Nacimiento</label>
                            <input type="date" class="form-control" id="edit_fecha_nacimiento" name="fecha_nacimiento">
                        </div>
                    </div>

                    <!-- Estado y sucursal -->
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Estado</label>
                            <select class="form-select" id="edit_Activo" name="Activo">
                                <option value="1">Activo</option>
                                <option value="0">Inactivo</option>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Sucursal Origen</label>
                            <input type="number" class="form-control" id="edit_sucursal_origen" name="sucursal_origen" readonly>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Sucursal Asignada</label>
                            <select class="form-select" id="sucursal_asignada" name="sucursal_asignada">
                                <option value="0">Seleccionar</option>
                                <option value="1">Sucursal Mercado</option>
                                <option value="2">Sucursal Jardin</option>
                                <option value="3">Sucursal Zacatipan</option>
                                <option value="4">Sucursal Boulevard</option>
                                <option value="5">Sucursal smg</option>
                                <option value="6">Sucursal sfo</option>
                                <option value="7">Sucursal hug</option>
                                <option value="8">Sucursal huc</option>
                            </select>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-warning" onclick="guardarEdicionUsuario()">Guardar cambios</button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
window.guardarEdicionUsuario = function() {
    const id = document.getElementById('edit_usuario_id').value;
    
    const formData = {
        Nombre: document.getElementById('edit_Nombre')?.value || '',
        ApPaterno: document.getElementById('edit_ApPaterno')?.value || '',
        ApMaterno: document.getElementById('edit_ApMaterno')?.value || null,
        usuario: document.getElementById('edit_usuario')?.value || '',
        contacto: document.getElementById('edit_contacto')?.value || null,
        TelefonoMovil: document.getElementById('edit_TelefonoMovil')?.value || null,
        Direccion: document.getElementById('edit_Direccion')?.value || null,
        Localidad: document.getElementById('edit_Localidad')?.value || null,
        Municipio: document.getElementById('edit_Municipio')?.value || null,
        curp: document.getElementById('edit_curp')?.value || null,
        fecha_nacimiento: document.getElementById('edit_fecha_nacimiento')?.value || null,
        Activo: document.getElementById('edit_Activo')?.value || 1,
        sucursal_origen: document.getElementById('edit_sucursal_origen')?.value || 0,
        sucursal_asignada: document.getElementById('edit_sucursal_asignada')?.value || null,
        _token: '{{ csrf_token() }}',
        _method: 'PUT'
    };

    // Solo incluir passw si se ingresó una nueva contraseña
    const passw = document.getElementById('edit_passw')?.value;
    if (passw) {
        formData.passw = passw;
    }

    if (!formData.Nombre || !formData.ApPaterno || !formData.usuario) {
        if (window.mostrarToast) window.mostrarToast('Completa los campos requeridos', 'warning');
        return;
    }

    fetch(`/seguridad/usuarios/${id}`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify(formData)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const modal = bootstrap.Modal.getInstance(document.getElementById('modalEditarUsuario'));
            modal.hide();
            if (window.mostrarToast) window.mostrarToast('Usuario actualizado', 'success');
            setTimeout(() => location.reload(), 1000);
        } else {
            if (window.mostrarToast) window.mostrarToast(data.message || 'Error al actualizar', 'danger');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        if (window.mostrarToast) window.mostrarToast('Error de conexión', 'danger');
    });
};

// Cargar datos al abrir el modal
document.addEventListener('DOMContentLoaded', function() {
    const modalEditar = document.getElementById('modalEditarUsuario');
    if (modalEditar) {
        modalEditar.addEventListener('show.bs.modal', function(event) {
            const button = event.relatedTarget;
            const usuarioId = button.getAttribute('data-usuario-id');
            
            fetch(`/seguridad/usuarios/${usuarioId}/edit`, {
                headers: { 'Accept': 'application/json' }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    document.getElementById('edit_usuario_id').value = data.data.id;
                    document.getElementById('edit_Nombre').value = data.data.Nombre || '';
                    document.getElementById('edit_ApPaterno').value = data.data.ApPaterno || '';
                    document.getElementById('edit_ApMaterno').value = data.data.ApMaterno || '';
                    document.getElementById('edit_usuario').value = data.data.usuario || '';
                    document.getElementById('edit_contacto').value = data.data.contacto || '';
                    document.getElementById('edit_TelefonoMovil').value = data.data.TelefonoMovil || '';
                    document.getElementById('edit_Direccion').value = data.data.Direccion || '';
                    document.getElementById('edit_Localidad').value = data.data.Localidad || '';
                    document.getElementById('edit_Municipio').value = data.data.Municipio || '';
                    document.getElementById('edit_curp').value = data.data.curp || '';
                    document.getElementById('edit_fecha_nacimiento').value = data.data.fecha_nacimiento || '';
                    document.getElementById('edit_Activo').value = data.data.Activo ? '1' : '0';
                    document.getElementById('edit_sucursal_origen').value = data.data.sucursal_origen || 0;
                    document.getElementById('edit_sucursal_asignada').value = data.data.sucursal_asignada || '';
                }
            })
            .catch(error => console.error('Error:', error));
        });
    }
});
</script>
@endpush