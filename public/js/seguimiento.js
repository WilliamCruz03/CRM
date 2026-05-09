// Funciones para el modal de seguimiento

if (!document.querySelector) {
    console.warn('QuerySelector no soportado');
}
// Variable global para el teléfono del cliente
window.telefonoClienteActual = null;

// Función para enviar mensaje por WhatsApp
window.enviarMensajeWhatsApp = function() {
    const mensajeElement = document.getElementById('seg_mensaje_cliente');
    const mensajeValue = mensajeElement ? mensajeElement.value : '';
    
    if (!mensajeValue.trim()) {
        if (window.mostrarToast) {
            window.mostrarToast('Escribe un mensaje antes de enviar', 'warning');
        } else {
            alert('Escribe un mensaje antes de enviar');
        }
        return;
    }
    
    if (!window.telefonoClienteActual) {
        if (window.mostrarToast) {
            window.mostrarToast('El cliente no tiene número de teléfono registrado', 'danger');
        } else {
            alert('El cliente no tiene número de teléfono registrado');
        }
        return;
    }
    
    const url = `https://wa.me/${window.telefonoClienteActual}?text=${encodeURIComponent(mensajeValue)}`;
    window.open(url, '_blank');
};

// Función para guardar seguimiento (unificada)
window.guardarSeguimiento = function() {
    const horaFinElement = document.getElementById('seg_hora_fin');
    const horaFin = horaFinElement ? horaFinElement.value : '';
    
    if (!horaFin) {
        if (window.mostrarToast) {
            window.mostrarToast('La hora de fin es obligatoria', 'warning');
        }
        if (horaFinElement) horaFinElement.focus();
        return;
    }
    
    const tipoElement = document.getElementById('seg_tipo');
    const folioElement = document.getElementById('seg_folio_referencia');
    const idClienteElement = document.getElementById('seg_id_cliente_maestro');
    const mensajeClienteElement = document.getElementById('seg_mensaje_cliente');
    const motivoElement = document.getElementById('seg_motivo_no_finalizacion');
    const conversacionElement = document.getElementById('seg_conversacion');
    const quejaElement = document.getElementById('seg_queja');
    const sugerenciaElement = document.getElementById('seg_sugerencia');
    
    const formData = {
        tipo: tipoElement ? tipoElement.value : '',
        folio_referencia: folioElement ? folioElement.value : '',
        id_cliente_maestro: idClienteElement ? idClienteElement.value : '',
        hora_fin: horaFin,
        mensaje_cliente: mensajeClienteElement ? mensajeClienteElement.value || null : null,
        motivo_no_finalizacion: motivoElement ? motivoElement.value || null : null,
        conversacion: conversacionElement ? conversacionElement.value || null : null,
        queja: quejaElement ? quejaElement.value || null : null,
        sugerencia: sugerenciaElement ? sugerenciaElement.value || null : null
    };
    
    if (window.mostrarToast) {
        window.mostrarToast('Guardando seguimiento...', 'warning');
    }
    
    fetch('/ventas/seguimiento/store', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
        },
        body: JSON.stringify(formData)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const modal = bootstrap.Modal.getInstance(document.getElementById('modalSeguimiento'));
            if (modal) modal.hide();
            
            if (window.mostrarToast) {
                window.mostrarToast(data.message, 'success');
            }
            
            setTimeout(() => location.reload(), 1000);
        } else {
            if (data.errors) {
                const errores = Object.values(data.errors).flat().join('\n');
                if (window.mostrarToast) {
                    window.mostrarToast(errores, 'danger');
                }
            } else {
                if (window.mostrarToast) {
                    window.mostrarToast(data.message || 'Error al guardar', 'danger');
                }
            }
        }
    })
    .catch(error => {
        console.error('Error:', error);
        if (window.mostrarToast) {
            window.mostrarToast('Error de conexión al guardar', 'danger');
        }
    });
};

