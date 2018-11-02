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
        
        l = ['e', 'd.l', 'ebi', 'b-en', 'eff', 'o', 'mae', 'tk', 'inhcet'];
        l.splice(6, 0, '@');
        r = l.join("");
        e = r.split("").reverse().join("");

        var technikEmail = document.getElementById('ofbi-technik-email');
        if (technikEmail) {
            technikEmail.innerHTML =
                '<a class="external text" href="mailto:' + e + '" rel="nofollow">'
              + e
              + '</a>';
        }
    });
});

