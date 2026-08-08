<!-- Modal Editar Usuario -->
<div class="modal fade" id="modalEditarUsuario" tabindex="-1" aria-labelledby="modalEditarUsuarioLabel">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-warning">
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
                            <input type="text" class="form-control" id="edit_usuario" name="usuario" autocomplete="off" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Nueva Contraseña</label>
                            <div style="position: relative;">
                                <input type="password" class="form-control" id="edit_passw" name="passw" placeholder="Dejar vacío para no cambiar" autocomplete="new-password" style="padding-right: 45px;">
                                <button type="button" id="toggleEditPasswBtn" style="
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
                            <small class="text-muted">********</small>
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
                            <label class="form-label">Sucursal Asignada</label>
                            <select class="form-select" id="edit_sucursal_asignada" name="sucursal_asignada">
                                <option value="0">CRM (Sistema)</option>
                                <!-- Las opciones se cargarán dinámicamente desde JavaScript -->
                            </select>
                            <small class="text-muted">Selecciona "CRM" si el usuario opera desde el sistema central</small>
                        </div>
                    </div>

                    <!-- Fechas de alta -->
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Fecha Ingreso</label>
                            <input type="date" class="form-control" id="edit_fecha_ingreso" name="fecha_ingreso">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Fecha Alta Sistema</label>
                            <input type="date" class="form-control" id="edit_fecha_alta_sistema" name="fecha_alta_sistema">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Fecha Alta Seguro</label>
                            <input type="date" class="form-control" id="edit_fecha_alta_seguro" name="fecha_alta_seguro">
                        </div>
                    </div>

                    <!-- Estado de Repartidor (SOLO LECTURA) -->
                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <div class="alert alert-info mb-0">
                                <div class="d-flex align-items-center">
                                    <i class="bi bi-truck fs-4 me-3"></i>
                                    <div>
                                        <strong class="d-block">Estado de Repartidor</strong>
                                        <div id="repartidorInfo">
                                            <span class="badge bg-secondary">
                                                <i class="bi bi-clock-history"></i> Cargando...
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
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
// ============================================
// FUNCIÓN PARA FORMATEAR FECHA
// ============================================
function formatearFecha(fechaISO) {
    if (!fechaISO) return '';
    const fecha = new Date(fechaISO);
    if (isNaN(fecha.getTime())) return '';
    
    const año = fecha.getFullYear();
    const mes = String(fecha.getMonth() + 1).padStart(2, '0');
    const dia = String(fecha.getDate()).padStart(2, '0');
    return `${año}-${mes}-${dia}`;
}

// ============================================
// CARGA DE DATOS DE USUARIO
// ============================================
let loadingUsuarioEdit = false;

