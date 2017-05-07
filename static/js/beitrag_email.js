require ([
    'event_utils'
], function (
    eventUtils
) {
    eventUtils.onReady( function() {
        var l = ['e', 'd.l', 'ebi', 'b-en', 'eff', 'o', 'gar', 'tie', 'Bniem'];
        l.splice(6, 0, '@');
        var r = l.join("");
        var e = r.split("").reverse().join("");

        var beitragEmail = document.getElementById('ofbi-beitrag-email');
        if (beitragEmail) {
            beitragEmail.innerHTML = 
                '<a class="external text" href="mailto:' + e + '?Subject=Hier%20meine%20Üb…20gerne%20einstellen%20dürft.%0A%0A%0AViele%20Grüße%0A%0AIch" rel="nofollow">'
              + e
              + '</a>';
        }
    });
});

