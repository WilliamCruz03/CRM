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
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            credentials: 'same-origin',
        });

        const contentType = res.headers.get('content-type') || '';
        if (!contentType.includes('application/json')) {
            const texto = await res.text();
            throw new Error('Respuesta no JSON: ' + texto.slice(0, 200));
        }

        const json = await res.json();
        if (!res.ok) {
            // Intentar extraer mensaje de validación de Laravel
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
                tbody.innerHTML = `<tr><td colspan="5" class="text-center text-muted py-4">Sin resultados para el período seleccionado.</td></tr>`;
                wrapper.classList.remove('d-none');
                return;
            }

            tbody.innerHTML = clientes.map((c) => {
                const nombre = [c.Nombre, c.apPaterno, c.apMaterno].filter(Boolean).join(' ').trim() || '—';
                const isSelected = state.clienteSeleccionado === c.id_Cliente;
                const selectedClass = isSelected ? 'table-primary' : '';
                return `
                    <tr class="fila-cliente ${selectedClass}" data-cliente-id="${c.id_Cliente}" data-cliente-nombre="${escapeHtml(nombre)}" role="button">
                        <td>${escapeHtml(nombre)}</td>
                        <td class="text-end">${escapeHtml(c.compras)}</td>
                        <td class="text-end">${escapeHtml(formatearFrecuencia(c.frecuencia))}</td>
                        <td>${escapeHtml(formatearFechaLocal(c.ultima_compra))}</td>
                        <td class="text-end">${escapeHtml(formatearMonto(c.monto_total))}</td>
                    </tr>`;
            }).join('');

            wrapper.classList.remove('d-none');

            tbody.querySelectorAll('.fila-cliente').forEach((tr) => {
                tr.addEventListener('click', () => {
                    const id = Number(tr.getAttribute('data-cliente-id'));
                    const nombre = tr.getAttribute('data-cliente-nombre') || '—';
                    seleccionarCliente(id, nombre);
                });
            });
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

            ticketsEl.innerHTML = tickets.map((t) => {
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

                return `
                    <div class="border rounded mb-3">
                        <div class="d-flex justify-content-between align-items-center px-3 py-2 bg-light border-bottom">
                            <span class="fw-semibold">Ticket ${escapeHtml(t.F_NUMTICKE)}</span>
                            <span class="text-muted small">${escapeHtml(formatearFechaLocal(t.FECHA_DT))}</span>
                        </div>
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
                    </div>`;
            }).join('');
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

                state.ultimaData = json;
                renderEncabezado(json.articulo);
                document.getElementById('barraOrden').classList.remove('d-none');
                document.getElementById('placeholderInicial').classList.add('d-none');
                renderTablaClientes(json.clientes);
                renderPaginacion(json.paginacion);

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

        function seleccionarCliente(clienteId, nombre) {
            state.clienteSeleccionado = clienteId;
            document.querySelectorAll('.fila-cliente').forEach((tr) => {
                const id = Number(tr.getAttribute('data-cliente-id'));
                tr.classList.toggle('table-primary', id === clienteId);
            });
            document.getElementById('placeholderDetalle').classList.add('d-none');
            document.getElementById('detalleWrapper').classList.remove('d-none');
            document.getElementById('detalleClienteNombre').textContent = nombre;
            cargarTickets(clienteId);
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

        function limpiarFiltros() {
            document.getElementById('inputEan').value = '';
            document.getElementById('inputFechaInicio').value = '';
            document.getElementById('inputFechaFin').value = '';
            document.getElementById('sortBySelect').value = SORT_BY_DEFAULT;
            document.getElementById('perPageSelect').value = String(PER_PAGE_DEFAULT);

            state.ean = '';
            state.fecha_inicio = '';
            state.fecha_fin = '';
            state.per_page = PER_PAGE_DEFAULT;
            state.sort_by = SORT_BY_DEFAULT;
            state.clienteSeleccionado = null;
            state.ultimaData = null;

            document.getElementById('articuloHeader').classList.add('d-none');
            document.getElementById('barraOrden').classList.add('d-none');
            document.getElementById('tablaWrapper').classList.add('d-none');
            document.getElementById('placeholderInicial').classList.remove('d-none');
            document.getElementById('detalleWrapper').classList.add('d-none');
            document.getElementById('placeholderDetalle').classList.remove('d-none');
            document.getElementById('errorContainer').classList.add('d-none');
        }

        function onBuscar() {
            if (!validarFiltros()) return;
            normalizarEanEnInput();

            state.ean = document.getElementById('inputEan').value.trim();
            state.fecha_inicio = document.getElementById('inputFechaInicio').value;
            state.fecha_fin = document.getElementById('inputFechaFin').value;
            state.per_page = Number(document.getElementById('perPageSelect').value) || PER_PAGE_DEFAULT;
            state.sort_by = document.getElementById('sortBySelect').value || SORT_BY_DEFAULT;

            const diasRango = (new Date(state.fecha_fin) - new Date(state.fecha_inicio)) / (1000 * 60 * 60 * 24);
            if (diasRango > 365 && typeof window.mostrarToast === 'function') {
                window.mostrarToast('Rango de fechas mayor a 365 días: la consulta puede ser lenta.', 'warning');
            }

            cargarData(1);
        }

        const eanInput = document.getElementById('inputEan');
        const fi = document.getElementById('inputFechaInicio');
        const ff = document.getElementById('inputFechaFin');
        const sortSel = document.getElementById('sortBySelect');
        const perSel = document.getElementById('perPageSelect');

        if (eanInput) { eanInput.value = ''; eanInput.addEventListener('blur', normalizarEanEnInput); }
        if (fi) fi.value = '';
        if (ff) ff.value = '';
        if (sortSel) sortSel.value = SORT_BY_DEFAULT;
        if (perSel) perSel.value = String(PER_PAGE_DEFAULT);

        document.getElementById('btnBuscar')?.addEventListener('click', onBuscar);
        document.getElementById('btnLimpiarFiltros')?.addEventListener('click', limpiarFiltros);
        document.getElementById('btnReintentar')?.addEventListener('click', () => { if (state.ean) cargarData(1); });
        document.getElementById('btnCerrarDetalle')?.addEventListener('click', () => {
            state.clienteSeleccionado = null;
            document.querySelectorAll('.fila-cliente').forEach((tr) => tr.classList.remove('table-primary'));
            document.getElementById('detalleWrapper').classList.add('d-none');
            document.getElementById('placeholderDetalle').classList.remove('d-none');
        });

        if (sortSel) sortSel.addEventListener('change', () => {
            if (!state.ean) return;
            state.sort_by = sortSel.value;
            cargarData(1);
        });
        if (perSel) perSel.addEventListener('change', () => {
            if (!state.ean) return;
            state.per_page = Number(perSel.value);
            cargarData(1);
        });
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
            clientes: [],
            chartGrupos: null,
            filtros: {
                fecha_inicio: '',
                fecha_fin: '',
            },
        };

        async function buscarClientes() {
            const q = (document.getElementById('inputBuscarCliente').value || '').trim();
            const cont = document.getElementById('clienteErrorContainer');
            if (cont) cont.classList.add('d-none');

            if (q.length < 2) {
                if (typeof window.mostrarToast === 'function') {
                    window.mostrarToast('Ingrese al menos 2 caracteres para buscar.', 'warning');
                }
                return;
            }

            try {
                const json = await fetchJson(`${RUTAS.buscarCliente}?q=${encodeURIComponent(q)}`);
                if (!json.success) {
                    mostrarErrorGenerico('clienteErrorContainer', 'clienteErrorMessage', json.message || 'Sin resultados.');
                    return;
                }

                state.clientes = json.clientes || [];
                renderListaClientes(state.clientes);
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
                            data-cliente-nombre="${escapeHtml(c.nombre_completo || '—')}">
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
                    seleccionarCliente(id, nombre);
                });
            });
        }

        function seleccionarCliente(clienteId, nombre) {
            state.clienteId = clienteId;
            state.clienteNombre = nombre;

            document.querySelectorAll('.cliente-item').forEach((btn) => {
                const id = Number(btn.getAttribute('data-cliente-id'));
                btn.classList.toggle('active', id === clienteId);
            });

            cargarDataCliente();
        }

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

            tbody.querySelectorAll('.btn-ver-ticket').forEach((btn) => {
                btn.addEventListener('click', () => {
                    const ticket = btn.getAttribute('data-ticket');
                    abrirModalTicket(ticket);
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

        document.getElementById('btnBuscarCliente')?.addEventListener('click', buscarClientes);
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