function cargarDatosUsuario(id) {
    if (loadingUsuarioEdit) return;
    
    id = parseInt(id);
    if (isNaN(id)) {
        console.error('ID inválido:', id);
        return;
    }
    
    loadingUsuarioEdit = true;
    
    fetch(`/seguridad/usuarios/${id}/edit`, {
        headers: { 
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => {
        if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);
        return response.json();
    })
    .then(data => {
        if (data.success) {
            // Datos básicos
            document.getElementById('edit_usuario_id').value = data.data.id_personal_empresa;
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
            document.getElementById('edit_fecha_nacimiento').value = formatearFecha(data.data.fecha_nacimiento);
            document.getElementById('edit_Activo').value = data.data.Activo ? '1' : '0';
            document.getElementById('edit_fecha_ingreso').value = formatearFecha(data.data.fecha_ingreso);
            document.getElementById('edit_fecha_alta_sistema').value = formatearFecha(data.data.fecha_alta_sistema);
            document.getElementById('edit_fecha_alta_seguro').value = formatearFecha(data.data.fecha_alta_seguro);
            
            // Sucursal asignada
            let sucursalAsignada = data.data.sucursal_asignada;
            if (sucursalAsignada === null || sucursalAsignada === undefined || sucursalAsignada === '') {
                sucursalAsignada = 0;
            }

            // Cargar sucursales
            if (data.sucursales && data.sucursales.length) {
                const selectSucursal = document.getElementById('edit_sucursal_asignada');
                let options = '<option value="0">CRM (Sistema)</option>';
                
                data.sucursales.forEach(sucursal => {
                    const selected = (sucursalAsignada == sucursal.id_sucursal) ? 'selected' : '';
                    options += `<option value="${sucursal.id_sucursal}" ${selected}>Sucursal ${sucursal.nombre}</option>`;
                });
                
                selectSucursal.innerHTML = options;
            } else {
                document.getElementById('edit_sucursal_asignada').value = sucursalAsignada;
            }

            // Estado de repartidor (solo lectura)
            const esRepartidor = data.data.es_repartidor || false;
            const tieneHorario = data.data.tiene_horario_repartidor || false;
            const repartidorInfo = document.getElementById('repartidorInfo');
            
            if (repartidorInfo) {
                if (esRepartidor && tieneHorario) {
                    repartidorInfo.innerHTML = `
                        <span class="badge bg-success">
                            <i class="bi bi-check-circle"></i> Usuario tiene horario activo
                        </span>
                        <small class="text-muted d-block mt-1">
                            Puede iniciar recorridos de reparto
                        </small>
                    `;
                } else if (esRepartidor && !tieneHorario) {
                    repartidorInfo.innerHTML = `
                        <span class="badge bg-warning">
                            <i class="bi bi-exclamation-triangle"></i> Usuario es repartidor sin horario
                        </span>
                        <small class="text-muted d-block mt-1">
                            Asigne un horario en Recursos Humanos para habilitar recorridos
                        </small>
                    `;
                } else {
                    repartidorInfo.innerHTML = `
                        <span class="badge bg-secondary">
                            <i class="bi bi-x-circle"></i> Sin horario de reparto
                        </span>
                        <small class="text-muted d-block mt-1">
                            Asigne un horario en Recursos Humanos para habilitar como repartidor
                        </small>
                    `;
                }
            }
            
        } else {
            console.error('Error en la respuesta:', data);
            if (window.mostrarToast) window.mostrarToast('Error al cargar datos del usuario', 'danger');
        }
        loadingUsuarioEdit = false;
    })
    .catch(error => {
        console.error('Error:', error);
        if (window.mostrarToast) window.mostrarToast('Error de conexión', 'danger');
        loadingUsuarioEdit = false;
    });
}

// ============================================
// GUARDAR EDICIÓN DE USUARIO
// ============================================
window.guardarEdicionUsuario = function() {
    const id = document.getElementById('edit_usuario_id').value;
    
    if (!id) {
        if (window.mostrarToast) window.mostrarToast('Error: No se encontró el ID del usuario', 'danger');
        return;
    }
    
    let sucursalAsignada = document.getElementById('edit_sucursal_asignada')?.value;
    if (sucursalAsignada === '' || sucursalAsignada === null) {
        sucursalAsignada = 0;
    }
    
    const formData = {
        Nombre: document.getElementById('edit_Nombre')?.value || '',
        ApPaterno: document.getElementById('edit_ApPaterno')?.value || '',
        ApMaterno: document.getElementById('edit_ApMaterno')?.value || null,
        usuario: document.getElementById('edit_usuario')?.value || '',
        passw: document.getElementById('edit_passw')?.value || null,
        contacto: document.getElementById('edit_contacto')?.value || null,
        TelefonoMovil: document.getElementById('edit_TelefonoMovil')?.value || null,
        Direccion: document.getElementById('edit_Direccion')?.value || null,
        Localidad: document.getElementById('edit_Localidad')?.value || null,
        Municipio: document.getElementById('edit_Municipio')?.value || null,
        curp: document.getElementById('edit_curp')?.value || null,
        fecha_nacimiento: document.getElementById('edit_fecha_nacimiento')?.value || null,
        Activo: document.getElementById('edit_Activo')?.value || 1,
        sucursal_asignada: parseInt(sucursalAsignada),
        fecha_ingreso: document.getElementById('edit_fecha_ingreso')?.value || null,
        fecha_alta_sistema: document.getElementById('edit_fecha_alta_sistema')?.value || null,
        fecha_alta_seguro: document.getElementById('edit_fecha_alta_seguro')?.value || null,
        _token: '{{ csrf_token() }}',
        _method: 'PUT'
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
    
    // Validar contraseña solo si se está cambiando
    if (formData.passw && formData.passw.length < 3) {
        if (window.mostrarToast) window.mostrarToast('La contraseña debe tener al menos 3 caracteres', 'warning');
        return;
    }

    const btn = document.querySelector('#modalEditarUsuario .btn-warning');
    const originalText = btn ? btn.innerHTML : 'Guardar';
    if (btn) {
        btn.disabled = true;
        btn.innerHTML = '<i class="bi bi-hourglass-split"></i> Guardando...';
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
            if (modal) modal.hide();
            
            if (window.mostrarToast) window.mostrarToast('Usuario actualizado correctamente', 'success');
            setTimeout(() => location.reload(), 1000);
        } else {
            let mensajeError = data.message || 'Error al actualizar';
            
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
            if (btn) {
                btn.disabled = false;
                btn.innerHTML = originalText;
            }
        }
    })
    .catch(error => {
        console.error('Error:', error);
        if (window.mostrarToast) window.mostrarToast('Error de conexión', 'danger');
        if (btn) {
            btn.disabled = false;
            btn.innerHTML = originalText;
        }
    });
};

// ============================================
// EVENTOS DEL MODAL
// ============================================
document.addEventListener('DOMContentLoaded', function() {
    const modalEditar = document.getElementById('modalEditarUsuario');
    if (modalEditar) {
        modalEditar.addEventListener('show.bs.modal', function(event) {
            const button = event.relatedTarget;
            const usuarioId = button?.getAttribute('data-usuario-id');
            if (usuarioId) {
                cargarDatosUsuario(usuarioId);
            }
        });
        
        modalEditar.addEventListener('hidden.bs.modal', function() {
            loadingUsuarioEdit = false;
            // Limpiar campos
            document.getElementById('edit_passw').value = '';
        });
    }
});

// Toggle para contraseña en modal editar usuario
const toggleEditPasswBtn = document.getElementById('toggleEditPasswBtn');
const editPasswInput = document.getElementById('edit_passw');

if (toggleEditPasswBtn && editPasswInput) {
    toggleEditPasswBtn.addEventListener('click', function() {
        const icon = this.querySelector('i');
        if (editPasswInput.type === 'password') {
            editPasswInput.type = 'text';
            icon.classList.remove('bi-eye');
            icon.classList.add('bi-eye-slash');
        } else {
            editPasswInput.type = 'password';
            icon.classList.remove('bi-eye-slash');
            icon.classList.add('bi-eye');
        }
    });
}
</script>
@endpush