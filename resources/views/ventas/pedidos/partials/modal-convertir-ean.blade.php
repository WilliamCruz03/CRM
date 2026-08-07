<!-- Modal Convertir EAN (Marcar listo) -->
<div class="modal fade" id="modalConvertirEAN" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title">
                    <i class="bi bi-check2-circle"></i> Marcar como listo
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="alertSobrePedido" class="alert alert-info" style="display: none;">
                    <i class="bi bi-info-circle"></i> Los productos marcados como <strong>"Sobre pedido"</strong> requieren un código EAN.
                    Ingrese el nuevo EAN para cada producto. Debe ser un código numérico válido (13 dígitos).
                </div>
                <form id="formConvertirEAN">
                    @csrf
                    <input type="hidden" id="convertir_pedido_id">
                    <input type="hidden" id="convertir_sucursal_id">
                    <input type="hidden" id="convertir_sucursal_pedido_id">
                    <input type="hidden" id="tiene_externos">

                    <!-- Envolver la tabla en un contenedor para mostrar/ocultar -->
                    <div id="tablaProductosExternosContainer">
                        <div class="table-responsive">
                            <table class="table table-bordered table-sm">
                                <thead class="table-light">
                                    <tr>
                                        <th>Producto</th>
                                        <th>Codigo actual (Temporal)</th>
                                        <th>Nuevo codigo real <strong class="text-danger">*</strong></th>
                                    </tr>
                                </thead>
                                <tbody id="tablaProductosExternos">
                                    <tr><td colspan="3" class="text-center">Cargando...</td></tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <!-- Campo Folio Ticket -->
                    <div class="mt-3">
                        <div class="row">
                            <div class="col-md-8">
                                <label class="form-label fw-bold">Folio Ticket <span class="text-danger">*</span></label>
                                <div class="p-3 bg-light rounded border">
                                    <div class="row g-3 align-items-center">
                                        <div class="col-12">
                                            <div class="row g-2">
                                                <div class="col-4">
                                                    <label class="form-label small mb-1 text-muted">Caja</label>
                                                    <input type="number" class="form-control form-control-lg text-center" id="folio_caja" 
                                                        placeholder="Caja" required max="9" maxlength="1" style="font-size: 2rem; height: 70px;">
                                                    <small class="text-muted">1 dígito (1-9)</small>
                                                </div>
                                                <div class="col-8">
                                                    <label class="form-label small mb-1 text-muted">Ticket</label>
                                                    <input type="number" class="form-control form-control-lg text-center" id="folio_ticket" 
                                                        placeholder="Ticket" required min="1" maxlength="6" style="font-size: 2rem; height: 70px;">
                                                    <small class="text-muted">6 dígitos</small>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <!-- Preview del folio completo - mejor alineado -->
                                        <div class="col-12 mt-2">
                                            <div class="d-flex align-items-center gap-2">
                                                <span class="text-muted small fw-bold">Folio completo:</span>
                                                <span class="badge bg-primary fs-6 p-2" id="previewFolioCompleto" style="min-width: 100px;">-</span>
                                                <span class="text-muted small">
                                                    <i class="bi bi-info-circle"></i> Caja + Ticket = 7 dígitos
                                                </span>
                                            </div>
                                        </div>
                                        
                                        <div class="col-12">
                                            <div class="alert alert-info mb-0 py-2">
                                                <i class="bi bi-lightbulb"></i>
                                                <small>
                                                    <strong>Ejemplo:</strong> Caja <strong>2</strong> + Ticket <strong>456387</strong> 
                                                    = Folio completo <strong>2456387</strong>
                                                </small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-success" id="btnGuardarConvertirEAN">
                    <i class="bi bi-check2-circle"></i> Marcar como listo
                </button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
let productosExternosData = [];

