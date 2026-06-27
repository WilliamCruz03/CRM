// Importar CSS
import 'bootstrap';
import 'bootstrap/dist/css/bootstrap.min.css';
import 'bootstrap-icons/font/bootstrap-icons.css';
// Importar tom-select
import 'tom-select/dist/css/tom-select.default.min.css';
import TomSelect from 'tom-select';
import Chart from 'chart.js/auto';

window.TomSelect = TomSelect;

// Cargar Bootstrap dinámicamente
async function cargarBootstrap() {
    try {
        // Intentar importar el bundle
        await import('bootstrap/dist/js/bootstrap.bundle.min.js');
        
        // Verificar si ya está en window
        if (typeof window.bootstrap !== 'undefined') {
            return;
        }
        
        // Si no, importar manualmente
        const bootstrap = await import('bootstrap');
        window.bootstrap = bootstrap.default || bootstrap;
    } catch (error) {
    }
}

// Ejecutar carga
cargarBootstrap();