<!-- Modal Editar Cliente -->
<div class="modal fade" id="modalEditarCliente" tabindex="-1" aria-labelledby="modalEditarClienteLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalEditarClienteLabel">
                    <i class="bi bi-pencil-square"></i> Editar Cliente
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="formEditarCliente">
                    @csrf
                    @method('PUT')
                    <input type="hidden" id="edit_id_Cliente" name="id_Cliente">
                    
                    <!-- Datos personales -->
                    <div class="row">
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Título</label>
                            <input type="text" class="form-control" id="edit_titulo" name="titulo" 
                                   placeholder="ING, LIC, SR., etc.">
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Nombre <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="edit_Nombre" name="Nombre" 
                                   onkeydown="return soloLetras(event)" required>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Ap. Paterno <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="edit_apPaterno" name="apPaterno" 
                                   onkeydown="return soloLetras(event)" required>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Ap. Materno</label>
                            <input type="text" class="form-control" id="edit_apMaterno" name="apMaterno" 
                                   onkeydown="return soloLetras(event)">
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Sexo</label>
                            <select class="form-select" id="edit_Sexo" name="Sexo">
                                <option value="">Seleccionar</option>
                                <option value="M">Masculino</option>
                                <option value="F">Femenino</option>
                                <option value="OTRO">Otro</option>
                            </select>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Fecha Nacimiento</label>
                            <input type="date" class="form-control" id="edit_FechaNac" name="FechaNac">
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Status</label>
                            <select class="form-select" id="edit_status" name="status">
                                <option value="PROSPECTO">Prospecto</option>
                                <option value="CLIENTE">Cliente</option>
                                <option value="BLOQUEADO">Bloqueado</option>
                            </select>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Sucursal Origen</label>
                            <input type="number" class="form-control" id="edit_sucursal_origen" name="sucursal_origen" value="0" readonly>
                            <small class="text-muted">0 = CRM</small>
                        </div>
                    </div>

                    <!-- Contacto -->
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Email Principal</label>
                            <input type="email" class="form-control" id="edit_email1" name="email1">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Teléfono Principal</label>
                            <input type="text" class="form-control" id="edit_telefono1" name="telefono1" 
                                   onkeydown="return soloNumeros(event)">
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Teléfono Secundario</label>
                            <input type="text" class="form-control" id="edit_telefono2" name="telefono2" 
                                   onkeydown="return soloNumeros(event)">
                        </div>
                    </div>

                    <!-- Dirección -->
                    <div class="mb-3">
                        <label class="form-label">Domicilio</label>
                        <textarea class="form-control" id="edit_Domicilio" name="Domicilio" rows="2"></textarea>
                    </div>

                    <!-- Ubicación (IDs) -->
                    <div class="row">
                        <div class="col-md-3 mb-3">
                            <label class="form-label">País ID</label>
                            <input type="number" class="form-control" id="edit_pais_id" name="pais_id">
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Estado ID</label>
                            <input type="number" class="form-control" id="edit_estado_id" name="estado_id">
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Municipio ID</label>
                            <input type="number" class="form-control" id="edit_municipio_id" name="municipio_id">
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Localidad ID</label>
                            <input type="number" class="form-control" id="edit_localidad_id" name="localidad_id">
                        </div>
                    </div>

                    <hr class="my-4">

                    <!-- SECCIÓN DE PATOLOGÍAS -->
                    <h6 class="mb-3">Patologías</h6>

                    <!-- Buscador de patologías -->
                    <div class="row mb-3">
                        <div class="col-12">
                            <div class="search-box">
                                <i class="bi bi-search"></i>
                                <input type="text" class="form-control" id="buscarPatologiaModal" 
                                       placeholder="Buscar patología para agregar...">
                            </div>
                            <small class="text-muted">Los resultados aparecerán automáticamente. Haz clic en uno para agregarlo.</small>
                        </div>
                    </div>

                    <!-- Resultados de búsqueda -->
                    <div id="resultadosPatologia" class="mb-3" style="display: none;">
                        <div class="card">
                            <div class="card-header bg-light py-2">
                                <small class="fw-bold">Resultados de búsqueda (haz clic para agregar)</small>
                            </div>
                            <div class="list-group list-group-flush" id="listaPatologia"></div>
                        </div>
                    </div>

                    <!-- Tabla de patologías del cliente -->
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover" id="tablaPatologiasCliente">
                            <thead class="table-light">
                                <tr>
                                    <th>No.</th>
                                    <th>Patología</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody id="patologiasClienteBody">
                                <tr id="sin-patologias-row">
                                    <td colspan="3" class="text-center py-4">
                                        <i class="bi bi-heart-pulse text-muted" style="font-size: 2rem;"></i>
                                        <p class="text-muted mt-2">Este cliente no tiene patologías registradas</p>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <small class="text-muted">
                        <i class="bi bi-info-circle"></i> 
                        Haz clic en cualquier resultado de búsqueda para agregar la patología automáticamente.
                    </small>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-warning" onclick="guardarEdicionCliente()">Guardar cambios</button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