// Función para cargar datos en el modal (unificada)
window.cargarDatosModalSeguimiento = function(data) {
    // Datos ocultos
    const segTipo = document.getElementById('seg_tipo');
    const segFolioReferencia = document.getElementById('seg_folio_referencia');
    const segIdCliente = document.getElementById('seg_id_cliente_maestro');
    
    if (segTipo) segTipo.value = data.tipo;
    if (segFolioReferencia) segFolioReferencia.value = data.folio;
    if (segIdCliente) segIdCliente.value = data.id_cliente_maestro;
    
    // Título del modal según tipo
    const tituloModal = document.getElementById('modalSeguimientoTitulo');
    if (tituloModal) {
        switch(data.tipo) {
            case 'cotizacion':
                tituloModal.textContent = 'Seguimiento a Cotización';
                break;
            case 'pedido':
                tituloModal.textContent = 'Seguimiento a Pedido';
                break;
            case 'venta':
                tituloModal.textContent = 'Seguimiento a Venta';
                break;
            default:
                tituloModal.textContent = 'Seguimiento';
        }
    }
    
    // Información del documento
    const segFolio = document.getElementById('seg_folio');
    const segFechaCreacion = document.getElementById('seg_fecha_creacion');
    const segEstado = document.getElementById('seg_estado');
    
    if (segFolio) segFolio.textContent = data.folio;
    if (segFechaCreacion) segFechaCreacion.textContent = data.fecha_creacion;
    if (segEstado) segEstado.innerHTML = `<span class="badge bg-info">${data.estado_nombre || 'En proceso'}</span>`;
    
    // Calcular días correctamente
    const segDias = document.getElementById('seg_dias');
    if (segDias && data.fecha_creacion) {
        const fechaCreacion = new Date(data.fecha_creacion);
        const hoy = new Date();
        fechaCreacion.setHours(0, 0, 0, 0);
        hoy.setHours(0, 0, 0, 0);
        const diffTime = hoy - fechaCreacion;
        const diffDias = Math.floor(diffTime / (1000 * 60 * 60 * 24));
        segDias.innerHTML = `<span class="badge ${diffDias >= 7 ? 'bg-warning' : 'bg-secondary'}">${diffDias} día(s)</span>`;
    }
    
    // Datos del cliente
    const segClienteNombre = document.getElementById('seg_cliente_nombre');
    const telefonoSpan = document.getElementById('seg_cliente_telefono');
    const btnWhatsApp = document.getElementById('btnEnviarWhatsApp');
    
    if (segClienteNombre) segClienteNombre.textContent = data.cliente_nombre;
    
    if (data.cliente_telefono) {
        let telefonoLimpio = data.cliente_telefono.replace(/[^0-9]/g, '');
        if (telefonoLimpio.startsWith('52')) {
            telefonoLimpio = telefonoLimpio.substring(2);
        }
        if (!telefonoLimpio.startsWith('52')) {
            telefonoLimpio = '52' + telefonoLimpio;
        }
        
        window.telefonoClienteActual = telefonoLimpio;
        if (telefonoSpan) telefonoSpan.textContent = data.cliente_telefono;
        if (btnWhatsApp) btnWhatsApp.style.display = 'block';
    } else {
        if (telefonoSpan) telefonoSpan.textContent = 'No registrado';
        if (btnWhatsApp) btnWhatsApp.style.display = 'none';
    }
    
    // Hora de inicio
    const segHoraInicio = document.getElementById('seg_hora_inicio');
    if (segHoraInicio) {
        const ahora = new Date();
        const fechaFormateada = ahora.toLocaleDateString('es-MX', {
            year: 'numeric',
            month: '2-digit',
            day: '2-digit'
        });
        const horaFormateada = ahora.toLocaleTimeString('es-MX', {
            hour: '2-digit',
            minute: '2-digit'
        });
        segHoraInicio.value = `${fechaFormateada} ${horaFormateada}`;
    }
    
    // Limpiar campos
    const inputsToClear = [
        'seg_hora_fin', 'seg_mensaje_cliente', 'seg_motivo_no_finalizacion',
        'seg_conversacion', 'seg_queja', 'seg_sugerencia'
    ];
    
    inputsToClear.forEach(id => {
        const element = document.getElementById(id);
        if (element) element.value = '';
    });
};