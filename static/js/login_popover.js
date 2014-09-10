require  ([ "jquery", "bootstrap"],
function  (  $                   ) {
    $(function(){
        var host = (window.location.protocol).concat("//").concat(window.location.hostname);

        // Enable popover for login button.
        $('#ofbi-auth-login-button').popover({
            html: true,
            content: function() {
                return $('#ofbi-auth-login-content').html();
            }
        });

        // Checking for login: http://www.mediawiki.org/wiki/API:Meta
        // If logged in: {"query":{"userinfo":{"id":12,"name":"Francis"}}}
        // If logged out: {"query":{"userinfo":{"id":0,"name":"127.0.0.1","anon":""}}}
        $.ajax({
            url: host + "/wiki/api.php?action=query&format=json&meta=userinfo&uiprop=hasmsg",
            dataType: "json",
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
                            url: host + "/wiki/api.php?action=logout",
                            async: false
                        });
                        // true = Reload from server and not from browser cache.
                        location.reload(true);
                    });
                }
                else {
                    $('#ofbi-auth-login-button').show();
                }
            },
            error: function(xhr, status, errorThrown) {
                $('#ofbi-auth-login-button').show();
                $('#ofbi-auth-logged-in-button').hide();
            }
        });
    });
});

