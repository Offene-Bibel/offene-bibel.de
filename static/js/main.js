requirejs.config({
    paths: {
        'bootstrap': 'lib/bootstrap',
        'bootstrap-hover-dropdown': 'lib/bootstrap-hover-dropdown'
    }
});

require([
'bootstrap_setup',
'bootstrap-hover-dropdown',
'login_popover',
'replacement',
'chapter_navigator'
], function () {});

