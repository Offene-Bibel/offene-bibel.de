require ([
    'event_utils',
//     'jquery',
//     'bootstrap'
], function (
    eventUtils/*,
    jq*/
) {
    eventUtils.onReady( function() {
        // Checking for login: http://www.mediawiki.org/wiki/API:Meta
        // If logged in: {"query":{"userinfo":{"id":12,"name":"Francis"}}}
        // If logged out: {"query":{"userinfo":{"id":0,"name":"127.0.0.1","anon":""}}}
        
        var toggle = document.getElementById( 'ofbi-navbar-toggle' );
        toggle.addEventListener( 'click', function() {
            var parentelem = toggle.parentElement.parentElement;
            parentelem.setAttribute( 'data-toggled', parentelem.getAttribute('data-toggled', 'false') !== 'true' );
        });
        toggle.parentElement.className = 'ofbi-dropdown ofbi-dropdown-right hidden-sm hidden-md hidden-lg';
        toggle.setAttribute( 'tabindex', 0 );
        
        [].forEach.call( document.querySelectorAll( '.ofbi-dropdown' ), function (elem) {
            var timeout = false;
            
            elem.addEventListener('focusin', function() {
                if (timeout && typeof timeout === 'number') {
                    clearTimeout(timeout);
                    timeout = false;
                }
                elem.setAttribute( 'data-hasfocus', 'true' );
            });

            elem.addEventListener('focusout', function( event ) {
                timeout = setTimeout( function() {
                    elem.removeAttribute( 'data-hasfocus' );
                    timeout = false;
                }, 200);
            });
            
            if (elem.getAttribute( 'data-hidden-xs', '' ) ) {
                elem.className += ' hidden-xs';
            }
        });
        
        var request = new XMLHttpRequest();
        request.open('GET', '/wiki/api.php?action=query&format=json&meta=userinfo&uiprop=hasmsg', true);
        request.timeout = 5000;
        request.addEventListener('load', function() {
            if (request.status >= 200 && request.status < 400) {
                var data = JSON.parse(request.responseText);
                var loggedIn = ! data.query.userinfo.hasOwnProperty('anon');
                if(loggedIn) {
                    var username = data.query.userinfo.name;
                    document.getElementById('ofbi-auth-logged-in-name').textContent = username;

                    replaceHTML( 'data-logged-in' );

//                     document.getElementById('ofbi-auth-userpage').setAttribute('href', '/wiki/Benutzer:' + username);
//                     document.getElementById('ofbi-auth-user-discussion').setAttribute('href', '/wiki/Benutzer_Diskussion:' + username);
//                     document.getElementById('ofbi-auth-user-contributions').setAttribute('href', '/wiki/Spezial:Beiträge/' + username);
//                             <li data-logged-out=""><a id="ofbi-auth-userpage" role="menuitem" tabindex="-1" href="#">Eigene Benutzerseite</a></li>
//                             <li data-logged-out=""><a id="ofbi-auth-user-discussion" role="menuitem" tabindex="-1" href="#">Eigene Diskussion</a></li>
//                             <li data-logged-out=""><a id="ofbi-auth-user-contributions" role="menuitem" tabindex="-1" href="#">Eigene Beiträge</a></li>

                    // Install logout trigger.
                    document.getElementById('ofbi-auth-logout').addEventListener('click', function (event) {
                        var request = new XMLHttpRequest();
                        request.open('GET', '/wiki/api.php?action=logout', false);
                        request.addEventListener('load', function() {
                            if (request.status >= 200 && request.status < 400) {
                                // true = Reload from server and not from browser cache.
                                location.reload(true);
                            }
                        });
                        request.send();
                        event.preventDefault();
                    });
                }
                else {
                    replaceHTML( 'data-logged-out' );

                    document.getElementById('ofbi-auth-login').addEventListener('submit', function( event ) {
                        toggleLoginErrorHighlight(false);
                        wiki_auth(document.getElementById('ofbi-login-name').value, document.getElementById('ofbi-login-password').value, '#');
                        event.preventDefault();
                    });
                }
            }
        });
        request.send();
    });
    
    function replaceHTML ( attributeName ) {
        var parentelem = document.getElementById( 'ofbi-menu-login' );
        [].forEach.call( parentelem.querySelectorAll( '[' + attributeName + ']'), function (elem) {
            var html = elem.getAttribute( attributeName );
            if (html) {
                elem.innerHTML = html;
            } else {
                elem.parentElement.removeChild( elem );
            }
        });
    }
    
    function toggleLoginErrorHighlight(on) {
        if(on) {
            document.getElementById('ofbi-login-error').classList.remove('hidden');
        } else {
            document.getElementById('ofbi-login-error').classList.add('hidden');
        }
    }

    function wiki_auth(login, pass, ref) {
        var req1 = new XMLHttpRequest();

        req1.open('POST', '/wiki/api.php?action=login&lgname=' + encodeURIComponent( login ) + 
                '&lgpassword=' + encodeURIComponent( pass ) + '&format=json');
        req1.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded; charset=UTF-8');
        req1.addEventListener('load', function() {
            if (req1.status >= 200 && req1.status < 400) {
                var data = JSON.parse(req1.responseText);
                if (data.login.result == 'NeedToken') {
                    var req2 = new XMLHttpRequest();
                    req2.open('POST', '/wiki/api.php?action=login&lgname=' + encodeURIComponent( login ) + 
                        '&lgpassword=' + encodeURIComponent( pass ) + '&lgtoken=' + encodeURIComponent( data.login.token ) + '&format=json');
                    req2.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded; charset=UTF-8');
                    req2.addEventListener('load', function() {
                        if (req2.status >= 200 && req2.status < 400) {
                            var data = JSON.parse(req2.responseText);
                            if (! data.error) {
                                if (data.login.result == "Success") { 
                                    location.reload(true);
                                } else {
                                    document.getElementById('ofbi-login-password').textContent = '';
                                    toggleLoginErrorHighlight(true);
                                }
                            } else {
                                document.getElementById('ofbi-login-error').textContent = 'Error 1: ' + data.error;
                                toggleLoginErrorHighlight(true);
                            }
                        }
                    });
                    req2.send();
                } else {
                    document.getElementById('ofbi-login-error').textContent = 'Error 2: ' + data.login.result;
                    toggleLoginErrorHighlight(true);
                }
            }
        });
        req1.send();
    }
});

