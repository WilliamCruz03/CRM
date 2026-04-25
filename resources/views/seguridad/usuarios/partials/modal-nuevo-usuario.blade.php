<!-- Modal Nuevo Usuario -->
<div class="modal fade" id="modalNuevoUsuario" tabindex="-1" aria-labelledby="modalNuevoUsuarioLabel">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalNuevoUsuarioLabel">
                    <i class="bi bi-person-plus"></i> Nuevo Usuario
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="formNuevoUsuario">
                    @csrf
                    
                    <!-- Datos personales -->
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Nombre <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="Nombre" name="Nombre"
                                    onkeydown="return soloLetras(event)"
                                    oninput="aMayusculas(event)"
                                    required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Ap. Paterno <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="ApPaterno" name="ApPaterno"
                                    onkeydown="return soloLetras(event)"
                                    oninput="aMayusculas(event)"
                                    required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Ap. Materno</label>
                            <input type="text" class="form-control" id="ApMaterno" name="ApMaterno"
                                    onkeydown="return soloLetras(event)"
                                    oninput="aMayusculas(event)">
                        </div>
                    </div>

                    <!-- Datos de cuenta -->
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Usuario <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="usuario" name="usuario" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Contraseña <span class="text-danger">*</span></label>
                            <input type="password" class="form-control" id="passw" name="passw" required>
                        </div>
                    </div>

                    <!-- Contacto -->
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Email / Contacto</label>
                            <input type="email" class="form-control" id="contacto" name="contacto">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Teléfono Móvil</label>
                            <input type="text" class="form-control" id="TelefonoMovil" name="TelefonoMovil"
                                    onkeydown="return soloNumeros(event)">
                        </div>
                    </div>

                    <!-- Dirección -->
                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <label class="form-label">Dirección</label>
                            <input type="text" class="form-control" id="Direccion" name="Direccion">
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Localidad</label>
                            <input type="text" class="form-control" id="Localidad" name="Localidad">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Municipio</label>
                            <input type="text" class="form-control" id="Municipio" name="Municipio">
                        </div>
                    </div>

                    <!-- Datos adicionales -->
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">CURP</label>
                            <input type="text" class="form-control" id="curp" name="curp" maxlength="18"
                                    oninput="aMayusculas(event)">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Fecha Nacimiento</label>
                            <input type="date" class="form-control" id="fecha_nacimiento" name="fecha_nacimiento">
                        </div>
                    </div>

                    <!-- Estado y sucursal -->
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Estado</label>
                            <select class="form-select" id="Activo" name="Activo">
                                <option value="1">Activo</option>
                                <option value="0">Inactivo</option>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Sucursal Asignada</label>
                            <select class="form-select" id="sucursal_asignada" name="sucursal_asignada">
                                <option value="0" selected>CRM (Sistema)</option>
                                <!-- Las opciones se cargarán dinámicamente desde JavaScript -->
                            </select>
                            <small class="text-muted">Selecciona "CRM" si el usuario opera desde el sistema central</small>
                        </div>
                    </div>

                    <!-- Fechas de alta -->
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Fecha Ingreso</label>
                            <input type="date" class="form-control" id="fecha_ingreso" name="fecha_ingreso">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Fecha Alta Sistema</label>
                            <input type="date" class="form-control" id="fecha_alta_sistema" name="fecha_alta_sistema">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Fecha Alta Seguro</label>
                            <input type="date" class="form-control" id="fecha_alta_seguro" name="fecha_alta_seguro">
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-success" onclick="guardarNuevoUsuario()">Guardar</button>
            </div>
        </div>
    </div>

    <!-- Campo oculto para sucursal_origen (siempre 0 = CRM) -->
<input type="hidden" id="sucursal_origen" name="sucursal_origen" value="0">
</div>

@push('scripts')
<script>
window.guardarNuevoUsuario = function() {
    // Obtener valor de sucursal_asignada, si está vacío o null, enviar 0
    let sucursalAsignada = document.getElementById('sucursal_asignada')?.value;
    if (sucursalAsignada === '' || sucursalAsignada === null) {
        sucursalAsignada = 0;
    }
    
    const formData = {
        Nombre: document.getElementById('Nombre')?.value || '',
        ApPaterno: document.getElementById('ApPaterno')?.value || '',
        ApMaterno: document.getElementById('ApMaterno')?.value || null,
        usuario: document.getElementById('usuario')?.value || '',
        passw: document.getElementById('passw')?.value || '',
        contacto: document.getElementById('contacto')?.value || null,
        TelefonoMovil: document.getElementById('TelefonoMovil')?.value || null,
        Direccion: document.getElementById('Direccion')?.value || null,
        Localidad: document.getElementById('Localidad')?.value || null,
        Municipio: document.getElementById('Municipio')?.value || null,
        curp: document.getElementById('curp')?.value || null,
        fecha_nacimiento: document.getElementById('fecha_nacimiento')?.value || null,
        Activo: document.getElementById('Activo')?.value || 1,
        sucursal_origen: document.getElementById('sucursal_origen')?.value || 0,
        sucursal_asignada: parseInt(sucursalAsignada), // Asegurar que sea número
        fecha_ingreso: document.getElementById('fecha_ingreso')?.value || null,
        fecha_alta_sistema: document.getElementById('fecha_alta_sistema')?.value || null,
        fecha_alta_seguro: document.getElementById('fecha_alta_seguro')?.value || null,
        _token: '{{ csrf_token() }}'
    };

    if (!formData.Nombre || !formData.ApPaterno || !formData.usuario || !formData.passw) {
        if (window.mostrarToast) window.mostrarToast('Completa los campos requeridos', 'warning');
        return;
    }

    fetch('/seguridad/usuarios', {
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
            const modal = bootstrap.Modal.getInstance(document.getElementById('modalNuevoUsuario'));
            modal.hide();
            if (window.mostrarToast) window.mostrarToast('Usuario creado correctamente', 'success');
            setTimeout(() => location.reload(), 1000);
        } else {
            if (window.mostrarToast) window.mostrarToast(data.message || 'Error al guardar', 'danger');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        if (window.mostrarToast) window.mostrarToast('Error de conexión', 'danger');
    });
};

// Función para cargar sucursales activas en el select del modal de nuevo usuario
function cargarSucursalesNuevoUsuario() {
    fetch('/sucursales/activas', {
        headers: { 'Accept': 'application/json' }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success && data.data) {
            const select = document.getElementById('sucursal_asignada');
            if (select) {
                let options = '<option value="0" selected>CRM (Sistema)</option>';
                data.data.forEach(sucursal => {
                    options += `<option value="${sucursal.id_sucursal}">Sucursal ${sucursal.nombre}</option>`;
                });
                select.innerHTML = options;
            }
        }
    })
    .catch(error => console.error('Error cargando sucursales:', error));
}

// Al abrir el modal de nuevo usuario, cargar las sucursales
const modalNuevoUsuario = document.getElementById('modalNuevoUsuario');
if (modalNuevoUsuario) {
    modalNuevoUsuario.addEventListener('show.bs.modal', function() {
        cargarSucursalesNuevoUsuario();
        // Resetear otros campos del formulario...
    });
}
</script>
@endpush