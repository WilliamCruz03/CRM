<!-- Modal Nuevo Cliente -->
<div class="modal fade" id="modalNuevoCliente" tabindex="-1" aria-labelledby="modalNuevoClienteLabel">
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
                            <select class="form-select" id="titulo" name="titulo">
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
                            <input type="text" class="form-control" id="Nombre" name="Nombre" autocomplete="off"
                                   onkeydown="return soloLetras(event)"
                                   oninput="aMayusculas(event)"
                                   required>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Ap. Paterno <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="apPaterno" name="apPaterno" autocomplete="off"
                                   onkeydown="return soloLetras(event)"
                                   oninput="aMayusculas(event)"
                                   required>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Ap. Materno</label>
                            <input type="text" class="form-control" id="apMaterno" name="apMaterno" autocomplete="off"
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
                                <option value="INACTIVO">Inactivo</option>
                                <option value="BLOQUEADO">Bloqueado</option>
                            </select>
                        </div>
                        {{--  
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Sucursal Origen</label>
                            <input type="number" class="form-control" id="sucursal_origen" name="sucursal_origen" value="0" readonly>
                            <small class="text-muted">0 = CRM</small>
                        </div>
                        --}}
                    </div>

                    <!-- Contacto -->
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Teléfono Principal</label>
                            <input type="text" class="form-control" id="telefono1" name="telefono1" autocomplete="off"
                                   onkeydown="return soloNumeros(event)">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Teléfono Secundario</label>
                            <input type="text" class="form-control" id="telefono2" name="telefono2" autocomplete="off"
                                   onkeydown="return soloNumeros(event)">
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" class="form-control" id="email1" name="email1" required autocomplete="offS">
                        </div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Preferencia de contacto</label>
                        <select class="form-select" id="contacto_id" name="contacto_id">
                            <option value="">Seleccionar tipo...</option>
                        </select>
                    </div>

                    <!-- Ubicación (IDs) -->
                    <div class="row">
                        <div class="col-md-3 mb-3">
                            <label class="form-label">País</label>
                            <select id="pais_select_nuevo" class="form-control">
                                <option value="">Cargando países...</option>
                            </select>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Estado</label>
                            <select id="estado_select_nuevo" class="form-control" disabled>
                                <option value="">Primero seleccione un país</option>
                            </select>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Municipio</label>
                            <select id="municipio_select_nuevo" class="form-control" disabled>
                                <option value="">Primero seleccione un estado</option>
                            </select>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Localidad</label>
                            <select id="localidad_select_nuevo" class="form-control" disabled>
                                <option value="">Primero seleccione un municipio</option>
                            </select>
                        </div>
                    </div>

                    <!-- Dirección -->
                    <div class="mb-3">
                        <label class="form-label">Domicilio</label>
                        <textarea class="form-control" id="Domicilio" name="Domicilio" rows="2"></textarea>
                    </div>

                    <!-- Mantén los campos ocultos para guardar los IDs -->
                    <input type="hidden" id="pais_id" name="pais_id">
                    <input type="hidden" id="estado_id" name="estado_id">
                    <input type="hidden" id="municipio_id" name="municipio_id">
                    <input type="hidden" id="localidad_id" name="localidad_id">

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
    // ============================================
    // VERIFICACIÓN PARA EVITAR DUPLICADOS
    // ============================================
    if (typeof modalNuevoInicializado !== 'undefined') {
        // Si ya está inicializado, no hacer nada
    } else {
        // Marcar como inicializado ANTES de cualquier declaración
        modalNuevoInicializado = true;

        // ============================================
        // VARIABLES LOCALES
        // ============================================
        let todasPatologias = [];
        window.patologiasNuevoCliente = [];
        let paisSelectNuevo, estadoSelectNuevo, municipioSelectNuevo, localidadSelectNuevo;

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
                }
            } catch (error) {
                console.error('Error al cargar catálogo:', error);
            }
        }

        // ============================================
        // CARGAR TIPOS DE CONTACTO
        // ============================================
        function cargarTiposContacto() {
            fetch('{{ route("clientes.tipos-contacto") }}', {
                headers: { 'Accept': 'application/json' }
            })
            .then(response => {
                return response.json();
            })
            .then(data => {
                if (data.success && data.data) {
                    const select = document.getElementById('contacto_id');
                    if (select) {
                        select.innerHTML = '<option value="">Seleccionar tipo...</option>';
                        data.data.forEach(tipo => {
                            select.innerHTML += `<option value="${tipo.id_tipo}">${tipo.nombre}</option>`;
                        });

                    }
                }
            })
            .catch(error => console.error('Error cargando tipos:', error));
        }

        // ============================================
        // FUNCIONES DE LA TABLA DE PATOLOGÍAS
        // ============================================
        function renderizarTablaPatologias() {
            const tbody = document.getElementById('patologiasNuevoClienteBody');
            if (!tbody) return;

            if (window.patologiasNuevoCliente.length === 0) {
                tbody.innerHTML = `<tr id="sin-patologias-nuevo-row">
                    <td colspan="3" class="text-center py-4">
                        <i class="bi bi-heart-pulse text-muted" style="font-size: 2rem;"></i>
                        <p class="text-muted mt-2">No hay patologías agregadas</p>
                    <\/td>
                <\/tr>`;
                return;
            }

            let html = '';
            window.patologiasNuevoCliente.forEach((pat, index) => {
                html += `<tr id="nuevo-patologia-row-${pat.id}">
                    <td class="text-center">${index + 1}<\/td>
                    <td>${escapeHtml(pat.nombre)}<\/td>
                    <td class="text-center">
                        <button type="button" class="btn btn-sm btn-outline-danger btn-action" 
                                onclick="window.eliminarPatologiaNuevoCliente(${pat.id})" 
                                title="Eliminar patología">
                            <i class="bi bi-trash"><\/i>
                        <\/button>
                    <\/td>
                <\/tr>`;
            });
            tbody.innerHTML = html;
        }

        function escapeHtml(text) {
            if (!text) return '';
            return text.replace(/[&<>]/g, function(m) {
                if (m === '&') return '&amp;';
                if (m === '<') return '&lt;';
                if (m === '>') return '&gt;';
                return m;
            });
        }

        // ============================================
        // BÚSQUEDA Y AGREGADO DE PATOLOGÍAS
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
                    const yaExiste = window.patologiasNuevoCliente.some(p => p.id === pat.id_patologia);
                    return `<div class="list-group-item list-group-item-action ${yaExiste ? 'disabled opacity-50' : ''}" 
                            onclick="${!yaExiste ? `window.agregarPatologiaNuevoCliente(${pat.id_patologia}, '${escapeHtml(pat.descripcion)}')` : ''}" 
                            style="cursor: ${yaExiste ? 'not-allowed' : 'pointer'};">
                            <div class="d-flex justify-content-between align-items-center">
                                <div><strong>${escapeHtml(pat.descripcion)}</strong></div>
                                ${yaExiste ? '<span class="badge bg-secondary">Ya agregada</span>' : '<span class="badge bg-success">Click para agregar</span>'}
                            </div>
                        </div>`;
                }).join('');
            }
            resultadosDiv.style.display = 'block';
        }

        window.agregarPatologiaNuevoCliente = function(id, descripcion) {
            if (window.patologiasNuevoCliente.some(p => p.id === id)) return;

            window.patologiasNuevoCliente.push({ 
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

            const patologia = window.patologiasNuevoCliente.find(p => p.id === id);
            
            document.getElementById('detalleConfirmacion').textContent = 
                `¿Eliminar "${patologia?.nombre}" de la lista?`;

            const btnConfirmar = document.getElementById('btnConfirmarEliminar');
            const originalOnClick = btnConfirmar.onclick;

            btnConfirmar.onclick = function() {
                window.patologiasNuevoCliente = window.patologiasNuevoCliente.filter(p => p.id !== id);
                renderizarTablaPatologias();
                if (window.mostrarToast) window.mostrarToast(`"${patologia?.nombre}" eliminada`, 'warning');
                btnConfirmar.onclick = originalOnClick;
                bootstrap.Modal.getInstance(modalConfirmar).hide();
            };

            new bootstrap.Modal(modalConfirmar).show();
        };

        // ============================================
        // GUARDAR NUEVO CLIENTE
        // ============================================
        window.guardarNuevoCliente = function() {
            const toNull = (valor) => valor === '' ? null : valor;

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
                pais_id: toNull(document.getElementById('pais_id')?.value),
                estado_id: toNull(document.getElementById('estado_id')?.value),
                municipio_id: toNull(document.getElementById('municipio_id')?.value),
                localidad_id: toNull(document.getElementById('localidad_id')?.value),
                enfermedades: window.patologiasNuevoCliente.map(p => p.id),
                contacto_id: toNull(document.getElementById('contacto_id')?.value),
                _token: '{{ csrf_token() }}'
            };

            // Validaciones básicas
            if (!formData.Nombre || !formData.apPaterno) {
                if (window.mostrarToast) window.mostrarToast('Completa los campos requeridos (Nombre y Apellido Paterno)', 'warning');
                return;
            }

            // Validar email SOLO si tiene valor
            if (formData.email1 && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(formData.email1)) {
                if (window.mostrarToast) window.mostrarToast('Correo electrónico no válido', 'warning');
                return;
            }

            fetch('{{ route("clientes.store") }}', {
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
                    // Cerrar modal de forma segura
                    const modalElement = document.getElementById('modalNuevoCliente');
                    if (modalElement) {
                        const modal = bootstrap.Modal.getInstance(modalElement);
                        if (modal) {
                            modal.hide();
                        } else {
                            // Fallback manual
                            modalElement.style.display = 'none';
                            modalElement.classList.remove('show');
                            document.body.classList.remove('modal-open');
                            const backdrop = document.querySelector('.modal-backdrop');
                            if (backdrop) backdrop.remove();
                        }
                    }
                    
                    if (window.mostrarToast) window.mostrarToast('Cliente creado correctamente', 'success');
                    setTimeout(() => location.reload(), 1500);
                } else if (data.errors) {
                    let mensajes = Object.values(data.errors).flat().join('\n');
                    if (window.mostrarToast) window.mostrarToast(mensajes, 'danger');
                } else {
                    if (window.mostrarToast) window.mostrarToast(data.message || 'Error al crear cliente', 'danger');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                if (window.mostrarToast) window.mostrarToast('Error de conexión', 'danger');
            });
        };

        // ============================================
        // INICIALIZACIÓN DE TOMSELECTS
        // ============================================
        function inicializarTomSelectsNuevo() {
            // Destruir instancias previas si existen
            const selectsIds = ['pais_select_nuevo', 'estado_select_nuevo', 'municipio_select_nuevo', 'localidad_select_nuevo'];
            selectsIds.forEach(id => {
                const el = document.getElementById(id);
                if (el && el.tomselect) {
                    el.tomselect.destroy();
                }
            });

            // 1. Inicializar País
            const paisElement = document.getElementById('pais_select_nuevo');
            if (paisElement) {
                paisSelectNuevo = new TomSelect(paisElement, {
                    create: false,
                    sortField: 'text',
                    placeholder: 'Buscar país...',
                    onChange: function(value) {
                        document.getElementById('pais_id').value = value || '';
                        
                        // Resetear campos ocultos dependientes
                        document.getElementById('estado_id').value = '';
                        document.getElementById('municipio_id').value = '';
                        document.getElementById('localidad_id').value = '';
                        
                        // Limpiar y deshabilitar estado
                        if (estadoSelectNuevo) {
                            estadoSelectNuevo.clear();
                            estadoSelectNuevo.clearOptions();
                            estadoSelectNuevo.disable();
                            estadoSelectNuevo.addOption({value: '', text: 'Primero seleccione un país'});
                            estadoSelectNuevo.setValue('');
                        }
                        
                        // Deshabilitar municipio y localidad
                        if (municipioSelectNuevo) {
                            municipioSelectNuevo.clear();
                            municipioSelectNuevo.clearOptions();
                            municipioSelectNuevo.disable();
                        }
                        if (localidadSelectNuevo) {
                            localidadSelectNuevo.clear();
                            localidadSelectNuevo.clearOptions();
                            localidadSelectNuevo.disable();
                        }
                        
                        if (value && estadoSelectNuevo) {
                            estadoSelectNuevo.enable();
                            estadoSelectNuevo.clearOptions();
                            estadoSelectNuevo.load(function(callback) {
                                fetch(`/api/estados/${value}`)
                                    .then(response => response.json())
                                    .then(data => {
                                        const options = [{value: '', text: 'Seleccione un estado...'}, ...data];
                                        callback(options);
                                    })
                                    .catch(() => callback([{value: '', text: 'Error al cargar estados'}]));
                            });
                        }
                    }
                });
                cargarPaisesEnSelectNuevo();
            }

            // 2. Inicializar Estado
            const estadoElement = document.getElementById('estado_select_nuevo');
            if (estadoElement) {
                estadoSelectNuevo = new TomSelect(estadoElement, {
                    create: false,
                    sortField: 'text',
                    placeholder: 'Buscar estado...',
                    load: function(query, callback) {
                        const paisId = document.getElementById('pais_id').value;
                        if (!paisId) return callback([{value: '', text: 'Primero seleccione un país'}]);
                        let url = `/api/estados/${paisId}`;
                        if (query && query.length > 0) {
                            url += `?q=${encodeURIComponent(query)}`;
                        }
                        fetch(url)
                            .then(response => response.json())
                            .then(data => {
                                const options = [{value: '', text: 'Seleccione un estado...'}, ...data];
                                callback(options);
                            })
                            .catch(() => callback([{value: '', text: 'Error al cargar estados'}]));
                    },
                    onChange: function(value) {
                        document.getElementById('estado_id').value = value || '';
                        document.getElementById('municipio_id').value = '';
                        document.getElementById('localidad_id').value = '';
                        
                        if (municipioSelectNuevo) {
                            municipioSelectNuevo.clear();
                            municipioSelectNuevo.clearOptions();
                            municipioSelectNuevo.disable();
                        }
                        if (localidadSelectNuevo) {
                            localidadSelectNuevo.clear();
                            localidadSelectNuevo.clearOptions();
                            localidadSelectNuevo.disable();
                        }
                        
                        if (value && municipioSelectNuevo) {
                            municipioSelectNuevo.enable();
                            municipioSelectNuevo.clearOptions();
                            municipioSelectNuevo.load(function(callback) {
                                fetch(`/api/municipios/${value}`)
                                    .then(response => response.json())
                                    .then(data => {
                                        const options = [{value: '', text: 'Seleccione un municipio...'}, ...data];
                                        callback(options);
                                    })
                                    .catch(() => callback([{value: '', text: 'Error al cargar municipios'}]));
                            });
                        }
                    }
                });
                estadoSelectNuevo.disable();
                estadoSelectNuevo.addOption({value: '', text: 'Primero seleccione un país'});
                estadoSelectNuevo.setValue('');
            }

            // 3. Inicializar Municipio
            const municipioElement = document.getElementById('municipio_select_nuevo');
            if (municipioElement) {
                municipioSelectNuevo = new TomSelect(municipioElement, {
                    create: false,
                    sortField: 'text',
                    placeholder: 'Buscar municipio...',
                    load: function(query, callback) {
                        const estadoId = document.getElementById('estado_id').value;
                        if (!estadoId) return callback([{value: '', text: 'Primero seleccione un estado'}]);
                        let url = `/api/municipios/${estadoId}`;
                        if (query && query.length > 0) {
                            url += `?q=${encodeURIComponent(query)}`;
                        }
                        fetch(url)
                            .then(response => response.json())
                            .then(data => {
                                const options = [{value: '', text: 'Seleccione un municipio...'}, ...data];
                                callback(options);
                            })
                            .catch(() => callback([{value: '', text: 'Error al cargar municipios'}]));
                    },
                    onChange: function(value) {
                        document.getElementById('municipio_id').value = value || '';
                        document.getElementById('localidad_id').value = '';
                        
                        if (localidadSelectNuevo) {
                            localidadSelectNuevo.clear();
                            localidadSelectNuevo.clearOptions();
                            localidadSelectNuevo.disable();
                        }
                        
                        if (value && localidadSelectNuevo) {
                            localidadSelectNuevo.enable();
                            localidadSelectNuevo.clearOptions();
                            localidadSelectNuevo.load(function(callback) {
                                fetch(`/api/localidades/${value}`)
                                    .then(response => response.json())
                                    .then(data => {
                                        const options = [{value: '', text: 'Seleccione una localidad...'}, ...data];
                                        callback(options);
                                    })
                                    .catch(() => callback([{value: '', text: 'Error al cargar localidades'}]));
                            });
                        }
                    }
                });
                municipioSelectNuevo.disable();
                municipioSelectNuevo.addOption({value: '', text: 'Primero seleccione un estado'});
                municipioSelectNuevo.setValue('');
            }

            // 4. Inicializar Localidad
            const localidadElement = document.getElementById('localidad_select_nuevo');
            if (localidadElement) {
                localidadSelectNuevo = new TomSelect(localidadElement, {
                    create: false,
                    sortField: 'text',
                    placeholder: 'Buscar localidad...',
                    load: function(query, callback) {
                        const municipioId = document.getElementById('municipio_id').value;
                        if (!municipioId) return callback([{value: '', text: 'Primero seleccione un municipio'}]);
                        let url = `/api/localidades/${municipioId}`;
                        if (query && query.length > 0) {
                            url += `?q=${encodeURIComponent(query)}`;
                        }
                        fetch(url)
                            .then(response => response.json())
                            .then(data => {
                                const options = [{value: '', text: 'Seleccione una localidad...'}, ...data];
                                callback(options);
                            })
                            .catch(() => callback([{value: '', text: 'Error al cargar localidades'}]));
                    },
                    onChange: function(value) {
                        document.getElementById('localidad_id').value = value || '';
                    }
                });
                localidadSelectNuevo.disable();
                localidadSelectNuevo.addOption({value: '', text: 'Primero seleccione un municipio'});
                localidadSelectNuevo.setValue('');
            }
        }

        // ============================================
        // FUNCIÓN PARA CARGAR PAÍSES VÍA AJAX
        // ============================================
        async function cargarPaisesEnSelectNuevo() {
            try {
                const response = await fetch('/api/paises');
                const paises = await response.json();
                
                if (paisSelectNuevo) {
                    paisSelectNuevo.clearOptions();
                    paisSelectNuevo.addOption({value: '', text: 'Seleccione un país...'});
                    paisSelectNuevo.addOption(paises.map(p => ({value: p.id, text: p.pais})));
                }
            } catch (error) {
                console.error('Error al cargar países:', error);
                if (paisSelectNuevo) {
                    paisSelectNuevo.clearOptions();
                    paisSelectNuevo.addOption({value: '', text: 'Error al cargar países'});
                }
            }
        }

        // ============================================
        // INICIALIZACIÓN PRINCIPAL
        // ============================================
        document.addEventListener('DOMContentLoaded', function() {
            inicializarTomSelectsNuevo();

            const modal = document.getElementById('modalNuevoCliente');
            if (modal) {
                modal.addEventListener('show.bs.modal', function() {
                    window.patologiasNuevoCliente = [];
                    renderizarTablaPatologias();
                    if (todasPatologias.length === 0) cargarCatalogoPatologias();
                    document.getElementById('buscarPatologiaNuevoModal').value = '';
                    document.getElementById('resultadosPatologiaNuevo').style.display = 'none';
                    
                    cargarTiposContacto();
                    
                    setTimeout(() => {
                        inicializarTomSelectsNuevo();
                    }, 50);
                });
            }

            const buscador = document.getElementById('buscarPatologiaNuevoModal');
            if (buscador) {
                buscador.addEventListener('input', function() {
                    buscarPatologias(this.value);
                });
            }

            document.addEventListener('click', function(event) {
                const resultados = document.getElementById('resultadosPatologiaNuevo');
                const buscador = document.getElementById('buscarPatologiaNuevoModal');
                if (resultados && !resultados.contains(event.target) && event.target !== buscador) {
                    resultados.style.display = 'none';
                }
            });
        });
    }
</script>
@endpush