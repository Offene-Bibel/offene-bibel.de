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

?>
