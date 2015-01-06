<?php
 
if ( ! defined ('MEDIAWIKI')) {
    die ('This file is a MediaWiki extension, it is not a valid entry point.');
}
 
$wgExtensionCredits ['parserhook'][] = array (
  'name' => 'OffeneBibel',
  'version' => '1.5',
  'url' => '',
  'author' => 'Olaf Schmidt-Wischhöfer',
  'description' => 'Customisation for offene-bibel.de',
);

$wgHooks ['ParserFirstCallInit'][] = 'ofbiSetup';
$wgHooks ['LanguageGetMagic'][] = 'ofbiLanguageGetMagic';
$wgHooks['BeforePageDisplay'][] = 'addLicencing';

$wgAutoloadClasses ['OfBi'] = dirname ( __FILE__ ) . "/OffeneBibel_body.php";
$wgAutoloadClasses ['Bibelstelle'] = dirname ( __FILE__ ) . "/OffeneBibel_Bibelstelle.php";
$wgSpecialPages ['Bibelstelle'] = 'Bibelstelle';
$wgExtensionMessagesFiles ['Bibelstelle'] = dirname ( __FILE__ ) . '/OffeneBibel_Bibelstelle.i18n.php';
$wgExtensionAliasesFiles ['Bibelstelle'] = dirname ( __FILE__ ) . '/OffeneBibel_Bibelstelle.alias.php';

function ofbiSetup (&$parser) {
  new OfBi;
  return true;
}

function ofbiLanguageGetMagic (&$magicWords, $langCode = 0) {
  $magicWords ['chapternumber'] = array ('0', 'chapternumber');
  $magicWords ['bookname'] = array ('0', 'bookname');

  $magicWords ['firstchapter'] = array ('0', 'firstchapter');
  $magicWords ['nextchapter'] = array ('0', 'nextchapter');
  $magicWords ['previouschapter'] = array ('0', 'previouschapter');
  $magicWords ['lastchapter'] = array ('0', 'lastchapter');

  $magicWords ['versenumber'] = array ('0', 'versenumber');
  $magicWords ['selectchapter'] = array ('0', 'selectchapter');
  $magicWords ['syntax_status'] = array ('0', 'syntax_status');
  return true;
}

/**
 * Add the terms of service to the registration form.
 * Die if this fails.
 * @return Boolean: true
 */
