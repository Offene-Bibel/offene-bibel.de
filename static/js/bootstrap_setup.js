require ([
    'event_utils',
    'jquery',
    'bootstrap'
], function (
    eventUtils,
    jq
) {
    eventUtils.onReady( function() {
        jq('.dropdown-toggle').dropdown();
/*        var elems = document.querySelectorAll('.dropdown-toggle');
        Array.prototype.forEach.call(elems, function( elem ) {
            elem.dropdown();
        });
*/
    });
});

