require ([ 'jquery', 'bootstrap' ],
function (  $                    ) {
    $(function(){
        // Enable popover for login button.
        $('#ofbi-auth-login-button')
        .popover()
        .on('shown.bs.popover', function () {
            // Since the content of the popover is only generated on actually triggering the popover,
            // we have to install the hooks after popover activation.

            // Install login trigger.
            $('#ofbi-auth-login').submit(function( event ) {
                wiki_auth($('#ofbi-login-name').val(), $('#ofbi-login-password').val(), '#');
                event.preventDefault();
            });
        });

        // Checking for login: http://www.mediawiki.org/wiki/API:Meta
        // If logged in: {"query":{"userinfo":{"id":12,"name":"Francis"}}}
        // If logged out: {"query":{"userinfo":{"id":0,"name":"127.0.0.1","anon":""}}}
        $.ajax({
            url: '/wiki/api.php?action=query&format=json&meta=userinfo&uiprop=hasmsg',
            dataType: 'json',
            timeout: 5000,
            success: function(data) {
                var loggedIn = ! data.query.userinfo.hasOwnProperty('anon');
                if(loggedIn) {
                    var username = data.query.userinfo.name;
                    $('#ofbi-auth-logged-in-name').text(username);
                    $('#ofbi-auth-logged-in-button').show();

                    $('#ofbi-auth-userpage').attr('href', '/wiki/index.php5?title=Benutzer:' + username);

                    // Install logout trigger.
                    $('#ofbi-auth-logout').click(function() {
                        $.ajax({
                            url: "/wiki/api.php?action=logout",
                            async: false
                        });
                        // true = Reload from server and not from browser cache.
                        location.reload(true);
                    });
                }
                else {
                    onLogin();
                }
            },
            error: function(xhr, status, errorThrown) {
                onLogin();
                $('#ofbi-auth-logged-in-button').hide();
            }
        });
    });

    function onLogin() {
        $('#ofbi-auth-login-button').show();
    }

    function toggleLoginErrorHighlight(on) {
        if(on) {
            $('#ofbi-login-error').show();
        } else {
            $('#ofbi-login-error').hide();
        }
    }

    function wiki_auth(login, pass, ref){
        $.post('/wiki/api.php?action=login&lgname=' + login + 
                '&lgpassword=' + pass + '&format=json',
        function(data) {
            if(data.login.result == 'NeedToken') {
                $.post('/wiki/api.php?action=login&lgname=' + login + 
                    '&lgpassword=' + pass + '&lgtoken='+data.login.token+'&format=json', 
                function(data) {
                    if(!data.error){
                        if (data.login.result == "Success") { 
                            location.reload(true);
                        } else {
                            $('#ofbi-login-password').text('');
                            $('#ofbi-login-error').text('Wrong username or password');
                            toggleLoginErrorHighlight(true);
                        }
                    } else {
                        $('#ofbi-login-error').text('Error 1: ' + data.error);
                        toggleLoginErrorHighlight(true);
                    }
                });
            } else {
                $('#ofbi-login-error').text('Error 2: ' + data.login.result)
                toggleLoginErrorHighlight(true);
            }
        });
    }
});

