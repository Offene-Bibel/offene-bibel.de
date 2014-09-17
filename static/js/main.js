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
'bootstrap_setup',
'login_popover',
'replacement',
'chapter_navigator'
], function ($) {});

