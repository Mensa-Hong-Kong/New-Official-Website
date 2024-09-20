import './bootstrap';
import bootstrap from '../../node_modules/bootstrap/js/index.umd';
window.bootstrap = bootstrap;
window.token = document.querySelector("meta[name='csrf-token']").getAttribute("content");
