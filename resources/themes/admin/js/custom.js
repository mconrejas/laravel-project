(function($) {
    /**
     * Global Templates 
     * @Author : Kirby Capangpangan
     * 
     */
    window.Templates = {

            noDataAvailable: function() {
                return "<span class='fa fa-envelope-open-o text-secondary' style='font-size: 3.5rem;'></span><span class='font-13 text-secondary'>No data available.<br> Try to reload this page.</span>";
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
               return  $.parseJSON(xhr.responseText).message  || "";
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


window.confirmDelete = function(el) {
    var form = $(el).parents('form');
    confirmation("Confirm delete ?", function() {
        form.submit();
    });
}

window.confirmAction = function(el, text, data) {
    var href = $(el).data('href');
    confirmation(text, function() {
        $.post(href, Object.assign({}, data))
            .done(function(data) {
                toast({
                        title: data.flash_message,
                        type: 'success'
                    })
                    .then(function() {
                        window.location.reload()
                    })
            })
            .fail(function(xhr) {
                alert({
                        title: window.Templates.getXHRMessage(xhr),
                        html: window.Templates.getXHRErrors(xhr),
                        type: 'error'
                    })
                    .then(function() {
                        window.location.reload()
                    })
            })
    });
};

window.toast = swal.mixin({
    toast: true,
    position: 'top-end',
    showConfirmButton: false,
    timer: 3000
});

window.alert = swal.mixin({
    confirmButtonClass: 'btn btn-buzzex btn-md px-5 rounded-0',
    buttonsStyling: false
});

const confirmTemplate = swal.mixin({
    confirmButtonClass: 'btn btn-primary rounded-0 px-5',
    cancelButtonClass: 'btn btn-dark rounded-0 mr-2',
    buttonsStyling: false,
    showCancelButton: true,
    confirmButtonText: 'Yes',
    cancelButtonText: 'No, cancel!',
    reverseButtons: true,
    type: 'warning',
    title: 'Confirmation required!',
    allowOutsideClick : false
});

window.confirmation = function(text, ok_callback, cancel_callback) {
    confirmTemplate({
        html: text
    }).then((result) => {
        if (result.value) {
            return typeof ok_callback === 'function' ? ok_callback() : true;
        } else if (result.dismiss === swal.DismissReason.cancel) {
            return typeof cancel_callback === 'function' ? cancel_callback() : false;
        }
    });
};

window.confirm2FA = function(text, callback) {
    confirmTemplate({
        text: text,
        input: 'text',
        inputPlaceholder: text,
        inputClass: 'text-center rounded-0 form-control w-75 ',
        inputAttributes: {
            autocorrect: 'off'
        },
        confirmButtonColor: 'linear-gradient(270deg, #22e6b8, #00c1ce)',
        confirmButtonText: 'Confirm',
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
$(document).ready(function() {
    // Dropdown menu
    $(".sidebar-dropdown > a").click(function() {
        $(".sidebar-submenu").slideUp(200);
        if ($(this).parent().hasClass("active")) {
            $(".sidebar-dropdown").removeClass("active");
            $(this).parent().removeClass("active");
        } else {
            $(".sidebar-dropdown").removeClass("active");
            $(this).next(".sidebar-submenu").slideDown(200);
            $(this).parent().addClass("active");
        }

    });

    // close sidebar 
    $("#close-sidebar").click(function() {
        $(".page-wrapper").removeClass("toggled");
    });

    //show sidebar
    $("#show-sidebar").click(function() {
        $(".page-wrapper").addClass("toggled");
    });

    //switch between themes 
    var themes = "chiller-theme ice-theme cool-theme light-theme";
    $('[data-theme]').click(function() {
        $('[data-theme]').removeClass("selected");
        $(this).addClass("selected");
        $('.page-wrapper').removeClass(themes);
        $('.page-wrapper').addClass($(this).attr('data-theme'));
    });

    // switch between background images
    var bgs = "bg1 bg2 bg3 bg4";
    $('[data-bg]').click(function() {
        $('[data-bg]').removeClass("selected");
        $(this).addClass("selected");
        $('.page-wrapper').removeClass(bgs);
        var bg = $(this).attr('data-bg');
        var route = $(this).attr('data-url');
        $('.page-wrapper').addClass(bg);

        $.post(route, {
                setting_key: 'admin_theme',
                setting_value: bg
            })
            .done(function() {
                toast({
                    type: 'success',
                    title: 'Sidebar theme updated!'
                });
            })
            .fail(function(e) {
                console.log(e)
            })
    });

    // toggle background image
    $("#toggle-bg").change(function(e) {
        e.preventDefault();
        $('.page-wrapper').toggleClass("sidebar-bg");
    });

    //custom scroll bar is only used on desktop
    if (!/Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent)) {
        $(".sidebar-content").mCustomScrollbar({
            axis: "y",
            autoHideScrollbar: true,
            scrollInertia: 300,
            mouseWheel:{ 
                enable: true,
                scrollAmount: 100
            }
        });
        $(".sidebar-content").addClass("desktop");

    }
    /**
     * bind numeric to element
     */
    $(".numeric").numeric({
        allowMinus: false,
        allowThouSep: false,
        maxDecimalPlaces: 10,
        min: 0.00000001,
        max: 999999999
    });

    /**
     * display bootstrap tooltip
     */
    $('body').tooltip({
        selector: '[data-toggle=tooltip], [rel=tooltip]',
        placement : 'left'
    });

    /**
     * check the checkbox that correspond this switcher
     */
    $(document).on('click', 'input[data-toggle="switch"]', function() {
        // check if switch button is checked
        if ($(this).attr("checked") == 'checked') {
            $(this).removeAttr('checked');
        } else {
            $(this).attr("checked", "checked");
        }
    });
})