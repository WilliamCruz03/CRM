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
                            <input type="email" class="form-control" id="email1" name="email1" required autocomplete="off">
                        </div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Preferencia de contacto</label>
                        <select class="form-select" id="contacto_id" name="contacto_id">
                            <option value="">Seleccionar tipo...</option>
                        </select>
                    </div>

                    <!-- Ubicación - Buscadores personalizados -->
                    <div class="row">
                        <!-- PAIS -->
                        <div class="col-md-3 mb-3">
                            <label class="form-label">País</label>
                            <div class="custom-select-wrapper" id="wrapper_pais_nuevo">
                                <input type="text" 
                                    class="form-control custom-select-input" 
                                    id="pais_search_nuevo"
                                    placeholder="Buscar país..."
                                    autocomplete="off">
                                <input type="hidden" id="pais_id_nuevo" name="pais_id">
                                <div class="custom-select-results" id="pais_results_nuevo"></div>
                            </div>
                        </div>
                        
                        <!-- ESTADO -->
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Estado</label>
                            <div class="custom-select-wrapper" id="wrapper_estado_nuevo">
                                <input type="text" 
                                    class="form-control custom-select-input" 
                                    id="estado_search_nuevo"
                                    placeholder="Primero seleccione un país"
                                    autocomplete="off"
                                    disabled>
                                <input type="hidden" id="estado_id_nuevo" name="estado_id">
                                <div class="custom-select-results" id="estado_results_nuevo"></div>
                            </div>
                        </div>
                        
                        <!-- MUNICIPIO -->
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Municipio</label>
                            <div class="custom-select-wrapper" id="wrapper_municipio_nuevo">
                                <input type="text" 
                                    class="form-control custom-select-input" 
                                    id="municipio_search_nuevo"
                                    placeholder="Primero seleccione un estado"
                                    autocomplete="off"
                                    disabled>
                                <input type="hidden" id="municipio_id_nuevo" name="municipio_id">
                                <div class="custom-select-results" id="municipio_results_nuevo"></div>
                            </div>
                        </div>
                        
                        <!-- LOCALIDAD -->
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Localidad</label>
                            <div class="custom-select-wrapper" id="wrapper_localidad_nuevo">
                                <input type="text" 
                                    class="form-control custom-select-input" 
                                    id="localidad_search_nuevo"
                                    placeholder="Primero seleccione un municipio"
                                    autocomplete="off"
                                    disabled>
                                <input type="hidden" id="localidad_id_nuevo" name="localidad_id">
                                <div class="custom-select-results" id="localidad_results_nuevo"></div>
                            </div>
                        </div>
                    </div>

                    <!-- Dirección -->
                    <div class="mb-3">
                        <label class="form-label">Domicilio</label>
                        <textarea class="form-control" id="Domicilio" name="Domicilio" rows="2"></textarea>
                    </div>

                    <!-- Intereses -->
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="buscador-intereses">Intereses</label>
                                <div class="input-group">
                                    <input type="text" id="buscador-intereses" class="form-control" 
                                        placeholder="Buscar intereses...">
                                </div>
                                <div id="resultados-intereses" class="list-group mt-2" style="max-height: 150px; overflow-y: auto; display: none;"></div>
                                <div id="intereses-seleccionados" class="mt-2"></div>
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

<style>
/* ============================================
   BUSCADOR PERSONALIZADO - ESTILO TOMSELECT
   ============================================ */

/* Contenedor principal */
.custom-select-wrapper {
    position: relative;
    width: 100%;
}

/* Input de búsqueda */
.custom-select-input {
    width: 100%;
    padding: 8px 12px;
    padding-right: 32px;
    font-size: 14px;
    font-weight: 400;
    line-height: 1.5;
    color: #212529;
    background-color: #fff;
    background-clip: padding-box;
    border: 1px solid #ced4da;
    border-radius: 4px;
    transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
}

.custom-select-input:focus {
    border-color: #86b7fe;
    outline: 0;
    box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
}

.custom-select-input:disabled {
    background-color: #e9ecef;
    opacity: 1;
    cursor: not-allowed;
    color: #6c757d;
}

