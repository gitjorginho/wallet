

import Alpine from 'alpinejs';
import {initCurrencyMask} from './wallet';

window.Alpine = Alpine;
Alpine.start();

document.addEventListener('DOMContentLoaded', () => {
    initCurrencyMask();
});
    