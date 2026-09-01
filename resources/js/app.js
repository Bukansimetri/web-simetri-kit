import Alpine from 'alpinejs';
import calculatorComponent from './calculator';

window.Alpine = Alpine;

Alpine.data('calculatorComponent', calculatorComponent);

Alpine.start();
