// Importar CSS
import './bootstrap';
import * as bootstrap from 'bootstrap';
import 'bootstrap-icons/font/bootstrap-icons.css';

// Importar tom-select
import 'tom-select/dist/css/tom-select.default.min.css';
import TomSelect from 'tom-select';
import Chart from 'chart.js/auto';

// Exponer globalmente
window.bootstrap = bootstrap;
window.TomSelect = TomSelect;
window.Chart = Chart;