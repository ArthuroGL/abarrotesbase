import './bootstrap';

import { initializeSidebar } from './modules/sidebar';
import { initializeMoneyInputs } from './modules/money-inputs';

document.addEventListener('DOMContentLoaded', () => {
    initializeSidebar();
    initializeMoneyInputs();
});
