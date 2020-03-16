(function($) {

    /**
     * Global setup for button processing, active and reset
     */
    $.fn.btnProcessing = function(label) {
        if (typeof label != 'undefined' && label == '.') {
            label = "";
        } else {
            var label = label || 'Processing...';
        }
        var button = this;
        var btnhtml = this.html();
        button.addClass('processing').addClass('disabled');
        button.data('old', btnhtml);
        button.css({
            'pointer-events': 'none',
            'cursor': 'not-allowed'
        });
        button.html('<span class="fa fa-spin fa-spinner"></span> ' + label);
        return button;
    };
    $.fn.btnReset = function() {
        var button = this;
        var btnhtml = this.data('old');
        button.removeClass('processing').removeClass('disabled');
        button.data('old', '');
        button.css({
            'pointer-events': 'all',
            'cursor': 'pointer'
        });
        if (typeof btnhtml !== 'undefined' || btnhtml != '') {
            button.html(btnhtml);
        }
        return button;
    };
    $.fn.btnActive = function(label) {
        var button = this;
        button.addClass('buzzex-active');
        if (typeof label != 'undefined') {
            button.html(label)
        }
        return button;
    };
    $.fn.btnInActive = function(label) {
        var button = this;
        button.removeClass('buzzex-active');
        if (typeof label != 'undefined') {
            button.html(label)
        }
        return button;
    };

}(jQuery));

window.toast = swal.mixin({
    toast: true,
    position: 'top-end',
    showConfirmButton: false,
    timer: 8000
});

window.notifications = function(options){
    var setup = Object.assign({
        image : '/img/58x58.png',
        message: 'Hello. welcome to Buzzex',
        position: 'topRight',
        timeout: 6000,
        layout: 2,
        close : true,
        transitionIn: 'flipInX',
        transitionOut: 'flipOutX'
    },options);

    iziToast.show(setup);
};

window.alert = swal.mixin({
    confirmButtonClass: 'btn btn-buzzex btn-md px-5 rounded-0',
    buttonsStyling: false
});

window.confirmTemplate = swal.mixin({
    confirmButtonClass: 'btn btn-buzzex rounded-0 px-5',
    cancelButtonClass: 'btn btn-dark rounded-0 mr-2',
    buttonsStyling: false,
    showCancelButton: true,
    confirmButtonText: 'Yes',
    cancelButtonText: 'No',
    reverseButtons: true,
    type: 'warning',
    title: '<span style="font-weight:normal;">Confirmation required!</span>',
    inputAttributes: {
        autocorrect: 'off'
    },
    allowOutsideClick : false
});

window.confirmation = function(text, ok_callback, cancel_callback) {
    confirmTemplate({
        html: text
    }).then((result) => {
        if (result.value) {
            return typeof ok_callback === 'function' ? ok_callback() : true;
        } else if (typeof result.dismiss !== 'undefined') {
            return typeof cancel_callback === 'function' ? cancel_callback() : false;
        }
    });
};

window.confirm2FA = function(text, callback) {
    confirmTemplate({
        text: text,
        input: 'text',
        inputPlaceholder: 'Enter 2FA code',
        inputClass: 'text-center rounded-0 form-control w-75 ',
        confirmButtonColor: 'linear-gradient(270deg, #22e6b8, #00c1ce)',
        confirmButtonText: 'Confirm',
        cancelButtonText: 'Cancel',
        inputValidator: function(value) {
            return !value && 'Code cannot be empty'
        }
    }).then((result) => {
        if (result.value) {
            $.post(window.location.origin + '/en/verifycode', {
                'code': result.value
            }).done(function(response) {
                return typeof callback === 'function' ? callback(response) : true;
            }).fail(function(xhr, status, error) {
                alert({
                    title: window.Templates.getXHRMessage(xhr),
                    html: window.Templates.getXHRErrors(xhr),
                    type: 'error'
                });
            })
        }
    })
};

window.requestEmailCode = function() {
    $.post(window.location.origin + '/en/email/request-code', {
        type : 'api-settings'
    }).done(function(response) {
        return response;
    }).fail(function(xhr, status, error) {
        return false;    
    })
};
