requirejs.config({
    paths: {
        'bootstrap': 'lib/bootstrap'
    }
});

require([
'bootstrap_setup',
'login_popover',
'replacement',
'chapter_navigator'
], function () {});

