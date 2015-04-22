<?php
include_once (dirname ( __FILE__ ) . '/OffeneBibel_abk.php');

class BibelStelle extends SpecialPage {
  function __construct() {
    parent::__construct ('Bibelstelle');
  }

  function execute( $par ) {
    global $wgRequest, $wgOut;

    $this->setHeaders();

    $suche = $wgRequest->getText ('abk');
    $abk = new OfBiAbk;
    $ergebnisse = $abk->analyse ($suche);

    $output = '';
    $output .= '<form action="' . $GLOBALS ['wgScriptPath'] . '"><p>';
    $output .= '<input type="hidden" name="title" value="Special:Bibelstelle_aufschlagen" />';
    $output .= '<label for="input_bibelstelle">Bibelstelle</label>: ';
    $output .= '<input type="text" name="abk" id="input_bibelstelle" value="' . htmlspecialchars ($suche) . '" />';
    $output .= '<input type="submit" value="Suchen!" />';
    $output .= '</p></form>';
    $wgOut->addHTML ($output);

    $eingebunden = false;
    if (count ($ergebnisse) > 0) {
      $ergebnis = reset ($ergebnisse);
      $titleObj = Title::makeTitle( NS_MAIN, $ergebnis [0]);
      if (count ($ergebnisse) == 1 && $titleObj->exists ()) {
        $output = '';
        $output .= '<div class="cactions"><ul>';
        $talkTitleObj = $titleObj->getTalkPage ();
        $output .= '<li>[[Diskussion:' . $ergebnis [0] . '|Diskussion]]</li>';
        if ($titleObj->quickUserCan ('edit')) {
          $output .= '<li>[' . $titleObj->getFullURL (array ('action' => 'edit')). ' Bearbeiten]</li>';
        }
        $output .= '<li>[' . $titleObj->getFullURL (array ('action' => 'history')). ' Versionen/Autoren]</li>';
        $output .= '</ul></div>';

        if (! isset ($ergebnis [2])) {
          $url = $titleObj->getFullURL ();
        } else {
          $url = $titleObj->getFullURL (array ('von'=>$ergebnis [1], 'bis'=>$ergebnis [2]));
        }
        $output .= '<p class="navi">[' . $url . ' Suchfeld ausblenden]</p>';

        $output .= "\n= " . $ergebnis [0] . " =\n";
        $output .= '{{#vardefine:Seitenname|' . $ergebnis [0] . '}}';
        if (isset ($ergebnis [1])) {
          $output .= '<activeverses from="' . htmlspecialchars ($ergebnis [1]) .'"';
          if (isset ($ergebnis [2])) {
            $output .= ' to="' . htmlspecialchars ($ergebnis [2]) . '"';
          }
          $output .= '/>';
        }
        $output .= '{{:' . $ergebnis [0] . '}}';
        $wgOut->addWikiText ($output);
      } else {
        $output = '<p>Sie meinten vermutlich:</p>';

        $output .= '<ul>';
        foreach ($ergebnisse as $ergebnis) {
          $titleObj = Title::makeTitle( NS_MAIN, $ergebnis [0]);
          $output .= '<li><p>';
          if (! $titleObj->exists ()) {
            $output .= '<a href="' . $titleObj->getEditURL () . '" class="new">';
            $linktext = $ergebnis [0] . ' (noch nicht übersetzt)';
          } elseif (! isset ($ergebnis [2])) {
            $output .= '<a href="' . $titleObj->getLocalURL () . '">';
            $linktext = $ergebnis [0];
          } else {
            $output .= '<a href="' . $titleObj->getLocalURL (array ('von'=>$ergebnis [1], 'bis'=>$ergebnis [2])) . '">';
            if ($ergebnis [1] != $ergebnis [2]) {
              $linktext = $ergebnis [0] . ',' . $ergebnis [1] . '–' . $ergebnis [2];
            } else {
              $linktext = $ergebnis [0] . ',' . $ergebnis [1];
            }
          }
          $output .= htmlspecialchars ($linktext);
          $output .= '</a>';
          $output .= '</p></li>';
        }
        $output .= '</ul>';
        $wgOut->addHTML ($output);
      }
    }
  }
}
?>
