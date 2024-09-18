import './bootstrap';
import '../../node_modules/bootstrap/dist/js/bootstrap.bundle';
window.token = document.querySelector("meta[name='csrf-token']").getAttribute("content");
