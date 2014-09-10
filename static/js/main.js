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
'fill_resize',
'replacement'
], function ($) {});

