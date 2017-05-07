require ([
    'event_utils'
], function (
    eventUtils
) {
    eventUtils.onReady( function() {
        var elems = document.querySelectorAll( '.ofbi-nojs' );
        Array.prototype.forEach.call(elems, function(elem) {
            elem.style.display = 'none';
        });
    });
});