function abrirModalConvertirEAN(pedidoId) {
    document.getElementById('convertir_pedido_id').value = pedidoId;
    document.getElementById('folio_caja').value = '';
    document.getElementById('folio_ticket').value = '';
    document.getElementById('folio_caja').classList.remove('is-invalid');
    document.getElementById('folio_ticket').classList.remove('is-invalid');
    document.getElementById('tablaProductosExternos').innerHTML = '<tr><td colspan="3" class="text-center">Cargando...</td></tr>';
    // Ocultar el alert por defecto
    document.getElementById('alertSobrePedido').style.display = 'none';
    
    fetch(`/ventas/pedidos/${pedidoId}/productos-externos`, {
        headers: { 'Accept': 'application/json' }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success && data.data.length > 0) {
            // Mostrar el alert solo si hay productos sobre pedido
            document.getElementById('alertSobrePedido').style.display = 'block';
            
            // Guardar en window para que esté disponible globalmente
            window.productosExternosData = data.data;
            let html = '';
            data.data.forEach((item, idx) => {
                html += `<tr>
                    <td><strong>${escapeHtml(item.descripcion)}</strong></td>
                    <td class="text-center"><span class="badge bg-secondary">${escapeHtml(item.ean_original)}</span></td>
                    <td>
                        <input type="text" class="form-control form-control-sm nuevo-ean" 
                               data-idx="${idx}" 
                               placeholder="Nuevo EAN (ej. 7501234567890)"
                               required>
                    </td>
                </tr>`;
            });
            document.getElementById('tablaProductosExternos').innerHTML = html;
            document.getElementById('btnGuardarConvertirEAN').disabled = false;
        } else {
            document.getElementById('tablaProductosExternos').innerHTML = '<tr><td colspan="3" class="text-center text-muted">No hay productos externos pendientes</td></tr>';
            document.getElementById('btnGuardarConvertirEAN').disabled = true;
        }
    })
    .catch(error => {
        console.error('Error:', error);
        document.getElementById('tablaProductosExternos').innerHTML = '<tr><td colspan="3" class="text-center text-danger">Error al cargar productos</td></tr>';
        document.getElementById('btnGuardarConvertirEAN').disabled = true;
    });
    
    new bootstrap.Modal(document.getElementById('modalConvertirEAN')).show();
}

