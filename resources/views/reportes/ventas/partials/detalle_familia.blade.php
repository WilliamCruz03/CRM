{{-- resources/views/reportes/ventas/partials/modal_detalle_familia.blade.php --}}
<!-- Modal Detalle de Productos por Familia -->
<div class="modal fade" id="modalDetalleFamilia" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title">
                    <i class="bi bi-boxes"></i> Productos de la familia: <strong id="modalFamiliaNombre"></strong>
                    <br>
                    <small id="modalClienteNombre"></small>
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-info" id="modalPeriodoInfo"></div>
                
                <div class="table-responsive">
                    <table class="table table-bordered table-striped" id="productosFamiliaTable">
                        <thead>
                            <tr>
                                <th>EAN</th>
                                <th>Descripción</th>
                                <th>Ventas</th>
                                <th>Cantidad Vendida</th>
                                <th>Monto Total</th>
                                <th>Precio Promedio</th>
                                <th>Última Venta</th>
                            </tr>
                        </thead>
                        <tbody id="productosFamiliaBody">
                            <tr>
                                <td colspan="7" class="text-center">Seleccione una familia para ver sus productos...</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
// Función global para cargar productos de una familia
window.cargarProductosFamilia = function(button) {
    const clienteId = button.getAttribute('data-cliente-id');
    const familiaId = button.getAttribute('data-familia-id');
    const familiaNombre = button.getAttribute('data-familia-nombre');
    const clienteNombre = button.getAttribute('data-cliente-nombre');
    const fechaInicio = button.getAttribute('data-fecha-inicio');
    const fechaFin = button.getAttribute('data-fecha-fin');
    
    const modal = document.getElementById('modalDetalleFamilia');
    const modalFamiliaNombre = document.getElementById('modalFamiliaNombre');
    const modalClienteNombre = document.getElementById('modalClienteNombre');
    const modalPeriodoInfo = document.getElementById('modalPeriodoInfo');
    const productosBody = document.getElementById('productosFamiliaBody');
    
    modalFamiliaNombre.textContent = familiaNombre;
    modalClienteNombre.textContent = clienteNombre;
    modalPeriodoInfo.innerHTML = `<strong>Período:</strong> ${new Date(fechaInicio).toLocaleDateString()} - ${new Date(fechaFin).toLocaleDateString()}`;
    productosBody.innerHTML = '<tr><td colspan="7" class="text-center">Cargando productos...</td></tr>';
    
    // Función para mostrar el modal cuando Bootstrap esté listo
    function mostrarModal() {
        if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
            const modalInstance = new bootstrap.Modal(modal);
            modalInstance.show();
        } else {
            setTimeout(mostrarModal, 100);
        }
    }
    
    mostrarModal();
    
    // Obtener los filtros actuales de la URL
    const urlParams = new URLSearchParams(window.location.search);
    const top = urlParams.get('top') || 'todos';
    const sortBy = urlParams.get('sort_by') || 'monto_total';
    const filtroFecha = urlParams.get('filtro_fecha') || 'este_mes';
    const indicacionId = urlParams.get('indicacion_id') || '';
    
    let url = `/reportes/ventas/cliente/${clienteId}/familia/${familiaId}?top=${top}&sort_by=${sortBy}&filtro_fecha=${filtroFecha}&fecha_inicio=${fechaInicio}&fecha_fin=${fechaFin}`;
    
    if (indicacionId) {
        url += `&indicacion_id=${indicacionId}`;
    }
    
    fetch(url)
        .then(response => response.json())
        .then(data => {
            if (data.success && data.data && data.data.length > 0) {
                let html = '';
                data.data.forEach(producto => {
                    html += `
                        <tr>
                            <td>${producto.ean}</td>
                            <td>${producto.descripcion}</td>
                            <td class="text-center">${Number(producto.transacciones).toLocaleString()}</td>
                            <td class="text-center">${Number(producto.cantidad_vendida).toLocaleString()}</td>
                            <td class="text-right">$${Number(producto.monto_total).toLocaleString('es-MX', {minimumFractionDigits: 2})}</td>
                            <td class="text-right">$${Number(producto.precio_promedio).toLocaleString('es-MX', {minimumFractionDigits: 2})}</td>
                            <td class="text-center">${new Date(producto.ultima_venta).toLocaleDateString()}</td>
                        </tr>
                    `;
                });
                productosBody.innerHTML = html;
                
                if (typeof $ !== 'undefined' && $.fn.DataTable) {
                    if ($.fn.DataTable.isDataTable('#productosFamiliaTable')) {
                        $('#productosFamiliaTable').DataTable().destroy();
                    }
                    $('#productosFamiliaTable').DataTable({
                        language: { url: '//cdn.datatables.net/plug-ins/1.10.24/i18n/Spanish.json' },
                        order: [[4, 'desc']],
                        pageLength: 25,
                        searching: true,
                        paging: true,
                        info: true
                    });
                }
            } else {
                productosBody.innerHTML = '<tr><td colspan="7" class="text-center">No se encontraron productos para esta familia en el período seleccionado</td><\/tr>';
            }
        })
        .catch(error => {
            console.error('Error:', error);
            productosBody.innerHTML = '<tr><td colspan="7" class="text-center text-danger">Error al cargar los productos</td><\/tr>';
        });
};
</script>
@endpush