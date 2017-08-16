<?php
function getOfBiHeader() {
    return <<<HEADER_END
<div id="wrap">
    <nav class="ofbi-navbar">
        <div class="container">
            <ul class="ofbi-nav">
                <li id="ofbi-navbar-logo-container" class="ofbi-dropdown">
                    <a id="ofbi-navbar-large-logo" class="ofbi-navbar-brand" href="/startseite">
                        <img id="header-image" src="/static/img/header_small.png" class="hidden-sm hidden-xs"/>
                        <img id="header-image-notext" src="/static/img/header_notext_small.png" class="hidden-lg hidden-md"/>
                    </a>
                </li>
                <li class="ofbi-dropdown ofbi-dropdown-right hidden" id="ofbi-navbar-toggle-container">
                    <div id="ofbi-navbar-toggle">
                        Menü
                    </div>
                </li>
                <li class="ofbi-dropdown" data-hidden-xs="1">
                    <div class="ofbi-dropdown-toggle" tabindex="0" data-hover="dropdown">Lesen <span class="caret"></span></div>
                    <div class="ofbi-dropdown-menu hidden multi-column">
                        <!-- there would normally be a row class on this div. But that added broken margin on the left and right. Leaving it of somehow doesn't break much... -->
                        <ul class="ofbi-dropdown-menu col-md-6">
                                <li><b>Bibel</b></li>
                                <li><a href="/wiki/Die_Offene_Bibel_online_lesen">Online lesen</a></li>
                                <li><a href="/wiki/Download:_Die_Offene_Bibel_in_Bibelprogrammen_und_als_PDF">Download</a></li>
                        </ul>
                        <ul class="ofbi-dropdown-menu col-md-6">
                            <li><b>Nebenprojekte</b></li>
                            <li><a href="/wiki/Kategorie:Lexikon">Bibellexikon</a></li>
                            <li><a href="/wiki/Kategorie:Grammatik">Grammatik</a></li>
                            <li><a href="/wiki/Offene_Sekundärliteratur">Sekundärliteratur</a></li>
                        </ul>
                    </div>
                </li>
                <li class="ofbi-dropdown" data-hidden-xs="1">
                    <div class="ofbi-dropdown-toggle" tabindex="0">Über uns <span class="caret"></span></div>
                    <ul class="ofbi-dropdown-menu hidden">
                        <li><a href="/kurzinfo">Kurzinfo</a></li>
                        <li><a href="/wiki/Unsere_Ziele">Unsere Ziele</a></li>
                        <li><a href="/wiki/Übersetzungskriterien">Übersetzungskriterien</a></li>
                        <li><a href="/wiki/Leichte_Sprache">Bibel in Leichter Sprache</a></li>
                        <li><a href="/verein">Verein / Kontakt</a></li>
                    </ul>
                </li>
                <li class="ofbi-dropdown" data-hidden-xs="1">
                    <div class="ofbi-dropdown-toggle" tabindex="0">Aktuelles <span class="caret"></span></div>
                    <ul class="ofbi-dropdown-menu hidden">
                        <li><a href="/news">News</a></li>
                        <li><a href="/blog">Blog</a></li>
                        <li><a href="/neuigkeiten">Letzte Aktivitäten</a></li>
                    </ul>
                </li>
                <li class="ofbi-dropdown" data-hidden-xs="1">
                    <div class="ofbi-dropdown-toggle" tabindex="0">Mitmachen<span class="visible-xs-inline visible-md-inline visible-lg-inline">/Feedback</span> <span class="caret"></span></div>
                    <div class="ofbi-dropdown-menu hidden multi-column">
                        <!-- there would normally be a row class on this div. But that added broken margin on the left and right. Leaving it of somehow doesn't break much... -->
                        <ul class="ofbi-dropdown-menu col-md-6">
                            <li><b>Mitmachen</b></li>
                            <li><a href="/wiki/Wie_kann_ich_helfen%3F">Wie kann ich helfen?</a></li>
                            <li><a href="/wiki/Autorenportal">Autorenportal</a></li>
                            <li><a href="/spenden">Spenden</a></li>
                            <li><a href="/wiki/Weitersagen">Weitersagen</a></li>
                        </ul>
                        <ul class="ofbi-dropdown-menu col-md-6">
                            <li><b>Feedback</b></li>
                            <li><a href="/forum">Forum</a></li>
                            <li><a href="/chat">Chat</a></li>
                            <li><a href="/mailingliste">Mailingliste</a></li>
                        </ul>
                    </div>
                </li>
                <li id="ofbi-menu-login" class="ofbi-dropdown ofbi-dropdown-right" data-hidden-xs="1">
                    <div id="ofbi-auth-logged-in-name" class="ofbi-dropdown-toggle" tabindex="0">Anmelden <span class="caret"></span></div>
                    <ul class="ofbi-dropdown-menu hidden">
                        <li data-logged-in=""><a href='/wiki/Spezial:Anmelden?type=signup' title='Ein neues Benutzerkonto erstellen.'>Neu registrieren</a></li>
                        <li data-logged-out="" data-logged-in="">
                            <a href="/wiki/Spezial:Anmelden">Anmelden</a>
                        </li>
                        <li data-logged-in="" class="divider"></li>
                        <li data-logged-out="
                            <form id='ofbi-auth-login'>
                                <div class='form-group'>
                                    <label class='control-label' for='ofbi-login-name'>Benutzername:</label>
                                    <input id='ofbi-login-name' class='form-control input-sm' type='text' name='name' maxlength='60' size='15' placeholder='Benutzername' required>
                                </div>
                                <div class='form-group'>
                                    <label class='control-label' for='ofbi-login-password'>Passwort:</label>
                                    <input id='ofbi-login-password' class='form-control input-sm' type='password' name='pass' maxlength='60' size='15' placeholder='Passwort' required>
                                </div>
                                <div id='ofbi-login-error' class='form-group has-error hidden'>
                                    <div class='help-block' >Benutzername oder Passwort falsch. Falls du dein Passwort zurückgesetzt hast, benutze bitte <a href='/wiki/Spezial:Anmelden'>dieses Anmeldeformular</a>.</div>
                                </div>
                                <button type='submit' name='op' class='btn btn-default'>Anmelden</button>
                            </form>
                        ">
                            <a id="ofbi-auth-logout" href="/wiki/Spezial:Abmelden">Abmelden</a>
                        </li>
                        <li class="divider"></li>
                        <li data-logged-out="
                            <a href='/wiki/Spezial:Passwort_neu_vergeben' title='Ein neues Passwort per E-Mail anfordern.'>Neues Passwort anfordern</a>
                        ">
                            <a role="menuitem" tabindex="-1" href="/wiki/Spezial:Beobachtungsliste">Beobachtungsliste</a>
                        </li>
                        <li data-logged-out=""><a role="menuitem" tabindex="-1" href="/wiki/Spezial:Einstellungen">Einstellungen</a></li>
                    </ul>
                </li>
                <li class="ofbi-dropdown ofbi-dropdown-right">
                    <div class="ofbi-dropdown-toggle" tabindex="0">Suche <span class="caret"></span></div>
                    <ul class="ofbi-dropdown-menu hidden">
                        <li>
                            <form action="/suche.php" method="get">
                                <div class='form-group'>
                                    <input class='form-control input-sm' id="ofbi-suche" name="suche" maxlength='60' size='15' placeholder='Suchbegriff' required>
                                    <select class='form-control' id="ofbi-suche-in" name="in">
                                        <option value="bibelstelle">Bibelstelle</option>
                                        <option value="volltext">Volltext</option>
                                        <option value="drupal">News, Blogs und Forum</option>
                                    </select>
                                    <button type='submit' name='op' class='btn btn-default'>Suche</button>
                                </div>
                            </form>
                        </li>
                    </ul>
                </li>
            </ul>
        </div><!-- container -->
    </nav><!-- ofbi-navbar -->
    <main><div class="container">
HEADER_END;
}


