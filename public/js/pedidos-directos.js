(() => {
    const modulo = document.getElementById('moduloSobrePedido');
    const buscador = document.getElementById('buscarClienteDirecto');
    const resultados = document.getElementById('resultadosClienteDirecto');
    const seleccionado = document.getElementById('clienteDirectoSeleccionado');
    const tarjetaCliente = document.getElementById('tarjetaClienteDirecto');
    const formCliente = document.getElementById('formClienteDirecto');
    const formPedido = document.getElementById('formSobrePedido');
    const articulos = document.getElementById('articulosSobrePedido');
    const plantilla = document.getElementById('filaArticuloSobrePedido');
    const tipoVenta = formPedido.elements.tipo_venta;
    const convenio = formPedido.elements.id_convenio;
    const altaCliente = document.getElementById('altaClienteDirecto');
    const vistaPrevia = document.getElementById('vistaPreviaPedidoDirecto');
    const confirmacion = document.getElementById('confirmacionPedidoDirecto');
    let pedidoGuardado = false;
    const tablaPedidos = document.getElementById('tabla-pedidos-directos');
    let paginaPedidos = new URL(window.location.href);
    let versionListado = 0;
    let demoraListado;
    let ultimoListadoFallido;

    function estadoConsultaListado(cargando, fallo = false) {
        document.getElementById('cargandoListadoPedidos')?.classList.toggle('d-none', !cargando);
        tablaPedidos.querySelector('[data-reintentar-listado-pedidos]')?.classList.toggle('d-none', !fallo);
    }

    async function refrescarPedidosDirectos(url = paginaPedidos) {
        const version = ++versionListado;
        tablaPedidos.setAttribute('aria-busy', 'true');
        estadoConsultaListado(true);
        try {
            const respuesta = await fetch(String(url), {
                headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                cache: 'no-store',
            });
            if (!respuesta?.ok) throw new Error('Consulta fallida');
            const datos = await respuesta.json();
            if (typeof datos.html !== 'string') throw new Error('Respuesta inesperada');
            if (version !== versionListado) return;
            const campo = document.getElementById('buscarPedidosDirectos');
            const textoBusqueda = campo.value;
            const enfocado = document.activeElement === campo;
            const seleccion = enfocado ? [campo.selectionStart, campo.selectionEnd] : null;
            tablaPedidos.innerHTML = datos.html;
            const nuevoCampo = document.getElementById('buscarPedidosDirectos');
            nuevoCampo.value = textoBusqueda;
            document.getElementById('limpiarBuscarPedidosDirectos').style.display = textoBusqueda ? 'block' : 'none';
            paginaPedidos = new URL(url, window.location.href);
            window.history.replaceState(null, '', paginaPedidos);
            ultimoListadoFallido = null;
            if (enfocado) {
                nuevoCampo.focus({preventScroll: true});
                nuevoCampo.setSelectionRange(...seleccion);
            }
        } catch {
            if (version !== versionListado) return;
            ultimoListadoFallido = new URL(url, window.location.href);
            estadoConsultaListado(false, true);
            avisar('No se pudo actualizar el listado de pedidos. Intenta de nuevo.', 'warning');
        } finally {
            if (version === versionListado) {
                tablaPedidos.removeAttribute('aria-busy');
                estadoConsultaListado(false, ultimoListadoFallido !== null && ultimoListadoFallido !== undefined);
            }
        }
    }

    tablaPedidos.addEventListener('click', evento => {
        if (evento.target.closest('[data-limpiar-busqueda-pedidos]')) {
            const campo = document.getElementById('buscarPedidosDirectos');
            campo.value = '';
            campo.focus();
            campo.dispatchEvent(new Event('input', {bubbles: true}));
            return;
        }
        if (evento.target.closest('[data-reintentar-listado-pedidos]')) {
            if (ultimoListadoFallido) refrescarPedidosDirectos(ultimoListadoFallido);
            return;
        }
        const enlace = evento.target.closest('.pagination a, [data-orden-pedidos]');
        if (!enlace || evento.ctrlKey || evento.metaKey || evento.shiftKey) return;
        evento.preventDefault();
        refrescarPedidosDirectos(new URL(enlace.href));
    });
    function buscarListadoPedidos(aplicarSucursal = false) {
        let termino = document.getElementById('buscarPedidosDirectos').value.trim();
        if (termino.length > 0 && termino.length < 3) {
            if (!aplicarSucursal) return;
            termino = '';
        }
        const url = new URL(paginaPedidos);
        if (termino) url.searchParams.set('buscar', termino);
        else url.searchParams.delete('buscar');
        const sucursal = document.getElementById('sucursalListadoPedidos')?.value || '';
        if (sucursal) url.searchParams.set('sucursal_listado', sucursal);
        else url.searchParams.delete('sucursal_listado');
        url.searchParams.delete('page');
        refrescarPedidosDirectos(url);
    }
    tablaPedidos.addEventListener('input', evento => {
        if (evento.target.id !== 'buscarPedidosDirectos') return;
        document.getElementById('limpiarBuscarPedidosDirectos').style.display = evento.target.value ? 'block' : 'none';
        clearTimeout(demoraListado);
        ++versionListado;
        tablaPedidos.removeAttribute('aria-busy');
        estadoConsultaListado(false);
        if (evento.target.value.trim() === '') buscarListadoPedidos();
        else demoraListado = setTimeout(buscarListadoPedidos, 300);
    });
    tablaPedidos.addEventListener('submit', evento => {
        if (!evento.target.matches('[data-buscar-pedidos]')) return;
        evento.preventDefault();
        clearTimeout(demoraListado);
        buscarListadoPedidos();
    });
    tablaPedidos.addEventListener('change', evento => {
        if (evento.target.id === 'sucursalListadoPedidos') {
            clearTimeout(demoraListado);
            buscarListadoPedidos(true);
            return;
        }
        if (evento.target.id !== 'pedidosPorPagina') return;
        const url = new URL(paginaPedidos);
        url.searchParams.set('per_page', evento.target.value);
        url.searchParams.delete('page');
        refrescarPedidosDirectos(url);
    });
    const anticipo = formPedido.elements.anticipo_cliente;
    const moneda = new Intl.NumberFormat('es-MX', { style: 'currency', currency: 'MXN' });
    const categorias = JSON.parse(modulo.dataset.categorias || 'null') ?? {};
    const consultasArticulo = new WeakMap();
    let demoraBusqueda;
    let busqueda;

    function avisar(mensaje, tipo) {
        if (window.mostrarToast) window.mostrarToast(mensaje, tipo);
    }

    function validarFormulario(formulario) {
        const campo = Array.from(formulario.elements).find(elemento =>
            elemento.willValidate && (!elemento.validity.valid || (elemento.required && !elemento.value.trim()))
        );
        if (!campo) return true;

        if (campo === convenio && tipoVenta.value === 'convenio' && !campo.value) {
            avisar('Selecciona un convenio del listado.', 'warning');
            campo.focus();
            return false;
        }

        const etiqueta = campo.labels?.[0]?.textContent.trim() || campo.getAttribute('aria-label') || campo.name;
        const mensaje = campo.required && !campo.value.trim()
            ? 'Completa el campo «' + etiqueta + '».'
            : etiqueta + ': ' + campo.validationMessage;
        avisar(mensaje, 'warning');
        campo.focus();
        return false;
    }

    const folioTicketEntrega = document.getElementById('folioTicketEntrega');
    folioTicketEntrega.addEventListener('input', () => {
        folioTicketEntrega.value = folioTicketEntrega.value.replace(/[^0-9]/g, '').slice(0, 6);
    });

    function confirmar(titulo, mensaje, origen, solicitarTicket = false) {
        const modal = bootstrap.Modal.getOrCreateInstance(confirmacion);
        const boton = confirmacion.querySelector('[data-confirmar]');
        confirmacion.querySelector('.modal-title').textContent = titulo;
        confirmacion.querySelector('#mensajeConfirmacionPedido').textContent = mensaje;
        folioTicketEntrega.closest('[data-ticket-entrega]').hidden = !solicitarTicket;
        folioTicketEntrega.value = '';
        boton.textContent = titulo;

        return new Promise(resolve => {
            let aceptado = false;
            boton.onclick = () => {
                if (solicitarTicket && !/^[0-9]{0,6}$/.test(folioTicketEntrega.value)) {
                    avisar('Ingresa un número de ticket de hasta 6 dígitos.', 'warning');
                    folioTicketEntrega.focus();
                    return;
                }
                aceptado = solicitarTicket ? folioTicketEntrega.value : true;
                modal.hide();
            };
            confirmacion.addEventListener('hidden.bs.modal', () => {
                boton.onclick = null;
                if (origen.isConnected) origen.focus();
                resolve(aceptado);
            }, { once: true });
            modal.show();
        });
    }

    async function solicitar(url, opciones) {
        const respuesta = await fetch(String(url), {
            ...opciones,
            headers: { Accept: 'application/json', 'Content-Type': 'application/json', 'X-CSRF-TOKEN': modulo.dataset.csrf },
        });
        // El interceptor del layout devuelve undefined cuando cancela una búsqueda.
        if (opciones.signal?.aborted) throw new DOMException('Búsqueda cancelada', 'AbortError');
        if (!respuesta) throw new Error('El servidor no devolvió una respuesta.');
        const datos = await respuesta.json().catch(() => ({}));
        if (!respuesta.ok) {
            const mensaje = respuesta.status < 500 ? (Object.values(datos.errors || {})[0]?.[0] || datos.message) : null;
            const error = new Error(mensaje || 'No se pudo completar la solicitud.');
            error.status = respuesta.status;
            throw error;
        }
        return datos;
    }

    function elegirCliente(cliente) {
        formPedido.elements.id_cliente.value = cliente.id;
        seleccionado.textContent = cliente.telefono ? cliente.nombre + ' · ' + cliente.telefono : cliente.nombre;
        tarjetaCliente.hidden = false;
        resultados.replaceChildren();
    }

    document.getElementById('quitarClienteDirecto').addEventListener('click', () => {
        buscador.value = '';
        buscador.dispatchEvent(new Event('input'));
        buscador.focus();
    });

    buscador.addEventListener('input', () => {
        clearTimeout(demoraBusqueda);
        busqueda?.abort();
        resultados.replaceChildren();
        formPedido.elements.id_cliente.value = '';
        seleccionado.textContent = '';
        tarjetaCliente.hidden = true;
        const termino = buscador.value.trim();
        if (termino.length < 2) return;

        demoraBusqueda = setTimeout(async () => {
            const peticion = new AbortController();
            busqueda = peticion;
            try {
                const url = new URL(modulo.dataset.buscarClientes, window.location.href);
                url.searchParams.set('q', termino);
                const clientes = await solicitar(url, { signal: peticion.signal });
                if (peticion.signal.aborted) return;
                if (clientes.length === 0) {
                    resultados.textContent = 'Sin resultados. Puedes registrar un cliente nuevo.';
                }
                for (const cliente of clientes) {
                    const boton = document.createElement('button');
                    boton.type = 'button';
                    boton.className = 'list-group-item list-group-item-action';
                    boton.textContent = cliente.telefono ? cliente.nombre + ' · ' + cliente.telefono : cliente.nombre;
                    boton.addEventListener('click', () => elegirCliente(cliente));
                    resultados.append(boton);
                }
            } catch (error) {
                if (error.name !== 'AbortError') avisar(error.message, 'danger');
            }
        }, 300);
    });

    document.getElementById('cancelarClienteDirecto').addEventListener('click', () => {
        formCliente.reset();
        bootstrap.Collapse.getOrCreateInstance(altaCliente, { toggle: false }).hide();
    });

    formCliente.addEventListener('submit', async (evento) => {
        evento.preventDefault();
        if (!validarFormulario(formCliente)) return;
        const boton = evento.submitter;
        boton.disabled = true;
        try {
            const cliente = await solicitar(formCliente.action, {
                method: 'POST',
                body: JSON.stringify(Object.fromEntries(new FormData(formCliente))),
            });
            clearTimeout(demoraBusqueda);
            busqueda?.abort();
            elegirCliente(cliente);
            buscador.value = '';
            formCliente.reset();
            bootstrap.Collapse.getOrCreateInstance(altaCliente, { toggle: false }).hide();
            avisar('Cliente registrado.', 'success');
        } catch (error) {
            avisar(error.message, error.status === 422 ? 'warning' : 'danger');
        } finally {
            boton.disabled = false;
        }
    });

    function calcularTotalCentavos() {
        return Array.from(articulos.children).reduce((total, fila) => {
            const precio = fila.querySelector('[name="precio_unitario"]').valueAsNumber || 0;
            const cantidad = fila.querySelector('[name="cantidad"]').valueAsNumber || 0;
            return total + Math.round(precio * 100) * cantidad;
        }, 0);
    }

    function actualizarTotales() {
        const totalCentavos = calcularTotalCentavos();
        const minimo = Math.ceil(totalCentavos / 2) / 100;
        anticipo.min = minimo.toFixed(2);
        document.getElementById('totalesPedidoDirecto').textContent =
            'Total estimado ' + moneda.format(totalCentavos / 100) + ' · Anticipo mínimo ' + moneda.format(minimo);
    }

    function agregarArticulo() {
        if (articulos.childElementCount >= 100) return;
        const fila = plantilla.content.querySelector('[data-articulo]').cloneNode(true);
        actualizarCategoria(fila);
        articulos.append(fila);
        actualizarTotales();
    }

    document.getElementById('agregarArticuloSobrePedido').addEventListener('click', agregarArticulo);
    function actualizarCategoria(fila) {
        const categoria = fila.querySelector('[name="categoria_promocion"]');
        const tipo = tipoVenta.value;
        categoria.disabled = !tipo;
        categoria.replaceChildren(new Option(tipo ? 'Selecciona una categoría' : 'Selecciona el tipo de venta', ''));
        for (const [clave, opcion] of Object.entries(categorias[tipo] || {})) {
            categoria.add(new Option(opcion.nombre + ' | ' + opcion.porcentaje + ' %', clave));
        }
    }

    tipoVenta.addEventListener('change', () => {
        convenio.disabled = tipoVenta.value !== 'convenio';
        convenio.required = !convenio.disabled;
        if (convenio.disabled) convenio.value = '';
        Array.from(articulos.children).forEach(actualizarCategoria);
    });

    async function buscarArticulo(campo) {
        if (!/^[0-9]{1,13}$/.test(campo.value)) return;
        const ean = campo.value.padStart(13, '0');
        campo.value = ean;
        if (consultasArticulo.get(campo)?.ean === ean) return;

        const fila = campo.closest('[data-articulo]');
        const estado = fila.querySelector('[data-estado-ean]');
        const descripcion = fila.querySelector('[name="descripcion"]');
        const precio = fila.querySelector('[name="precio_unitario"]');
        const descripcionAnterior = descripcion.value;
        const precioAnterior = precio.value;
        const consulta = { ean, peticion: new AbortController() };
        consultasArticulo.set(campo, consulta);
        estado.hidden = false;
        estado.textContent = 'Buscando en el catálogo maestro…';

        try {
            const url = new URL(modulo.dataset.buscarArticulos, window.location.href);
            url.searchParams.set('ean', ean);
            const { articulo } = await solicitar(url, { signal: consulta.peticion.signal });
            if (consulta.peticion.signal.aborted || !campo.isConnected) return;

            if (!articulo) {
                estado.textContent = 'Código no encontrado en el catálogo maestro. Captura la descripción y el precio.';
                descripcion.focus();
                return;
            }

            if (descripcion.value === descripcionAnterior && articulo.descripcion) {
                descripcion.value = articulo.descripcion;
                consulta.descripcion = descripcion.value;
            }
            if (precio.value === precioAnterior && Number(articulo.precio) > 0) {
                precio.value = articulo.precio;
                consulta.precio = precio.value;
            }
            estado.textContent = 'Artículo encontrado en el catálogo maestro.';
            actualizarTotales();
            precio.focus();
        } catch (error) {
            if (consulta.peticion.signal.aborted || !campo.isConnected) return;
            consultasArticulo.delete(campo);
            estado.textContent = error.message;
        }
    }

    articulos.addEventListener('input', (evento) => {
        const campo = evento.target;
        if (campo.name === 'ean_consultado') {
            const fila = campo.closest('[data-articulo]');
            const consulta = consultasArticulo.get(campo);
            if (consulta) {
                consulta.peticion.abort();
                consultasArticulo.delete(campo);
                const descripcion = fila.querySelector('[name="descripcion"]');
                const precio = fila.querySelector('[name="precio_unitario"]');
                if (descripcion.value === consulta.descripcion) descripcion.value = '';
                if (precio.value === consulta.precio) precio.value = '';
            }
            fila.querySelector('[data-estado-ean]').hidden = true;
            if (campo.value.length === 13) buscarArticulo(campo);
        }
        actualizarTotales();
    });
    articulos.addEventListener('focusout', (evento) => {
        if (evento.target.name === 'ean_consultado') buscarArticulo(evento.target);
        if (evento.target.name === 'cantidad' && Number.isInteger(evento.target.valueAsNumber)) {
            evento.target.value = String(evento.target.valueAsNumber);
        }
    });
    articulos.addEventListener('keydown', (evento) => {
        if (evento.key !== 'Enter' || evento.target.name !== 'ean_consultado') return;
        evento.preventDefault();
        buscarArticulo(evento.target);
    });
    articulos.addEventListener('click', async (evento) => {
        const boton = evento.target.closest('[data-quitar]');
        if (!boton) return;
        if (articulos.childElementCount === 1) {
            avisar('El pedido debe tener al menos un artículo.', 'warning');
            return;
        }
        if (!await confirmar('Quitar artículo', '¿Quieres quitar este artículo del pedido?', boton)) return;
        boton.closest('[data-articulo]').remove();
        actualizarTotales();
    });
    agregarArticulo();

    document.getElementById('limpiarPedidoDirecto').addEventListener('click', async (evento) => {
        if (!await confirmar('Limpiar captura', '¿Quieres borrar los datos capturados de este pedido?', evento.currentTarget)) return;
        clearTimeout(demoraBusqueda);
        busqueda?.abort();
        buscador.value = '';
        resultados.replaceChildren();
        seleccionado.textContent = '';
        tarjetaCliente.hidden = true;
        formCliente.reset();
        bootstrap.Collapse.getOrCreateInstance(altaCliente, { toggle: false }).hide();
        formPedido.reset();
        formPedido.elements.id_cliente.value = '';
        convenio.disabled = true;
        convenio.required = false;
        articulos.replaceChildren();
        agregarArticulo();
        buscador.focus();
    });

    async function guardarYRecargar(boton, url, opciones, mensaje) {
        boton.disabled = true;
        try {
            const datos = await solicitar(url, opciones);
            avisar(mensaje(datos), 'success');
            if (boton.closest('#editarPedidoDirecto')) {
                bootstrap.Modal.getOrCreateInstance(boton.closest('#editarPedidoDirecto')).hide();
            }
            await refrescarPedidosDirectos();
        } catch (error) {
            avisar(error.message, error.status === 422 ? 'warning' : 'danger');
            boton.disabled = false;
        } finally {
            boton.disabled = false;
        }
    }

    const modalPrevia = () => bootstrap.Modal.getOrCreateInstance(vistaPrevia);

    function actualizarVistaPrevia() {
        const lista = vistaPrevia.querySelector('[data-articulos-previos]');
        lista.replaceChildren();
        const totalCentavos = calcularTotalCentavos();
        const umbralPublico = Number(modulo.dataset.umbralPublico);
        const aplicaPromocion = tipoVenta.value !== 'publico' || totalCentavos >= umbralPublico;
        for (const fila of articulos.children) {
            const campo = nombre => fila.querySelector('[name="' + nombre + '"]').value;
            const porcentaje = categorias[tipoVenta.value]?.[campo('categoria_promocion')]?.porcentaje;
            const item = document.createElement('li');
            item.className = 'list-group-item d-flex justify-content-between align-items-center gap-3';
            const texto = document.createElement('span');
            const importeCentavos = Number(campo('cantidad')) * Math.round(Number(campo('precio_unitario')) * 100);
            const descripcion = document.createElement('strong');
            descripcion.textContent = campo('descripcion');
            const importe = document.createElement('span');
            importe.className = 'd-block small text-muted';
            importe.textContent = campo('cantidad') + ' × ' + moneda.format(campo('precio_unitario')) + ' = ' + moneda.format(importeCentavos / 100);
            texto.append(descripcion, importe);
            if (aplicaPromocion && porcentaje != null) {
                const descuento = document.createElement('span');
                descuento.className = 'badge text-bg-warning mt-1';
                descuento.textContent = 'Descuento ' + porcentaje + '%';
                texto.append(descuento);
            }
            const quitar = document.createElement('button');
            quitar.type = 'button';
            quitar.className = 'btn btn-sm btn-outline-danger';
            quitar.textContent = 'Quitar';
            quitar.addEventListener('click', () => {
                if (articulos.childElementCount === 1) {
                    avisar('El pedido debe tener al menos un artículo.', 'warning');
                    return;
                }
                vistaPrevia.addEventListener('hidden.bs.modal', async () => {
                    if (await confirmar('Quitar artículo', '¿Quieres quitar este artículo del pedido?', formPedido.querySelector('[type="submit"]'))) {
                        fila.remove();
                        actualizarTotales();
                        actualizarVistaPrevia();
                    }
                    modalPrevia().show();
                }, { once: true });
                modalPrevia().hide();
            });
            item.append(texto, quitar);
            lista.append(item);
        }
        vistaPrevia.querySelector('[data-cliente-previo]').textContent = seleccionado.textContent;
        vistaPrevia.querySelector('[data-total-previo]').textContent = moneda.format(totalCentavos / 100);
        vistaPrevia.querySelector('[data-aviso-promocion]').textContent = aplicaPromocion ? '' :
            'El descuento de Público en general aplica en pedidos desde ' + moneda.format(umbralPublico / 100) + '.';
        vistaPrevia.querySelector('[data-anticipo-previo]').textContent = moneda.format(anticipo.value);
    }

    formPedido.addEventListener('submit', (evento) => {
        evento.preventDefault();
        if (!formPedido.elements.id_cliente.value) {
            avisar('Selecciona un cliente.', 'warning');
            buscador.focus();
            return;
        }
        if (!validarFormulario(formPedido)) return;
        actualizarVistaPrevia();
        modalPrevia().show();
    });

    vistaPrevia.querySelector('[data-agregar-previo]').addEventListener('click', () => {
        vistaPrevia.addEventListener('hidden.bs.modal', () => {
            agregarArticulo();
            articulos.lastElementChild.querySelector('[name="ean_consultado"]').focus();
        }, { once: true });
        modalPrevia().hide();
    });

    vistaPrevia.querySelector('[data-guardar-previo]').addEventListener('click', async (evento) => {
        const boton = evento.currentTarget;
        boton.disabled = true;
        try {
            const datos = await solicitar(formPedido.action, {
                method: 'POST',
                body: JSON.stringify({
                    id_cliente: formPedido.elements.id_cliente.value,
                    anticipo_cliente: anticipo.value,
                    fecha_encargo: formPedido.elements.fecha_encargo.value,
                    id_sucursal_origen: formPedido.elements.id_sucursal_origen?.value,
                    tipo_venta: tipoVenta.value,
                    id_convenio: convenio.disabled ? null : convenio.value,
                    articulos: Array.from(articulos.children, fila => Object.fromEntries(
                        Array.from(fila.querySelectorAll('input, select:not(:disabled)'), campo => [
                            campo.name,
                            campo.name === 'cantidad' ? campo.valueAsNumber : campo.value,
                        ])
                    )),
                }),
            });
            pedidoGuardado = true;
            vistaPrevia.querySelector('.modal-title').textContent = 'Pedido ' + datos.folio + ' guardado en proceso';
            const pdf = document.createElement('iframe');
            pdf.title = 'Comprobante del pedido';
            pdf.className = 'w-100 border-0 d-block';
            pdf.style.height = '65vh';
            pdf.src = datos.ticket;
            const cuerpo = vistaPrevia.querySelector('.modal-body');
            for (const elemento of cuerpo.children) elemento.hidden = true;
            pdf.dataset.pdfGuardado = '';
            cuerpo.append(pdf);

            const enlace = document.createElement('a');
            enlace.className = 'btn btn-outline-primary';
            enlace.href = datos.ticket;
            enlace.target = '_blank';
            enlace.rel = 'noopener';
            enlace.textContent = 'Abrir comprobante';
            enlace.dataset.pdfGuardado = '';
            const pie = vistaPrevia.querySelector('.modal-footer');
            for (const elemento of pie.children) elemento.hidden = true;
            pie.append(enlace);
            await refrescarPedidosDirectos();
            if (!vistaPrevia.classList.contains('show')) restablecerCapturaGuardada();
        } catch (error) {
            avisar(error.message, error.status === 422 ? 'warning' : 'danger');
            boton.disabled = false;
        }
    });

    function restablecerCapturaGuardada() {
        if (!pedidoGuardado) return;
        pedidoGuardado = false;
        vistaPrevia.querySelectorAll('[data-pdf-guardado]').forEach(elemento => elemento.remove());
        for (const selector of ['.modal-body', '.modal-footer']) {
            for (const elemento of vistaPrevia.querySelector(selector).children) elemento.hidden = false;
        }
        vistaPrevia.querySelector('[data-guardar-previo]').disabled = false;
        vistaPrevia.querySelector('.modal-title').textContent = 'Vista previa del ticket';
        clearTimeout(demoraBusqueda);
        busqueda?.abort();
        formPedido.reset();
        formPedido.elements.id_cliente.value = '';
        buscador.value = '';
        seleccionado.textContent = '';
        tarjetaCliente.hidden = true;
        resultados.replaceChildren();
        convenio.disabled = true;
        convenio.required = false;
        articulos.replaceChildren();
        agregarArticulo();
    }
    vistaPrevia.addEventListener('hidden.bs.modal', restablecerCapturaGuardada);

    document.addEventListener('click', async (evento) => {
        const boton = evento.target.closest('[data-finalizar]');
        if (!boton?.dataset.finalizar) return;
        const folio = await confirmar('Marcar como entregado', '¿Confirmas que el cliente recibió el pedido?', boton, true);
        if (folio !== false) {
            guardarYRecargar(boton, boton.dataset.finalizar, {
                method: 'PATCH',
                body: JSON.stringify({ folio_ticket_entrega: folio || null }),
            }, datos => datos.message);
        }
    });

    const modalCancelacion = document.getElementById('modalCancelarPedido');
    const confirmarCancelacion = modalCancelacion.querySelector('#btnConfirmarCancelar');
    const motivoCancelacion = modalCancelacion.querySelector('#cancelar_pedido_motivo');
    let origenCancelacion;
    let rutaCancelacion;

    tablaPedidos.addEventListener('click', evento => {
        const boton = evento.target.closest('[data-cancelar-pedido-directo]');
        if (!boton || confirmarCancelacion.disabled) return;
        origenCancelacion = boton;
        rutaCancelacion = boton.dataset.cancelarPedidoDirecto;
        const folioPedido = modalCancelacion.querySelector('#cancelar_pedido_folio');
        folioPedido.textContent = boton.dataset.folio;
        const nombreCliente = document.createElement('strong');
        nombreCliente.textContent = boton.dataset.cliente || 'Cliente sin registro';
        folioPedido.closest('p').replaceChildren('¿Estás seguro de cancelar el pedido del cliente ', nombreCliente, ' con folio: ', folioPedido, '?');
        modalCancelacion.querySelector('#cancelar_pedido_id').value = boton.dataset.pedidoId;
        motivoCancelacion.value = '';
        motivoCancelacion.classList.remove('is-invalid');
        bootstrap.Modal.getOrCreateInstance(modalCancelacion).show();
    });
    modalCancelacion.addEventListener('shown.bs.modal', () => motivoCancelacion.focus());
    modalCancelacion.addEventListener('hidden.bs.modal', () => {
        if (origenCancelacion?.isConnected) origenCancelacion.focus();
    });
    motivoCancelacion.addEventListener('input', () => motivoCancelacion.classList.remove('is-invalid'));
    confirmarCancelacion.addEventListener('click', async () => {
        if (confirmarCancelacion.disabled || !rutaCancelacion) return;
        const motivo = motivoCancelacion.value.trim();
        if (!motivo) {
            motivoCancelacion.classList.add('is-invalid');
            avisar('Debe ingresar un motivo de cancelación.', 'warning');
            motivoCancelacion.focus();
            return;
        }
        const textoBoton = confirmarCancelacion.innerHTML;
        confirmarCancelacion.disabled = true;
        confirmarCancelacion.innerHTML = '<span class="spinner-border spinner-border-sm" aria-hidden="true"></span> Cancelando…';
        modalCancelacion.querySelectorAll('[data-bs-dismiss]').forEach(boton => boton.disabled = true);
        try {
            const datos = await solicitar(rutaCancelacion, {method: 'DELETE', body: JSON.stringify({motivo})});
            bootstrap.Modal.getOrCreateInstance(modalCancelacion).hide();
            avisar(datos.message, 'success');
            const url = new URL(paginaPedidos);
            if (tablaPedidos.querySelector('tbody').rows.length === 1 && Number(url.searchParams.get('page')) > 1) {
                url.searchParams.set('page', Number(url.searchParams.get('page')) - 1);
            }
            await refrescarPedidosDirectos(url);
        } catch (error) {
            avisar(error.message, error.status === 422 || error.status === 409 ? 'warning' : 'danger');
        } finally {
            confirmarCancelacion.disabled = false;
            confirmarCancelacion.innerHTML = textoBoton;
            modalCancelacion.querySelectorAll('[data-bs-dismiss]').forEach(boton => boton.disabled = false);
        }
    });

    const modalEdicion = document.getElementById('editarPedidoDirecto');
    if (modalEdicion) {
        const formulario = modalEdicion.querySelector('form');
        const campos = formulario.elements;
        let estadoInicial;
        function actualizarCamposEntrega() {
            const entregado = campos.status.value === '3';
            campos.folio_ticket_entrega.disabled = !entregado;
            modalEdicion.querySelector('[data-editar-ticket]').hidden = !entregado;
            modalEdicion.querySelector('[data-aviso-reapertura]').hidden = entregado || estadoInicial !== '3';
        }
        campos.status.addEventListener('change', actualizarCamposEntrega);
        modulo.addEventListener('click', evento => {
            const boton = evento.target.closest('[data-editar-pedido]');
            if (!boton) return;
            formulario.action = boton.dataset.editarPedido;
            estadoInicial = boton.dataset.estado;
            campos.status.value = estadoInicial;
            campos.fecha_encargo.value = boton.dataset.fecha;
            campos.anticipo_cliente.value = boton.dataset.anticipo || '0';
            campos.folio_ticket_entrega.value = boton.dataset.ticket;
            modalEdicion.querySelector('.modal-title').textContent = 'Editar ' + boton.dataset.folio;
            actualizarCamposEntrega();
            bootstrap.Modal.getOrCreateInstance(modalEdicion).show(boton);
        });
        formulario.addEventListener('submit', evento => {
            evento.preventDefault();
            if (!validarFormulario(formulario)) return;
            guardarYRecargar(evento.submitter, formulario.action, {
                method: 'PATCH',
                body: JSON.stringify(Object.fromEntries(new FormData(formulario))),
            }, datos => datos.message);
        });
    }

    const estadoCarga = document.getElementById('estadoCargaSobrePedido');
    if (categorias.convenio && categorias.publico) {
        estadoCarga?.remove();
    } else if (estadoCarga) {
        estadoCarga.textContent = 'Las categorías de descuento no están disponibles en la configuración del servidor.';
    }
    const aviso = sessionStorage.getItem('crm.sobre-pedido.aviso');
    if (aviso) {
        sessionStorage.removeItem('crm.sobre-pedido.aviso');
        avisar(aviso, 'success');
    }
})();
