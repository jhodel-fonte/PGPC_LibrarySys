import { Html5Qrcode } from 'html5-qrcode';
window.Html5Qrcode = Html5Qrcode;

import tallstackui_chart from '../../vendor/tallstackui/tallstackui/src/Components/Chart/alpine.js';

// Expose globally so Alpine x-data="tallstackui_chart(...)" evaluates without race conditions
window.tallstackui_chart = tallstackui_chart;

const registerTallStackChart = () => {
    if (window.Alpine) {
        window.Alpine.data('tallstackui_chart', tallstackui_chart);
    }
};

registerTallStackChart();
document.addEventListener('alpine:init', registerTallStackChart);
document.addEventListener('livewire:navigated', registerTallStackChart);