/* Icono de flecha (similar a TomSelect) */
.custom-select-arrow {
    position: absolute;
    right: 12px;
    top: 50%;
    transform: translateY(-50%);
    pointer-events: none;
    color: #6c757d;
    font-size: 12px;
    transition: transform 0.15s ease-in-out;
}

.custom-select-wrapper.active .custom-select-arrow {
    transform: translateY(-50%) rotate(180deg);
}

/* Contenedor de resultados */
.custom-select-results {
    position: absolute;
    top: calc(100% + 2px);
    left: 0;
    right: 0;
    z-index: 9999;
    max-height: 200px;
    overflow-y: auto;
    background: #fff;
    border: 1px solid #ced4da;
    border-radius: 4px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    display: none;
    margin-top: 2px;
}

.custom-select-results.active {
    display: block;
}

/* Estilo de los items (similar a TomSelect) */
.custom-select-item {
    padding: 8px 12px;
    cursor: pointer;
    font-size: 14px;
    color: #212529;
    border-bottom: 1px solid #f0f0f0;
    transition: background-color 0.15s ease-in-out;
    display: flex;
    align-items: center;
    justify-content: space-between;
}

.custom-select-item:last-child {
    border-bottom: none;
}

.custom-select-item:hover,
.custom-select-item.selected {
    background-color: #f0f7ff;
}

.custom-select-item .highlight {
    font-weight: 600;
    color: #0d6efd;
}

.custom-select-item .badge {
    font-size: 11px;
    padding: 2px 8px;
    border-radius: 12px;
    background-color: #e9ecef;
    color: #6c757d;
}

/* Estados */
.custom-select-loading {
    padding: 12px;
    text-align: center;
    color: #6c757d;
    font-size: 14px;
}

.custom-select-loading::after {
    content: '';
    display: inline-block;
    width: 16px;
    height: 16px;
    margin-left: 8px;
    border: 2px solid #6c757d;
    border-top-color: transparent;
    border-radius: 50%;
    animation: custom-select-spin 0.6s linear infinite;
}

.custom-select-no-results {
    padding: 12px;
    text-align: center;
    color: #6c757d;
    font-size: 14px;
}

/* Scrollbar personalizada */
.custom-select-results::-webkit-scrollbar {
    width: 6px;
}

.custom-select-results::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 0 4px 4px 0;
}

.custom-select-results::-webkit-scrollbar-thumb {
    background: #c1c7cd;
    border-radius: 3px;
}

.custom-select-results::-webkit-scrollbar-thumb:hover {
    background: #a8b0b8;
}

/* Animación */
@keyframes custom-select-spin {
    to { transform: rotate(360deg); }
}

