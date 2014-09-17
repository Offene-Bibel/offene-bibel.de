require  ([ "jquery" ],
function  (  $       ) {
    $(function(){
        $( '#ofbi-nav-book' ).change(function () {
            var book = $( '#ofbi-nav-book option:selected' ).val();
            window.location.href = '/wiki/' + book;
        });

        $( '#ofbi-nav-chapter' ).change(function () {
            var chapter = $( '#ofbi-nav-chapter option:selected' ).val();
            window.location.href = '/wiki/' + chapter;
        });
    });
});

