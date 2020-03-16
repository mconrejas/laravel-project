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
    require("jquery-mousewheel");

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
/**
 * load custom jquery plugins
 */
require('jquery.alphanum');
require('@chenfengyuan/datepicker/dist/datepicker.js');
require('malihu-custom-scrollbar-plugin');
require('croppie');
require('chart.js');
require('select2/dist/js/select2.js');
require('../../../../public/vendor/multiselect/js/multiselect.js');

window.Tabulator = require('tabulator-tables');
window.Clipboard = require('clipboard');
window.swal = require('sweetalert2');
    

require('./custom.js');

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