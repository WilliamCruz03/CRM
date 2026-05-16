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

// En modal-convertir-ean.blade.php, dentro del script

function confirmarConvertirEAN(pedidoId) {
    const productosExternos = [];
    const inputs = document.querySelectorAll('#tablaProductosExternos .nuevo-ean');
    let todosCompletos = true;
    let todosValidos = true;
    
    // Solo validar si hay inputs (productos externos)
    if (inputs.length > 0) {
        inputs.forEach(input => {
            const nuevoEan = input.value.trim();
            const idx = input.getAttribute('data-idx');
            
            if (!nuevoEan) {
                todosCompletos = false;
                input.classList.add('is-invalid');
            } 
            else if (!/^\d{13}$/.test(nuevoEan) && !/^T\d{12}$/.test(nuevoEan)) {
                todosValidos = false;
                input.classList.add('is-invalid');
                input.setCustomValidity('Debe ser un código de 13 dígitos numéricos');
            } else {
                input.classList.remove('is-invalid');
                input.setCustomValidity('');
                productosExternos.push({
                    id_detalle: productosExternosData[idx].id_detalle,
                    nuevo_ean: nuevoEan
                });
            }
        });
        
        if (!todosCompletos) {
            if (window.mostrarToast) window.mostrarToast('Completa todos los códigos de barras', 'warning');
            return;
        }
        
        if (!todosValidos) {
            if (window.mostrarToast) window.mostrarToast('Los códigos de barras deben tener 13 dígitos numéricos', 'warning');
            return;
        }
    }
    
    // Mostrar loading en el botón
    const btn = document.getElementById('btnGuardarConvertirEAN');
    const textoOriginal = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<i class="bi bi-hourglass-split"></i> Procesando...';
    
    // Enviar tanto productos externos (con nuevo EAN) como el pedido
    fetch('/ventas/pedidos/marcar-listo-con-ean', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}'
        },
        body: JSON.stringify({
            pedido_id: pedidoId,
            productos_externos: productosExternos  // Array de conversiones
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
        if (window.mostrarToast) window.mostrarToast('Error de conexión', 'danger');
        btn.disabled = false;
        btn.innerHTML = textoOriginal;
    });
}

// Asignar la función al botón del modal
document.getElementById('btnGuardarConvertirEAN')?.addEventListener('click', function() {
    const pedidoId = document.getElementById('convertir_pedido_id').value;
    if (pedidoId) {
        confirmarConvertirEAN(pedidoId);
    }
});

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