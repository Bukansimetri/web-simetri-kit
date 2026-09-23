import Alpine from 'alpinejs';
import calculatorComponent from './calculator';
import pltsCalculatorComponent from './calculator-plts';
import heroSlider from './hero-slider';
import cookieConsent from './cookie-consent';
import initScrollFx from './scroll-fx';

window.Alpine = Alpine;

Alpine.data('calculatorComponent', calculatorComponent);
Alpine.data('pltsCalculatorComponent', pltsCalculatorComponent);
Alpine.data('heroSlider', heroSlider);
Alpine.data('cookieConsent', cookieConsent);

Alpine.start();

document.addEventListener('DOMContentLoaded', initScrollFx);