//                         <li>
//                             <table><tr>
//                                 <th colspan=4>Altes Testament</th>
//                                 <th colspan=3>Neues Testament</th>
//                             </tr><tr>
//                                 <td style="vertical-align:top">
//                                     <ul>
//                                         <li>Genesis (1 Mose)
//                                         <li>Exodus (2 Mose)
//                                         <li>Levitikus (3 Mose)
//                                         <li>Numeri (4 Mose)
//                                         <li>Deutero&shy;nomium (5 Mose)
//                                     </ul>
//                                 </td>
//                                 <td style="vertical-align:top">
//                                     <ul>
//                                         <li>Josua
//                                         <li>Richter
//                                         <li>1-2 Samuel
//                                         <li>1-2 Könige
//                                     </ul>
//                                     <hr>
//                                     <ul>
//                                         <li>Jesaja
//                                         <li>Jeremia
//                                         <li>Ezechiel (Hesekiel)
//                                     </ul>
//                                 </td>
//                                 <td style="vertical-align:top">
//                                     <ul>
//                                         <li>Hosea
//                                         <li>Joel
//                                         <li>Amos
//                                         <li>Obadja
//                                         <li>Jona
//                                         <li>Micha
//                                         <li>Nahum
//                                         <li>Habakuk
//                                         <li>Zefanja
//                                         <li>Haggai
//                                         <li>Sacharja
//                                         <li>Maleachi
//                                     </ul>
//                                 </td>
//                                 <td style="vertical-align:top">
//                                     <ul>
//                                         <li>Psalmen
//                                         <li>Ijob (Hiob)
//                                         <li>Sprichwörter
//                                         <li>Rut
//                                         <li>Hohelied
//                                         <li>Kohelet (Prediger)
//                                     </ul>
//                                     <hr>
//                                     <ul>
//                                         <li>Klagelieder
//                                         <li>Ester
//                                         <li>Daniel
//                                         <li>Esra
//                                         <li>Nehemia
//                                         <li>1-2 Chronik
//                                     </ul>
//                                 </td>
//                                 <td style="vertical-align:top">
//                                     <ul>
//                                         <li>Matthäus
//                                         <li>Markus
//                                         <li>Lukas
//                                         <li>Johannes
//                                     </ul>
//                                     <hr>
//                                     <ul>
//                                         <li>Apostel&shy;geschichte
//                                     </ul>
//                                 </td>
//                                 <td style="vertical-align:top">
//                                     <ul>
//                                         <li>Römer
//                                         <li>1-2 Korinther
//                                         <li>Galater
//                                         <li>Epheser
//                                         <li>Philipper
//                                         <li>Kolosser
//                                         <li>1-2 Thessa&shy;lonicher
//                                         <li>1-2 Timotheus
//                                         <li>Titus
//                                         <li>Philemon
//                                         <li>Hebräer
//                                     </ul>
//                                 </td>
//                                 <td style="vertical-align:top">
//                                     <ul>
//                                         <li>Jakobus
//                                         <li>1-2 Petrus
//                                         <li>1-3 Johannes
//                                         <li>Judas
//                                     </ul>
//                                     <hr>
//                                     <ul>
//                                         <li>Offenbarung
//                                     </ul>
//                                 </td>
//                             </tr><tr>
//                                 <th style="vertical-align:top">Spätschriften</th>
//                                 <td style="vertical-align:top">
//                                     <ul>
//                                         <li>Baruch
//                                     </ul>
//                                 </td>
//                                 <td colspan="2" style="vertical-align:top">
//                                     <ul>
//                                         <li>Psalmen (Ergänzungen)
//                                         <li>Ester (griechische Version)
//                                         <li>Daniel (griechische Version)
//                                         <li>Esra (Ergänzungen)
//                                     </ul>
//                                 </td>
//                                 <td colspan="3" style="vertical-align:top">
//                                     <ul>
//                                         <li>Judit
//                                         <li>Tobit
//                                         <li>1-4 Makkabäer
//                                         <li>Oden (Manasse)
//                                     </ul>
//                                 </td>
//                             </tr></table>
//                         </li>



function getOfBiFooter() {
    return <<<FOOTER_END
    <footer>
        <hr/>
        <small>
            Der Inhalt ist verfügbar unter der Creative-Commons-Lizenz <a rel="license" href="http://creativecommons.org/licenses/by-sa/3.0/">CC BY-SA 3.0</a> bei Angabe von <a rel="cc:attributionURL" property="cc:attributionName" href="//offene-bibel.de/">offene-bibel.de</a> als Quelle.
            <br/>
            <a href="/seiten/impressum">Impressum und Disclaimer</a>
        </small>
    </footer>
        </div><!-- container -->
        </main>
</div><!-- wrap -->
FOOTER_END;
}

?>