// Función unificada para confirmar y guardar
window.confirmarConvertirEAN = function() {
    const pedidoId = document.getElementById('convertir_pedido_id').value;
    const sucursalPedidoId = document.getElementById('convertir_sucursal_pedido_id').value;
    const tieneExternos = parseInt(document.getElementById('tiene_externos').value || 0);
    const folioCaja = document.getElementById('folio_caja').value.trim();
    const folioTicket = document.getElementById('folio_ticket').value.trim();
    
    if (!pedidoId) {
        if (window.mostrarToast) window.mostrarToast('Error: No se encontró el ID del pedido', 'danger');
        return;
    }
    
    // Validar caja (1 dígito)
    if (!folioCaja) {
        document.getElementById('folio_caja').classList.add('is-invalid');
        if (window.mostrarToast) window.mostrarToast('Debe ingresar el número de caja (1 dígito)', 'warning');
        return;
    }
    
    if (isNaN(folioCaja) || parseInt(folioCaja) < 1 || parseInt(folioCaja) > 9) {
        document.getElementById('folio_caja').classList.add('is-invalid');
        if (window.mostrarToast) window.mostrarToast('El número de caja debe ser un dígito entre 1 y 9', 'warning');
        return;
    }
    
    document.getElementById('folio_caja').classList.remove('is-invalid');
    
    // Validar ticket (6 dígitos)
    if (!folioTicket) {
        document.getElementById('folio_ticket').classList.add('is-invalid');
        if (window.mostrarToast) window.mostrarToast('Debe ingresar el número de ticket (6 dígitos)', 'warning');
        return;
    }
    
    if (isNaN(folioTicket) || parseInt(folioTicket) <= 0 || folioTicket.length !== 6) {
        document.getElementById('folio_ticket').classList.add('is-invalid');
        if (window.mostrarToast) window.mostrarToast('El ticket debe tener exactamente 6 dígitos', 'warning');
        return;
    }
    
    document.getElementById('folio_ticket').classList.remove('is-invalid');
    
    // Combinar caja + ticket en un solo número de 7 dígitos
    const folioCompleto = parseInt(folioCaja + folioTicket);
    
    // Declarar btn UNA SOLA VEZ
    const btn = document.getElementById('btnGuardarConvertirEAN');
    const textoOriginal = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<i class="bi bi-hourglass-split"></i> Procesando...';
    
    // Si no tiene externos, marcar directamente
    if (tieneExternos === 0) {
        fetch(`/ventas/pedidos/sucursal/${sucursalPedidoId}/marcar-listo`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}'
            },
            body: JSON.stringify({
                folio_ticket: folioCompleto
            })
        })
        .then(response => response.json())
        .then(data => {
            const modal = bootstrap.Modal.getInstance(document.getElementById('modalConvertirEAN'));
            if (modal) modal.hide();
            
            if (data.success) {
                if (window.mostrarToast) window.mostrarToast(data.message, 'success');
                setTimeout(() => location.reload(), 1500);
            } else {
                if (window.mostrarToast) window.mostrarToast(data.message, 'danger');
                btn.disabled = false;
                btn.innerHTML = textoOriginal;
            }
        })
        .catch(error => {
            console.error('Error:', error);
            if (window.mostrarToast) window.mostrarToast('Error de conexión: ' + error.message, 'danger');
            btn.disabled = false;
            btn.innerHTML = textoOriginal;
        });
        return;
    }
    
    // Si tiene externos, procesar conversión
    if (!window.productosExternosData || window.productosExternosData.length === 0) {
        if (window.mostrarToast) window.mostrarToast('Error: No se pudieron cargar los productos externos', 'danger');
        btn.disabled = false;
        btn.innerHTML = textoOriginal;
        return;
    }
    
    const productosExternos = [];
    const inputs = document.querySelectorAll('#tablaProductosExternos .nuevo-ean');
    let todosCompletos = true;
    let todosValidos = true;

    inputs.forEach(input => {
        const nuevoEan = input.value.trim();
        const idx = parseInt(input.getAttribute('data-idx'));
        
        if (!nuevoEan) {
            todosCompletos = false;
            input.classList.add('is-invalid');
        } else if (!/^\d{13}$/.test(nuevoEan) && !/^T\d{12}$/.test(nuevoEan)) {
            todosValidos = false;
            input.classList.add('is-invalid');
            input.setCustomValidity('Debe ser un código de 13 dígitos numéricos');
        } else {
            input.classList.remove('is-invalid');
            input.setCustomValidity('');
            
            const productoData = window.productosExternosData[idx];
            if (productoData && productoData.id_detalle) {
                productosExternos.push({
                    id_detalle: productoData.id_detalle,
                    nuevo_ean: nuevoEan
                });
            } else {
                console.error('Producto no encontrado para índice:', idx);
                todosValidos = false;
                input.classList.add('is-invalid');
            }
        }
    });

    if (!todosCompletos) {
        if (window.mostrarToast) window.mostrarToast('Los codigos de barra no pueden estar vacios', 'warning');
        btn.disabled = false;
        btn.innerHTML = textoOriginal;
        return;
    }
    
    if (!todosValidos) {
        if (window.mostrarToast) window.mostrarToast('Los códigos de barras deben tener 13 dígitos numéricos', 'warning');
        btn.disabled = false;
        btn.innerHTML = textoOriginal;
        return;
    }
    
    // REUTILIZAR btn, no declarar de nuevo
    btn.disabled = true;
    btn.innerHTML = '<i class="bi bi-hourglass-split"></i> Procesando...';
    
    fetch('/ventas/pedidos/marcar-listo-ean', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}'
        },
        body: JSON.stringify({
            pedido_id: pedidoId,
            productos_externos: productosExternos,
            folio_ticket: folioCompleto
        })
    })
    .then(response => response.json())
    .then(data => {
        const modal = bootstrap.Modal.getInstance(document.getElementById('modalConvertirEAN'));
        if (modal) modal.hide();
        
        if (data.success) {
            if (window.mostrarToast) window.mostrarToast(data.message, 'success');
            setTimeout(() => location.reload(), 1500);
        } else {
            if (window.mostrarToast) window.mostrarToast(data.message, 'danger');
            btn.disabled = false;
            btn.innerHTML = textoOriginal;
        }
    })
    .catch(error => {
        console.error('Error:', error);
        if (window.mostrarToast) window.mostrarToast('Error de conexión: ' + error.message, 'danger');
        btn.disabled = false;
        btn.innerHTML = textoOriginal;
    });
};