(function() {
    // ============================================
    // VARIABLES LOCALES
    // ============================================
    let todasPatologias = [];
    let patologiasCliente = [];

    // ============================================
    // FUNCIÓN PARA CARGAR EL CATÁLOGO DE PATOLOGÍAS
    // ============================================
    async function cargarCatalogoPatologias() {
        try {
            const response = await fetch('/patologias/todas', { 
                headers: { 'Accept': 'application/json' } 
            });
            const data = await response.json();
            if (data.success) {
                todasPatologias = data.data;
                console.log('Catálogo de patologías cargado:', todasPatologias.length);
            }
        } catch (error) {
            console.error('Error al cargar catálogo:', error);
        }
    }

    // ============================================
    // FUNCIÓN PARA CARGAR DATOS DEL CLIENTE
    // ============================================
    async function cargarDatosCliente(clienteId) {
        try {
            const response = await fetch(`/clientes/${clienteId}/edit`, { 
                headers: { 'Accept': 'application/json' } 
            });
            const data = await response.json();

            if (data.success) {
                // Llenar datos básicos
                document.getElementById('edit_id_Cliente').value = data.data.id_Cliente;
                document.getElementById('edit_Nombre').value = data.data.Nombre;
                document.getElementById('edit_apPaterno').value = data.data.apPaterno;
                document.getElementById('edit_apMaterno').value = data.data.apMaterno || '';
                document.getElementById('edit_titulo').value = data.data.titulo || '';
                document.getElementById('edit_email1').value = data.data.email1;
                document.getElementById('edit_telefono1').value = data.data.telefono1 || '';
                document.getElementById('edit_telefono2').value = data.data.telefono2 || '';
                document.getElementById('edit_Domicilio').value = data.data.Domicilio || '';
                document.getElementById('edit_Sexo').value = data.data.Sexo || '';
                
                // CORREGIDO: Formatear fecha correctamente
                if (data.data.FechaNac) {
                    const fecha = new Date(data.data.FechaNac);
                    const año = fecha.getFullYear();
                    const mes = String(fecha.getMonth() + 1).padStart(2, '0');
                    const dia = String(fecha.getDate()).padStart(2, '0');
                    document.getElementById('edit_FechaNac').value = `${año}-${mes}-${dia}`;
                } else {
                    document.getElementById('edit_FechaNac').value = '';
                }
                
                document.getElementById('edit_status').value = data.data.status || 'PROSPECTO';
                document.getElementById('edit_pais_id').value = data.data.pais_id || '';
                document.getElementById('edit_estado_id').value = data.data.estado_id || '';
                document.getElementById('edit_municipio_id').value = data.data.municipio_id || '';
                document.getElementById('edit_localidad_id').value = data.data.localidad_id || '';
                document.getElementById('edit_sucursal_origen').value = data.data.sucursal_origen || 0;

                // Cargar catálogo si es necesario
                if (todasPatologias.length === 0) {
                    await cargarCatalogoPatologias();
                }

                // Procesar patologías del cliente
                patologiasCliente = [];
                if (data.data.enfermedades && todasPatologias.length > 0) {
                    data.data.enfermedades.forEach(patId => {
                        const pat = todasPatologias.find(p => p.id_patologia === patId);
                        if (pat) {
                            patologiasCliente.push({
                                id: pat.id_patologia,
                                nombre: pat.descripcion
                            });
                        }
                    });
                }
                renderizarTablaPatologias();
            }
        } catch (error) {
            console.error('Error al cargar datos del cliente:', error);
        }
    }

    // ============================================
    // FUNCIONES DE LA TABLA
    // ============================================
    function renderizarTablaPatologias() {
        const tbody = document.getElementById('patologiasClienteBody');
        if (!tbody) return;

        if (patologiasCliente.length === 0) {
            tbody.innerHTML = `<tr id="sin-patologias-row">
                <td colspan="3" class="text-center py-4">
                    <i class="bi bi-heart-pulse text-muted" style="font-size: 2rem;"></i>
                    <p class="text-muted mt-2">Este cliente no tiene patologías registradas</p>
                </td>
            </tr>`;
            return;
        }

        let html = '';
        patologiasCliente.forEach((pat, index) => {
            html += `<tr id="patologia-row-${pat.id}">
                <td>${index + 1}</td>
                <td>${pat.nombre}</td>
                <td>
                    <button type="button" class="btn btn-sm btn-outline-danger btn-action" 
                            onclick="window.eliminarPatologiaDeTabla(${pat.id})" 
                            title="Eliminar patología">
                        <i class="bi bi-trash"></i>
                    </button>
                </td>
            </tr>`;
        });
        tbody.innerHTML = html;
    }

    // ============================================
    // FUNCIONES DE BÚSQUEDA Y AGREGADO
    // ============================================
    function buscarPatologias(termino) {
        if (!termino || termino.length < 2) {
            document.getElementById('resultadosPatologia').style.display = 'none';
            return;
        }

        const resultados = todasPatologias.filter(pat => 
            pat.descripcion.toLowerCase().includes(termino.toLowerCase())
        );

        const resultadosDiv = document.getElementById('resultadosPatologia');
        const listaResultados = document.getElementById('listaPatologia');

        if (resultados.length === 0) {
            listaResultados.innerHTML = `<div class="list-group-item text-muted">
                <i class="bi bi-exclamation-circle"></i> No se encontraron resultados
            </div>`;
        } else {
            listaResultados.innerHTML = resultados.map(pat => {
                const yaExiste = patologiasCliente.some(p => p.id === pat.id_patologia);
                return `<div class="list-group-item list-group-item-action ${yaExiste ? 'disabled opacity-50' : ''}" 
                        onclick="${!yaExiste ? `window.agregarPatologiaACliente(${pat.id_patologia}, '${pat.descripcion}')` : ''}" 
                        style="cursor: ${yaExiste ? 'not-allowed' : 'pointer'};">
                        <div class="d-flex justify-content-between align-items-center">
                            <div><strong>${pat.descripcion}</strong></div>
                            ${yaExiste ? '<span class="badge bg-secondary">Ya agregada</span>' : '<span class="badge bg-success">Click para agregar</span>'}
                        </div>
                    </div>`;
            }).join('');
        }
        resultadosDiv.style.display = 'block';
    }

    window.agregarPatologiaACliente = function(id, descripcion) {
        if (patologiasCliente.some(p => p.id === id)) return;

        patologiasCliente.push({ 
            id: id, 
            nombre: descripcion
        });

        renderizarTablaPatologias();
        document.getElementById('buscarPatologiaModal').value = '';
        document.getElementById('resultadosPatologia').style.display = 'none';
        if (window.mostrarToast) window.mostrarToast('Patología agregada', 'success');
    };

    window.eliminarPatologiaDeTabla = function(id) {
        const modalConfirmar = document.getElementById('modalConfirmarEliminar');
        if (!modalConfirmar) return;

        const patologia = patologiasCliente.find(p => p.id === id);
        
        window.contextoEliminarPatologia = { id: id, nombre: patologia?.nombre };

        document.getElementById('detalleConfirmacion').textContent = 
            `¿Eliminar "${patologia?.nombre}" de la lista?`;

        const btnConfirmar = document.getElementById('btnConfirmarEliminar');
        const originalOnClick = btnConfirmar.onclick;

        btnConfirmar.onclick = function() {
            patologiasCliente = patologiasCliente.filter(p => p.id !== id);
            renderizarTablaPatologias();
            if (window.mostrarToast) window.mostrarToast(`"${patologia?.nombre}" eliminada`, 'warning');
            btnConfirmar.onclick = originalOnClick;
            bootstrap.Modal.getInstance(modalConfirmar).hide();
        };

        new bootstrap.Modal(modalConfirmar).show();
    };

    // ============================================
    // FUNCIÓN PARA GUARDAR
    // ============================================
    window.guardarEdicionCliente = function() {
        // Obtener valor de fecha y formatearlo
        let fechaNacEdit = document.getElementById('edit_FechaNac')?.value || '';

        const id = document.getElementById('edit_id_Cliente')?.value;
        const formData = {
            Nombre: document.getElementById('edit_Nombre')?.value || '',
            apPaterno: document.getElementById('edit_apPaterno')?.value || '',
            apMaterno: document.getElementById('edit_apMaterno')?.value || '',
            titulo: document.getElementById('edit_titulo')?.value || '',
            email1: document.getElementById('edit_email1')?.value || '',
            telefono1: document.getElementById('edit_telefono1')?.value || '',
            telefono2: document.getElementById('edit_telefono2')?.value || '',
            Domicilio: document.getElementById('edit_Domicilio')?.value || '',
            Sexo: document.getElementById('edit_Sexo')?.value || '',
            FechaNac: fechaNacEdit,
            status: document.getElementById('edit_status')?.value || 'PROSPECTO',
            pais_id: document.getElementById('edit_pais_id')?.value || '',
            estado_id: document.getElementById('edit_estado_id')?.value || '',
            municipio_id: document.getElementById('edit_municipio_id')?.value || '',
            localidad_id: document.getElementById('edit_localidad_id')?.value || '',
            enfermedades: patologiasCliente.map(p => p.id),
            _token: '{{ csrf_token() }}',
            _method: 'PUT'
        };

        // Validaciones básicas
        if (!formData.Nombre || !formData.apPaterno || !formData.email1) {
            if (window.mostrarToast) window.mostrarToast('Completa los campos requeridos', 'warning');
            return;
        }

        if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(formData.email1)) {
            if (window.mostrarToast) window.mostrarToast('Correo electrónico no válido', 'warning');
            return;
        }

        fetch(`/clientes/${id}`, {
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
                const modal = bootstrap.Modal.getInstance(document.getElementById('modalEditarCliente'));
                modal.hide();
                if (window.mostrarToast) window.mostrarToast('Cliente actualizado correctamente', 'success');
                setTimeout(() => location.reload(), 1000);
            } else if (data.errors) {
                let mensajes = Object.values(data.errors).flat().join('\n');
                if (window.mostrarToast) window.mostrarToast(mensajes, 'danger');
            } else {
                if (window.mostrarToast) window.mostrarToast('Error al actualizar', 'danger');
            }
        }).catch(error => {
            console.error(error);
            if (window.mostrarToast) window.mostrarToast('Error de conexión', 'danger');
        });
    };

    // ============================================
    // EVENT LISTENERS
    // ============================================
    document.addEventListener('DOMContentLoaded', function() {
        const modalEditar = document.getElementById('modalEditarCliente');
        if (modalEditar) {
            modalEditar.addEventListener('show.bs.modal', function(event) {
                const clienteId = event.relatedTarget.getAttribute('data-cliente-id');
                
                // Limpiar búsqueda
                document.getElementById('buscarPatologiaModal').value = '';
                document.getElementById('resultadosPatologia').style.display = 'none';
                
                // Cargar datos del cliente
                cargarDatosCliente(clienteId);
            });
        }

        document.getElementById('buscarPatologiaModal')?.addEventListener('input', function() {
            buscarPatologias(this.value);
        });

        document.addEventListener('click', function(event) {
            const resultados = document.getElementById('resultadosPatologia');
            const buscador = document.getElementById('buscarPatologiaModal');
            if (resultados && !resultados.contains(event.target) && event.target !== buscador) {
                resultados.style.display = 'none';
            }
        });
    });
})();
</script>
@endpush