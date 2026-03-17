<!-- Modal Nuevo Cliente -->
<div class="modal fade" id="modalNuevoCliente" tabindex="-1" aria-labelledby="modalNuevoClienteLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalNuevoClienteLabel">
                    <i class="bi bi-person-plus"></i> Nuevo Cliente
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="formNuevoCliente">
                    @csrf
                    
                    <!-- Datos personales -->
                    <div class="row">
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Título</label>
                            <select class="form-select" id="edit_titulo" name="titulo">
                                <option value="">Seleccionar</option>
                                <option value="SR.">SR.</option>
                                <option value="SRA.">SRA.</option>
                                <option value="SRTA.">SRTA.</option>
                                <option value="ING.">ING.</option>
                                <option value="LIC.">LIC.</option>
                                <option value="DR.">DR.</option>
                                <option value="DRA.">DRA.</option>
                                <option value="PROF.">PROF.</option>
                                <option value="PROFA.">PROFA.</option>
                                <option value="ARQ.">ARQ.</option>
                                <option value="C.P.">C.P.</option>
                                <option value="MTRO.">MTRO.</option>
                                <option value="MTRA.">MTRA.</option>
                            </select>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Nombre <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="Nombre" name="Nombre" 
                                   onkeydown="return soloLetras(event)"
                                   oninput="aMayusculas(event)"
                                   required>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Ap. Paterno <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="apPaterno" name="apPaterno" 
                                   onkeydown="return soloLetras(event)"
                                   oninput="aMayusculas(event)"
                                   required>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Ap. Materno</label>
                            <input type="text" class="form-control" id="apMaterno" name="apMaterno" 
                                   onkeydown="return soloLetras(event)"
                                   oninput="aMayusculas(event)">
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Sexo</label>
                            <select class="form-select" id="Sexo" name="Sexo">
                                <option value="">Seleccionar (opcional)</option>
                                <option value="M">Masculino</option>
                                <option value="F">Femenino</option>
                                <option value="OTRO">Otro</option>
                            </select>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Fecha Nacimiento</label>
                            <input type="date" class="form-control" id="FechaNac" name="FechaNac">
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Status</label>
                            <select class="form-select" id="status" name="status">
                                <option value="PROSPECTO">Prospecto</option>
                                <option value="CLIENTE">Cliente</option>
                                <option value="BLOQUEADO">Bloqueado</option>
                            </select>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Sucursal Origen</label>
                            <input type="number" class="form-control" id="sucursal_origen" name="sucursal_origen" value="0" readonly>
                            <small class="text-muted">0 = CRM</small>
                        </div>
                    </div>

                    <!-- Contacto -->
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Email Principal <span class="text-danger">*</span></label>
                            <input type="email" class="form-control" id="email1" name="email1" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Teléfono Principal</label>
                            <input type="text" class="form-control" id="telefono1" name="telefono1" 
                                   onkeydown="return soloNumeros(event)">
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Teléfono Secundario</label>
                            <input type="text" class="form-control" id="telefono2" name="telefono2" 
                                   onkeydown="return soloNumeros(event)">
                        </div>
                    </div>

                    <!-- Dirección -->
                    <div class="mb-3">
                        <label class="form-label">Domicilio</label>
                        <textarea class="form-control" id="Domicilio" name="Domicilio" rows="2"></textarea>
                    </div>

                    <!-- Ubicación (IDs) -->
                    <div class="row">
                        <div class="col-md-3 mb-3">
                            <label class="form-label">País ID</label>
                            <input type="number" class="form-control" id="pais_id" name="pais_id">
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Estado ID</label>
                            <input type="number" class="form-control" id="estado_id" name="estado_id">
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Municipio ID</label>
                            <input type="number" class="form-control" id="municipio_id" name="municipio_id">
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Localidad ID</label>
                            <input type="number" class="form-control" id="localidad_id" name="localidad_id">
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
                                <input type="text" class="form-control" id="buscarPatologiaNuevoModal" 
                                       placeholder="Buscar patología para agregar...">
                            </div>
                            <small class="text-muted">Los resultados aparecerán automáticamente. Haz clic en uno para agregarlo.</small>
                        </div>
                    </div>

                    <!-- Resultados de búsqueda -->
                    <div id="resultadosPatologiaNuevo" class="mb-3" style="display: none;">
                        <div class="card">
                            <div class="card-header bg-light py-2">
                                <small class="fw-bold">Resultados de búsqueda</small>
                            </div>
                            <div class="list-group list-group-flush" id="listaPatologiaNuevo"></div>
                        </div>
                    </div>

                    <!-- Tabla de patologías del nuevo cliente -->
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover" id="tablaPatologiasNuevoCliente">
                            <thead class="table-light">
                                <tr>
                                    <th>No.</th>
                                    <th>Patología</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody id="patologiasNuevoClienteBody">
                                <tr id="sin-patologias-nuevo-row">
                                    <td colspan="3" class="text-center py-4">
                                        <i class="bi bi-heart-pulse text-muted" style="font-size: 2rem;"></i>
                                        <p class="text-muted mt-2">No hay patologías agregadas</p>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-success" onclick="guardarNuevoCliente()">Guardar</button>
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
    let patologiasNuevoCliente = [];

    // ============================================
    // FUNCIÓN PARA CARGAR EL CATÁLOGO
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
    // FUNCIONES DE LA TABLA
    // ============================================
    function renderizarTablaPatologias() {
        const tbody = document.getElementById('patologiasNuevoClienteBody');
        if (!tbody) return;

        if (patologiasNuevoCliente.length === 0) {
            tbody.innerHTML = `<tr id="sin-patologias-nuevo-row">
                <td colspan="3" class="text-center py-4">
                    <i class="bi bi-heart-pulse text-muted" style="font-size: 2rem;"></i>
                    <p class="text-muted mt-2">No hay patologías agregadas</p>
                </td>
            </tr>`;
            return;
        }

        let html = '';
        patologiasNuevoCliente.forEach((pat, index) => {
            html += `<tr id="nuevo-patologia-row-${pat.id}">
                <td>${index + 1}</td>
                <td>${pat.nombre}</td>
                <td>
                    <button type="button" class="btn btn-sm btn-outline-danger btn-action" 
                            onclick="window.eliminarPatologiaNuevoCliente(${pat.id})" 
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
            document.getElementById('resultadosPatologiaNuevo').style.display = 'none';
            return;
        }

        const resultados = todasPatologias.filter(pat => 
            pat.descripcion.toLowerCase().includes(termino.toLowerCase())
        );

        const resultadosDiv = document.getElementById('resultadosPatologiaNuevo');
        const listaResultados = document.getElementById('listaPatologiaNuevo');

        if (resultados.length === 0) {
            listaResultados.innerHTML = `<div class="list-group-item text-muted">
                <i class="bi bi-exclamation-circle"></i> No se encontraron resultados
            </div>`;
        } else {
            listaResultados.innerHTML = resultados.map(pat => {
                const yaExiste = patologiasNuevoCliente.some(p => p.id === pat.id_patologia);
                return `<div class="list-group-item list-group-item-action ${yaExiste ? 'disabled opacity-50' : ''}" 
                        onclick="${!yaExiste ? `window.agregarPatologiaNuevoCliente(${pat.id_patologia}, '${pat.descripcion}')` : ''}" 
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

    window.agregarPatologiaNuevoCliente = function(id, descripcion) {
        if (patologiasNuevoCliente.some(p => p.id === id)) return;

        patologiasNuevoCliente.push({ 
            id: id, 
            nombre: descripcion
        });

        renderizarTablaPatologias();
        document.getElementById('buscarPatologiaNuevoModal').value = '';
        document.getElementById('resultadosPatologiaNuevo').style.display = 'none';
        if (window.mostrarToast) window.mostrarToast('Patología agregada', 'success');
    };

    window.eliminarPatologiaNuevoCliente = function(id) {
        const modalConfirmar = document.getElementById('modalConfirmarEliminar');
        if (!modalConfirmar) return;

        const patologia = patologiasNuevoCliente.find(p => p.id === id);
        
        window.contextoEliminarNuevo = { id: id, nombre: patologia?.nombre };

        document.getElementById('detalleConfirmacion').textContent = 
            `¿Eliminar "${patologia?.nombre}" de la lista?`;

        const btnConfirmar = document.getElementById('btnConfirmarEliminar');
        const originalOnClick = btnConfirmar.onclick;

        btnConfirmar.onclick = function() {
            patologiasNuevoCliente = patologiasNuevoCliente.filter(p => p.id !== id);
            renderizarTablaPatologias();
            if (window.mostrarToast) window.mostrarToast(`"${patologia?.nombre}" eliminada`, 'warning');
            btnConfirmar.onclick = originalOnClick;
            bootstrap.Modal.getInstance(modalConfirmar).hide();
        };

        new bootstrap.Modal(modalConfirmar).show();
    };

    // ============================================
    // FUNCIÓN PARA GUARDAR NUEVO CLIENTE
    // ============================================
    window.guardarNuevoCliente = function() {
    // Función auxiliar para convertir vacío a null
    const toNull = (valor) => {
        if (valor === undefined || valor === null) return null;
        return valor === '' ? null : valor;
    };

    // Obtener valores del formulario
    let fechaNac = document.getElementById('FechaNac')?.value || null;
    
    const formData = {
        Nombre: document.getElementById('Nombre')?.value || '',
        apPaterno: document.getElementById('apPaterno')?.value || '',
        apMaterno: toNull(document.getElementById('apMaterno')?.value),
        titulo: toNull(document.getElementById('titulo')?.value),
        email1: toNull(document.getElementById('email1')?.value),
        telefono1: toNull(document.getElementById('telefono1')?.value),
        telefono2: toNull(document.getElementById('telefono2')?.value),
        Domicilio: toNull(document.getElementById('Domicilio')?.value),
        Sexo: toNull(document.getElementById('Sexo')?.value),
        FechaNac: fechaNac,
        status: document.getElementById('status')?.value || 'PROSPECTO',
        // Campos numéricos: convertir a número o null
        pais_id: document.getElementById('pais_id')?.value || 0, // 0 como valor por defecto
        estado_id: toNull(document.getElementById('estado_id')?.value),
        municipio_id: toNull(document.getElementById('municipio_id')?.value),
        localidad_id: toNull(document.getElementById('localidad_id')?.value),
        enfermedades: patologiasNuevoCliente.map(p => p.id),
        _token: '{{ csrf_token() }}'
    };

        // Validaciones básicas
        if (!formData.Nombre || !formData.apPaterno) {
            if (window.mostrarToast) {
                window.mostrarToast('Completa los campos requeridos (Nombre y Apellido Paterno)', 'warning');
            }
            return;
        }

        // Validar email SOLO si tiene valor
        if (formData.email1 && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(formData.email1)) {
            if (window.mostrarToast) {
                window.mostrarToast('Correo electrónico no válido', 'warning');
            }
            return;
        }

        console.log('Enviando datos:', JSON.stringify(formData, null, 2));

        fetch('{{ route("clientes.store") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify(formData)
        })
        .then(response => {
            if (!response.ok) {
                return response.json().then(err => { throw err; });
            }
            return response.json();
        })
        .then(data => {
            console.log('Respuesta del servidor:', data);
            if (data.success) {
                const modal = bootstrap.Modal.getInstance(document.getElementById('modalNuevoCliente'));
                modal.hide();
                
                if (data.html) {
                    document.getElementById('clientes-table-container').innerHTML = data.html;
                }
                
                if (window.mostrarToast) {
                    window.mostrarToast('Cliente creado correctamente', 'success');
                }
                
                // Limpiar formulario
                document.getElementById('formNuevoCliente').reset();
                
                // Recargar después de un momento
                setTimeout(() => location.reload(), 1500);
            } else if (data.errors) {
                let mensajes = Object.values(data.errors).flat().join('\n');
                if (window.mostrarToast) {
                    window.mostrarToast(mensajes, 'danger');
                }
            } else {
                if (window.mostrarToast) {
                    window.mostrarToast('Error al crear cliente', 'danger');
                }
            }
        })
        .catch(error => {
            console.error('Error completo:', error);
            if (error.errors) {
                let mensajes = Object.values(error.errors).flat().join('\n');
                if (window.mostrarToast) {
                    window.mostrarToast(mensajes, 'danger');
                }
            } else {
                if (window.mostrarToast) {
                    window.mostrarToast('Error: ' + (error.message || 'Error de conexión'), 'danger');
                }
            }
        });
    };

    // ============================================
    // INICIALIZACIÓN
    // ============================================
    document.addEventListener('DOMContentLoaded', function() {
        const modal = document.getElementById('modalNuevoCliente');
        if (modal) {
            modal.addEventListener('show.bs.modal', function() {
                patologiasNuevoCliente = [];
                renderizarTablaPatologias();
                if (todasPatologias.length === 0) cargarCatalogoPatologias();
                document.getElementById('buscarPatologiaNuevoModal').value = '';
                document.getElementById('resultadosPatologiaNuevo').style.display = 'none';
            });
        }

        document.getElementById('buscarPatologiaNuevoModal')?.addEventListener('input', function() {
            buscarPatologias(this.value);
        });

        document.addEventListener('click', function(event) {
            const resultados = document.getElementById('resultadosPatologiaNuevo');
            const buscador = document.getElementById('buscarPatologiaNuevoModal');
            if (resultados && !resultados.contains(event.target) && event.target !== buscador) {
                resultados.style.display = 'none';
            }
        });
    });
})();
</script>
@endpush