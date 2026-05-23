// Importar CSS
import 'bootstrap/dist/css/bootstrap.min.css';
import 'bootstrap-icons/font/bootstrap-icons.css';

// Cargar Bootstrap dinámicamente
async function cargarBootstrap() {
    try {
        // Intentar importar el bundle
        await import('bootstrap/dist/js/bootstrap.bundle.min.js');
        
        // Verificar si ya está en window
        if (typeof window.bootstrap !== 'undefined') {
            console.log('Bootstrap cargado correctamente');
            return;
        }
        
        // Si no, importar manualmente
        const bootstrap = await import('bootstrap');
        window.bootstrap = bootstrap.default || bootstrap;
        console.log('Bootstrap cargado manualmente');
    } catch (error) {
        console.error('Error cargando Bootstrap:', error);
    }
}

// Ejecutar carga
cargarBootstrap();

document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM listo - Bootstrap:', typeof window.bootstrap !== 'undefined');
});