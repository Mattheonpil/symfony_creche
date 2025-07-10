import './bootstrap.js';
import './styles/app.css';
import { setupEntityListSorting } from './entityList.js';
import { setupRecoveryCollection } from './recoveryCollection.js';

document.addEventListener('DOMContentLoaded', () => {
    setupEntityListSorting();
    setupRecoveryCollection();
});





