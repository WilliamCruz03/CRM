(function () {
    'use strict';

    // =================================================================
    // Helpers compartidos
    // =================================================================

    function escapeHtml(str) {
        if (str === null || str === undefined) return '';
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    function formatearFechaLocal(valor) {
        if (!valor) return '—';
        const d = new Date(valor);
        if (isNaN(d.getTime())) return valor;
        const dia = String(d.getDate()).padStart(2, '0');
        const mes = String(d.getMonth() + 1).padStart(2, '0');
        const anio = d.getFullYear();
        return `${dia}/${mes}/${anio}`;
    }

    function formatearMonto(valor) {
        const n = Number(valor);
        if (!isFinite(n)) return '—';
        return '$' + n.toLocaleString('es-MX', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2,
        });
    }

    function formatearFrecuencia(valor) {
        const n = Number(valor);
        if (!isFinite(n) || n <= 0) return '—';
        return n.toFixed(2) + ' días';
    }

    function detectarMensajeError(err) {
        const msg = String(err && err.message ? err.message : err || '');
        if (/SQLSTATE/i.test(msg) || /Invalid object name/i.test(msg) || /Invalid column/i.test(msg)) {
            return 'Error de base de datos. Verifique la conexión o contacte al administrador.';
        }
        if (/Connection refused|ECONNREFUSED/i.test(msg)) {
            return 'No se pudo conectar con el servidor. Intente nuevamente.';
        }
        return msg || 'Ocurrió un error inesperado.';
    }

    function mostrarErrorGenerico(contId, spanId, mensaje) {
        const cont = document.getElementById(contId);
        const span = document.getElementById(spanId);
        if (!cont || !span) return;
        span.textContent = mensaje;
        cont.classList.remove('d-none');
        if (typeof window.mostrarToast === 'function') {
            window.mostrarToast(mensaje, 'danger');
        }
    }

    async function fetchJson(url) {
        const res = await fetch(url, {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
            credentials: 'same-origin',
            redirect: 'follow',
        });

        // Detectar redirect a login (o a cualquier página HTML).
        const contentType = res.headers.get('content-type') || '';
        const esJson = contentType.includes('application/json');

        if (!esJson) {
            const texto = await res.text();
            // Heurística: si el HTML contiene la pantalla de login, avisamos.
            if (/Iniciar Sesi[oó]n|name=["']password["']|id=["']login/i.test(texto)) {
                throw new Error('Tu sesión expiró. Recargá la página e iniciá sesión nuevamente.');
            }
            throw new Error('Respuesta no JSON: ' + texto.slice(0, 200));
        }

        const json = await res.json();
        if (!res.ok) {
            if (json && json.errors) {
                const first = Object.values(json.errors)[0];
                throw new Error(Array.isArray(first) ? first[0] : String(first));
            }
            throw new Error(json.message || `HTTP ${res.status}`);
        }
        return json;
    }

    // =================================================================
    // TAB 1 — Búsqueda por Artículo
    // =================================================================

    function initTab1() {
        const RUTAS = {
            data:    window.REPORTE_BUSQUEDA_ARTICULO_DATA_URL,
            tickets: window.REPORTE_BUSQUEDA_ARTICULO_TICKETS_URL || '',
        };

        const PER_PAGE_DEFAULT = 15;
        const SORT_BY_DEFAULT = 'compras_desc';

        const state = {
            ean: '',
            fecha_inicio: '',
            fecha_fin: '',
            per_page: PER_PAGE_DEFAULT,
            sort_by: SORT_BY_DEFAULT,
            sortKey: 'compras',
            sortDir: 'desc',
            clienteSeleccionado: null,
            ultimaData: null,
        };

        function normalizarEanEnInput() {
            const input = document.getElementById('inputEan');
            if (!input) return;
            const valor = (input.value || '').trim();
            if (/^\d+$/.test(valor) && valor.length < 13) {
                input.value = valor.padStart(13, '0');
            } else {
                input.value = valor;
            }
        }

        function renderEncabezado(articulo) {
            const cont = document.getElementById('articuloHeader');
            const eanEl = document.getElementById('articuloEan');
            const descEl = document.getElementById('articuloDescripcion');
            if (!cont || !eanEl || !descEl) return;
            eanEl.textContent = articulo.ean || '—';
            descEl.textContent = articulo.descripcion || '—';
            cont.classList.remove('d-none');
        }

        function renderTablaClientes(clientes) {
            const tbody = document.getElementById('tablaClientesBody');
            const wrapper = document.getElementById('tablaWrapper');
            if (!tbody || !wrapper) return;

            if (!clientes || clientes.length === 0) {
                tbody.innerHTML = `<tr><td colspan="8" class="text-center text-muted py-4">Sin resultados para el período seleccionado.</td></tr>`;
                wrapper.classList.remove('d-none');
                return;
            }

            tbody.innerHTML = clientes.map((c) => {
                const nombre = c.nombre_cliente || 'CLIENTE NO IDENTIFICADO';
                const telefono = c.telefono || '—';
                const sucursal = c.sucursal || '—';
                const isSelected = state.clienteSeleccionado === c.id_Cliente;
                const selectedClass = isSelected ? 'table-primary' : '';
                return `
                    <tr class="fila-cliente ${selectedClass}"
                        data-cliente-id="${c.id_Cliente || ''}"
                        data-cliente-nombre="${escapeHtml(nombre)}"
                        data-sucursal-id="${c.id_sucursal || ''}"
                        role="button">
                        <td>${escapeHtml(nombre)}</td>
                        <td>${escapeHtml(telefono)}</td>
                        <td>${escapeHtml(sucursal)}</td>
                        <td class="text-end">${escapeHtml(c.compras)}</td>
                        <td class="text-end">${escapeHtml(formatearFrecuencia(c.frecuencia))}</td>
                        <td>${escapeHtml(formatearFechaLocal(c.primera_compra))}</td>
                        <td>${escapeHtml(formatearFechaLocal(c.ultima_compra))}</td>
                        <td class="text-end">${escapeHtml(formatearMonto(c.monto_total))}</td>
                    </tr>`;
            }).join('');

            wrapper.classList.remove('d-none');
        }

        function renderPaginacion(paginacion) {
            const container = document.getElementById('paginacionContainer');
            const resumen = document.getElementById('resumenMostrando');
            if (container) container.innerHTML = paginacion && paginacion.links ? paginacion.links : '';
            if (resumen) {
                if (paginacion && paginacion.total > 0) {
                    resumen.textContent = `Mostrando ${paginacion.from} hasta ${paginacion.to} de ${paginacion.total} resultados`;
                } else {
                    resumen.textContent = '';
                }
            }

            if (container) {
                container.querySelectorAll('a[href]').forEach((a) => {
                    a.addEventListener('click', (ev) => {
                        ev.preventDefault();
                        const href = a.getAttribute('href') || '';
                        const url = new URL(href, window.location.origin);
                        const page = url.searchParams.get('page');
                        if (page) cargarData(Number(page));
                    });
                });
            }
        }

        function renderPanelDetalle(nombreCliente, tickets, articulo) {
            const wrapper = document.getElementById('detalleWrapper');
            const placeholder = document.getElementById('placeholderDetalle');
            const nombreEl = document.getElementById('detalleClienteNombre');
            const ticketsEl = document.getElementById('detalleTickets');

            if (!wrapper || !placeholder || !nombreEl || !ticketsEl) return;

            placeholder.classList.add('d-none');
            wrapper.classList.remove('d-none');
            nombreEl.textContent = nombreCliente || '—';

            if (!tickets || tickets.length === 0) {
                ticketsEl.innerHTML = `<div class="text-muted text-center py-3">Sin tickets para este cliente en el período.</div>`;
                return;
            }

            const eanBuscado = articulo && articulo.ean ? articulo.ean : state.ean;

            ticketsEl.innerHTML = tickets.map((t, idx) => {
                const artRows = (t.articulos || []).map((a) => {
                    const isBuscado = String(a.ean) === String(eanBuscado);
                    const destacado = isBuscado ? 'table-warning fw-semibold' : '';
                    return `
                        <tr class="${destacado}">
                            <td class="small">${escapeHtml(a.ean)}</td>
                            <td class="small">${escapeHtml(a.descripcion || '—')}</td>
                            <td class="small text-end">${escapeHtml(formatearMonto(a.monto))}</td>
                        </tr>`;
                }).join('');

                const ticketId = `ticket-body-${idx}`;
                const totalArticulos = (t.articulos || []).length;

                return `
                    <div class="border rounded mb-2">
                        <button type="button"
                                class="btn btn-link text-decoration-none w-100 d-flex justify-content-between align-items-center px-3 py-2 bg-light border-bottom ticket-toggle"
                                data-bs-toggle="collapse"
                                data-bs-target="#${ticketId}"
                                aria-expanded="false"
                                aria-controls="${ticketId}">
                            <span class="fw-semibold text-dark">
                                <i class="bi bi-chevron-right ticket-chevron"></i>
                                Ticket ${escapeHtml(t.F_NUMTICKE)}
                            </span>
                            <span class="text-muted small">
                                ${escapeHtml(formatearFechaLocal(t.FECHA_DT))} &middot; ${totalArticulos} art.
                            </span>
                        </button>
                        <div class="collapse ${idx === 0 ? 'show' : ''}" id="${ticketId}">
                            <div class="table-responsive">
                                <table class="table table-sm table-bordered mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th class="small">EAN</th>
                                            <th class="small">Descripción</th>
                                            <th class="small text-end">Monto</th>
                                        </tr>
                                    </thead>
                                    <tbody>${artRows}</tbody>
                                </table>
                            </div>
                        </div>
                    </div>`;
            }).join('');

            // Rotar el chevron al abrir/cerrar.
            ticketsEl.querySelectorAll('.collapse').forEach((el) => {
                el.addEventListener('show.bs.collapse', () => {
                    const btn = ticketsEl.querySelector(`[data-bs-target="#${el.id}"]`);
                    if (btn) btn.querySelector('.ticket-chevron')?.classList.add('rotated');
                });
                el.addEventListener('hide.bs.collapse', () => {
                    const btn = ticketsEl.querySelector(`[data-bs-target="#${el.id}"]`);
                    if (btn) btn.querySelector('.ticket-chevron')?.classList.remove('rotated');
                });
            });
        }

        function setLoading(loading) {
            const el = document.getElementById('loadingIndicator');
            if (!el) return;
            el.classList.toggle('d-none', !loading);
        }

        function setDetalleLoading(loading) {
            const el = document.getElementById('detalleLoading');
            if (!el) return;
            el.classList.toggle('d-none', !loading);
        }

        async function cargarData(page) {
        const errorCont = document.getElementById('errorContainer');
        if (errorCont) errorCont.classList.add('d-none');

        setLoading(true);
        try {
            const params = new URLSearchParams({
                ean: state.ean,
                fecha_inicio: state.fecha_inicio,
                fecha_fin: state.fecha_fin,
                per_page: String(state.per_page),
                sort_by: state.sort_by,
            });
            if (page && page > 1) params.set('page', String(page));

            const json = await fetchJson(`${RUTAS.data}?${params.toString()}`);
            if (!json.success) {
                mostrarErrorGenerico('errorContainer', 'errorMessage', json.message || 'No se pudo obtener el reporte.');
                return;
            }

            // Orden por defecto: compras DESC (in-place, una sola vez).
            const clientes = json.clientes || [];
            clientes.sort((a, b) => (Number(b.compras) || 0) - (Number(a.compras) || 0));
            json.clientes = clientes;

            state.ultimaData = json;
            state.sortKey = 'compras';
            state.sortDir = 'desc';

            renderEncabezado(json.articulo);
            renderKpis(json.kpis || null);
            document.getElementById('placeholderInicial').classList.add('d-none');
            renderTablaClientes(json.clientes);
            renderPaginacion(json.paginacion);
            actualizarIndicadoresOrden();

            state.clienteSeleccionado = null;
            document.getElementById('detalleWrapper').classList.add('d-none');
            document.getElementById('placeholderDetalle').classList.remove('d-none');
        } catch (err) {
            mostrarErrorGenerico('errorContainer', 'errorMessage', detectarMensajeError(err));
        } finally {
            setLoading(false);
        }
        }

        async function cargarTickets(clienteId) {
            if (!state.ultimaData) return;
            setDetalleLoading(true);
            try {
                const params = new URLSearchParams({
                    ean: state.ean,
                    fecha_inicio: state.fecha_inicio,
                    fecha_fin: state.fecha_fin,
                });
                const url = RUTAS.tickets.replace('__ID__', String(clienteId)) + '?' + params.toString();
                const json = await fetchJson(url);
                if (!json.success) {
                    mostrarErrorGenerico('errorContainer', 'errorMessage', json.message || 'No se pudo obtener el detalle.');
                    return;
                }
                const nombre = document.getElementById('detalleClienteNombre').textContent || '';
                renderPanelDetalle(nombre, json.tickets, json.articulo);
            } catch (err) {
                mostrarErrorGenerico('errorContainer', 'errorMessage', detectarMensajeError(err));
            } finally {
                setDetalleLoading(false);
            }
        }

        function seleccionarCliente(clienteId, nombre, sucursalId) {
            state.clienteSeleccionado = clienteId;
            state.sucursalSeleccionada = sucursalId;
            document.querySelectorAll('.fila-cliente').forEach((tr) => {
                const id = tr.getAttribute('data-cliente-id');
                tr.classList.toggle('table-primary', id === String(clienteId));
            });
            document.getElementById('placeholderDetalle').classList.add('d-none');
            document.getElementById('detalleWrapper').classList.remove('d-none');
            document.getElementById('detalleClienteNombre').textContent = nombre;
            cargarTickets(clienteId);
        }

        function renderKpis(kpis) {
            const wrapper = document.getElementById('kpisWrapper');
            if (!wrapper) return;

            if (!kpis) {
                wrapper.classList.add('d-none');
                return;
            }

            const articuloCaro = kpis.articulo_mayor_costo;
            const inv = kpis.inventario_por_costo || [];
            const productoMayor = kpis.producto_mayor_venta;

            if (articuloCaro) {
                document.getElementById('kpiArticuloCaro').textContent =
                    `${articuloCaro.ean} — ${articuloCaro.descripcion}`;
                document.getElementById('kpiArticuloCaroCosto').textContent =
                    `Costo: ${formatearMonto(articuloCaro.costo)}`;
            } else {
                document.getElementById('kpiArticuloCaro').textContent = '—';
                document.getElementById('kpiArticuloCaroCosto').textContent = '';
            }

            if (inv.length > 0) {
                const total = inv.reduce((acc, r) => acc + Number(r.inventario_costo || 0), 0);
                document.getElementById('kpiInventarioCosto').textContent = formatearMonto(total);
                document.getElementById('kpiInventarioSucursales').textContent =
                    `${inv.length} sucursales`;
            } else {
                document.getElementById('kpiInventarioCosto').textContent = '—';
                document.getElementById('kpiInventarioSucursales').textContent = '';
            }

            if (productoMayor) {
                document.getElementById('kpiProductoMayorVenta').textContent =
                    `${productoMayor.ean} — ${productoMayor.descripcion}`;
                document.getElementById('kpiProductoMayorVentaMonto').textContent =
                    formatearMonto(productoMayor.monto);
            } else {
                document.getElementById('kpiProductoMayorVenta').textContent = '—';
                document.getElementById('kpiProductoMayorVentaMonto').textContent = '';
            }

            wrapper.classList.remove('d-none');
        }

        // ------------------------------------------------------------------
        // Ordenamiento por clic en columnas
        // ------------------------------------------------------------------

        function getValorOrdenable(cliente, clave) {
            switch (clave) {
                case 'nombre':
                    return (cliente.nombre_cliente || '').toLowerCase();
                case 'telefono':
                    return (cliente.telefono || '').toLowerCase();
                case 'sucursal':
                    return (cliente.sucursal || '').toLowerCase();
                case 'compras':
                    return Number(cliente.compras) || 0;
                case 'frecuencia':
                    return Number(cliente.frecuencia) || 0;
                case 'primera_compra':
                    return cliente.primera_compra ? new Date(cliente.primera_compra).getTime() : 0;
                case 'ultima_compra':
                    return cliente.ultima_compra ? new Date(cliente.ultima_compra).getTime() : 0;
                case 'monto_total':
                    return Number(cliente.monto_total) || 0;
                default:
                    return 0;
            }
        }

        function ordenarClientes(clave, direccion) {
            if (!state.ultimaData || !state.ultimaData.clientes) return;

            state.ultimaData.clientes.sort((a, b) => {   // sin copia
                const va = getValorOrdenable(a, clave);
                const vb = getValorOrdenable(b, clave);
                if (va < vb) return direccion === 'asc' ? -1 : 1;
                if (va > vb) return direccion === 'asc' ? 1 : -1;
                return 0;
            });

            // Guardar estado de orden en memoria
            state.sortKey = clave;
            state.sortDir = direccion;

            renderTablaClientes(state.ultimaData.clientes);
            actualizarIndicadoresOrden();
        }

        function actualizarIndicadoresOrden() {
            document.querySelectorAll('#tablaClientesArticulo th.sortable').forEach((th) => {
                const key = th.getAttribute('data-sort-key');
                const ind = th.querySelector('.sort-indicator');
                if (!ind) return;
                if (key === state.sortKey) {
                    ind.textContent = state.sortDir === 'asc' ? '▲' : '▼';
                    th.classList.add('table-active');
                } else {
                    ind.textContent = '';
                    th.classList.remove('table-active');
                }
            });
        }

        function bindOrdenamientoColumnas() {
            document.querySelectorAll('#tablaClientesArticulo th.sortable').forEach((th) => {
                th.addEventListener('click', () => {
                    const key = th.getAttribute('data-sort-key');
                    if (!key || !state.ultimaData) return;

                    // Si es la misma columna: toggle asc/desc. Si es otra: default desc.
                    const nuevaDir = (state.sortKey === key && state.sortDir === 'desc') ? 'asc' : 'desc';
                    ordenarClientes(key, nuevaDir);
                });
            });
        }

        function validarFiltros() {
            const ean = (document.getElementById('inputEan').value || '').trim();
            const fi = document.getElementById('inputFechaInicio').value;
            const ff = document.getElementById('inputFechaFin').value;

            if (!ean) {
                window.mostrarToast && window.mostrarToast('Ingrese un código de barras.', 'warning');
                return false;
            }
            if (!fi || !ff) {
                window.mostrarToast && window.mostrarToast('Seleccione fecha inicio y fin.', 'warning');
                return false;
            }
            if (ff < fi) {
                window.mostrarToast && window.mostrarToast('La fecha fin debe ser igual o posterior a la fecha inicio.', 'warning');
                return false;
            }
            return true;
        }

        function onBuscar() {
            const eanEl = document.getElementById('inputEan');
            const fiEl = document.getElementById('inputFechaInicio');
            const ffEl = document.getElementById('inputFechaFin');

            if (!eanEl || !fiEl || !ffEl) return;

            const ean = (eanEl.value || '').trim();
            const fi = fiEl.value;
            const ff = ffEl.value;

            if (!ean) {
                window.mostrarToast && window.mostrarToast('Ingrese un código de barras.', 'warning');
                return;
            }
            if (!fi || !ff) {
                window.mostrarToast && window.mostrarToast('Seleccione fecha inicio y fin.', 'warning');
                return;
            }
            if (ff < fi) {
                window.mostrarToast && window.mostrarToast('La fecha fin debe ser igual o posterior a la fecha inicio.', 'warning');
                return;
            }

            normalizarEanEnInput();

            state.ean = ean;
            state.fecha_inicio = fi;
            state.fecha_fin = ff;

            const diasRango = (new Date(ff) - new Date(fi)) / (1000 * 60 * 60 * 24);
            if (diasRango > 365 && typeof window.mostrarToast === 'function') {
                window.mostrarToast('Rango de fechas mayor a 365 días: la consulta puede ser lenta.', 'warning');
            }

            cargarData(1);
        }

        function limpiarFiltros() {
            const eanEl = document.getElementById('inputEan');
            const fiEl = document.getElementById('inputFechaInicio');
            const ffEl = document.getElementById('inputFechaFin');

            if (eanEl) eanEl.value = '';
            if (fiEl) fiEl.value = '';
            if (ffEl) ffEl.value = '';

            state.ean = '';
            state.fecha_inicio = '';
            state.fecha_fin = '';
            state.per_page = PER_PAGE_DEFAULT;
            state.sort_by = SORT_BY_DEFAULT;
            state.sortKey = 'compras';
            state.sortDir = 'desc';
            state.clienteSeleccionado = null;
            state.ultimaData = null;

            document.getElementById('articuloHeader')?.classList.add('d-none');
            document.getElementById('tablaWrapper')?.classList.add('d-none');
            document.getElementById('placeholderInicial')?.classList.remove('d-none');
            document.getElementById('detalleWrapper')?.classList.add('d-none');
            document.getElementById('placeholderDetalle')?.classList.remove('d-none');
            document.getElementById('errorContainer')?.classList.add('d-none');

            actualizarIndicadoresOrden();
        }

        const eanInput = document.getElementById('inputEan');
        const fi = document.getElementById('inputFechaInicio');
        const ff = document.getElementById('inputFechaFin');

        if (eanInput) { eanInput.value = ''; eanInput.addEventListener('blur', normalizarEanEnInput); }
        if (fi) fi.value = '';
        if (ff) ff.value = '';

        document.getElementById('btnBuscar')?.addEventListener('click', onBuscar);
        document.getElementById('btnLimpiarFiltros')?.addEventListener('click', limpiarFiltros);
        document.getElementById('btnReintentar')?.addEventListener('click', () => { if (state.ean) cargarData(1); });
        document.getElementById('btnCerrarDetalle')?.addEventListener('click', () => {
            state.clienteSeleccionado = null;
            document.querySelectorAll('.fila-cliente').forEach((tr) => tr.classList.remove('table-primary'));
            document.getElementById('detalleWrapper').classList.add('d-none');
            document.getElementById('placeholderDetalle').classList.remove('d-none');
        });
        
        bindOrdenamientoColumnas();

    }

    // =================================================================
    // TAB 2 — Búsqueda por Cliente
    // =================================================================

    function initTab2() {
        const RUTAS = {
            buscarCliente: window.REPORTE_BUSQUEDA_CLIENTE_SEARCH_URL,
            dataCliente:   window.REPORTE_BUSQUEDA_CLIENTE_DATA_URL || '',
            detalleTicket: window.REPORTE_BUSQUEDA_CLIENTE_TICKET_URL || '',
        };

        const state = {
            clienteId: null,
            clienteNombre: null,
            clienteTelefono: null,
            chartGrupos: null,
            filtros: {
                fecha_inicio: '',
                fecha_fin: '',
            },
        };

        // =================================================================
        // Búsqueda de clientes (botón manual)
        // =================================================================

        async function buscarClientes() {
            const input = document.getElementById('inputBuscarCliente');
            if (!input) return;

            const q = (input.value || '').trim();
            const cont = document.getElementById('clienteErrorContainer');
            if (cont) cont.classList.add('d-none');

            if (q.length < 3) {
                window.mostrarToast && window.mostrarToast('Ingrese al menos 3 caracteres para buscar.', 'warning');
                return;
            }

            try {
                const json = await fetchJson(`${RUTAS.buscarCliente}?q=${encodeURIComponent(q)}`);
                if (!json.success) {
                    mostrarErrorGenerico('clienteErrorContainer', 'clienteErrorMessage', json.message || 'Sin resultados.');
                    return;
                }
                renderListaClientes(json.clientes || []);
            } catch (err) {
                mostrarErrorGenerico('clienteErrorContainer', 'clienteErrorMessage', detectarMensajeError(err));
            }
        }

        function renderListaClientes(clientes) {
            const wrapper = document.getElementById('listaClientesWrapper');
            const lista = document.getElementById('listaClientes');
            if (!wrapper || !lista) return;

            if (!clientes || clientes.length === 0) {
                lista.innerHTML = `<div class="list-group-item text-muted">Sin coincidencias.</div>`;
                wrapper.classList.remove('d-none');
                return;
            }

            lista.innerHTML = clientes.map((c) => {
                const tel = [c.telefono1, c.telefono2].filter(Boolean).join(' / ') || 'Sin teléfono';
                return `
                    <button type="button"
                            class="list-group-item list-group-item-action cliente-item"
                            data-cliente-id="${c.id_Cliente}"
                            data-cliente-nombre="${escapeHtml(c.nombre_completo || '—')}"
                            data-cliente-tel="${escapeHtml(tel)}">
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="fw-semibold">${escapeHtml(c.nombre_completo || '—')}</span>
                            <small class="text-muted">${escapeHtml(tel)}</small>
                        </div>
                    </button>`;
            }).join('');

            wrapper.classList.remove('d-none');

            lista.querySelectorAll('.cliente-item').forEach((btn) => {
                btn.addEventListener('click', () => {
                    const id = Number(btn.getAttribute('data-cliente-id'));
                    const nombre = btn.getAttribute('data-cliente-nombre') || '—';
                    const tel = btn.getAttribute('data-cliente-tel') || '';
                    seleccionarCliente(id, nombre, tel);
                });
            });
        }

        function seleccionarCliente(clienteId, nombre, sucursalId) {
            state.clienteSeleccionado = clienteId;
            state.sucursalSeleccionada = sucursalId;
            document.querySelectorAll('.fila-cliente').forEach((tr) => {
                const id = tr.getAttribute('data-cliente-id');
                tr.classList.toggle('table-primary', id === String(clienteId));
            });
            document.getElementById('placeholderDetalle').classList.add('d-none');
            document.getElementById('detalleWrapper').classList.remove('d-none');
            document.getElementById('detalleClienteNombre').textContent = nombre;
            cargarTickets(clienteId);
        }

        // =================================================================
        // Carga de data del cliente
        // =================================================================

        function validarFechas() {
            const fi = document.getElementById('inputFechaInicioCli').value;
            const ff = document.getElementById('inputFechaFinCli').value;

            if (!fi || !ff) {
                window.mostrarToast && window.mostrarToast('Seleccione fecha inicio y fin.', 'warning');
                return null;
            }
            if (ff < fi) {
                window.mostrarToast && window.mostrarToast('La fecha fin debe ser igual o posterior a la fecha inicio.', 'warning');
                return null;
            }
            return { fi, ff };
        }

        async function cargarDataCliente() {
            if (!state.clienteId) return;
            const fechas = validarFechas();
            if (!fechas) return;

            state.filtros.fecha_inicio = fechas.fi;
            state.filtros.fecha_fin = fechas.ff;

            const loading = document.getElementById('clienteLoading');
            const placeholder = document.getElementById('clientePlaceholder');
            const resultado = document.getElementById('clienteResultado');
            const errorCont = document.getElementById('clienteErrorContainer');
            if (errorCont) errorCont.classList.add('d-none');

            if (loading) loading.classList.remove('d-none');
            if (placeholder) placeholder.classList.add('d-none');
            if (resultado) resultado.classList.add('d-none');

            try {
                const params = new URLSearchParams({
                    fecha_inicio: state.filtros.fecha_inicio,
                    fecha_fin: state.filtros.fecha_fin,
                });
                const url = RUTAS.dataCliente.replace('__ID__', String(state.clienteId)) + '?' + params.toString();
                const json = await fetchJson(url);

                if (!json.success) {
                    mostrarErrorGenerico('clienteErrorContainer', 'clienteErrorMessage', json.message || 'No se pudo obtener el detalle.');
                    return;
                }

                renderClienteResultado(json);
                if (resultado) resultado.classList.remove('d-none');
            } catch (err) {
                mostrarErrorGenerico('clienteErrorContainer', 'clienteErrorMessage', detectarMensajeError(err));
            } finally {
                if (loading) loading.classList.add('d-none');
            }
        }

        // =================================================================
        // Render del resultado
        // =================================================================

        function renderClienteResultado(json) {
            document.getElementById('clienteNombreCompleto').textContent = json.cliente?.nombre_completo || '—';
            document.getElementById('clienteTelefono').textContent =
                [json.cliente?.telefono1, json.cliente?.telefono2].filter(Boolean).join(' / ') || 'Sin teléfono';

            document.getElementById('resumenTickets').textContent = json.resumen?.total_tickets ?? 0;
            document.getElementById('resumenLineas').textContent = json.resumen?.total_lineas ?? 0;
            document.getElementById('resumenImporte').textContent = formatearMonto(json.resumen?.importe_total);

            renderGraficaGruposMadre(json.grafica_grupos_madre || []);
            renderTablaArticulos(json.articulos || []);
            renderTablaTickets(json.tickets || []);
        }

        function renderGraficaGruposMadre(grupos) {
            const canvas = document.getElementById('graficaGruposMadre');
            if (!canvas) return;

            if (typeof Chart === 'undefined') {
                canvas.parentElement.innerHTML =
                    '<div class="text-muted text-center py-4">Chart.js no disponible.</div>';
                return;
            }

            if (state.chartGrupos) {
                state.chartGrupos.destroy();
                state.chartGrupos = null;
            }

            const labels = grupos.map((g) => g.descripciongrupomadre || '—');
            const data = grupos.map((g) => Number(g.importe) || 0);

            state.chartGrupos = new Chart(canvas, {
                type: 'bar',
                data: {
                    labels,
                    datasets: [{
                        label: 'Importe',
                        data,
                        backgroundColor: 'rgba(13, 110, 253, 0.6)',
                        borderColor: 'rgba(13, 110, 253, 1)',
                        borderWidth: 1,
                    }],
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            callbacks: {
                                label: (ctx) => formatearMonto(ctx.parsed.y),
                            },
                        },
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: (v) => formatearMonto(v),
                            },
                        },
                    },
                },
            });
        }

        function renderTablaArticulos(articulos) {
            const tbody = document.getElementById('tablaArticulosClienteBody');
            if (!tbody) return;

            if (!articulos || articulos.length === 0) {
                tbody.innerHTML = `<tr><td colspan="4" class="text-center text-muted py-3">Sin artículos en el período.</td></tr>`;
                return;
            }

            tbody.innerHTML = articulos.map((a) => `
                <tr>
                    <td class="small">${escapeHtml(a.ean)}</td>
                    <td class="small">${escapeHtml(a.descripcion || '—')}</td>
                    <td class="small text-end">${escapeHtml(a.repeticiones)}</td>
                    <td class="small text-end">${escapeHtml(formatearMonto(a.importe))}</td>
                </tr>`).join('');
        }

        function renderTablaTickets(tickets) {
            const tbody = document.getElementById('tablaTicketsClienteBody');
            if (!tbody) return;

            if (!tickets || tickets.length === 0) {
                tbody.innerHTML = `<tr><td colspan="3" class="text-center text-muted py-3">Sin tickets en el período.</td></tr>`;
                return;
            }

            tbody.innerHTML = tickets.map((t) => `
                <tr>
                    <td class="small">${escapeHtml(t.F_NUMTICKE)}</td>
                    <td class="small">${escapeHtml(formatearFechaLocal(t.FECHA_DT))}</td>
                    <td class="text-end">
                        <button type="button"
                                class="btn btn-sm btn-outline-primary btn-ver-ticket"
                                data-ticket="${escapeHtml(t.F_NUMTICKE)}">
                            <i class="bi bi-eye"></i> Ver
                        </button>
                    </td>
                </tr>`).join('');

            tbody.querySelectorAll('.fila-cliente').forEach((tr) => {
                tr.addEventListener('click', () => {
                    const id = Number(tr.getAttribute('data-cliente-id'));
                    const nombre = tr.getAttribute('data-cliente-nombre') || '—';
                    const sucursalId = tr.getAttribute('data-sucursal-id');
                    seleccionarCliente(id, nombre, sucursalId);
                });
            });
        }

        async function abrirModalTicket(ticket) {
            const modalEl = document.getElementById('modalTicketCliente');
            if (!modalEl || !window.bootstrap) return;

            document.getElementById('modalTicketNumero').textContent = ticket;
            const tbody = document.getElementById('modalTicketArticulosBody');
            const loading = document.getElementById('modalTicketLoading');
            if (tbody) tbody.innerHTML = '';
            if (loading) loading.classList.remove('d-none');

            const modal = window.bootstrap.Modal.getOrCreateInstance(modalEl);
            modal.show();

            try {
                const params = new URLSearchParams({
                    fecha_inicio: state.filtros.fecha_inicio,
                    fecha_fin: state.filtros.fecha_fin,
                });
                const url = RUTAS.detalleTicket
                    .replace('__ID__', String(state.clienteId))
                    .replace('__TICKET__', encodeURIComponent(ticket))
                    + '?' + params.toString();

                const json = await fetchJson(url);
                if (!json.success) {
                    mostrarErrorGenerico('clienteErrorContainer', 'clienteErrorMessage', json.message || 'No se pudo cargar el ticket.');
                    return;
                }

                if (tbody) {
                    tbody.innerHTML = (json.articulos || []).map((a) => `
                        <tr>
                            <td class="small">${escapeHtml(a.ean)}</td>
                            <td class="small">${escapeHtml(a.descripcion || '—')}</td>
                            <td class="small text-end">${escapeHtml(formatearMonto(a.monto))}</td>
                        </tr>`).join('') ||
                        `<tr><td colspan="3" class="text-center text-muted py-2">Sin artículos.</td></tr>`;
                }
            } catch (err) {
                mostrarErrorGenerico('clienteErrorContainer', 'clienteErrorMessage', detectarMensajeError(err));
            } finally {
                if (loading) loading.classList.add('d-none');
            }
        }

        // =================================================================
        // Limpiar filtros tab 2
        // =================================================================

        function limpiarTab2() {
            state.clienteId = null;
            state.clienteNombre = null;
            state.clienteTelefono = null;
            state.filtros.fecha_inicio = '';
            state.filtros.fecha_fin = '';

            const inputBuscar = document.getElementById('inputBuscarCliente');
            const fi = document.getElementById('inputFechaInicioCli');
            const ff = document.getElementById('inputFechaFinCli');
            if (inputBuscar) inputBuscar.value = '';
            if (fi) fi.value = '';
            if (ff) ff.value = '';

            document.getElementById('listaClientesWrapper')?.classList.add('d-none');
            document.getElementById('clienteSeleccionadoWrap')?.classList.add('d-none');
            document.getElementById('clienteErrorContainer')?.classList.add('d-none');
            document.getElementById('clienteResultado')?.classList.add('d-none');
            document.getElementById('clientePlaceholder')?.classList.remove('d-none');

            if (state.chartGrupos) {
                state.chartGrupos.destroy();
                state.chartGrupos = null;
            }
        }

        // =================================================================
        // Bind eventos tab 2
        // =================================================================

        document.getElementById('btnBuscarCliente')?.addEventListener('click', buscarClientes);
        document.getElementById('btnLimpiarCliente')?.addEventListener('click', limpiarTab2);

        document.getElementById('btnReintentarCliente')?.addEventListener('click', () => {
            if (state.clienteId) cargarDataCliente();
            else buscarClientes();
        });

        document.getElementById('inputBuscarCliente')?.addEventListener('keydown', (ev) => {
            if (ev.key === 'Enter') {
                ev.preventDefault();
                buscarClientes();
            }
        });

        document.getElementById('inputFechaInicioCli')?.addEventListener('change', () => {
            if (state.clienteId) cargarDataCliente();
        });
        document.getElementById('inputFechaFinCli')?.addEventListener('change', () => {
            if (state.clienteId) cargarDataCliente();
        });
    }

    // =================================================================
    // Bootstrap del módulo
    // =================================================================

    document.addEventListener('DOMContentLoaded', () => {
        initTab1();
        initTab2();
    });
})();