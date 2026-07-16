<!-- Modal Convertir EAN (Marcar listo) -->
<div class="modal fade" id="modalConvertirEAN" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title">
                    <i class="bi bi-check2-circle"></i> Marcar como listo - Convertir EAN
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-info">
                    <i class="bi bi-info-circle"></i> Los productos marcados como <strong>"Sobre pedido"</strong> requieren un código EAN real.
                    Ingrese el nuevo EAN para cada producto. Debe ser un código numérico válido.
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
                                        <th>EAN actual (Temporal)</th>
                                        <th>Nuevo EAN real *</th>
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
                            <div class="col-md-6">
                                <label for="folio_ticket" class="form-label fw-bold">Folio Ticket <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" id="folio_ticket" 
                                    placeholder="Ingrese el folio completo (ej: 2456387)" required min="1">
                                <small class="text-muted">Primer dígito = Caja, los 6 siguientes = Ticket. Ej: 2456387 (Caja 2, Ticket 456387)</small>
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
    document.getElementById('folio_ticket').value = '';
    document.getElementById('folio_ticket').classList.remove('is-invalid');
    document.getElementById('tablaProductosExternos').innerHTML = '<tr><td colspan="3" class="text-center">Cargando...</td></tr>';
    
    fetch(`/ventas/pedidos/${pedidoId}/productos-externos`, {
        headers: { 'Accept': 'application/json' }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success && data.data.length > 0) {
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
    const folioTicket = document.getElementById('folio_ticket').value.trim();
    
    if (!pedidoId) {
        if (window.mostrarToast) window.mostrarToast('Error: No se encontró el ID del pedido', 'danger');
        return;
    }
    
    // Validar folio ticket
    if (!folioTicket) {
        document.getElementById('folio_ticket').classList.add('is-invalid');
        if (window.mostrarToast) window.mostrarToast('Debe ingresar el folio del ticket', 'warning');
        return;
    }
    
    if (isNaN(folioTicket) || parseInt(folioTicket) <= 0) {
        document.getElementById('folio_ticket').classList.add('is-invalid');
        if (window.mostrarToast) window.mostrarToast('El folio ticket debe ser un número válido', 'warning');
        return;
    }
    
    document.getElementById('folio_ticket').classList.remove('is-invalid');
    
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
                folio_ticket: parseInt(folioTicket)
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
        if (window.mostrarToast) window.mostrarToast('Completa todos los códigos de barras', 'warning');
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
    // const btn ya está declarado, solo reutilizar
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
            folio_ticket: parseInt(folioTicket)
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

// event listener
document.addEventListener('DOMContentLoaded', function() {
    const btnGuardar = document.getElementById('btnGuardarConvertirEAN');
    if (btnGuardar) {
        btnGuardar.addEventListener('click', window.confirmarConvertirEAN);
    }
    
    // Validar folio_ticket al escribir
    const folioInput = document.getElementById('folio_ticket');
    if (folioInput) {
        folioInput.addEventListener('input', function() {
            if (this.value && parseInt(this.value) > 0) {
                this.classList.remove('is-invalid');
            }
        });
    }
});
</script>
@endpush