function escapeHtml(str) {
    if (!str) return '';
    return str.replace(/[&<>]/g, function(m) {
        if (m === '&') return '&amp;';
        if (m === '<') return '&lt;';
        if (m === '>') return '&gt;';
        return m;
    });
}

function formatearFolioTicket(folioCompleto) {
    if (!folioCompleto) return '-';
    const str = String(folioCompleto);
    if (str.length <= 1) return str;
    const caja = str.charAt(0);
    const ticket = str.substring(1);
    return `Caja: ${caja} | Ticket: ${ticket}`;
}

// Actualizar preview del folio completo (con validación mejorada)
function actualizarPreviewFolio() {
    const caja = document.getElementById('folio_caja').value || '';
    const ticket = document.getElementById('folio_ticket').value || '';
    const preview = document.getElementById('previewFolioCompleto');
    
    // Validar que caja tenga exactamente 1 dígito y ticket exactamente 6 dígitos
    const cajaValida = caja.length === 1 && /^[1-9]$/.test(caja);
    const ticketValido = ticket.length === 6 && /^\d+$/.test(ticket);
    
    if (cajaValida && ticketValido) {
        const folioCompleto = caja + ticket;
        preview.textContent = folioCompleto;
        preview.classList.remove('text-muted', 'bg-secondary');
        preview.classList.add('bg-primary');
    } else {
        // Mostrar qué falta
        let mensaje = '';
        if (!cajaValida && !ticketValido) {
            mensaje = 'Faltan ambos campos';
        } else if (!cajaValida) {
            mensaje = 'Falta caja (1 dígito)';
        } else if (!ticketValido) {
            mensaje = `Falta ticket (${ticket.length}/6 dígitos)`;
        }
        preview.textContent = mensaje || '-';
        preview.classList.remove('bg-primary');
        preview.classList.add('text-muted', 'bg-secondary');
    }
}

// Limitar el input de caja a 1 dígito y el de ticket a 6 dígitos
function limitarLongitud(input, maxLength) {
    if (input.value.length > maxLength) {
        input.value = input.value.slice(0, maxLength);
    }
}

// Configurar eventos
document.addEventListener('DOMContentLoaded', function() {
    const cajaInput = document.getElementById('folio_caja');
    const ticketInput = document.getElementById('folio_ticket');
    const preview = document.getElementById('previewFolioCompleto');
    
    if (cajaInput) {
        // Limitar a 1 dígito
        cajaInput.addEventListener('input', function() {
            limitarLongitud(this, 1);
            // Eliminar caracteres no numéricos
            this.value = this.value.replace(/[^0-9]/g, '');
            if (this.value && parseInt(this.value) > 9) {
                this.value = '9';
            }
            actualizarPreviewFolio();
        });
    }
    
    if (ticketInput) {
        // Limitar a 6 dígitos
        ticketInput.addEventListener('input', function() {
            limitarLongitud(this, 6);
            // Eliminar caracteres no numéricos
            this.value = this.value.replace(/[^0-9]/g, '');
            actualizarPreviewFolio();
        });
    }
    
    // Inicializar preview
    if (preview) {
        preview.textContent = '-';
        preview.classList.add('text-muted');
    }
});

// event listener
document.addEventListener('DOMContentLoaded', function() {
    const btnGuardar = document.getElementById('btnGuardarConvertirEAN');
    if (btnGuardar) {
        btnGuardar.addEventListener('click', window.confirmarConvertirEAN);
    }
    
    // Validar caja al escribir
    const cajaInput = document.getElementById('folio_caja');
    if (cajaInput) {
        cajaInput.addEventListener('input', function() {
            if (this.value && parseInt(this.value) >= 1 && parseInt(this.value) <= 9) {
                this.classList.remove('is-invalid');
            }
            // Limitar a 1 dígito
            if (this.value.length > 1) {
                this.value = this.value.slice(0, 1);
            }
        });
    }
    
    // Validar ticket al escribir
    const ticketInput = document.getElementById('folio_ticket');
    if (ticketInput) {
        ticketInput.addEventListener('input', function() {
            if (this.value && this.value.length === 6 && parseInt(this.value) > 0) {
                this.classList.remove('is-invalid');
            }
            // Limitar a 6 dígitos
            if (this.value.length > 6) {
                this.value = this.value.slice(0, 6);
            }
        });
    }
});
</script>
@endpush