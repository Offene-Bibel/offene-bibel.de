require ([
    'event_utils'
], function (
    eventUtils
) {
    eventUtils.onReady( function() {
        var navBook = document.getElementById( 'ofbi-nav-book' );
        navBook && navBook.addEventListener( 'change', function () {
            var book = document.getElementById( 'ofbi-nav-book' ).value;
            window.location.href = '/wiki/' + book;
        });

        var navChapter = document.getElementById( 'ofbi-nav-chapter' );
        navChapter && navChapter.addEventListener( 'change', function () {
            var chapter = document.getElementById( 'ofbi-nav-chapter' ).value;
            window.location.href = '/wiki/' + chapter;
        });
    });
});