function addLicencing(&$out,&$skin) {
    $context = $out;
    $title = $context->getTitle();
    $pagename = '';
    list( $pagename, /*...*/ ) = SpecialPageFactory::resolveAlias( $title->getBaseText() );
    if($title->isSpecialPage() && $pagename == "Userlogin"){
        $query = $context->getRequest()->getQueryValues();
        if(!empty($query["type"]) && $query["type"] == "signup") {
            // Append the licence iframe and text message
            $search_token = '<div class="mw-createacct-benefits-container">';
            $replacement = <<<EOT
<div>
<h2>Nutzungsbedingungen</h2>
<ul>
    <li>Mit der Anmeldung auf „offene-bibel.de“ inklusive aller Unterbereiche und Subdomains (im Folgenden „Offene Bibel“) schließt du einen Nutzungsvertrag mit dem Betreiber der Offenen Bibel ab (im Folgenden „Betreiber“) und erklärst dich mit den nachfolgenden Regelungen einverstanden.</li>
    <li>Wenn du mit diesen Regelungen nicht einverstanden bist, so darfst du die Offene Bibel nicht weiter nutzen. Für die Nutzung der Offenen Bibel gelten jeweils die an dieser Stelle veröffentlichten Regelungen.</li>
    <li>Der Nutzungsvertrag wird auf unbestimmte Zeit geschlossen und kann von beiden Seiten ohne Einhaltung einer Frist jederzeit gekündigt werden.</li>
</ul>
<h2>Lizenz der Beiträge</h2>
<ul>
    <li>Mit dem Erstellen eines Beitrags (Forenbeitrag, Blogeintrag, Newsbeitrag, Kommentar, Wikieintrag, ...) akzeptierst du, dass du deinen Beitrag damit automatisch unter die <a title="Urheberrecht" href="/wiki/Die_Offene_Bibel:Urheberrecht">CC-by-sa 3.0</a> stellst. Abweichende Lizenzen eingestellter Texte oder Medien, Bilder, Dateien,... müssen vermerkt werden.</li>
    <li>Diese Lizenz für deinen Beitrag bleibt unabhängig von der Weiternutzung der Offenen Bibel bestehen.</li>
</ul>
<h2>Pflichten des Nutzers</h2>
<ul>
    <li>Du erklärst mit der Erstellung eines Beitrags, dass er keine Inhalte enthält, die gegen geltendes Recht oder die guten Sitten verstoßen. Du erklärst insbesondere, dass du das Recht besitzt, die in deinen Beiträgen verwendeten Links und Bilder zu setzen bzw. zu verwenden.</li>
    <li>Du nimmst zur Kenntnis, dass der Betreiber keine Verantwortung für die Inhalte von Beiträgen übernimmt, die er nicht selbst erstellt hat oder die er nicht zur Kenntnis genommen hat. Du gestattest dem Betreiber, dein Benutzerkonto, Beiträge und Funktionen jederzeit zu löschen oder zu sperren.</li>
    <li>Du gestattest dem Betreiber darüber hinaus, deine Beiträge abzuändern, sofern sie gegen o. g. Regeln verstoßen oder geeignet sind, dem Betreiber oder einem Dritten Schaden zuzufügen.</li>
    <li>Du verpflichtest dich, deine Zugangsdaten (insbesondere Passwörter) geheim zu halten und nicht an Dritte weiterzugeben.</li>
</ul>
<h2>Änderungsvorbehalt</h2>
<ul>
    <li>Der Betreiber ist berechtigt, die Nutzungsbedingungen, die Eckpunkte  und die Datenschutzrichtlinie zu ändern. Die Änderung wird dem Nutzer per E-Mail mitgeteilt.</li>
    <li>Der Nutzer ist berechtigt, den Änderungen zu widersprechen. Im Falle des Widerspruchs erlischt das zwischen dem Betreiber und dem Nutzer bestehende Vertragsverhältnis mit sofortiger Wirkung.</li>
    <li>Die Änderungen gelten als anerkannt und verbindlich, wenn der Nutzer den Änderungen zugestimmt hat.</li>
</ul>
<h2>Weitere Regeln</h2>
<ul>
    <li>Du verpflichtest dich, keine illegalen, pornografischen, menschenverachtenden und/oder gegen die guten Sitten verstoßenden Beiträge einzustellen. Achte bei deinen Beiträgen auf einen angemessenen Ton, beleidige niemanden.</li>
    <li>Die Offene Bibel für Werbezwecke zu nutzen ist untersagt (Ausnahmen sind <i>nach Rücksprache mit dem Betreiber</i> möglich).</li>
    <li>Der Betreiber behält sich vor, bei Verstößen gegen diese Regeln oder die <a href="/wiki/Eckpunkte">Eckpunkte</a> Benutzer zeitweise oder ganz zu sperren.</li>
    <li>Der Betreiber behält sich das Recht vor, ohne Rücksprache Beiträge zu ändern oder zu löschen, falls sie den Regeln oder <a href="/wiki/Eckpunkte">Eckpunkten</a> widersprechen oder den Portalfrieden gefährden. </li>
</ul>
</div>
<div class="mw-createacct-benefits-container" style="display:none">
EOT;
            $replacement_count = 0;
            $out->mBodytext = preg_replace("/$search_token/","$replacement",$out->mBodytext, -1, $replacement_count);
            if($replacement_count != 1) {
                die("Registration form terms of service logic broken.");
            }
        }
    }
    return true;
}

?>
