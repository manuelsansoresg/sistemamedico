import './bootstrap';
import '@fortawesome/fontawesome-free/css/all.css';

import Alpine from 'alpinejs';

window.Alpine = Alpine;

Alpine.start();

const catalogItemsEl = document.getElementById('catalog-items-json');
if (catalogItemsEl && catalogItemsEl.textContent) {
    try {
        window.catalogItems = JSON.parse(catalogItemsEl.textContent);
    } catch (e) {
        window.catalogItems = [];
    }
}
