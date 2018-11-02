<?php
include_once (dirname ( __FILE__ ) . '/OffeneBibel_abk.php');

class BibelStelle extends SpecialPage {
  function __construct() {
    parent::__construct ('Bibelstelle');
  }

  function execute( $par ) {
    global $wgRequest, $wgOut;

    $this->setHeaders();

    $suche = str_replace( "\n", " ", $wgRequest->getText ('abk'));
    $abk = new OfBiAbk;
    $ergebnisse = $abk->analyse ($suche);

    $outputSearchResult = '<div class="search-result">';
    $outputSearchResult .= '<form action="' . $GLOBALS ['wgScriptPath'] . '/index.php"><p>';
    $outputSearchResult .= '<input type="hidden" name="title" value="Special:Bibelstelle" />';
    $outputSearchResult .= '<label for="input_bibelstelle">Suchwort/Bibelstelle</label>: ';
    $outputSearchResult .= '<input type="text" name="abk" id="input_bibelstelle" value="' . htmlspecialchars ($suche) . '" /> ';
    $outputSearchResult .= '<input type="submit" value="Neue Suche" />';
    $outputSearchResult .= '</p></form>';
    
    $search = \MediaWiki\MediaWikiServices::getInstance()->newSearchEngine();
    $nearMatch = $search->getNearMatcher($this->getConfig())->getNearMatch($suche);

    $totalMatches = is_null($nearMatch) ? 0 : 1;
    $titleMatches = $search->searchTitle($suche);
    if ($titleMatches) {
      $totalMatches = $titleMatches->getTotalHits();
    }
    $textMatches = $search->searchText($suche);
    if ($textMatches) {
      $totalMatches = max($totalMatches, $textMatches->getTotalHits());
    }

    if ($totalMatches > 0) {
      $outputSearchResult .= '<form action="' . $GLOBALS ['wgScriptPath'] . '/index.php"><p>';
      $outputSearchResult .= '<input type="hidden" name="title" value="Special:Search" />';
      $outputSearchResult .= '<input type="hidden" name="fulltext" value="Search" />';
      $outputSearchResult .= '<input type="hidden" name="search" " value="' . htmlspecialchars ($suche) . '" />';
      $outputSearchResult .= 'Die Volltextsuche hat ' . $totalMatches . ' Ergebnisse für „' . htmlspecialchars($suche) . '“. ';
      $outputSearchResult .= '<input type="submit" value="Anzeigen" />';
      $outputSearchResult .= '</p></form>';
      $searchPage = Title::makeTitle( NS_SPECIAL, 'Search');
      $searchUrl = $searchPage->getFullURL();
    } else {
      $outputResultList = '<div class="nothing-found">Keine Treffer in der Volltextsuche.</div>';
    }

    if (count ($ergebnisse) > 0) {
      $ergebnis = reset ($ergebnisse);
      $titleObj = Title::makeTitle( NS_MAIN, $ergebnis [0]);
      if (count ($ergebnisse) == 1 && $titleObj->exists ()) {

        if (! isset ($ergebnis [2])) {
          $url = $titleObj->getFullURL ();
        } else {
          $url = $titleObj->getFullURL (array ('von'=>$ergebnis [1], 'bis'=>$ergebnis [2]));
        }
        $this->getOutput()->setHTMLTitle($ergebnis [0] . ' – ' . $this->getOutput()->getHTMLTitle());
        $this->getOutput()->setCanonicalUrl($titleObj->getCanonicalURL());

        $outputContent .= '<div>';
        $outputContent .= "\n= " . $ergebnis [0] . " =\n";
        $outputContent .= '{{#vardefine:Seitenname|' . $ergebnis [0] . '}}';
        if (isset ($ergebnis [1])) {
          $outputContent .= '<activeverses from="' . htmlspecialchars ($ergebnis [1]) .'"';
          if (isset ($ergebnis [2])) {
            $outputContent .= ' to="' . htmlspecialchars ($ergebnis [2]) . '"';
          }
          $outputContent .= '/>';
        }
        $outputContent .= '{{:' . $ergebnis [0] . '}}' . "\n";
        $outputContent .= '</div>';

        $outputContent .= '<div class="historycredit">[' . $titleObj->getFullURL (array ('action' => 'history')). ' Versionsgeschichte / Übersetzer·innen bzw. Autor·en dieser Seite]</div>';

        $outputSearchResult .= '<p class="navi"><a href="' . $url . '">Suchfeld ausblenden</a></p>';
        $outputSearchResult .= '</div>';
        $outputSearchResult .= '<div class="umschalter">';
        $outputSearchResult .= OfBiAbk::kapitel_umschalter($ergebnis [0]);
        $outputSearchResult .= OfBiAbk::fassungen_umschalter($ergebnis [0]);
        $outputSearchResult .= '<div id="p-cactions" class="portlet">';
        if (is_string($titleObj->getFullURL (array ('action' => 'talk')))) {
          $outputSearchResult .= '<div class="pBody"><ul id="p-cactions-ul"><li id="ca-talk">';
          $outputSearchResult .= '<a href="' . $titleObj->getFullURL (array ('action' => 'talk')) . '">';
          $outputSearchResult .= 'Diskussion';
          $outputSearchResult .= '</a>';
          $outputSearchResult .= '</li></ul></div>';
        }
        $outputSearchResult .= '</div>';
        $outputSearchResult .= '</div>';
        $wgOut->addHTML($outputSearchResult);
        $wgOut->addWikiText ($outputContent);
      } else {
        $outputResultList = '<p>Sie meinten vermutlich:</p>';

        $outputResultList .= '<ul>';
        foreach ($ergebnisse as $ergebnis) {
          $titleObj = Title::makeTitle( NS_MAIN, $ergebnis [0]);
          $outputResultList .= '<li><p>';
          if (! $titleObj->exists ()) {
            $outputResultList .= '<a href="' . $titleObj->getEditURL () . '" class="new">';
            $linktext = $ergebnis [0] . ' (noch nicht übersetzt)';
          } elseif (! isset ($ergebnis [2])) {
            $outputResultList .= '<a href="' . $titleObj->getLocalURL () . '">';
            $linktext = $ergebnis [0];
          } else {
            $outputResultList .= '<a href="' . $titleObj->getLocalURL (array ('von'=>$ergebnis [1], 'bis'=>$ergebnis [2])) . '">';
            if ($ergebnis [1] != $ergebnis [2]) {
              $linktext = $ergebnis [0] . ',' . $ergebnis [1] . '–' . $ergebnis [2];
            } else {
              $linktext = $ergebnis [0] . ',' . $ergebnis [1];
            }
          }
          $outputResultList .= htmlspecialchars ($linktext);
          $outputResultList .= '</a>';
          $outputResultList .= '</p></li>';
        }
        $outputResultList .= '</ul>';

        $outputSearchResult .= '</div>';
        $wgOut->addHTML($outputSearchResult);
        
        $wgOut->addHTML ($outputResultList);
      }
    } else {
      $outputSearchResult .= '</div>';
      $wgOut->addHTML($outputSearchResult);
      $wgOut->addHTML ($outputResultList);
    }
  }
}
