(() => {
    const formulario = document.getElementById('filtrosReporteSobrePedido');
    const resultado = document.getElementById('resultadoSobrePedido');
    const excel = document.getElementById('excelReporteSobrePedido');
    const boton = formulario.querySelector('[type="submit"]');
    let grafica;
    const modalClientes = document.getElementById('clientesReporteSobrePedido');
    const cuerpoClientes = modalClientes.querySelector('.modal-body');
    const moneda = new Intl.NumberFormat('es-MX', { style: 'currency', currency: 'MXN' });

    async function cargarDetalle(url, celda) {
        const spinner = document.createElement('span');
        spinner.className = 'spinner-border spinner-border-sm';
        spinner.setAttribute('aria-label', 'Consultando');
        celda.replaceChildren(spinner);
        try {
            const respuesta = await consultar(url);
            if (!respuesta?.ok) throw new Error();
            const filas = await respuesta.json();
            if (!Array.isArray(filas)) throw new Error();
            const clientes = url.searchParams.get('detalle') === 'clientes';
            const tabla = document.createElement('table');
            tabla.className = 'table table-hover table-sm mb-0';
            const cabecera = tabla.createTHead().insertRow();
            for (const texto of clientes ? ['#', 'Cliente', 'Artículos', 'Importe recibido', 'Importe estimado', 'Importe vendido', 'Saldo pendiente']
                : ['Código', 'Descripción', 'Cantidad', 'Precio aproximado', 'Monto estimado', 'Número de ticket', 'Fecha de encargo', 'Importe vendido (POS)']) {
                const th = document.createElement('th');
                th.scope = 'col';
                th.textContent = texto;
                cabecera.append(th);
            }
            const cuerpo = tabla.createTBody();
            filas.forEach((fila, indice) => {
                const renglon = cuerpo.insertRow();
                const valores = clientes ? [indice + 1, fila.nombre, fila.unidades, ...[fila.anticipo, fila.estimado, fila.vendido, fila.saldo].map(valor => moneda.format(valor))]
                    : [fila.codigo, fila.descripcion, fila.cantidad, moneda.format(fila.precio), moneda.format(fila.monto),
                        fila.folio_ticket_entrega ?? 'Sin ticket',
                        fila.fecha_encargo ? fila.fecha_encargo.split('-').reverse().join('/') : '—',
                        fila.monto_vendido == null ? 'Sin venta vinculada' : moneda.format(fila.monto_vendido)];
                for (const valor of valores) renglon.insertCell().textContent = valor ?? '—';
                if (!clientes) {
                    if (fila.temporal) {
                        renglon.cells[0].textContent = fila.ean_vendido ?? fila.temporal;
                        if (!fila.ean_vendido) {
                            const nota = document.createElement('small');
                            nota.className = 'd-block text-muted';
                            nota.textContent = 'EAN de venta pendiente';
                            renglon.cells[0].append(nota);
                        }
                    }
                    return;
                }
                const articulos = new URL(url);
                articulos.searchParams.set('detalle', 'articulos');
                articulos.searchParams.set('cliente', fila.id);
                renglon.dataset.articulos = articulos.href;
                renglon.dataset.cliente = fila.nombre;
                renglon.style.cursor = 'pointer';
                const boton = document.createElement('button');
                boton.type = 'button';
                boton.className = 'btn btn-sm p-0 text-reset fw-semibold';
                boton.textContent = fila.nombre;
                boton.setAttribute('aria-expanded', 'false');
                renglon.cells[1].replaceChildren(boton);
            });
            const contenedor = document.createElement('div');
            contenedor.className = 'table-responsive';
            contenedor.append(tabla);
            celda.replaceChildren(filas.length ? contenedor : 'Sin pedidos registrados para estos filtros.');
        } catch {
            const reintentar = document.createElement('button');
            reintentar.type = 'button';
            reintentar.className = 'btn btn-sm btn-outline-primary';
            reintentar.textContent = 'Reintentar';
            reintentar.addEventListener('click', () => cargarDetalle(url, celda));
            celda.replaceChildren(reintentar);
            window.mostrarToast?.('No se pudo consultar el detalle. Intenta de nuevo.', 'warning');
        } finally {
            spinner.remove();
        }
    }

    const desplegados = new WeakMap();
    function alternarDetalle(fila, ruta) {
        let panel = desplegados.get(fila);
        if (panel) {
            panel.hidden = !panel.hidden;
        } else {
            panel = document.createElement('tr');
            panel.dataset.detalle = '';
            const celda = panel.insertCell();
            celda.colSpan = fila.cells.length;
            celda.className = 'bg-light p-3';
            const esCliente = fila.hasAttribute('data-articulos');
            const bloque = document.createElement('section');
            bloque.className = esCliente
                ? 'border border-start border-3 border-success-subtle rounded bg-white ms-3 p-3'
                : 'border border-start border-3 border-primary-subtle rounded bg-white p-3';
            const titulo = document.createElement('h3');
            titulo.className = esCliente ? 'h6 text-success-emphasis' : 'h6 text-primary-emphasis';
            titulo.textContent = esCliente ? 'Artículos de ' + fila.dataset.cliente : 'Clientes de ' + fila.dataset.sucursal;
            const contenido = document.createElement('div');
            contenido.setAttribute('aria-live', 'polite');
            bloque.append(titulo, contenido);
            celda.append(bloque);
            fila.after(panel);
            desplegados.set(fila, panel);
            cargarDetalle(new URL(ruta, window.location.href), contenido);
        }
        fila.classList.toggle('table-active', !panel.hidden);
        fila.querySelector('button')?.setAttribute('aria-expanded', String(!panel.hidden));
    }

    let botonSucursal;
    modalClientes.addEventListener('hidden.bs.modal', () => botonSucursal?.focus());
    cuerpoClientes.addEventListener('click', evento => {
        const cliente = evento.target.closest('[data-articulos]');
        if (cliente) alternarDetalle(cliente, cliente.dataset.articulos);
    });

    resultado.addEventListener('click', evento => {
        const sucursal = evento.target.closest('[data-clientes]');
        if (sucursal) {
            const url = new URL(sucursal.dataset.clientes, window.location.href);
            botonSucursal = sucursal.querySelector('button');
            modalClientes.querySelector('.modal-title').textContent = 'Clientes de ' + sucursal.dataset.sucursal;
            modalClientes.querySelector('[data-periodo]').textContent =
                url.searchParams.get('inicio') + ' al ' + url.searchParams.get('fin');
            const panel = document.createElement('div');
            cuerpoClientes.replaceChildren(panel);
            cargarDetalle(url, panel);
            bootstrap.Modal.getOrCreateInstance(modalClientes).show();
            return;
        }
        const control = evento.target.closest('[data-orden]');
        if (!control) return;
        const columna = Number(control.dataset.orden);
        const tabla = control.closest('table');
        const cabecera = control.closest('th');
        const descendente = cabecera.getAttribute('aria-sort') === 'ascending';
        const cuerpo = tabla.tBodies[0];
        if (cuerpo.rows[0]?.cells.length < 2) return;
        for (const encabezado of tabla.tHead.rows[0].cells) {
            encabezado.removeAttribute('aria-sort');
            encabezado.querySelector('i').className = 'bi bi-arrow-down-up';
        }
        cabecera.setAttribute('aria-sort', descendente ? 'descending' : 'ascending');
        control.querySelector('i').className = descendente ? 'bi bi-arrow-down' : 'bi bi-arrow-up';
        const valor = fila => fila.cells[columna].dataset.valor ?? fila.cells[columna].textContent.trim();
        const filas = Array.from(cuerpo.rows).filter(fila => !fila.hasAttribute('data-detalle')).sort((a, b) => (descendente ? -1 : 1) *
            (columna === 1 ? valor(a).localeCompare(valor(b), 'es') : Number(valor(a)) - Number(valor(b))));
        for (const fila of filas) {
            cuerpo.append(fila);
            const panel = desplegados.get(fila);
            if (panel) cuerpo.append(panel);
        }
    });

    function dibujarGrafica() {
        grafica?.destroy();
        const canvas = resultado.querySelector('[data-grafica]');
        if (!canvas || !window.Chart) return;

        const filas = JSON.parse(canvas.dataset.grafica);
        grafica = new Chart(canvas, {
            type: 'bar',
            data: {
                labels: filas.map(fila => fila[0]),
                datasets: [{
                    label: 'Pedidos generados',
                    data: filas.map(fila => fila[1]),
                    backgroundColor: '#0d6efd',
                    borderRadius: 4,
                    maxBarThickness: 48,
                }],
            },
            options: {
                maintainAspectRatio: false,
                indexAxis: 'x',
                plugins: { legend: { display: false } },
                scales: { y: { beginAtZero: true, ticks: { precision: 0 } } },
            },
        });
    }

    async function consultar(url) {
        let respuesta;
        for (let intento = 0; intento < 2; intento++) {
            try {
                respuesta = await fetch(url.href, {
                    headers: { Accept: 'application/json' },
                    cache: 'no-store',
                    credentials: 'same-origin',
                    signal: AbortSignal.timeout(20000),
                });
            } catch (error) {
                if (intento === 1) throw error;
            }

            if (respuesta && ![500, 502, 503, 504].includes(respuesta.status)) break;
            if (intento === 0) await new Promise(resolve => setTimeout(resolve, 600));
        }

        return respuesta;
    }

    formulario.addEventListener('submit', async (evento) => {
        evento.preventDefault();
        if (boton.disabled) return;
        const url = new URL(formulario.action, window.location.href);
        url.search = new URLSearchParams(new FormData(formulario)).toString();
        boton.disabled = true;
        boton.textContent = 'Consultando…';
        resultado.setAttribute('aria-busy', 'true');

        try {
            const respuesta = await consultar(url);
            if (respuesta?.status === 401 || respuesta?.status === 419 ||
                (respuesta?.redirected && new URL(respuesta.url).pathname.endsWith('/login'))) {
                window.mostrarToast?.('Tu sesión expiró. Vuelve a iniciar sesión para consultar.', 'warning');
                return;
            }
            if (respuesta?.status === 403 || respuesta?.status === 404) {
                window.mostrarToast?.('La sucursal seleccionada no está disponible para tu cuenta.', 'warning');
                return;
            }
            if (respuesta?.status === 422) {
                window.mostrarToast?.('Revisa la sucursal y el rango de fechas.', 'warning');
                return;
            }
            if (!respuesta?.ok) throw new Error('Consulta fallida');
            const datos = await respuesta.json();
            if (typeof datos.html !== 'string') throw new Error('Respuesta inesperada');
            resultado.innerHTML = datos.html;
            excel.hidden = !datos.excel;
            if (datos.excel) excel.href = datos.excel;
            else excel.removeAttribute('href');
            window.history.replaceState(null, '', url);
            dibujarGrafica();
        } catch {
            window.mostrarToast?.('No se pudo consultar el reporte. Intenta de nuevo.', 'danger');
        } finally {
            boton.disabled = false;
            boton.textContent = 'Filtrar';
            resultado.removeAttribute('aria-busy');
        }
    });

    dibujarGrafica();
})();