/* Estado de selección */
.custom-select-selected {
    border-color: #86b7fe;
    background-color: #f8faff;
}
</style>

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
        // CLASE BUSCADOR PERSONALIZADO (estilo TomSelect)
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
                
                // Elementos DOM
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
                // Input events
                this.input.addEventListener('input', this.onInput.bind(this));
                this.input.addEventListener('keydown', this.onKeydown.bind(this));
                this.input.addEventListener('focus', this.onFocus.bind(this));
                this.input.addEventListener('blur', this.onBlur.bind(this));
                this.input.addEventListener('click', this.onClick.bind(this));
                
                // Click fuera para cerrar
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
                    const parentHidden = document.getElementById(this.dependsOn + '_id_nuevo');
                    if (parentHidden) {
                        return parentHidden.value;
                    }
                    const parentInput = document.getElementById(this.dependsOn + '_search_nuevo');
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
                } else {
                    if (this.selectedValue) {
                        this.search('');
                    }
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
        // INICIALIZACIÓN DE BUSCADORES - MODAL NUEVO
        // ============================================
        function inicializarBuscadoresNuevo() {
            const wrapperPais = document.getElementById('wrapper_pais_nuevo');
            const wrapperEstado = document.getElementById('wrapper_estado_nuevo');
            const wrapperMunicipio = document.getElementById('wrapper_municipio_nuevo');
            const wrapperLocalidad = document.getElementById('wrapper_localidad_nuevo');
            
            // 1. PAIS
            const paisSearch = new CustomSelect({
                inputId: 'pais_search_nuevo',
                resultsId: 'pais_results_nuevo',
                hiddenId: 'pais_id_nuevo',
                wrapperId: 'wrapper_pais_nuevo',
                url: '/api/paises',
                placeholder: 'Buscar país...',
                minChars: 1,
                dependents: []
            });
            if (wrapperPais) wrapperPais._customSelect = paisSearch;
            
            // 2. ESTADO
            const estadoSearch = new CustomSelect({
                inputId: 'estado_search_nuevo',
                resultsId: 'estado_results_nuevo',
                hiddenId: 'estado_id_nuevo',
                wrapperId: 'wrapper_estado_nuevo',
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
                inputId: 'municipio_search_nuevo',
                resultsId: 'municipio_results_nuevo',
                hiddenId: 'municipio_id_nuevo',
                wrapperId: 'wrapper_municipio_nuevo',
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
                inputId: 'localidad_search_nuevo',
                resultsId: 'localidad_results_nuevo',
                hiddenId: 'localidad_id_nuevo',
                wrapperId: 'wrapper_localidad_nuevo',
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
            
            window.buscadoresNuevo = {
                pais: paisSearch,
                estado: estadoSearch,
                municipio: municipioSearch,
                localidad: localidadSearch
            };
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
                    </td>
                </tr>`;
                return;
            }

            let html = '';
            window.patologiasNuevoCliente.forEach((pat, index) => {
                html += `<tr id="nuevo-patologia-row-${pat.id}">
                    <td class="text-center">${index + 1}</td>
                    <td>${escapeHtml(pat.nombre)}</td>
                    <td class="text-center">
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
            const patologia = window.patologiasNuevoCliente.find(p => p.id === id);
            if (!patologia) return;

            // Eliminar directamente sin modal
            window.patologiasNuevoCliente = window.patologiasNuevoCliente.filter(p => p.id !== id);
            renderizarTablaPatologias();
            
            if (window.mostrarToast) {
                window.mostrarToast(`"${patologia.nombre}" eliminada`, 'warning');
            }
        };

        // ============================================
        // GUARDAR NUEVO CLIENTE
        // ============================================
        window.guardarNuevoCliente = function() {
            const toNull = (valor) => valor === '' ? null : valor;

            let fechaNac = document.getElementById('FechaNac')?.value || null;
            
            // Obtener intereses seleccionados
            const interesesIds = document.getElementById('intereses_ids')?.value || '';
            const interesesArray = interesesIds ? interesesIds.split(',').map(Number) : [];
            
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
                pais_id: toNull(document.getElementById('pais_id_nuevo')?.value),
                estado_id: toNull(document.getElementById('estado_id_nuevo')?.value),
                municipio_id: toNull(document.getElementById('municipio_id_nuevo')?.value),
                localidad_id: toNull(document.getElementById('localidad_id_nuevo')?.value),
                enfermedades: window.patologiasNuevoCliente.map(p => p.id),
                contacto_id: toNull(document.getElementById('contacto_id')?.value),
                intereses: interesesArray,
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
        // INICIALIZACIÓN PRINCIPAL
        // ============================================
        document.addEventListener('DOMContentLoaded', function() {
            const modal = document.getElementById('modalNuevoCliente');
            if (modal) {
                modal.addEventListener('show.bs.modal', async function() {
                    // Si ya está inicializando, omitir
                    if (window._modalNuevoInicializando) {
                        return;
                    }
                    window._modalNuevoInicializando = true;
                    
                    try {
                        window.patologiasNuevoCliente = [];
                        renderizarTablaPatologias();
                        if (todasPatologias.length === 0) await cargarCatalogoPatologias();
                        document.getElementById('buscarPatologiaNuevoModal').value = '';
                        document.getElementById('resultadosPatologiaNuevo').style.display = 'none';
                        
                        cargarTiposContacto();
                        
                        // Inicializar buscadores
                        if (!window.buscadoresNuevo) {
                            inicializarBuscadoresNuevo();
                        } else {
                            // Resetear buscadores
                            const b = window.buscadoresNuevo;
                            b.pais.clear();
                            b.estado.clear();
                            b.municipio.clear();
                            b.localidad.clear();
                            b.estado.setDisabled(true);
                            b.municipio.setDisabled(true);
                            b.localidad.setDisabled(true);
                        }
                    } finally {
                        window._modalNuevoInicializando = false;
                    }
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