<!-- Modal Nuevo Usuario -->
<div class="modal fade" id="modalNuevoUsuario" tabindex="-1" aria-labelledby="modalNuevoUsuarioLabel" data-bs-backdrop="static">
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
                                    autocomplete="off"
                                    required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Ap. Paterno <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="ApPaterno" name="ApPaterno"
                                    onkeydown="return soloLetras(event)"
                                    oninput="aMayusculas(event)"
                                    autocomplete="off"
                                    required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Ap. Materno</label>
                            <input type="text" class="form-control" id="ApMaterno" name="ApMaterno"
                                    onkeydown="return soloLetras(event)"
                                    oninput="aMayusculas(event)"
                                    autocomplete="off">
                        </div>
                    </div>

                    <!-- Datos de cuenta -->
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Usuario <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="usuario" name="usuario" autocomplete="off" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Contraseña <span class="text-danger">*</span></label>
                            <div style="position: relative;">
                                <input type="password" class="form-control" id="passw" name="passw" autocomplete="new-password" required style="padding-right: 45px;">
                                <button type="button" id="togglePasswBtn" style="
                                    position: absolute;
                                    right: 0;
                                    top: 50%;
                                    transform: translateY(-50%);
                                    border: none;
                                    background: transparent;
                                    padding: 0 15px;
                                    cursor: pointer;
                                    color: #6c757d;
                                    z-index: 10;
                                    height: 100%;
                                    display: flex;
                                    align-items: center;
                                    justify-content: center;
                                    transition: color 0.2s;
                                " onmouseover="this.style.color='#333'" onmouseout="this.style.color='#6c757d'">
                                    <i class="bi bi-eye"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Contacto -->
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Email / Contacto</label>
                            <input type="email" class="form-control" id="contacto" name="contacto" autocomplete="off">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Teléfono Móvil</label>
                            <input type="text" class="form-control" id="TelefonoMovil" name="TelefonoMovil"
                                    onkeydown="return soloNumeros(event)"
                                    autocomplete="off">
                        </div>
                    </div>

                    <!-- Dirección -->
                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <label class="form-label">Dirección</label>
                            <input type="text" class="form-control" id="Direccion" name="Direccion" autocomplete="off">
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Localidad</label>
                            <input type="text" class="form-control" id="Localidad" name="Localidad" autocomplete="off">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Municipio</label>
                            <input type="text" class="form-control" id="Municipio" name="Municipio" autocomplete="off">
                        </div>
                    </div>

                    <!-- Datos adicionales -->
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">CURP</label>
                            <input type="text" class="form-control" id="curp" name="curp" maxlength="18"
                                    oninput="aMayusculas(event)" autocomplete="off">
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
                            <select class="form-select" id="Activo" name="Activo" autocomplete="off">
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
        sucursal_asignada: parseInt(sucursalAsignada),
        fecha_ingreso: document.getElementById('fecha_ingreso')?.value || null,
        fecha_alta_sistema: document.getElementById('fecha_alta_sistema')?.value || null,
        fecha_alta_seguro: document.getElementById('fecha_alta_seguro')?.value || null,
        _token: '{{ csrf_token() }}'
    };

    // Validaciones específicas por campo
    if (!formData.Nombre) {
        if (window.mostrarToast) window.mostrarToast('El nombre es obligatorio', 'warning');
        return;
    }
    
    if (!formData.ApPaterno) {
        if (window.mostrarToast) window.mostrarToast('El apellido paterno es obligatorio', 'warning');
        return;
    }
    
    if (!formData.usuario) {
        if (window.mostrarToast) window.mostrarToast('El nombre de usuario es obligatorio', 'warning');
        return;
    }
    
    if (!formData.passw) {
        if (window.mostrarToast) window.mostrarToast('La contraseña es obligatoria', 'warning');
        return;
    }
    
    if (formData.passw.length < 3) {
        if (window.mostrarToast) window.mostrarToast('La contraseña debe tener al menos 3 caracteres', 'warning');
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
            let mensajeError = data.message || 'Error al guardar';
            
            if (data.errors) {
                const primerCampo = Object.keys(data.errors)[0];
                if (primerCampo) {
                    const primerError = data.errors[primerCampo];
                    if (Array.isArray(primerError) && primerError.length > 0) {
                        mensajeError = primerError[0];
                    }
                }
            }
            
            if (window.mostrarToast) window.mostrarToast(mensajeError, 'danger');
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

// MODAL NUEVO USUARIO - Eventos
const modalNuevoUsuario = document.getElementById('modalNuevoUsuario');
if (modalNuevoUsuario) {
    // Evento al abrir: cargar sucursales
    modalNuevoUsuario.addEventListener('show.bs.modal', function() {
        // Limpiar campos ANTES de mostrar (evita autocompletado de Edge)
        document.getElementById('usuario').value = '';
        document.getElementById('passw').value = '';
        cargarSucursalesNuevoUsuario();
    });
    
    // Evento al cerrar: limpiar todos los campos
    modalNuevoUsuario.addEventListener('hidden.bs.modal', function() {
        // Limpiar todos los inputs del formulario
        const inputs = document.querySelectorAll('#formNuevoUsuario input, #formNuevoUsuario select');
        inputs.forEach(input => {
            if (input.type === 'text' || input.type === 'password' || input.type === 'email' || input.type === 'date') {
                input.value = '';
            } else if (input.type === 'select-one' || input.tagName === 'SELECT') {
                input.selectedIndex = 0;
            } else if (input.type === 'hidden') {
                return;
            }
        });
        
        // Restablecer el select de sucursal a CRM (valor 0)
        const selectSucursal = document.getElementById('sucursal_asignada');
        if (selectSucursal) {
            selectSucursal.value = '0';
        }
        
        // Quitar clases de error
        document.querySelectorAll('#formNuevoUsuario .is-invalid').forEach(el => {
            el.classList.remove('is-invalid');
        });
    });
}

// Toggle para contraseña en modal nuevo usuario
const togglePasswBtn = document.getElementById('togglePasswBtn');
const passwInput = document.getElementById('passw');

if (togglePasswBtn && passwInput) {
    togglePasswBtn.addEventListener('click', function() {
        const icon = this.querySelector('i');
        if (passwInput.type === 'password') {
            passwInput.type = 'text';
            icon.classList.remove('bi-eye');
            icon.classList.add('bi-eye-slash');
        } else {
            passwInput.type = 'password';
            icon.classList.remove('bi-eye-slash');
            icon.classList.add('bi-eye');
        }
    });
}
</script>
@endpush