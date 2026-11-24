window.inicializarTablaDetalleReporte = function (tabla, opciones = {}) {
    if (!tabla) return;
    tabla.limpiarDetalleReporte?.();
    const cuerpo = tabla.tBodies[0];
    const filas = Array.from(cuerpo.rows).filter(fila => fila.cells.length === tabla.tHead.rows[0].cells.length);
    const controles = tabla.querySelectorAll('[data-orden-detalle]');
    const parametros = new URLSearchParams(window.location.search);
    const paginar = opciones.paginacion && filas.length > 15;
    let porPagina = Number(parametros.get('per_page')) || 15;
    if (![5, 10, 15, 20, 50].includes(porPagina)) porPagina = 15;
    let pagina = Math.max(1, Number(parametros.get('page')) || 1);
    let columna = opciones.columnaInicial;
    let descendente = opciones.descendente ?? true;
    const panel = document.createElement('div');
    if (paginar) {
        panel.className = 'd-flex flex-wrap justify-content-between align-items-center gap-2 mt-3 paginacion-bloque';
        tabla.closest('.table-responsive').after(panel);
    }

    function mostrar() {
        const paginas = Math.max(1, Math.ceil(filas.length / porPagina));
        pagina = Math.min(paginas, Math.max(1, Math.floor(pagina)));
        const inicio = (pagina - 1) * porPagina;
        filas.forEach((fila, indice) => {
            fila.hidden = paginar && (indice < inicio || indice >= inicio + porPagina);
            cuerpo.append(fila);
        });
        if (!paginar) return;
        const numeros = paginas <= 14
            ? Array.from({length: paginas}, (_, indice) => indice + 1)
            : [...new Set([1, 2, ...Array.from({length: 7}, (_, indice) => pagina - 3 + indice), paginas - 1, paginas])]
                .filter(numero => numero >= 1 && numero <= paginas).sort((a, b) => a - b);
        const enlaces = numeros.map((numero, indice) => {
            const separador = indice && numero - numeros[indice - 1] > 1
                ? '<li class="page-item disabled" aria-disabled="true"><span class="page-link">...</span></li>' : '';
            return separador + (numero === pagina
                ? `<li class="page-item active" aria-current="page"><span class="page-link">${numero}</span></li>`
                : `<li class="page-item"><button type="button" class="page-link" data-pagina="${numero}">${numero}</button></li>`);
        }).join('');
        panel.innerHTML = `
            <div class="text-muted small">Mostrando <strong>${inicio + 1}</strong> hasta <strong>${Math.min(inicio + porPagina, filas.length)}</strong> de <strong>${filas.length}</strong> resultados</div>
            <div class="d-flex flex-wrap align-items-center gap-3">
                <label class="d-flex align-items-center gap-2 text-muted small">Mostrar
                    <select id="perPageSelect" class="form-select form-select-sm" style="width:auto">
                        ${[5, 10, 15, 20, 50].map(valor => `<option value="${valor}" ${valor === porPagina ? 'selected' : ''}>${valor}</option>`).join('')}
                    </select> por página
                </label>
                <nav aria-label="Paginación"><ul class="pagination mb-0">
                    <li class="page-item ${pagina === 1 ? 'disabled' : ''}" ${pagina === 1 ? 'aria-disabled="true"' : ''}><button type="button" class="page-link" data-pagina="${pagina - 1}" aria-label="Anterior" ${pagina === 1 ? 'disabled' : ''}><i class="bi bi-chevron-left" aria-hidden="true"></i></button></li>
                    ${enlaces}
                    <li class="page-item ${pagina === paginas ? 'disabled' : ''}" ${pagina === paginas ? 'aria-disabled="true"' : ''}><button type="button" class="page-link" data-pagina="${pagina + 1}" aria-label="Siguiente" ${pagina === paginas ? 'disabled' : ''}><i class="bi bi-chevron-right" aria-hidden="true"></i></button></li>
                </ul></nav>
            </div>`;
        window.inicializarSelectorPerPage?.('perPageSelect', () => {
            porPagina = Number(new URLSearchParams(window.location.search).get('per_page')) || 15;
            pagina = 1;
            mostrar();
        });
    }

    function ordenar() {
        if (!filas.length) return;
        const valor = fila => fila.cells[columna].dataset.valor ?? fila.cells[columna].textContent.trim();
        filas.sort((a, b) => (descendente ? -1 : 1) * ((opciones.numericas || []).includes(columna)
            ? Number(valor(a)) - Number(valor(b))
            : valor(a).localeCompare(valor(b), 'es', {numeric: true})));
        controles.forEach(control => {
            const activo = Number(control.dataset.ordenDetalle) === columna;
            control.closest('th').removeAttribute('aria-sort');
            if (activo) control.closest('th').setAttribute('aria-sort', descendente ? 'descending' : 'ascending');
            control.querySelector('i').className = 'bi ' + (activo ? (descendente ? 'bi-arrow-down' : 'bi-arrow-up') : 'bi-arrow-down-up');
        });
    }

    function alOrdenar(evento) {
        const control = evento.target.closest('[data-orden-detalle]');
        if (!control || !tabla.contains(control)) return;
        const nuevaColumna = Number(control.dataset.ordenDetalle);
        descendente = nuevaColumna === columna ? !descendente : false;
        columna = nuevaColumna;
        pagina = 1;
        ordenar();
        mostrar();
    }
    panel.addEventListener('click', evento => {
        const boton = evento.target.closest('[data-pagina]');
        if (!boton || boton.disabled) return;
        pagina = Number(boton.dataset.pagina);
        const url = new URL(window.location.href);
        url.searchParams.set('page', pagina);
        window.history.replaceState({}, '', url);
        mostrar();
    });
    tabla.addEventListener('click', alOrdenar);
    tabla.limpiarDetalleReporte = () => {
        tabla.removeEventListener('click', alOrdenar);
        panel.remove();
    };
    ordenar();
    mostrar();
};
