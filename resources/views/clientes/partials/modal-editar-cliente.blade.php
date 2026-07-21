<!-- Modal Editar Cliente -->
<div class="modal fade" id="modalEditarCliente" tabindex="-1" aria-labelledby="modalEditarClienteLabel">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-warning">
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
                            <input type="text" class="form-control" id="edit_Nombre" name="Nombre" 
                                   onkeydown="return soloLetras(event)"
                                   oninput="aMayusculas(event)"
                                   required>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Ap. Paterno <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="edit_apPaterno" name="apPaterno" 
                                   onkeydown="return soloLetras(event)"
                                   oninput="aMayusculas(event)"
                                   required>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Ap. Materno</label>
                            <input type="text" class="form-control" id="edit_apMaterno" name="apMaterno" 
                                   onkeydown="return soloLetras(event)"
                                   oninput="aMayusculas(event)">
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
                                <option value="INACTIVO">Inactivo</option>
                                <option value="BLOQUEADO">Bloqueado</option>
                            </select>
                        </div>
                    </div>

                    <!-- Contacto -->
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Teléfono Principal</label>
                            <input type="text" class="form-control" id="edit_telefono1" name="telefono1" 
                                   onkeydown="return soloNumeros(event)">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Teléfono Secundario</label>
                            <input type="text" class="form-control" id="edit_telefono2" name="telefono2" 
                                   onkeydown="return soloNumeros(event)">
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" class="form-control" id="edit_email1" name="email1">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Preferencia de contacto</label>
                            <select class="form-select" id="edit_contacto_id" name="edit_contacto_id">
                                <option value="">Seleccionar tipo...</option>
                            </select>
                        </div>
                    </div>

                    <!-- Ubicación - Buscadores personalizados -->
                    <div class="row">
                        <!-- PAIS -->
                        <div class="col-md-3 mb-3">
                            <label class="form-label">País</label>
                            <div class="custom-select-wrapper" id="wrapper_pais_edit">
                                <input type="text" 
                                       class="form-control custom-select-input" 
                                       id="pais_search_edit"
                                       placeholder="Buscar país..."
                                       autocomplete="off">
                                <input type="hidden" id="pais_id_edit" name="pais_id">
                                <div class="custom-select-results" id="pais_results_edit"></div>
                            </div>
                        </div>
                        
                        <!-- ESTADO -->
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Estado</label>
                            <div class="custom-select-wrapper" id="wrapper_estado_edit">
                                <input type="text" 
                                       class="form-control custom-select-input" 
                                       id="estado_search_edit"
                                       placeholder="Primero seleccione un país"
                                       autocomplete="off"
                                       disabled>
                                <input type="hidden" id="estado_id_edit" name="estado_id">
                                <div class="custom-select-results" id="estado_results_edit"></div>
                            </div>
                        </div>
                        
                        <!-- MUNICIPIO -->
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Municipio</label>
                            <div class="custom-select-wrapper" id="wrapper_municipio_edit">
                                <input type="text" 
                                       class="form-control custom-select-input" 
                                       id="municipio_search_edit"
                                       placeholder="Primero seleccione un estado"
                                       autocomplete="off"
                                       disabled>
                                <input type="hidden" id="municipio_id_edit" name="municipio_id">
                                <div class="custom-select-results" id="municipio_results_edit"></div>
                            </div>
                        </div>
                        
                        <!-- LOCALIDAD -->
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Localidad</label>
                            <div class="custom-select-wrapper" id="wrapper_localidad_edit">
                                <input type="text" 
                                       class="form-control custom-select-input" 
                                       id="localidad_search_edit"
                                       placeholder="Primero seleccione un municipio"
                                       autocomplete="off"
                                       disabled>
                                <input type="hidden" id="localidad_id_edit" name="localidad_id">
                                <div class="custom-select-results" id="localidad_results_edit"></div>
                            </div>
                        </div>
                    </div>

                    <!-- Dirección -->
                    <div class="mb-3">
                        <label class="form-label">Domicilio</label>
                        <textarea class="form-control" id="edit_Domicilio" name="Domicilio" rows="2"></textarea>
                    </div>

                    <!-- Campo de intereses en el modal -->
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="buscador-intereses-edit">Intereses</label>
                                <div class="input-group">
                                    <input type="text" id="buscador-intereses-edit" class="form-control" 
                                        placeholder="Buscar intereses...">
                                    <button class="btn btn-outline-secondary" type="button">
                                        <i class="bi bi-search"></i>
                                    </button>
                                </div>
                                <div id="resultados-intereses-edit" class="list-group mt-2" style="max-height: 150px; overflow-y: auto; display: none;"></div>
                                <div id="intereses-seleccionados-edit" class="mt-2"></div>
                                <small class="text-muted">Selecciona los intereses del cliente</small>
                            </div>
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
// ============================================
// FUNCIÓN GLOBAL PARA CARGAR INTERESES (disponible desde cualquier vista)
// ============================================
window.cargarInteresesCliente = function(idCliente) {
    fetch('/clientes/' + idCliente + '/intereses')
        .then(response => response.json())
        .then(data => {
            if (data.success && data.data) {
                // Usar la variable global interesesSeleccionadosEdit
                window.interesesSeleccionadosEdit = data.data.map(item => ({
                    id: item.id_interes,
                    text: item.Descripcion
                }));
                // Si la función renderizarInteresesEdit existe, llamarla
                if (typeof window.renderizarInteresesEdit === 'function') {
                    window.renderizarInteresesEdit();
                }
            }
        })
        .catch(error => {
            console.error('Error al cargar intereses:', error);
        });
};

