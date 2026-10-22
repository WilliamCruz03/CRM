/**
 * Reporte: Búsqueda por Artículo (Reportes → Compras por Cliente).
 * Reutiliza helpers globales: window.mostrarToast, window.inicializarSelectorPerPage.
 * Si en R5 se extraen helpers compartidos, reemplazar las locales por las importadas.
 */
(function () {
    'use strict';

    const RUTAS = {
        data:    window.REPORTE_BUSQUEDA_ARTICULO_DATA_URL,
        tickets: window.REPORTE_BUSQUEDA_ARTICULO_TICKETS_URL, // base, se concatena {clienteId}
    };

    const ESTADO_KEY = 'reporte_busqueda_articulo_estado';
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

    // -----------------------------------------------------------------
    // Helpers locales (a reemplazar por los compartidos si R5 los extrae)
    // -----------------------------------------------------------------

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

    // -----------------------------------------------------------------
    // Manejo de errores (mismo patrón que clientes.blade.php)
    // -----------------------------------------------------------------

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

    function mostrarError(mensaje) {
        const cont = document.getElementById('errorContainer');
        const span = document.getElementById('errorMessage');
        if (!cont || !span) return;
        span.textContent = mensaje;
        cont.classList.remove('d-none');
        if (typeof window.mostrarToast === 'function') {
            window.mostrarToast(mensaje, 'danger');
        }
    }

    function limpiarError() {
        const cont = document.getElementById('errorContainer');
        if (cont) cont.classList.add('d-none');
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
            throw new Error(json.message || `HTTP ${res.status}`);
        }
        return json;
    }

    // -----------------------------------------------------------------
    // Estado persistido
    // -----------------------------------------------------------------

    function guardarEstado() {
        try {
            sessionStorage.setItem(ESTADO_KEY, JSON.stringify({
                ean: state.ean,
                fecha_inicio: state.fecha_inicio,
                fecha_fin: state.fecha_fin,
                per_page: state.per_page,
                sort_by: state.sort_by,
            }));
        } catch (e) {
            /* no crítico */
        }
    }

    function restaurarEstado() {
        try {
            const raw = sessionStorage.getItem(ESTADO_KEY);
            if (!raw) return;
            const parsed = JSON.parse(raw);
            if (parsed.ean) state.ean = parsed.ean;
            if (parsed.fecha_inicio) state.fecha_inicio = parsed.fecha_inicio;
            if (parsed.fecha_fin) state.fecha_fin = parsed.fecha_fin;
            if (parsed.per_page) state.per_page = parsed.per_page;
            if (parsed.sort_by) state.sort_by = parsed.sort_by;
        } catch (e) {
            /* no crítico */
        }
    }

    // -----------------------------------------------------------------
    // Normalización EAN (UX; backend es fuente de verdad)
    // -----------------------------------------------------------------

    function normalizarEanEnInput() {
        const input = document.getElementById('inputEan');
        if (!input) return;
        const valor = (input.value || '').trim();
        // Solo aplicamos padding visual; el backend re-normaliza con largo real.
        // No forzamos 13 hardcodeado; usamos 13 como placeholder UX mínimo.
        if (/^\d+$/.test(valor) && valor.length < 13) {
            input.value = valor.padStart(13, '0');
        } else {
            input.value = valor;
        }
    }

    // -----------------------------------------------------------------
    // Render
    // -----------------------------------------------------------------

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
            tbody.innerHTML = `
                <tr>
                    <td colspan="5" class="text-center text-muted py-4">
                        Sin resultados para el período seleccionado.
                    </td>
                </tr>`;
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
                resumen.textContent =
                    `Mostrando ${paginacion.from} hasta ${paginacion.to} de ${paginacion.total} resultados`;
            } else {
                resumen.textContent = '';
            }
        }

        // Reenganchar clicks de paginación para re-fetch sin recargar.
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

    // -----------------------------------------------------------------
    // Carga de datos
    // -----------------------------------------------------------------

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
        limpiarError();
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
                mostrarError(json.message || 'No se pudo obtener el reporte.');
                return;
            }

            state.ultimaData = json;

            renderEncabezado(json.articulo);
            document.getElementById('barraOrden').classList.remove('d-none');
            document.getElementById('placeholderInicial').classList.add('d-none');
            renderTablaClientes(json.clientes);
            renderPaginacion(json.paginacion);

            // Reset panel detalle al cambiar resultados.
            state.clienteSeleccionado = null;
            document.getElementById('detalleWrapper').classList.add('d-none');
            document.getElementById('placeholderDetalle').classList.remove('d-none');

            guardarEstado();
        } catch (err) {
            mostrarError(detectarMensajeError(err));
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
            const url = `${RUTAS.tickets}/${clienteId}?${params.toString()}`;
            const json = await fetchJson(url);
            if (!json.success) {
                mostrarError(json.message || 'No se pudo obtener el detalle.');
                return;
            }
            const nombre = document.getElementById('detalleClienteNombre').textContent || '';
            renderPanelDetalle(nombre, json.tickets, json.articulo);
        } catch (err) {
            mostrarError(detectarMensajeError(err));
        } finally {
            setDetalleLoading(false);
        }
    }

    function seleccionarCliente(clienteId, nombre) {
        state.clienteSeleccionado = clienteId;
        // Refrescar highlight en la tabla.
        document.querySelectorAll('.fila-cliente').forEach((tr) => {
            const id = Number(tr.getAttribute('data-cliente-id'));
            tr.classList.toggle('table-primary', id === clienteId);
        });
        document.getElementById('placeholderDetalle').classList.add('d-none');
        document.getElementById('detalleWrapper').classList.remove('d-none');
        document.getElementById('detalleClienteNombre').textContent = nombre;
        cargarTickets(clienteId);
    }

    // -----------------------------------------------------------------
    // Handlers
    // -----------------------------------------------------------------

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
        if (!validarFiltros()) return;
        normalizarEanEnInput();

        state.ean = document.getElementById('inputEan').value.trim();
        state.fecha_inicio = document.getElementById('inputFechaInicio').value;
        state.fecha_fin = document.getElementById('inputFechaFin').value;
        state.per_page = Number(document.getElementById('perPageSelect').value) || PER_PAGE_DEFAULT;
        state.sort_by = document.getElementById('sortBySelect').value || SORT_BY_DEFAULT;

        cargarData(1);
    }

    function onCambiarOrden() {
        if (!state.ean) return;
        state.sort_by = document.getElementById('sortBySelect').value || SORT_BY_DEFAULT;
        cargarData(1);
    }

    function onCambiarPerPage() {
        if (!state.ean) return;
        state.per_page = Number(document.getElementById('perPageSelect').value) || PER_PAGE_DEFAULT;
        cargarData(1);
    }

    // -----------------------------------------------------------------
    // Init
    // -----------------------------------------------------------------

    document.addEventListener('DOMContentLoaded', () => {
        restaurarEstado();

        const eanInput = document.getElementById('inputEan');
        const fi = document.getElementById('inputFechaInicio');
        const ff = document.getElementById('inputFechaFin');
        const sortSel = document.getElementById('sortBySelect');
        const perSel = document.getElementById('perPageSelect');

        if (eanInput) eanInput.value = state.ean || '';
        if (fi) fi.value = state.fecha_inicio || '';
        if (ff) ff.value = state.fecha_fin || '';
        if (sortSel) sortSel.value = state.sort_by || SORT_BY_DEFAULT;
        if (perSel) perSel.value = String(state.per_page || PER_PAGE_DEFAULT);

        // Normalizar en blur (UX) y re-normalizar al enviar (backend es fuente de verdad).
        if (eanInput) eanInput.addEventListener('blur', normalizarEanEnInput);

        document.getElementById('btnBuscar')?.addEventListener('click', onBuscar);
        document.getElementById('btnReintentar')?.addEventListener('click', () => {
            if (state.ean) cargarData(1);
        });
        document.getElementById('btnCerrarDetalle')?.addEventListener('click', () => {
            state.clienteSeleccionado = null;
            document.querySelectorAll('.fila-cliente').forEach((tr) => tr.classList.remove('table-primary'));
            document.getElementById('detalleWrapper').classList.add('d-none');
            document.getElementById('placeholderDetalle').classList.remove('d-none');
        });

        if (sortSel) sortSel.addEventListener('change', onCambiarOrden);
        if (perSel) perSel.addEventListener('change', onCambiarPerPage);

        // Si el layout tiene inicializarSelectorPerPage, lo usamos para per_page.
        if (typeof window.inicializarSelectorPerPage === 'function') {
            window.inicializarSelectorPerPage('perPageSelect');
        }

        // Auto-búsqueda si venimos de sessionStorage o de una URL compartida.
        if (state.ean && state.fecha_inicio && state.fecha_fin) {
            cargarData(1);
        }
    });
})();