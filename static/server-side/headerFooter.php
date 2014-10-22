<?php
function getOfBiHeader() {
    return <<<HEADER_END
<div id="wrap">
    <div class="navbar navbar-default navbar-fixed-top">
        <div class="container">
            <div class="navbar-header">
                <button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".main-navbar-collapse">
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                </button>
                <a id="navbar-large-logo" class="navbar-brand" href="/startseite">
                    <img id="header-image" src="/static/img/header.png"/>
                </a>
            </div>
            <div class="navbar-collapse collapse main-navbar-collapse">
                <ul class="nav navbar-nav">
<li class="dropdown">
  <a href="" class="dropdown-toggle" data-toggle="dropdown">Lesen <span class="caret"></span></a>
  <div class="dropdown-menu multi-column">
        <!-- there would normally be a row class on this div. But that added broken margin on the left and right. Leaving it of somehow doesn't break much... -->
                <ul class="dropdown-menu col-md-6">
                        <li>
                                <b>Bibel</b>
                        </li>
                <li>
                  <a href="/wiki/Übersetzungen">Online</a>
                </li>
                <li>
                  <a href="/wiki/Offene_Bibel_Module_für_Bibelprogramme">Download</a>
                </li>
        </ul>
        <ul class="dropdown-menu col-md-6">
                        <li>
                                <b>Nebenprojekte</b>
                        </li>
                <li>
                  <a href="/wiki/Bibellexikon">Bibellexikon</a>
                </li>
                <li>
                  <a href="/wiki/Grammatik">Grammatik</a>
                </li>
                <li>
                  <a href="/wiki/Sekundärliteratur_Hauptseite">Sekundärliteratur</a>
                </li>
        </ul>
       </div>
</li>
<li class="dropdown">
        <a href="#about" class="dropdown-toggle" data-toggle="dropdown">Über uns <span class="caret"></span></a>
        <ul class="dropdown-menu">
                <li><a href="/kurzinfo">Kurzinfo</a></li>
                <li><a href="/wiki/über_uns">Unsere Ziele</a></li>
                <li><a href="/wiki/Übersetzungskriterien">Eigenschaften der Übersetzungen</a></li>
                <li><a href="/wiki/Leichte_Sprache">Bibel in Leichter Sprache</a></li>
                <li><a href="/verein">Verein</a></li>
        </ul>
</li>
<li class="dropdown">
        <a href="#about" class="dropdown-toggle" data-toggle="dropdown">Aktuelles <span class="caret"></span></a>
        <ul class="dropdown-menu">
                <li><a href="/blog">Blog</a></li>
                <li><a href="/neuigkeiten">Letzte Aktivitäten</a></li>
        </ul>
</li>
<li class="dropdown">
  <a href="" class="dropdown-toggle" data-toggle="dropdown">Mitmachen/Feedback <span class="caret"></span></a>
  <div class="dropdown-menu multi-column">
        <!-- there would normally be a row class on this div. But that added broken margin on the left and right. Leaving it of somehow doesn't break much... -->
                <ul class="dropdown-menu col-md-6">
                <li>
                  <b>Mitmachen</b>
                </li>
                <li>
                  <a href="/wiki/Mithelfen">Wie kann ich helfen?</a>
                </li>
                <li>
                  <a href="/wiki/Autorenportal">Autorenportal</a>
                </li>
                <li>
                  <a href="/spenden">Spenden</a>
                </li>
                <li>
                  <a href="/wiki/Werben">Weitersagen</a>
                </li>
        </ul>
        <ul class="dropdown-menu col-md-6">
                <li>
                  <b>Feedback</b>
                </li>
                <li>
                  <a href="/forum">Forum</a>
                </li>
                <li>
                  <a href="/chat">Chat</a>
                </li>
                <li>
                  <a href="/mailingliste">Mailingliste</a>
                </li>
        </ul>
       </div>
</li>
                  </ul>
                  <ul class="nav navbar-nav navbar-right">
                      <li id="ofbi-auth-login-button" class="dropdown" data-container="body" data-toggle="popover" data-placement="bottom" style="display: none" data-html="true" data-content="
<form role='form' accept-charset='UTF-8' id='ofbi-auth-login'>
    <div class='form-group has-error'>
        <label for='ofbi-login-name'>Benutzername:<span class='form-required' title='Diese Angabe wird benötigt.'></span></label>
        <input id='ofbi-login-name' class='form-control input-sm' type='text' name='name' maxlength='60' size='15' placeholder='Benutzername'/>
    </div>
    <div class='form-group has-error'>
        <label for='ofbi-login-password'>Passwort:<span class='form-required' title='Diese Angabe wird benötigt.'></span></label>
        <input id='ofbi-login-password' class='form-control input-sm' type='password' name='pass' maxlength='60' size='15' placeholder='Passwort'/>
    </div>
    <div class='has-error' id='ofbi-login-error' type='hidden'>Benutzername oder Passwort falsch.</div>
    <button type='submit' name='op' class='btn btn-default'>Anmelden</button>
    <div class='item-list'>
        <ul>
          <li><a href='/user/register' title='Ein neues Benutzerkonto erstellen.'>Registrieren</a></li>
          <li><a href='/user/password' title='Ein neues Passwort per E-Mail anfordern.'>Neues Passwort anfordern</a></li>
        </ul>
    </div>
</form>
">
                          <a href='#'>Einloggen</a>
                      </li>
                      <li id="ofbi-auth-logged-in-button" class="dropdown" style="display: none">
                          <a class="dropdown-toggle" data-toggle="dropdown" href="#"><span id="ofbi-auth-logged-in-name"></span> <b class="caret"></b></a>
                          <ul class="dropdown-menu" role="menu" aria-labelledby="dLabel">
                              <li role="presentation"><a role="menuitem" tabindex="-1" href="#">Beobachtungsliste</a></li>
                              <li role="presentation"><a id="ofbi-auth-userpage" role="menuitem" tabindex="-1" href="#">Eigene Benutzerseite</a></li>
                              <li role="presentation"><a role="menuitem" tabindex="-1" href="#">Eigene Diskussion</a></li>
                              <li role="presentation"><a role="menuitem" tabindex="-1" href="#">Eigene Beiträge</a></li>
                              <li role="presentation"><a role="menuitem" tabindex="-1" href="#"></a></li>
                              <li role="presentation" class="divider"></li>
                              <li role="presentation"><a role="menuitem" tabindex="-1" href="#">Einstellungen</a></li>
                              <li id="ofbi-auth-logout" role="presentation"><a role="menuitem" tabindex="-1" href="#">Abmelden</a></li>
                        </ul>
                    </li>
                </ul>
            </div><!-- navbar-collapse -->
        </div><!-- container -->
    </div><!-- navbar -->
    <div class="container">
HEADER_END;
}

function getOfBiFooter() {
    return <<<FOOTER_END
        </div><!-- container -->
</div><!-- wrap -->
<div class="container">
<div id="ofbi-replacement-dropdown-content" style="display:none">
    <!-- This container holds the contents injected into all replacement name selector dropdowns. -->
    <div class="ofbi-replacements"><!-- Filled via JS --></div>
    <p><small>Hier steht im Urtext der Gottesname <a href=/wiki/JHWH>JHWH</a>,<br/>dessen genaue Aussprache unbekannt ist und<br/>der im Christentum und Judentum meistens<br/>durch eine Ersatzlesung wiedergegeben wird.</small></p>
</div>
    <footer>
        <hr/>
        <small>
            Der Inhalt ist verfügbar unter der Creative-Commons-Lizenz <a rel="license" href="http://creativecommons.org/licenses/by-sa/3.0/">CC BY-SA 3.0</a> bei Angabe von <a rel="cc:attributionURL" property="cc:attributionName" href="http://offene-bibel.de/">offene-bibel.de</a> als Quelle.
            <br/>
            <a href="http://www.offene-bibel.de/seiten/impressum">Impressum und Disclaimer</a>
        </small>
    </footer>
</div><!-- container -->
FOOTER_END;
}

?>
