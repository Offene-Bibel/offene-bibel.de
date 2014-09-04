//require(['js/vendor/modernizr-2.6.2-respond-1.1.0.min.js'], function(modernizr) { }
//require(['js/vendor/jquery-1.10.1.min.js'], function(jquery) { }
//require(['js/vendor/bootstrap.min.js'], function(bootstrap) { }
//requirejs(['js/vendor/modernizr-2.6.2-respond-1.1.0.min.js', '', '', 'login_popover'], function(modernizr, $, bs, login_popover) {
//});

requirejs.config({
    paths: {
        "jquery": "lib/jquery",
        "bootstrap": "lib/bootstrap"
    },
    shim: {
        "bootstrap": {
            deps: ["jquery"],
            exports: 'jQuery.fn.modal'
        }
    }
});

require([
'login_popover',
'fill_resize'
], function ($) {});
