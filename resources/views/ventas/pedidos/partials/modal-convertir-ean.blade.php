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
    document.getElementById('tablaProductosExternos').innerHTML = '<tr><td colspan="3" class="text-center">Cargando...</td></tr>';
    
    fetch(`/ventas/pedidos/${pedidoId}/productos-externos`, {
        headers: { 'Accept': 'application/json' }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success && data.data.length > 0) {
            productosExternosData = data.data;
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
        } else {
            document.getElementById('tablaProductosExternos').innerHTML = '<tr><td colspan="3" class="text-center text-muted">No hay productos externos pendientes</td></tr>';
            document.getElementById('btnGuardarConvertirEAN').disabled = true;
        }
    })
    .catch(error => {
        console.error('Error:', error);
        document.getElementById('tablaProductosExternos').innerHTML = '<tr><td colspan="3" class="text-center text-danger">Error al cargar productos</td></tr>';
    });
    
    new bootstrap.Modal(document.getElementById('modalConvertirEAN')).show();
}

function escapeHtml(str) {
    if (!str) return '';
    return str.replace(/[&<>]/g, function(m) {
        if (m === '&') return '&amp;';
        if (m === '<') return '&lt;';
        if (m === '>') return '&gt;';
        return m;
    });
}

document.getElementById('btnGuardarConvertirEAN')?.addEventListener('click', function() {
    const pedidoId = document.getElementById('convertir_pedido_id').value;
    const productos = [];
    const inputs = document.querySelectorAll('#tablaProductosExternos .nuevo-ean');
    let valid = true;
    
    inputs.forEach((input, idx) => {
        const nuevoEan = input.value.trim();
        if (!nuevoEan) {
            valid = false;
            input.classList.add('is-invalid');
        } else {
            input.classList.remove('is-invalid');
            productos.push({
                id_detalle: productosExternosData[idx].id_detalle,
                tipo: productosExternosData[idx].tipo, // 'cotizacion' o 'pedido'
                nuevo_ean: nuevoEan
            });
        }
    });
    
    if (!valid) {
        if (window.mostrarToast) window.mostrarToast('Complete todos los nuevos EAN', 'warning');
        return;
    }
    
    const btn = this;
    btn.disabled = true;
    btn.innerHTML = '<i class="bi bi-hourglass-split"></i> Procesando...';
    
    fetch('/ventas/pedidos/marcar-listo-con-ean', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({
            pedido_id: pedidoId,
            productos: productos
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            if (window.mostrarToast) window.mostrarToast(data.message, 'success');
            const modal = bootstrap.Modal.getInstance(document.getElementById('modalConvertirEAN'));
            modal.hide();
            setTimeout(() => location.reload(), 1000);
        } else {
            if (window.mostrarToast) window.mostrarToast(data.message, 'danger');
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-check2-circle"></i> Marcar como listo';
        }
    })
    .catch(error => {
        console.error('Error:', error);
        if (window.mostrarToast) window.mostrarToast('Error de conexión', 'danger');
        btn.disabled = false;
        btn.innerHTML = '<i class="bi bi-check2-circle"></i> Marcar como listo';
    });
});
</script>
@endpush