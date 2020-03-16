window._ = require('lodash');
window.Popper = require('popper.js').default;

/**
 * We'll load jQuery and the Bootstrap jQuery plugin which provides support
 * for JavaScript based Bootstrap features such as modals and tabs. This
 * code may be modified to fit the specific needs of your application.
 * Will load also other files that required jquery
 */


try {
    window.$ = window.jQuery = require('jquery');

    require('jquery-ui-bundle');
    require('bootstrap');
    require('jquery.alphanum');
    $.fn.slider = null;
    require('bootstrap-slider/dist/bootstrap-slider.js');

} catch (e) {
    console.error(e);
}

/**
 * Next we will register the CSRF Token as a common header with Axios so that
 * all outgoing HTTP requests automatically have it attached. This is just
 * a simple convenience so we don't have to attach every token manually.
 */
let token = window.csrfToken = document.head.querySelector('meta[name="csrf-token"]');
if (token) {
    /**
     * Attach csrf token to all ajax request
     */
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': token.content,
            'X-Requested-With': 'XMLHttpRequest',
        }
    });
} else {
    console.error('CSRF token not found: https://laravel.com/docs/csrf#csrf-x-csrf-token');
}

require('jquery-countdown');
require('password-strength-meter');
require('@chenfengyuan/datepicker/dist/datepicker.js');
require('croppie');

window.Tabulator = require('tabulator-tables');
window.Clipboard = require('clipboard');
window.swal = require('sweetalert2');
window.iziToast = require('izitoast');
window.Visibility = require('visibilityjs');

require('./custom-plugins.js');

/**
 * Echo exposes an expressive API for subscribing to channels and listening
 * for events that are broadcast by Laravel. Echo and event broadcasting
 * allows your team to easily build robust real-time web applications.
 */
let port = document.head.querySelector('meta[name="nodeport"]');
import Echo from 'laravel-echo'

window.io = require('socket.io-client');
var hostname = window.location.hostname;

if (typeof io !== 'undefined') {
    window.Echo = new Echo({
        namespace: 'Buzzex\\Events',
        broadcaster: 'socket.io',
        host: (hostname == 'buzzex.local') ? hostname+':'+port.content : hostname
    });
}


import Binance from 'binance-api-node';
window.BinanceClient = Binance();

window.Templates = {
    generateEmptyTable : function(selector, columns){
        return new Tabulator(selector, Object.assign({}, columns));
    },
    noDataAvailable: function() {
        return "<span class='fa fa-envelope-open-o text-secondary' style='font-size: 3rem;'></span><span class='text-secondary'>No data available.<br> <small style='font-size:.9rem;'>Try to reload this page.</small></span>";
    },
    loadingRecordBook: function() {
        return "<span class='fa fa-spin fa-spinner text-secondary' style='font-size: 2.5rem;'></span><span class='text-secondary'>Loading Order Book.<br> <small style='font-size:.9rem;'>Please wait...</small></span>";
    },
    loading: function() {
        return "<div style='display:inline-block; border:4px solid #333; border-radius:10px; background:#fff; font-weight:bold; font-size:16px; color:#000; padding:10px 20px;'>Custom Loading Data</div>";
    },
    popupCenter: function(url, title, w, h) {
        // Fixes dual-screen position Most browsers Firefox
        var dualScreenLeft = window.screenLeft != undefined ? window.screenLeft : window.screenX;
        var dualScreenTop = window.screenTop != undefined ? window.screenTop : window.screenY;

        var width = window.innerWidth ? window.innerWidth : document.documentElement.clientWidth ? document.documentElement.clientWidth : screen.width;
        var height = window.innerHeight ? window.innerHeight : document.documentElement.clientHeight ? document.documentElement.clientHeight : screen.height;

        var left = ((width / 2) - (w / 2)) + dualScreenLeft;
        var top = ((height / 2) - (h / 2)) + dualScreenTop;
        var newWindow = window.open(url, title, 'scrollbars=yes, width=' + w + ', height=' + h + ', top=' + top + ', left=' + left);

        // Puts focus on the newWindow
        if (window.focus) {
            newWindow.focus();
        }
    },
    getXHRMessage: function(xhr) {
        return $.parseJSON(xhr.responseText).message || "";
    },
    getXHRErrors: function(xhr) {
        var display = "";
        var errors = $.parseJSON(xhr.responseText).errors;
        if (typeof errors !== 'undefined') {
            // display
            $.each(errors, function(item) {
                display += errors[item][0] + "<br>";
            })
        }
        return display;
    }
}