// Función global para renderizar intereses en edición
window.renderizarInteresesEdit = function() {
    const container = document.getElementById('intereses-seleccionados-edit');
    if (!container) return;
    
    const intereses = window.interesesSeleccionadosEdit || [];
    let html = '';
    
    if (intereses.length > 0) {
        html = '<div class="d-flex flex-wrap gap-1">';
        intereses.forEach(function(item) {
            html += `<span class="badge bg-primary d-inline-flex align-items-center" style="font-size: 14px; padding: 8px 12px;">
                        ${item.text}
                        <i class="bi bi-x-circle ms-1" style="cursor: pointer;" 
                           onclick="window.quitarInteresEdit(${item.id})"></i>
                    </span>`;
        });
        html += '</div>';
        html += `<input type="hidden" id="intereses_ids_edit" name="intereses_ids_edit" value="${intereses.map(i => i.id).join(',')}">`;
    } else {
        html = '<small class="text-muted">No hay intereses seleccionados</small>';
        html += `<input type="hidden" id="intereses_ids_edit" name="intereses_ids_edit" value="">`;
    }
    
    container.innerHTML = html;
};

// Función global para quitar intereses
window.quitarInteresEdit = function(id) {
    if (window.interesesSeleccionadosEdit) {
        window.interesesSeleccionadosEdit = window.interesesSeleccionadosEdit.filter(i => i.id != id);
        window.renderizarInteresesEdit();
    }
};

// Función global para agregar intereses
window.agregarInteresEdit = function(id, text) {
    if (!window.interesesSeleccionadosEdit) {
        window.interesesSeleccionadosEdit = [];
    }
    if (!window.interesesSeleccionadosEdit.some(i => i.id === id)) {
        window.interesesSeleccionadosEdit.push({ id: parseInt(id), text: text });
        window.renderizarInteresesEdit();
        const resultados = document.getElementById('resultados-intereses-edit');
        if (resultados) resultados.style.display = 'none';
        const buscador = document.getElementById('buscador-intereses-edit');
        if (buscador) buscador.value = '';
    }
};

