import Alpine from 'alpinejs';
import calculatorComponent from './calculator';
import initScrollFx from './scroll-fx';

window.Alpine = Alpine;

Alpine.data('calculatorComponent', calculatorComponent);

Alpine.start();

document.addEventListener('DOMContentLoaded', initScrollFx);
