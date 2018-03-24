require ([
    'event_utils'
], function (
    eventUtils
) {
    eventUtils.onReady( function() {
        var navBook = document.getElementById( 'ofbi-nav-book' );
        navBook && navBook.addEventListener( 'change', function () {
            var book = navBook.value;
            window.location.href = '/wiki/' + book;
        });

        var navChapter = document.getElementById( 'ofbi-nav-chapter' );
        navChapter && navChapter.addEventListener( 'change', function () {
            var chapter = navChapter.value;
            window.location.href = '/wiki/' + chapter;
        });
    });
});