// Función global para buscar intereses en edición
window.buscarInteresesEdit = function(term) {
    if (term.length < 2) {
        const resultados = document.getElementById('resultados-intereses-edit');
        if (resultados) resultados.style.display = 'none';
        return;
    }

    fetch('/clientes/buscar-intereses?q=' + encodeURIComponent(term))
        .then(response => response.json())
        .then(data => {
            const resultadosDiv = document.getElementById('resultados-intereses-edit');
            if (!resultadosDiv) return;
            
            if (data.results && data.results.length > 0) {
                let html = '';
                data.results.forEach(function(item) {
                    const yaSeleccionado = (window.interesesSeleccionadosEdit || []).some(i => i.id === item.id);
                    if (!yaSeleccionado) {
                        html += `<button type="button" class="list-group-item list-group-item-action" 
                                    data-id="${item.id}" data-text="${item.text}"
                                    onclick="window.agregarInteresEdit(${item.id}, '${item.text.replace(/'/g, "\\'")}')">
                                    ${item.text}
                                </button>`;
                    }
                });
                if (html) {
                    resultadosDiv.innerHTML = html;
                    resultadosDiv.style.display = 'block';
                } else {
                    resultadosDiv.innerHTML = '<div class="list-group-item text-muted">Todos los intereses ya están seleccionados</div>';
                    resultadosDiv.style.display = 'block';
                }
            } else {
                resultadosDiv.innerHTML = '<div class="list-group-item text-muted">No se encontraron intereses</div>';
                resultadosDiv.style.display = 'block';
            }
        })
        .catch(error => {
            console.error('Error al buscar intereses:', error);
            const resultadosDiv = document.getElementById('resultados-intereses-edit');
            if (resultadosDiv) {
                resultadosDiv.innerHTML = '<div class="list-group-item text-danger">Error al buscar intereses</div>';
                resultadosDiv.style.display = 'block';
            }
        });
};
</script>
<script>
// ============================================
// VERIFICACIÓN PARA EVITAR DUPLICADOS
// ============================================
if (typeof window.modalEditarInicializado !== 'undefined') {
    // Si ya está inicializado, no hacer nada
} else {
    // Marcar como inicializado ANTES de cualquier declaración
    window.modalEditarInicializado = true;

    // ============================================
    // VARIABLES LOCALES
    // ============================================
        // Variables para intereses seleccionados
    let interesesSeleccionados = [];
    let clienteActualId = null;
    let todasPatologias = [];
    window.patologiasCliente = [];
    let paisSelect, estadoSelect, municipioSelect, localidadSelect;

    // ============================================
    // CLASE BUSCADOR PERSONALIZADO
    // ============================================
    class CustomSelect {
        constructor(config) {
            this.inputId = config.inputId;
            this.resultsId = config.resultsId;
            this.hiddenId = config.hiddenId;
            this.wrapperId = config.wrapperId || null;
            this.url = config.url;
            this.dependsOn = config.dependsOn || null;
            this.dependents = config.dependents || [];
            this.placeholder = config.placeholder || 'Buscar...';
            this.minChars = config.minChars || 1;
            this.debounceTime = config.debounceTime || 300;
            this.parentParam = config.parentParam || ':parentId';
            this.valueField = config.valueField || 'value';
            this.textField = config.textField || 'text';
            
            this.selectedValue = null;
            this.selectedText = '';
            this.data = [];
            this.isLoading = false;
            this.isOpen = false;
            this.debounceTimer = null;
            
            this.input = document.getElementById(this.inputId);
            this.results = document.getElementById(this.resultsId);
            this.hidden = document.getElementById(this.hiddenId);
            this.wrapper = this.wrapperId ? document.getElementById(this.wrapperId) : this.input?.closest('.custom-select-wrapper');
            
            if (!this.input) return;
            
            this.initEvents();
            this.setPlaceholder(this.placeholder);
            this.renderArrow();
        }
        
        initEvents() {
            this.input.addEventListener('input', this.onInput.bind(this));
            this.input.addEventListener('keydown', this.onKeydown.bind(this));
            this.input.addEventListener('focus', this.onFocus.bind(this));
            this.input.addEventListener('blur', this.onBlur.bind(this));
            this.input.addEventListener('click', this.onClick.bind(this));
            
            document.addEventListener('click', (e) => {
                if (!this.wrapper?.contains(e.target)) {
                    this.hideResults();
                }
            });
        }
        
        renderArrow() {
            if (this.wrapper) {
                const arrow = document.createElement('span');
                arrow.className = 'custom-select-arrow';
                arrow.innerHTML = '▼';
                arrow.style.fontSize = '10px';
                this.wrapper.appendChild(arrow);
            }
        }
        
        setPlaceholder(text) {
            this.input.placeholder = text;
        }
        
        setDisabled(disabled) {
            this.input.disabled = disabled;
            if (disabled) {
                this.input.value = '';
                this.hidden.value = '';
                this.selectedValue = null;
                this.selectedText = '';
                this.hideResults();
                this.input.classList.remove('custom-select-selected');
            }
        }
        
        setValue(value, text) {
            this.selectedValue = value;
            this.selectedText = text || '';
            this.input.value = text || '';
            this.hidden.value = value || '';
            this.hideResults();
            if (value) {
                this.input.classList.add('custom-select-selected');
            } else {
                this.input.classList.remove('custom-select-selected');
            }
            this.notifyDependents();
        }
        
        clear() {
            this.input.value = '';
            this.hidden.value = '';
            this.selectedValue = null;
            this.selectedText = '';
            this.hideResults();
            this.input.classList.remove('custom-select-selected');
            this.notifyDependents();
        }
        
        notifyDependents() {
            this.dependents.forEach(dep => {
                dep.clear();
                if (this.selectedValue) {
                    dep.setEnabled(true);
                    dep.setPlaceholder('Buscar...');
                } else {
                    dep.setEnabled(false);
                    dep.setPlaceholder(this.getDependentPlaceholder());
                }
            });
        }
        
        getDependentPlaceholder() {
            const names = {
                'pais': 'Primero seleccione un país',
                'estado': 'Primero seleccione un estado',
                'municipio': 'Primero seleccione un municipio'
            };
            return names[this.dependsOn] || 'Primero seleccione el anterior';
        }
        
        setEnabled(enabled) {
            this.input.disabled = !enabled;
            if (!enabled) {
                this.clear();
            }
        }
        
        getParentId() {
            if (this.dependsOn) {
                const parentHidden = document.getElementById(this.dependsOn + '_id_edit');
                if (parentHidden) {
                    return parentHidden.value;
                }
                const parentInput = document.getElementById(this.dependsOn + '_search_edit');
                if (parentInput) {
                    return parentInput.dataset.selectedValue || null;
                }
            }
            return null;
        }
        
        onInput(e) {
            const query = this.input.value.trim();
            this.selectedText = query;
            
            clearTimeout(this.debounceTimer);
            
            if (query.length >= this.minChars) {
                this.debounceTimer = setTimeout(() => {
                    this.search(query);
                }, this.debounceTime);
            } else {
                this.hideResults();
                if (query.length === 0) {
                    this.hidden.value = '';
                    this.selectedValue = null;
                    this.input.classList.remove('custom-select-selected');
                    this.notifyDependents();
                }
            }
        }
        
        onFocus() {
            const query = this.input.value.trim();
            if (query.length >= this.minChars) {
                this.search(query);
            } else if (query.length === 0 && this.data.length > 0) {
                this.renderResults('');
            } else if (this.selectedValue) {
                this.search('');
            }
        }
        
        onClick() {
            const query = this.input.value.trim();
            if (query.length >= this.minChars) {
                this.search(query);
            } else if (this.data.length > 0) {
                this.renderResults('');
            } else if (this.selectedValue) {
                this.search('');
            }
        }
        
        onBlur() {
            setTimeout(() => {
                if (this.wrapper && !this.wrapper.contains(document.activeElement)) {
                    this.hideResults();
                }
            }, 200);
        }
        
        onKeydown(e) {
            const items = this.results.querySelectorAll('.custom-select-item:not(.custom-select-no-results)');
            if (items.length === 0) return;
            
            let currentIndex = -1;
            items.forEach((item, index) => {
                if (item.classList.contains('selected')) {
                    currentIndex = index;
                    item.classList.remove('selected');
                }
            });
            
            if (e.key === 'ArrowDown') {
                e.preventDefault();
                const nextIndex = (currentIndex + 1) % items.length;
                items[nextIndex].classList.add('selected');
                items[nextIndex].scrollIntoView({ block: 'nearest' });
            } else if (e.key === 'ArrowUp') {
                e.preventDefault();
                const prevIndex = (currentIndex - 1 + items.length) % items.length;
                items[prevIndex].classList.add('selected');
                items[prevIndex].scrollIntoView({ block: 'nearest' });
            } else if (e.key === 'Enter') {
                e.preventDefault();
                const selected = this.results.querySelector('.custom-select-item.selected');
                if (selected) {
                    selected.click();
                } else if (items.length > 0) {
                    items[0].click();
                }
            } else if (e.key === 'Escape') {
                this.hideResults();
                this.input.blur();
            }
        }
        
        async search(query) {
            if (this.isLoading) return;
            
            const parentId = this.getParentId();
            if (this.dependsOn && !parentId) {
                this.showError('Seleccione el elemento anterior primero');
                return;
            }
            
            this.isLoading = true;
            this.showLoading();
            
            try {
                let url = this.url;
                if (this.dependsOn && parentId) {
                    url = url.replace(this.parentParam, parentId);
                }
                
                if (query && query.length > 0) {
                    url += (url.includes('?') ? '&' : '?') + `q=${encodeURIComponent(query)}`;
                }
                
                const response = await fetch(url);
                if (!response.ok) {
                    throw new Error(`HTTP ${response.status}`);
                }
                
                this.data = await response.json();
                this.renderResults(query);
            } catch (error) {
                console.error('Error en búsqueda:', error);
                this.showError('Error al cargar datos');
            } finally {
                this.isLoading = false;
            }
        }
        
        showLoading() {
            this.results.innerHTML = '<div class="custom-select-loading">Buscando...</div>';
            this.results.classList.add('active');
            this.isOpen = true;
            if (this.wrapper) {
                this.wrapper.classList.add('active');
            }
        }
        
        showError(message) {
            this.results.innerHTML = `<div class="custom-select-no-results">${message}</div>`;
            this.results.classList.add('active');
            this.isOpen = true;
            if (this.wrapper) {
                this.wrapper.classList.add('active');
            }
        }
        
        renderResults(query) {
            if (!this.data || this.data.length === 0) {
                this.results.innerHTML = `<div class="custom-select-no-results">No se encontraron resultados</div>`;
                this.results.classList.add('active');
                this.isOpen = true;
                if (this.wrapper) {
                    this.wrapper.classList.add('active');
                }
                return;
            }
            
            const regex = query ? new RegExp(`(${query.replace(/[.*+?^${}()|[\]\\]/g, '\\$&')})`, 'gi') : null;
            
            let html = '';
            this.data.forEach(item => {
                const value = item[this.valueField] || item.value || item.id || '';
                const text = item[this.textField] || item.text || item.nombre || item.pais || '';
                const highlighted = regex ? text.replace(regex, '<span class="highlight">$1</span>') : text;
                
                html += `
                    <div class="custom-select-item" 
                         data-value="${value}" 
                         data-text="${text.replace(/"/g, '&quot;')}"
                         onclick="window.__customSelectSelect(this, '${this.inputId}', '${this.hiddenId}')">
                        <span>${highlighted}</span>
                    </div>
                `;
            });
            
            this.results.innerHTML = html;
            this.results.classList.add('active');
            this.isOpen = true;
            if (this.wrapper) {
                this.wrapper.classList.add('active');
            }
        }
        
        hideResults() {
            this.results.classList.remove('active');
            this.isOpen = false;
            if (this.wrapper) {
                this.wrapper.classList.remove('active');
            }
        }
    }

    // ============================================
    // FUNCIÓN GLOBAL PARA SELECCIONAR ITEM
    // ============================================
    window.__customSelectSelect = function(element, inputId, hiddenId) {
        const value = element.dataset.value;
        const text = element.dataset.text;
        
        const input = document.getElementById(inputId);
        if (input) {
            const wrapper = input.closest('.custom-select-wrapper');
            if (wrapper && wrapper._customSelect) {
                wrapper._customSelect.setValue(value, text);
            }
        }
        
        const hidden = document.getElementById(hiddenId);
        if (hidden) {
            hidden.value = value;
        }
    };

    // ============================================
    // INICIALIZACIÓN DE BUSCADORES - MODAL EDITAR
    // ============================================
    function inicializarBuscadoresEditar() {
        const wrapperPais = document.getElementById('wrapper_pais_edit');
        const wrapperEstado = document.getElementById('wrapper_estado_edit');
        const wrapperMunicipio = document.getElementById('wrapper_municipio_edit');
        const wrapperLocalidad = document.getElementById('wrapper_localidad_edit');
        
        // 1. PAIS
        const paisSearch = new CustomSelect({
            inputId: 'pais_search_edit',
            resultsId: 'pais_results_edit',
            hiddenId: 'pais_id_edit',
            wrapperId: 'wrapper_pais_edit',
            url: '/api/paises',
            placeholder: 'Buscar país...',
            minChars: 1,
            dependents: []
        });
        if (wrapperPais) wrapperPais._customSelect = paisSearch;
        
        // 2. ESTADO
        const estadoSearch = new CustomSelect({
            inputId: 'estado_search_edit',
            resultsId: 'estado_results_edit',
            hiddenId: 'estado_id_edit',
            wrapperId: 'wrapper_estado_edit',
            url: '/api/estados/:parentId',
            placeholder: 'Primero seleccione un país',
            minChars: 1,
            dependsOn: 'pais',
            parentParam: ':parentId',
            dependents: []
        });
        if (wrapperEstado) wrapperEstado._customSelect = estadoSearch;
        
        // 3. MUNICIPIO
        const municipioSearch = new CustomSelect({
            inputId: 'municipio_search_edit',
            resultsId: 'municipio_results_edit',
            hiddenId: 'municipio_id_edit',
            wrapperId: 'wrapper_municipio_edit',
            url: '/api/municipios/:parentId',
            placeholder: 'Primero seleccione un estado',
            minChars: 1,
            dependsOn: 'estado',
            parentParam: ':parentId',
            dependents: []
        });
        if (wrapperMunicipio) wrapperMunicipio._customSelect = municipioSearch;
        
        // 4. LOCALIDAD
        const localidadSearch = new CustomSelect({
            inputId: 'localidad_search_edit',
            resultsId: 'localidad_results_edit',
            hiddenId: 'localidad_id_edit',
            wrapperId: 'wrapper_localidad_edit',
            url: '/api/localidades/:parentId',
            placeholder: 'Primero seleccione un municipio',
            minChars: 1,
            dependsOn: 'municipio',
            parentParam: ':parentId',
            dependents: []
        });
        if (wrapperLocalidad) wrapperLocalidad._customSelect = localidadSearch;
        
        // Establecer dependencias
        paisSearch.dependents = [estadoSearch];
        estadoSearch.dependents = [municipioSearch];
        municipioSearch.dependents = [localidadSearch];
        
        // Deshabilitar dependientes inicialmente
        estadoSearch.setDisabled(true);
        municipioSearch.setDisabled(true);
        localidadSearch.setDisabled(true);
        
        window.buscadoresEditar = {
            pais: paisSearch,
            estado: estadoSearch,
            municipio: municipioSearch,
            localidad: localidadSearch
        };
    }

    // ============================================
    // CARGAR UBICACIONES EN MODAL EDITAR
    // ============================================
    function cargarUbicacionesCliente(data) {
        if (!data) return;
        
        const buscadores = window.buscadoresEditar;
        if (!buscadores) return;
        
        if (data.pais_id && data.pais_nombre) {
            buscadores.pais.setValue(data.pais_id, data.pais_nombre);
        }
        
        if (data.estado_id && data.estado_nombre) {
            buscadores.estado.setValue(data.estado_id, data.estado_nombre);
        }
        
        if (data.municipio_id && data.municipio_nombre) {
            buscadores.municipio.setValue(data.municipio_id, data.municipio_nombre);
        }
        
        if (data.localidad_id && data.localidad_nombre) {
            buscadores.localidad.setValue(data.localidad_id, data.localidad_nombre);
        }
    }

    // ============================================
    // CARGAR TIPOS DE CONTACTO PARA EDITAR
    // ============================================
    function cargarTiposContactoEdit() {
        const select = document.getElementById('edit_contacto_id');
        if (!select) {
            console.warn('Select edit_contacto_id no encontrado, reintentando...');
            setTimeout(cargarTiposContactoEdit, 100);
            return;
        }

        fetch('{{ route("clientes.tipos-contacto") }}', {
            headers: { 'Accept': 'application/json' }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success && data.data) {
                select.innerHTML = '<option value="">Seleccionar tipo...</option>';
                data.data.forEach(tipo => {
                    select.innerHTML += `<option value="${tipo.id_tipo}">${tipo.nombre}</option>`;
                });
            }
        })
        .catch(error => console.error('Error cargando tipos:', error));
    }

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
                return true;
            }
            return false;
        } catch (error) {
            console.error('Error al cargar catálogo:', error);
            return false;
        }
    }

    // Función global para cargar patologías desde el index
    window.cargarPatologiasCliente = function(enfermedadesIds) {
        if (todasPatologias.length === 0) {
            cargarCatalogoPatologias().then(() => {
                procesarPatologias(enfermedadesIds);
            });
        } else {
            procesarPatologias(enfermedadesIds);
        }
    };

    function procesarPatologias(enfermedadesIds) {
        window.patologiasCliente = [];
        if (enfermedadesIds && Array.isArray(enfermedadesIds) && todasPatologias.length > 0) {
            enfermedadesIds.forEach(patId => {
                const patEncontrada = todasPatologias.find(p => p.id_patologia === patId);
                if (patEncontrada) {
                    window.patologiasCliente.push({
                        id: patEncontrada.id_patologia,
                        nombre: patEncontrada.descripcion
                    });
                }
            });
        }
        renderizarTablaPatologias();
    }

    // ============================================
    // FUNCIÓN PARA CARGAR DATOS DEL CLIENTE
    // ============================================
    async function cargarDatosCliente(clienteId) {
        try {
            // Primero cargar el catálogo si está vacío
            if (todasPatologias.length === 0) {
                await cargarCatalogoPatologias();
            }
            
            const response = await fetch(`/clientes/${clienteId}/edit`, { 
                headers: { 'Accept': 'application/json' } 
            });
            
            // Si la respuesta es HTML (redirección al login)
            const contentType = response.headers.get('content-type');
            if (contentType && contentType.includes('text/html')) {
                window.location.href = '/login';
                return;
            }
            
            const data = await response.json();

            if (data.success) {
                // Llenar datos básicos
                document.getElementById('edit_id_Cliente').value = data.data.id_Cliente;
                document.getElementById('edit_Nombre').value = data.data.Nombre;
                document.getElementById('edit_apPaterno').value = data.data.apPaterno;
                document.getElementById('edit_apMaterno').value = data.data.apMaterno || '';
                document.getElementById('edit_titulo').value = data.data.titulo || '';
                document.getElementById('edit_email1').value = data.data.email1 || '';
                document.getElementById('edit_telefono1').value = data.data.telefono1 || '';
                document.getElementById('edit_telefono2').value = data.data.telefono2 || '';
                document.getElementById('edit_Domicilio').value = data.data.Domicilio || '';
                document.getElementById('edit_Sexo').value = data.data.Sexo || '';
                
                // Formatear fecha correctamente
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

                const selectContacto = document.getElementById('edit_contacto_id');
                if (selectContacto) {
                    selectContacto.value = data.data.contacto_id || '';
                }
                
                // Cargar ubicaciones usando los buscadores personalizados
                cargarUbicacionesCliente(data.data);
                window.patologiasCliente = [];
                if (data.data.enfermedades && Array.isArray(data.data.enfermedades)) {
                    data.data.enfermedades.forEach(patId => {
                        const patEncontrada = todasPatologias.find(p => p.id_patologia === patId);
                        if (patEncontrada) {
                            window.patologiasCliente.push({
                                id: patEncontrada.id_patologia,
                                nombre: patEncontrada.descripcion
                            });
                        }
                    });
                }
                renderizarTablaPatologias();
            }
        } catch (error) {
            console.error('Error al cargar datos del cliente:', error);
            if (window.mostrarToast) window.mostrarToast('Error al cargar datos del cliente', 'danger');
        }
    }

    // ============================================
    // FUNCIONES DE LA TABLA DE PATOLOGÍAS
    // ============================================
    function renderizarTablaPatologias() {
        const tbody = document.getElementById('patologiasClienteBody');
        if (!tbody) return;

        if (window.patologiasCliente.length === 0) {
            tbody.innerHTML = `<tr id="sin-patologias-row">
                <td colspan="3" class="text-center py-4">
                    <i class="bi bi-heart-pulse text-muted" style="font-size: 2rem;"></i>
                    <p class="text-muted mt-2">Este cliente no tiene patologías registradas</p>
                </td>
            </tr>`;
            return;
        }

        let html = '';
        window.patologiasCliente.forEach((pat, index) => {
            html += `<tr id="patologia-row-${pat.id}">
                <td class="text-center">${index + 1}</td>
                <td>${escapeHtml(pat.nombre)}</td>
                <td class="text-center">
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
    // FUNCIONES DE BÚSQUEDA Y AGREGADO DE PATOLOGÍAS
    // ============================================
    function buscarPatologias(termino) {
        if (!termino || termino.length < 2) {
            document.getElementById('resultadosPatologia').style.display = 'none';
            return;
        }

        if (todasPatologias.length === 0) {
            cargarCatalogoPatologias().then(() => {
                buscarPatologias(termino);
            });
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
                const yaExiste = window.patologiasCliente.some(p => p.id === pat.id_patologia);
                return `<div class="list-group-item list-group-item-action ${yaExiste ? 'disabled opacity-50' : ''}" 
                        onclick="${!yaExiste ? `window.agregarPatologiaACliente(${pat.id_patologia}, '${escapeHtml(pat.descripcion)}')` : ''}" 
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

    window.agregarPatologiaACliente = function(id, descripcion) {
        if (window.patologiasCliente.some(p => p.id === id)) return;

        window.patologiasCliente.push({ 
            id: id, 
            nombre: descripcion
        });

        renderizarTablaPatologias();
        document.getElementById('buscarPatologiaModal').value = '';
        document.getElementById('resultadosPatologia').style.display = 'none';
        if (window.mostrarToast) window.mostrarToast('Patología agregada', 'success');
    };

    window.eliminarPatologiaDeTabla = function(id) {
        const patologia = window.patologiasCliente.find(p => p.id === id);
        if (!patologia) return;

        // Eliminar directamente sin modal
        window.patologiasCliente = window.patologiasCliente.filter(p => p.id !== id);
        renderizarTablaPatologias();
        
        if (window.mostrarToast) {
            window.mostrarToast(`"${patologia.nombre}" eliminada`, 'warning');
        }
    };

    // ============================================
    // FUNCIÓN PARA GUARDAR EDICIÓN
    // ============================================
    window.guardarEdicionCliente = function() {
        const selectContacto = document.getElementById('edit_contacto_id');
        
        let contactoId = null;
        if (selectContacto) {
            contactoId = selectContacto.value;
            if (contactoId === '' || contactoId === null || contactoId === undefined) {
                contactoId = null;
            } else {
                contactoId = parseInt(contactoId);
            }
        }
        
        const toNull = (valor) => valor === '' ? null : valor;
        let fechaNacEdit = document.getElementById('edit_FechaNac')?.value || null;
        const id = document.getElementById('edit_id_Cliente')?.value;
        
        // Obtener intereses seleccionados - DECLARAR CORRECTAMENTE
        const interesesIdsEdit = document.getElementById('intereses_ids_edit')?.value || '';
        const interesesArrayEdit = interesesIdsEdit ? interesesIdsEdit.split(',').map(Number) : [];
        
        const formData = {
            Nombre: document.getElementById('edit_Nombre')?.value || '',
            apPaterno: document.getElementById('edit_apPaterno')?.value || '',
            apMaterno: document.getElementById('edit_apMaterno')?.value || null,
            titulo: document.getElementById('edit_titulo')?.value || null,
            email1: document.getElementById('edit_email1')?.value || null,
            telefono1: document.getElementById('edit_telefono1')?.value || null,
            telefono2: document.getElementById('edit_telefono2')?.value || null,
            Domicilio: document.getElementById('edit_Domicilio')?.value || null,
            Sexo: document.getElementById('edit_Sexo')?.value || null,
            FechaNac: fechaNacEdit,
            status: document.getElementById('edit_status')?.value || 'PROSPECTO',
            pais_id: toNull(document.getElementById('pais_id_edit')?.value),
            estado_id: toNull(document.getElementById('estado_id_edit')?.value),
            municipio_id: toNull(document.getElementById('municipio_id_edit')?.value),
            localidad_id: toNull(document.getElementById('localidad_id_edit')?.value),
            enfermedades: window.patologiasCliente.map(p => p.id),
            contacto_id: contactoId,
            intereses: interesesArrayEdit,
            _token: '{{ csrf_token() }}',
            _method: 'PUT'
        };
        
        // Validaciones
        if (!formData.Nombre || !formData.apPaterno) {
            if (window.mostrarToast) window.mostrarToast('Completa los campos requeridos (Nombre y Apellido Paterno)', 'warning');
            return;
        }

        // Enviar
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
                if (modal) modal.hide();
                if (window.mostrarToast) window.mostrarToast('Cliente actualizado correctamente', 'success');
                setTimeout(() => location.reload(), 1000);
            } else if (data.errors) {
                let mensajes = Object.values(data.errors).flat().join('\n');
                if (window.mostrarToast) window.mostrarToast(mensajes, 'danger');
            } else {
                if (window.mostrarToast) window.mostrarToast(data.message || 'Error al actualizar cliente', 'danger');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            if (window.mostrarToast) window.mostrarToast('Error de conexión', 'danger');
        });
    };

    // ============================================
    // EVENT LISTENERS E INICIALIZACIÓN PRINCIPAL
    // ============================================
    document.addEventListener('DOMContentLoaded', function() {
        const modalEditar = document.getElementById('modalEditarCliente');
        if (modalEditar) {
            modalEditar.addEventListener('show.bs.modal', async function(event) {
                if (window._modalEditarInicializando) {
                    return;
                }
                window._modalEditarInicializando = true;
                
                try {
                    let clienteId = event.relatedTarget?.getAttribute('data-cliente-id');
                    if (!clienteId && window.clienteActualId) {
                        clienteId = window.clienteActualId;
                    }
                    
                    if (!clienteId) {
                        console.error('No se pudo obtener el ID del cliente');
                        return;
                    }
                    
                    // Inicializar buscadores
                    if (!window.buscadoresEditar) {
                        inicializarBuscadoresEditar();
                    } else {
                        // Resetear buscadores
                        const b = window.buscadoresEditar;
                        b.pais.clear();
                        b.estado.clear();
                        b.municipio.clear();
                        b.localidad.clear();
                        b.estado.setDisabled(true);
                        b.municipio.setDisabled(true);
                        b.localidad.setDisabled(true);
                    }
                    
                    const buscador = document.getElementById('buscarPatologiaModal');
                    if (buscador) buscador.value = '';
                    
                    const resultadosDiv = document.getElementById('resultadosPatologia');
                    if (resultadosDiv) resultadosDiv.style.display = 'none';
                    
                    window.patologiasCliente = [];
                    cargarTiposContactoEdit();
                    
                    // Cargar datos del cliente (patologías, ubicaciones, etc.)
                    await cargarDatosCliente(clienteId);
                    
                    // ============================================
                    // CARGAR INTERESES DEL CLIENTE
                    // ============================================
                    cargarInteresesCliente(clienteId);
                    
                } catch (error) {
                    console.error('Error en modal editar:', error);
                } finally {
                    window._modalEditarInicializando = false;
                }
            });
        }

        // Buscador de patologías
        const buscador = document.getElementById('buscarPatologiaModal');
        if (buscador) {
            buscador.addEventListener('input', function() {
                buscarPatologias(this.value);
            });
        }

        // Cerrar resultados al hacer clic fuera
        document.addEventListener('click', function(event) {
            const resultados = document.getElementById('resultadosPatologia');
            const buscador = document.getElementById('buscarPatologiaModal');
            if (resultados && !resultados.contains(event.target) && event.target !== buscador) {
                resultados.style.display = 'none';
            }
        });
    });
}
</script>
@endpush