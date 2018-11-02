<?php

class OfBiAbk {
  public static function buchnamekapitel ($eingabe) {
    $eingabe = self::plural2singular($eingabe);

    if (isset (self::$versnummern [$eingabe])) {
      if (count (self::$versnummern [$eingabe]) == 1 && key (self::$versnummern [$eingabe]) === 0) {
        return array ($eingabe, 0);
      }
      return array ($eingabe, false);
    }

    $slashpos = mb_strpos ($eingabe, '/');
    if ($slashpos !== false) {
      $eingabe = mb_substr ($eingabe, 0, $slashpos);
    }
    
    if (strtolower(substr($eingabe, -20)) === ' in leichter sprache') {
      $eingabe = mb_substr ($eingabe, 0, -20);
    }
    
    $commapos = mb_strpos ($eingabe, ',');
    if ($commapos !== false) {
      $eingabe = mb_substr ($eingabe, 0, $commapos);
    }
    
    $spacepos = mb_strrpos ($eingabe, ' ');
    if ($spacepos !== false) {
      $buchname = mb_substr ($eingabe, 0, $spacepos);
      $kapitel = mb_substr ($eingabe, $spacepos+1);

      if (isset ($singularnamen [$buchname])) {
        $buchname = $singularnamen [$buchname];
      }

      if (isset (self::$versnummern [$buchname]) && isset (self::$versnummern [$buchname] [$kapitel])) {
        return array ($buchname, $kapitel);
      }
    }
    return array (false, false);
  }

  public static function versnummern ($buchname, $kapitel)  {
    $buchname = self::plural2singular($buchname);
    if (isset (self::$versnummern [$buchname]) && isset (self::$versnummern [$buchname] [$kapitel])) {
      $versnummern = self::$versnummern [$buchname] [$kapitel];
      if (is_int ($versnummern)) {
        return range (1, $versnummern);
      }
      return $versnummern;
    }
    return array ();
  }

  public static function erstes_kapitel ($buchname) {
    if (isset (self::$versnummern [$buchname])) {
      reset (self::$versnummern [$buchname]);
      if (count (self::$versnummern [$buchname]) > 1 || key (self::$versnummern [$buchname]) !== 0) {
        return key (self::$versnummern [$buchname]);
      }
    }
    return false;
  }

  public static function letztes_kapitel ($buchname) {
    if (isset (self::$versnummern [$buchname])) {
      end (self::$versnummern [$buchname]);
      if (count (self::$versnummern [$buchname]) > 1 || key (self::$versnummern [$buchname]) !== 0) {
        return key (self::$versnummern [$buchname]);
      }
    }
    return false;
  }

  public static function naechstes_kapitel ($buchname, $kapitel) {
    if (isset (self::$versnummern [$buchname]) && isset (self::$versnummern [$buchname] [$kapitel])) {
      $kapitelnummern = array_keys (self::$versnummern [$buchname]);
      $currentkey = array_search ($kapitel, $kapitelnummern);
      if ($currentkey + 1 < count ($kapitelnummern)) { 
        return $kapitelnummern [$currentkey + 1];
      } else {
        return false;
      }
    } else {
      return false;
    }
  }

  public static function voriges_kapitel ($buchname, $kapitel) {
    if (isset (self::$versnummern [$buchname]) && isset (self::$versnummern [$buchname] [$kapitel])) {
      $kapitelnummern = array_keys (self::$versnummern [$buchname]);
      $currentkey = array_search ($kapitel, $kapitelnummern);
      if ($currentkey > 0) { 
        return $kapitelnummern [$currentkey - 1];
      } else {
        return false;
      }
    } else {
      return false;
    }
  }
  
  public static function singular2plural ($buchname) {
    if (isset (self::$pluralnamen [$buchname])) {
      $buchname = self::$pluralnamen [$buchname];
    }
    return $buchname;
  }
  
  public static function plural2singular ($buchname) {
    $singularnamen = array_flip (self::$pluralnamen);
    if (isset ($singularnamen [$buchname])) {
      $buchname = $singularnamen [$buchname];
    }
    return $buchname;
  }

  public static function buchnamen_alphabetisch ($aktueller_buchname) {
    $liste = self::$alternativnamen;
    foreach (self::$buchnamen as $buchname) {
      $buchname = self::singular2plural($buchname);
      if (isset (self::$kurznamen [$buchname])) {
        $liste [self::$kurznamen [$buchname]] = $buchname;
      } else {
        $liste [$buchname] = $buchname;
      }
    }

    uksort($liste, "strnatcasecmp");

    $aktueller_buchname = self::singular2plural($aktueller_buchname);
    if (isset (self::$kurznamen [$aktueller_buchname])) {
      $aktueller_buchname = self::$kurznamen [$aktueller_buchname];
    } elseif (! isset ($liste [$aktueller_buchname])) {
      $liste = array ('Buch aufschlagen:'=>'') + $liste;
      $aktueller_buchname = 'Buch aufschlagen:';
    }
    return array($liste, $aktueller_buchname);
  }

  public static function analyse ($eingabe) {
    $name = preg_replace ('#([^0-9 ])([0-9])#', '$1 $2', $eingabe);
    $name = preg_replace ('#([0-9])([^0-9 ])#', '$1 $2', $name);
    $name = trim (strtolower ($name));
    $kapitelvers = array ();
    if (preg_match ('#^(([1-5] )?[^0-9]+)([0-9 .,:;()\\\\/–_-]*)$#', $name, $match) > 0) {
      $name = trim ($match [1]);
      $match [3] = preg_replace ('#[\'’]#', '', $match [3]);
      $match [3] = preg_replace ('#[.,:;()\\\\/–_-]#', ' ', $match [3]);
      $match [3] = preg_replace ('# +#', ' ', $match [3]);
      $kapitelvers = explode (' ', trim ($match [3]));
      if (count ($kapitelvers) == 1 && $kapitelvers [0] == '') {
        $kapitelvers = array ();
      }
    }
    $name = preg_replace ('# +#', ' ', $name);
    $name = preg_replace ('#[.,:;()\\\\/–_-]#', ' ', $name);
    $name = trim ($name);

    if ($name == '') {
      return array ();
    }

    $ergebnisse = self::prüfe_existenz ($name, $kapitelvers);
    if (count ($ergebnisse) == 0) {
      $namensteile = explode (' ', $name);
      foreach ($namensteile as $nr=>$namensteil) {
        if (isset (self::$teilworte [$namensteil])) {
          $namensteile [$nr] = self::$teilworte [$namensteil];
        }
      }
      $name = implode (' ', array_unique ($namensteile));
      $name = trim ($name);
      $ergebnisse = self::prüfe_existenz ($name, $kapitelvers);
    }
    if (count ($ergebnisse) == 0) {
      $name = ' ' . $name . ' ';
      $name = preg_replace ('# i #', ' 1 ', $name);
      $name = preg_replace ('# ii #', ' 2 ', $name);
      $name = preg_replace ('# iii #', ' 3 ', $name);
      $name = preg_replace ('# iv #', ' 4 ', $name);
      $name = preg_replace ('# v #', ' 5 ', $name);
      $name = trim ($name);
      $namensteile = explode (' ', $name);
      natsort ($namensteile);
      $name = implode (' ', array_unique ($namensteile));
      $name = trim ($name);
      $ergebnisse = self::prüfe_existenz ($name, $kapitelvers);
    }
    if (count ($ergebnisse) == 0) {
      $namensteile = explode (' ', $name);
      for ($i = 0; $i < pow (2, count ($namensteile)); $i++) {
        $namensteile2 = array ();
        for ($j = 0; $j < count ($namensteile); $j++) {
          if (($i & pow (2, $j)) > 0) {
            $namensteile2 [] = $namensteile [$j];
          }
        }
        $name2 = implode (' ', $namensteile2);
        $ergebnisse = array_merge ($ergebnisse, self::prüfe_existenz ($name2, $kapitelvers));
      }
    }
    if (count ($ergebnisse) == 0) {
      foreach ($namensteile as $namensteil) {
        if ($namensteil != '') {
          foreach (self::$buchnamen as $kürzel=>$buchname) {
            if (mb_strpos ($kürzel, $namensteil) === 0 || mb_strpos (strtolower ($buchname), $namensteil) === 0) {
              $ergebnisse = array_merge ($ergebnisse, self::prüfe_existenz ($kürzel, $kapitelvers));
            }
          }
          foreach (array_keys (self::$abkürzungen) as $abkürzung) {
            if (mb_strpos ($abkürzung, ' ') === false && mb_strpos ($abkürzung, $namensteil) === 0) {
              $ergebnisse = array_merge ($ergebnisse, self::prüfe_existenz ($abkürzung, $kapitelvers));
            }
          }
          foreach (self::$teilworte as $teilwort=>$abkürzung) {
            if (mb_strpos ($teilwort, $namensteil) === 0) {
              $ergebnisse = array_merge ($ergebnisse, self::prüfe_existenz ($abkürzung, $kapitelvers));
            }
          }
        }
      }
    }

    $ausgabe = array ();
    foreach ($ergebnisse as $ergebnis) {
      $ausgab [0] = isset (self::$buchnamen [$ergebnis [0]]) ? self::$buchnamen [$ergebnis [0]] : $ergebnis [0];
      if (count (self::$versnummern [$ausgab [0]]) > 1 || key (self::$versnummern [$ausgab [0]]) !== 0) {
        if (isset ($ergebnis [1])) {
          $ausgab [0] .= ' ' . $ergebnis [1];
        }
        unset ($ergebnis [1]);
      }
      unset ($ergebnis [0]);
      if (count ($ergebnis) > 0) {
        $ausgab [1] = min ($ergebnis);
        $ausgab [2] = max ($ergebnis);
      }
      $ausgabe [implode ('##', $ausgab)] = $ausgab;
    }
    return $ausgabe;
  }

  private static function prüfe_existenz ($name, $kapitelvers) {
    $ergebnisse = array ();
    if (isset (self::$abkürzungen [$name])) {
      $ergebnisse = self::$abkürzungen [$name];
    } elseif (isset (self::$buchnamen [$name])) {
      $ergebnisse [] = array ($name);
    } elseif (isset (self::$pro_kapitel [$name])) {
      if (count ($kapitelvers) == 0) {
        foreach (array_keys (self::$pro_kapitel [$name]) as $nr) {
          $ergebnisse [] = array ($name, $nr);
        }
      } elseif (isset (self::$pro_kapitel [$name] [$kapitelvers  [0]])) {
        $ergebnisse [] = array ($name);
      }
    } else {
      if (isset (self::$buchnamen ['1 ' . $name])) {
        $ergebnisse [] = array ('1 ' . $name);
      }
      if (isset (self::$buchnamen ['2 ' . $name])) {
        $ergebnisse [] = array ('2 ' . $name);
      }
      if (isset (self::$buchnamen ['3 ' . $name])) {
        $ergebnisse [] = array ('3 ' . $name);
      }
      if (isset (self::$buchnamen ['4 ' . $name])) {
        $ergebnisse [] = array ('4 ' . $name);
      }
      if (isset (self::$buchnamen ['5 ' . $name])) {
        $ergebnisse [] = array ('5 ' . $name);
      }
    }
    foreach ($ergebnisse as $nr=>$ergebnis) {
      $ergebnisse [$nr] = array_merge ($ergebnis, $kapitelvers);
    }
    foreach ($ergebnisse as $nr=>$ergebnis) {
      if (isset (self::$pro_kapitel [$ergebnis [0]]) && isset (self::$pro_kapitel [$ergebnis [0]] [$ergebnis [1]])) {
        $kapitelversneu = $kapitelvers;
        unset ($kapitelvers [0]);
        $ergebnisse [$nr] = array_merge (self::$pro_kapitel [$ergebnis [0]] [$ergebnis [1]], $kapitelvers);
      }
    }
    return $ergebnisse;
  }
  
  public static function kapitel_umschalter ($pagename) {
    $make_option = function ($value, $text, $active) {
      if ($active) {
        return '<option value="' . htmlspecialchars ($value) . '" selected="selected">' . htmlspecialchars ($text) . '</option>';
      } else {
        return '<option value="' . htmlspecialchars ($value) . '">' . htmlspecialchars ($text) . '</option>';
      }
    };
    
    list($name, $kapitel) = self::buchnamekapitel($pagename);

    $text = '';
    $kapitel = (int)trim($kapitel);
    $text .= '<div class="kapitelwahl">';

    $text .= '<div class="buchwahl ofbi-dropdown">
      <div class="ofbi-dropdown-toggle">Bibelstelle/Suche <span class="caret"></span></div>
      <div class="ofbi-dropdown-menu hidden">
        <form action="/mediawiki/" method="get">
          <div class="form-group">
            <div class="input-group">
              <input type=hidden name=title value="Spezial:Bibelstelle">
              <input class="form-control" id="ofbi-suche" name="abk" maxlength="60" size="15" placeholder="Stellenangabe oder Suchwort" aria-label="Stellenangabe oder Suchwort" required>
              <label class="input-group-addon"><button type="submit">Suche</button>
            </div>
          </div>
        </form>
        <table class="bible-link-table">
          <tbody>
            <tr>
              <th colspan="6">
                Neues&nbsp;Testament:
              </th>
            </tr>
            <tr>
              <td aria-label="Evan&#173;ge&#173;lien">
                <p>
                  <a href="/wiki/Matthäus">Matt&shy;häus</a>
                  <a href="/wiki/Markus">Markus</a>
                  <a href="/wiki/Lukas">Lukas</a>
                  <a href="/wiki/Johannes">Johan&shy;nes</a>
                </p>
              </td>
              <td>
                <p>
                  <a href="/wiki/Apostelgeschichte">Apostel&shy;geschichte</a>
                </p>
              </td>
              <td aria-label="Paulus&#173;briefe">
                <p>
                  <a href="/wiki/Römer">Römer</a>
                  <a href="/wiki/1-2_Korinther">1-2 Korinther</a>
                  <a href="/wiki/Galater">Galater</a>
                  <a href="/wiki/Epheser">Epheser</a>
                  <a href="/wiki/Philipper">Philipper</a>
                  <a href="/wiki/Kolosser">Kolosser</a>
                  <a href="/wiki/1-2_Thessalonicher">1-2 Thessa&shy;lo&shy;nicher</a>
                  <a href="/wiki/1-2_Timotheus">1-2 Timotheus</a>
                  <a href="/wiki/Titus">Titus</a>
                  <a href="/wiki/Philemon">Philemon</a>
                </p>
              </td>
              <td aria-label="Brief ">
                <p>
                  <a href="/wiki/Hebräer">Hebräer</a>
                </p>
              </td>
              <td aria-label="Pastoral&#173;briefe">
                <p>
                  <a href="/wiki/Jakobus">Jakobus</a>
                  <a href="/wiki/1-2_Petrus">1-2 Petrus</a>
                  <a href="/wiki/1-3_Johannes">1-3 Johannes</a>
                  <a href="/wiki/Judas">Judas</a>
                </p>
              </td>
              <td>
                <p>
                  <a href="/wiki/Offenbarung">Offen&shy;ba&shy;rung​​​<br>=Apo&shy;kalypse</a>
                </p>
              </td>
            </tr>
          </tbody>
          <tbody>
            <tr>
              <th colspan="6">
                Altes Testament:
              </th>
            </tr>
            <tr>
              <td data-short-label="Mose" aria-label="Penta&#173;teuch,&#xa;jüdisch: „Tora“ (Gesetz),&#xa;christ&#173;lich: Geschichts&#173;bücher">
                <p>
                  <a href="/wiki/Genesis">1M.=&#8203;Genesis​​​&#8203;</a>
                  <a href="/wiki/Exodus">2M.=&#8203;Exodus​​​&#8203;</a>
                  <a href="/wiki/Levitikus">3M.=&#8203;Levitikus​​​&#8203;</a>
                  <a href="/wiki/Numeri">4M.=&#8203;Numeri​​​&#8203;</a>
                  <a href="/wiki/Deuteronomium">5M.=&#8203;Deute&shy;ro&shy;nomium​​​&#8203;</a>
                </p>
              </td>
              <td data-short-label="dtr.Geschichte" aria-label="Deute&#173;rono&#173;misti&#173;sches Ges&#173;chichts&#173;werk,&#xa;jüdisch: „Vordere Pro&#173;phe&#173;ten“,&#xa;christ&#173;lich: Geschichts&#173;bücher">
                <p>
                  <a href="/wiki/Josua">Josua</a>
                  <a href="/wiki/Richter">Richter</a>
                  <a href="/wiki/1-2_Samuel">1-2 Samuel</a>
                  <a href="/wiki/1-2_Könige">1-2 Könige</a>
                </p>
              </td>
              <td colspan="4" data-short-label="Pro&#173;phe&#173;tie" aria-label="Pro&#173;pheten&#173;bücher&#xa;jüdisch: „Hintere Pro&#173;phe&#173;ten“">
                <div>
                  <p>
                    <a href="/wiki/Jesaja">Jesaja</a>
                    <a href="/wiki/Jeremia">Jeremia</a>
                    <a href="/wiki/Ezechiel">Ezechiel​​​<br/>=Hesekiel</a>
                  </p>
                  <p>
                    <a href="/wiki/Hosea">Hosea</a>
                    <a href="/wiki/Joel">Joel</a>
                    <a href="/wiki/Amos">Amos</a>
                    <a href="/wiki/Obadja">Obadja</a>
                    <a href="/wiki/Jona">Jona</a>
                    <a href="/wiki/Micha">Micha</a>
                    <a href="/wiki/Nahum">Nahum</a>
                    <a href="/wiki/Habakuk">Habakuk</a>
                    <a href="/wiki/Zefanja">Zefanja</a>
                    <a href="/wiki/Haggai">Haggai</a>
                    <a href="/wiki/Sacharja">Sacharja</a>
                    <a href="/wiki/Maleachi">Maleachi</a>
                  </p>
                </div>
              </td>
            </tr>
            <tr>
              <td data-short-label="Poesie" aria-label="Poe&#173;ti&#173;sche Schrif&#173;ten,&#xa;christ&#173;lich: Lehr&#173;bücher">
                <p>
                  <a href="/wiki/Psalmen">Psalmen</a>
                  <a href="/wiki/Ijob">Ijob​​​&#8203;=Hiob</a>
                  <a href="/wiki/Sprichwörter">Sprich&shy;wörter</a>
                </p>
              </td>
              <td data-short-label="Schrift/Geschichte" aria-label="jüdisch: Schrif&#173;ten,&#xa;christ&#173;lich: Geschichts&#173;bücher">
                <p>
                  <a href="/wiki/1-2_Chronik">1-2 Chronik</a>
                  <a href="/wiki/Esra">Esra</a>
                  <a href="/wiki/Nehemia">Nehemia</a>
                </p>
              </td>
              <td colspan="4" data-short-label="Schrift/Prophetie" aria-label="jüdisch: Schrif&#173;ten,&#xa;christ&#173;lich: Pro&#173;phe&#173;ten">
                <p>
                  <a href="/wiki/Daniel">Daniel</a>
                </p>
              </td>
            </tr>
            <tr>
              <td data-short-label="Festrolle/Lehre" aria-label="jüdisch: Schrif&#173;ten der „Megil&#173;lot“ (Fest&#173;rolle),&#xa;christ&#173;lich: Lehr&#173;bücher">
                <p>
                  <a href="/wiki/Hohelied">Hohelied</a>
                  <a href="/wiki/Kohelet">Kohelet​​​&#8203;=Prediger</a>
                </p>
              </td>
              <td data-short-label="Festrolle/Geschichte" aria-label="jüdisch: Schrif&#173;ten der „Megil&#173;lot“ (Fest&#173;rolle),&#xa;christ&#173;lich: Geschichts&#173;bücher">
                <p>
                  <a href="/wiki/Rut">Rut</a>
                  <a href="/wiki/Ester">Ester</a>
                </p>
              </td>
              <td colspan="4" data-short-label="Festrolle/Prophetie" aria-label="jüdisch: Schrif&#173;ten der „Megil&#173;lot“ (Fest&#173;rolle),&#xa;christ&#173;lich: Pro&#173;phe&#173;ten">
                <p>
                  <a href="/wiki/Klagelieder">Klage&shy;lieder</a>
                </p>
              </td>
            </tr>
            <tr>
              <th colspan="6">
                Spätschriften zum Alten Testament:
              </th>
            </tr>
            <tr>
              <td width="243" data-short-label="Lehre" aria-label="katho&#173;lisch und ortho&#173;dox: Lehr&#173;bücher&#xa;evan&#173;ge&#173;lisch: „apokryph“ (verdeckt, dunkel)">
                <p>
                  <a href="/wiki/Psalmen_(Ergänzungen)">Psalmen (Ergän&shy;zungen)</a>
                  <a href="/wiki/Oden">Oden</a>
                  <a href="/wiki/Weisheit">Weisheit</a>
                  <a href="/wiki/Jesus_Sirach">Jesus Sirach</a>
                </p>
              </td>
              <td data-short-label="Geschichte" aria-label="katho&#173;lisch und ortho&#173;dox: Geschichts&#173;bücher&#xa;evan&#173;ge&#173;lisch: „apokryph“ (verdeckt, dunkel)">
                <p>
                  <a href="/wiki/Esra_(Ergänzungen)">Esra (Ergän&shy;zungen)</a>
                  <a href="/wiki/Judith">Judith</a>
                  <a href="/wiki/Tobit">Tobit</a>
                  <a href="/wiki/Ester">Ester (grie&shy;chi&shy;sche Version)</a>
                  <a href="/wiki/Makkabäer">1-4&nbsp;Makka&shy;bäer</a>
                </p>
              </td>
              <td colspan="4" data-short-label="Prophetie" aria-label="katho&#173;lisch und ortho&#173;dox: Pro&#173;phe&#173;ten&#xa;evan&#173;ge&#173;lisch: „apokryph“ (verdeckt, dunkel)">
                <p>
                  <a href="/wiki/Baruch">Baruch</a>
                  <a href="/wiki/Brief_des_Jeremia">Brief des Jeremia</a>
                  <a href="/wiki/Daniel_(griechische_Version)">Daniel (grie&shy;chische Version), Susanna, Bel </a>
                </p>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>';

    if (self::erstes_kapitel ($name) !== false) {
      $text .= '<form> ';
      $text .= '<input type="submit" class="submitbutton2" value="Geh zu" onload="" class="zelle" /> ';
      $text .= '<span class="zelle">';
      $text .= '<select name="title" id="ofbi-nav-chapter" aria-label="Kapitel:">';

      if ($kapitel === 0) {
        $text .= $make_option ($name, 'Kapitel:', true);
      }
      $chapternumber = self::erstes_kapitel ($name);
      do {
        $pagename_for_option = $name . ' ' . $chapternumber;
        $option = htmlspecialchars ($pagename_for_option);
        $titleObj = Title::makeTitle( NS_MAIN, $option);
        if (! $titleObj->exists ()) {
          $pagename_for_option = '(' . $pagename_for_option . ')';
        }
        $text .= $make_option ($option, $pagename_for_option, $chapternumber === $kapitel);
        $chapternumber = self::naechstes_kapitel ($name, $chapternumber);
      } while ($chapternumber !== false);

      $text .= '</select>';

      if (self::voriges_kapitel ($name, $kapitel) !== false) {
        $titleObj = Title::makeTitle( NS_MAIN, $name . ' ' . self::voriges_kapitel ($name, $kapitel));
        $text .= ' <a href="' . htmlspecialchars ($titleObj->getLocalURL ()) . '"';
        $text .= ' title="' . htmlspecialchars ($name . ' ' . self::voriges_kapitel ($name, $kapitel)) . '"';
        if (! $titleObj->exists ()) {
          $text .= ' class="new"';
        }
        $text .= '>←</a> ';
      }

      if (self::naechstes_kapitel ($name, $kapitel) !== false) {
        $titleObj = Title::makeTitle( NS_MAIN, $name . ' ' . self::naechstes_kapitel ($name, $kapitel));
        $text .= ' <a href="' . htmlspecialchars ($titleObj->getLocalURL ()) . '"';
        $text .= ' title="' . htmlspecialchars ($name . ' ' . self::naechstes_kapitel ($name, $kapitel)) . '"';
        if (! $titleObj->exists ()) {
          $text .= ' class="new"';
        }
        $text .= '>→</a> ';
      }

      $text .= '</span>';
      $text .= '</form>';
    }

    $text .= '<script type="text/javascript"> ';
    $text .= '[].forEach.call(document.querySelectorAll(".submitbutton1"), function(elem){elem.style.display = "none"}); ';
    if (self::erstes_kapitel ($name) !== false) {
      $text .= '[].forEach.call(document.querySelectorAll(".submitbutton2"), function(elem){elem.style.display = "none"}); ';
    }
    $text .= '</script>';

    $text .= '</div>';
    return $text;
  }

  public static function fassungen_umschalter ($pagename) {
    $result = '';
    $category = null;
    $sortedTitles = array();

    list ($buchname, $kapitel) = self::buchnamekapitel ($pagename);
    if (is_string($kapitel)) {
      $category = Category::newFromName($buchname . ' ' . $kapitel);
    }

    if ($category instanceof Category) {
      $titles = array();
      foreach ($category->getMembers() as $title) {
        $titleText = $title->getText();
        $titles[$titleText] = $title;
      }

      // Skip current page title
      $thisTitleText = Title::newFromText($pagename)->getText();
      unset($titles[$thisTitleText]);

      // If the current page title contains " in Leichter Sprache",
      // then add all title with " in Leichter Sprache" first
      if (strpos(mb_strtolower($thisTitleText), ' in leichter sprache') !== false) {
        foreach ($titles as $titleText=>$title) {
          if (strpos(mb_strtolower($titleText), ' in leichter sprache') !== false) { 
            $sortedTitles[$titleText] = $title;
            unset($titles[$titleText]);
          }
        }
      }

      // Then add the page "<Bookname> <Chapternumber>"
      $title = Title::newFromText($buchname . ' ' . $kapitel);
      $titleText = $title->getText();
      if (array_key_exists($titleText, $titles)) {
        $sortedTitles[$titleText] = $title;
        unset($titles[$titleText]);
      }
    
      // Then add the page "<Bookname> <Chapternumber> in Leichter Sprache"
      $title = Title::newFromText($buchname . ' ' . $kapitel . ' in Leichter Sprache');
      $titleText = $title->getText();
      if (array_key_exists($titleText, $titles)) {
        $sortedTitles[$titleText] =  $title;
        unset($titles[$titleText]);
      }

      // Add all other pages from this category last
      foreach ($titles as $titleText=>$title) {
        $sortedTitles[$titleText] =  $title;
      }
    }
    
    if (count($sortedTitles) > 0) {
        $result = '<div class="andere-fassungen ofbi-dropdown"><div class="ofbi-dropdown-toggle">';
        $result .= 'Andere Fassungen von ' . $buchname . ' ' . $kapitel;
        $result .= ' <span class="caret"></span></div><ul class="ofbi-dropdown-menu hidden">';
        
        foreach ($sortedTitles as $titleText=>$title) {
        $result .= '<li><a href="' . htmlspecialchars ($title->getFullURL()) . '">' . htmlspecialchars($titleText) . '</a></li>';
        }
        $result .= '</ul></div>';
    }

    return $result;
  }
  
  public static $buchnamen = array (
    'genesis' => 'Genesis',
    'exodus' => 'Exodus',
    'levitikus' => 'Levitikus',
    'numeri' => 'Numeri',
    'deuteronomium' => 'Deuteronomium',
    'josua' => 'Josua',
    'richter' => 'Richter',
    '1 samuel' => '1 Samuel',
    '2 samuel' => '2 Samuel',
    '1 könige' => '1 Könige',
    '2 könige' => '2 Könige',
    'jesaja' => 'Jesaja',
    'jeremia' => 'Jeremia',
    'ezechiel' => 'Ezechiel',
    'hosea' => 'Hosea',
    'joel' => 'Joel',
    'amos' => 'Amos',
    'obadja' => 'Obadja',
    'jona' => 'Jona',
    'micha' => 'Micha',
    'nahum' => 'Nahum',
    'habakuk' => 'Habakuk',
    'zefanja' => 'Zefanja',
    'haggai' => 'Haggai',
    'sacharja' => 'Sacharja',
    'maleachi' => 'Maleachi',
    'psalm' => 'Psalm',
    'ijob' => 'Ijob',
    'sprichwörter' => 'Sprichwörter',
    'rut' => 'Rut',
    'hohelied' => 'Hohelied',
    'kohelet' => 'Kohelet',
    'klagelieder' => 'Klagelieder',
    'ester' => 'Ester',
    'daniel' => 'Daniel',
    'esra' => 'Esra',
    'nehemia' => 'Nehemia',
    '1 chronik' => '1 Chronik',
    '2 chronik' => '2 Chronik',
    'baruch' => 'Baruch',
    'brief des jeremia' => 'Brief des Jeremia',
    'psalm ergänzung' => 'Psalm (Ergänzung)',
    'psalm des salomo' => 'Psalm des Salomo',
    'ode' => 'Ode',
    'jesus sirach' => 'Jesus Sirach',
    'weisheit' => 'Weisheit',
    'ester griechische version' => 'Ester (griechische Version)',
    'susanna' => 'Susanna (Daniel, griechische Version)',
    'daniel griechische version' => 'Daniel (griechische Version)',
    'bel' => 'Bel (Daniel, griechische Version)',
    'esra griechische ergänzung' => 'Esra (griechische Ergänzung)',
    'esra lateinische ergänzung' => 'Esra (lateinische Ergänzung)',
    'judit' => 'Judit',
    'tobit' => 'Tobit',
    '1 makkabäer' => '1 Makkabäer',
    '2 makkabäer' => '2 Makkabäer',
    '3 makkabäer' => '3 Makkabäer',
    '4 makkabäer' => '4 Makkabäer',
    'matthäus' => 'Matthäus',
    'markus' => 'Markus',
    'lukas' => 'Lukas',
    'johannes' => 'Johannes',
    'apostelgeschichte' => 'Apostelgeschichte',
    'römer' => 'Römer',
    '1 korinther' => '1 Korinther',
    '2 korinther' => '2 Korinther',
    'galater' => 'Galater',
    'epheser' => 'Epheser',
    'philipper' => 'Philipper',
    'kolosser' => 'Kolosser',
    '1 thessalonicher' => '1 Thessalonicher',
    '2 thessalonicher' => '2 Thessalonicher',
    '1 timotheus' => '1 Timotheus',
    '2 timotheus' => '2 Timotheus',
    'titus' => 'Titus',
    'philemon' => 'Philemon',
    'hebräer' => 'Hebräer',
    'jakobus' => 'Jakobus',
    '1 petrus' => '1 Petrus',
    '2 petrus' => '2 Petrus',
    '1 johannes' => '1 Johannes',
    '2 johannes' => '2 Johannes',
    '3 johannes' => '3 Johannes',
    'judas' => 'Judas',
    'offenbarung' => 'Offenbarung',
  );

  public static $alternativnamen = array (
    '1 Mose = Genesis' => 'Genesis',
    '2 Mose = Exodus' => 'Exodus',
    '3 Mose = Levitikus' => 'Levitikus',
    '4 Mose = Deuteronomium' => 'Deuteronomium',
    '5 Mose = Numeri' => 'Numeri',
    'Abdias = Obadja' => 'Obadja',
    'Aggäus = Haggai' => 'Haggai',
    'Apokalypse = Offenbarung' => 'Offenbarung',
    'Daniel 13 = Susanna' => 'Susanna (Daniel, griechische Version)',
    'Daniel 14 = Bel' => 'Bel (Daniel, griechische Version)',
    'Ecclesiastes = Kohelet' => 'Kohelet',
    'Ecclesiasticus = Jesus Sirach' => 'Jesus Sirach',
    'Gebet Manasses = Ode 12' => 'Ode 12',
    'Hesekiel = Ezechiel' => 'Ezechiel',
    'Isaias = Jesaja' => 'Jesaja',
    'Hiob = Ijob' => 'Ijob',
    'Job = Ijob' => 'Ijob',
    'Jeremia-Brief' => 'Brief des Jeremia',
    'Manasses Gebet = Ode 12' => 'Ode 12',
    'Osee = Hosea' => 'Hosea',
    'Prolog Jesus Sirach' => 'Jesus Sirach Prolog',
    'Prediger = Kohelet' => 'Kohelet',
    'Sirach' => 'Jesus Sirach',
    'Sophonias = Zefanja' => 'Zefanja',
    'Zacharias = Sacharja' => 'Sacharja',
  );

  public static $kurznamen = array (
    'Bel (Daniel, griechische Version)' => 'Bel',
    'Daniel (griechische Version)' => 'Daniel, griech. Version',
    'Ester (griechische Version)' => 'Ester, griech. Version',
    'Esra (griechische Ergänzung)' => 'Esra, griech. Ergänzungen',
    'Esra (lateinische Ergänzung)' => 'Esra, latein. Ergänzungen',
    'Psalmen (Ergänzungen)' => 'Psalmen, Ergänzungen',
    'Psalmen des Salomo' => 'Psalmen Salomos',
    'Susanna (Daniel, griechische Version)' => 'Susanna',
    'Sprichwörter' => 'Sprichwörter (Sprüche)',
    'Tobit' => 'Tobit (Tobias)',
  );

  public static $pluralnamen = array (
    'Psalm' => 'Psalmen',
    'Psalm (Ergänzung)' => 'Psalmen (Ergänzungen)',
    'Psalm des Salomo' => 'Psalmen des Salomo',
    'Ode' => 'Oden',
  );

  public static $versnummern = array (
    'Genesis' => array (1=>31, 25, 24, 26, 32, 22, 24, 22, 29, 32, 32, 20, 18, 24, 21, 16, 27, 33, 38, 18, 34, 24, 20, 67, 34, 35, 46, 22, 35, 43, 54, 33, 20, 31, 29, 43, 36, 30, 23, 23, 57, 38, 34, 34, 28, 34, 31, 22, 33, 26, ),
    'Exodus' => array (1=>22, 25, 22, 31, 23, 30, 29, 28, 35, 29, 10, 51, 22, 31, 27, 36, 16, 27, 25, 26, 37, 30, 33, 18, 40, 37, 21, 43, 46, 38, 18, 35, 23, 35, 35, 38, 29, 31, 43, 38, ),
    'Levitikus' => array (1=>17, 16, 17, 35, 26, 23, 38, 36, 24, 20, 47, 8, 59, 57, 33, 34, 16, 30, 37, 27, 24, 33, 44, 23, 55, 46, 34, ),
    'Numeri' => array (1=>54, 34, 51, 49, 31, 27, 89, 26, 23, 36, 35, 16, 33, 45, 41, 35, 28, 32, 22, 29, 35, 41, 30, 25, 19, 65, 23, 31, 39, 17, 54, 42, 56, 29, 34, 13, ),
    'Deuteronomium' => array (1=>46, 37, 29, 49, 33, 25, 26, 20, 29, 22, 32, 31, 19, 29, 23, 22, 20, 22, 21, 20, 23, 29, 26, 22, 19, 19, 26, 69, 28, 20, 30, 52, 29, 12, ),
    'Josua' => array (1=>18, 24, 17, 24, 15, 27, 26, 35, 27, 43, 23, 24, 33, 15, 63, 10, 18, 28, 51, 9, 45, 34, 16, 33, ),
    'Richter' => array (1=>36, 23, 31, 24, 31, 40, 25, 35, 57, 18, 40, 15, 25, 20, 20, 31, 13, 31, 30, 48, 25, ),
    '1 Samuel' => array (1=>28, 36, 21, 22, 12, 21, 17, 22, 27, 27, 15, 25, 23, 52, 35, 23, 58, 30, 24, 42, 16, 23, 28, 23, 44, 25, 12, 25, 11, 31, 13, ),
    '2 Samuel' => array (1=>27, 32, 39, 12, 25, 23, 29, 18, 13, 19, 27, 31, 39, 33, 37, 23, 29, 32, 44, 26, 22, 51, 39, 25, ),
    '1 Könige' => array (1=>53, 46, 28, 20, 32, 38, 51, 66, 28, 29, 43, 33, 34, 31, 34, 34, 24, 46, 21, 43, 29, 54, ),
    '2 Könige' => array (1=>18, 25, 27, 44, 27, 33, 20, 29, 37, 36, 20, 22, 25, 29, 38, 20, 41, 37, 37, 21, 26, 20, 37, 20, 30, ),
    'Jesaja' => array (1=>31, 22, 26, 6, 30, 13, 25, 23, 20, 34, 16, 6, 22, 32, 9, 14, 14, 7, 25, 6, 17, 25, 18, 23, 12, 21, 13, 29, 24, 33, 9, 20, 24, 17, 10, 22, 38, 22, 8, 31, 29, 25, 28, 28, 25, 13, 15, 22, 26, 11, 23, 15, 12, 17, 13, 12, 21, 14, 21, 22, 11, 12, 19, 11, 25, 24, ),
    'Jeremia' => array (1=>19, 37, 25, 31, 31, 30, 34, 23, 25, 25, 23, 17, 27, 22, 21, 21, 27, 23, 15, 18, 14, 30, 40, 10, 38, 24, 22, 17, 32, 24, 40, 44, 26, 22, 19, 32, 21, 28, 18, 16, 18, 22, 13, 30, 5, 28, 7, 47, 39, 46, 64, 34, ),
    'Ezechiel' => array (1=>28, 10, 27, 17, 17, 14, 27, 18, 11, 22, 25, 28, 23, 23, 8, 63, 24, 32, 14, 44, 37, 31, 49, 27, 17, 21, 36, 26, 21, 26, 18, 32, 33, 31, 15, 38, 28, 23, 29, 49, 26, 20, 27, 31, 25, 24, 23, 35, ),
    'Hosea' => array (1=>9, 25, 5, 19, 15, 11, 16, 14, 17, 15, 11, 15, 15, 10, ),
    'Joel' => array (1=>20, 27, 5, 21, ),
    'Amos' => array (1=>15, 16, 15, 13, 27, 14, 17, 14, 15, ),
    'Obadja' => array (21, ),
    'Jona' => array (1=>16, 11, 10, 11, ),
    'Micha' => array (1=>16, 13, 12, 14, 14, 16, 20, ),
    'Nahum' => array (1=>14, 14, 19, ),
    'Habakuk' => array (1=>17, 20, 19, ),
    'Zefanja' => array (1=>18, 15, 20, ),
    'Haggai' => array (1=>15, 23, ),
    'Sacharja' => array (1=>17, 17, 10, 14, 11, 15, 14, 23, 17, 12, 17, 14, 9, 21, ),
    'Maleachi' => array (1=>14, 17, 24, ),
    'Psalm' => array (1=>6, 12, 9, 9, 13, 11, 18, 10, 21, 18, 7, 9, 6, 7, 5, 11, 15, 51, 15, 10, 14, 32, 6, 10, 22, 12, 14, 9, 11, 13, 25, 11, 22, 23, 28, 13, 40, 23, 14, 18, 14, 12, 5, 27, 18, 12, 10, 15, 21, 23, 21, 11, 7, 9, 24, 14, 12, 12, 18, 14, 9, 13, 12, 11, 14, 20, 8, 36, 37, 6, 24, 20, 28, 23, 11, 13, 21, 72, 13, 20, 17, 8, 19, 13, 14, 17, 7, 19, 53, 17, 16, 16, 5, 23, 11, 13, 12, 9, 9, 5, 8, 29, 22, 35, 45, 48, 43, 14, 31, 7, 10, 10, 9, 8, 18, 19, 2, 29, 176, 7, 8, 9, 4, 8, 5, 6, 5, 6, 8, 8, 3, 18, 3, 3, 21, 26, 9, 8, 24, 14, 10, 8, 12, 15, 21, 10, 20, 14, 9, 6, ),
    'Psalm (Ergänzung)' => array (151=>7, ),
    'Ijob' => array (1=>22, 13, 26, 21, 27, 30, 21, 22, 35, 22, 20, 25, 28, 22, 35, 22, 16, 21, 29, 29, 34, 30, 17, 25, 6, 14, 23, 28, 25, 31, 40, 22, 33, 37, 16, 33, 24, 41, 30, 32, 26, 17, ),
    'Sprichwörter' => array (1=>33, 22, 35, 27, 23, 35, 27, 36, 18, 32, 31, 28, 25, 35, 33, 33, 28, 24, 29, 30, 31, 29, 35, 34, 28, 28, 27, 28, 27, 33, 31, ),
    'Rut' => array (1=>22, 23, 18, 22, ),
    'Hohelied' => array (1=>17, 17, 11, 16, 16, 12, 14, 14, ),
    'Kohelet' => array (1=>18, 26, 22, 17, 19, 12, 29, 17, 18, 20, 10, 14, ),
    'Klagelieder' => array (1=>22, 22, 66, 22, 22, ),
    'Ester' => array (1=>22, 23, 15, 17, 14, 14, 10, 17, 32, 3, ),
    'Ester (griechische Version)' => array (
      1=>array ('1a', '1b', '1c', '1d', '1e', '1f', '1g', '1h', '1i', '1j', '1k', '1l', '1m', '1n', '1o', '1q', '1r', '1s', 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20, 21, 22),
      23,
      array (1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, '13a', '13b', '13c', '13d', '13e', '13f', '13g', 14, 15),
      array (1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, '17a', '17b', '17c', '17d', '17e', '17f', '17g', '17h', '17i', '17k', '17l', '17m', '17n', '17o', '17p', '17q', '17r', '17s', '17t', '17u', '17w', '17x', '17y', '17z', ),
      array (1, '1a', '1b', '1c', '1d', '1e', '1f', 2, '2a', '2b', 3, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14),
      14,
      10,
      array (1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, '12a', '12b', '12c', '12d', '12e', '12f', '12g', '12h', '12i', '12l', '12m', '12n', '12o', '12p', '12q', '12r', '12s', '12t', '12u', '12x', 13, 14, 15, 16, 17),
      32,
      array (1, 2, 3, '3a', '3b', '3c', '3d', '3e', '3f', '3g', '3h', '3i', '3k', '3l', ),
    ),
    'Daniel' => array (1=>21, 49, 33, 34, 30, 29, 28, 27, 27, 21, 45, 13, ),
    'Susanna (Daniel, griechische Version)' => array (64, ),
    'Daniel (griechische Version)' => array (1=>21, 49, 97, 37, array (0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20, 21, 22, 23, 24, 25, 26, 27, 28, 29, 30, ), 29, 28, 28, 28, 21, 45, 14, ),
    'Bel (Daniel, griechische Version)' => array (array (0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20, 21, 22, 23, 24, 25, 26, 27, 28, 29, 30, 31, 32, 33, 34, 35, 36, 37, 38, 39, 40, 41, 42, ), ),
    'Esra' => array (1=>11, 70, 13, 24, 17, 22, 28, 36, 15, 44, ),
    'Esra (griechische Ergänzung)' => array (1=>55, 26, 24, 63, 71, 33, 15, 92, 55, ),
    'Esra (lateinische Ergänzung)' => array (1=>40, 48, 36, 52, 55, 59, 139, 63, 47, 60, 46, 51, 58, 47, 63, 78, ),
    'Nehemia' => array (1=>11, 20, 38, 17, 19, 19, 72, 18, 37, 40, 36, 47, 31, ),
    '1 Chronik' => array (1=>54, 55, 24, 43, 41, 66, 40, 40, 44, 14, 47, 41, 14, 17, 29, 43, 27, 17, 19, 8, 30, 19, 32, 31, 31, 32, 34, 21, 30, ),
    '2 Chronik' => array (1=>18, 17, 17, 22, 14, 42, 22, 18, 31, 19, 23, 16, 23, 14, 19, 14, 19, 34, 11, 37, 20, 12, 21, 27, 28, 23, 9, 27, 36, 27, 21, 33, 25, 33, 27, 23, ),
    'Judit' => array (1=>16, 28, 10, 15, 24, 21, 32, 36, 14, 23, 23, 20, 20, 19, 14, 25, ),
    'Tobit' => array (1=>22, 14, 17, 21, 23, 19, 17, 21, 6, 14, 19, 22, 18, 15, ),
    '1 Makkabäer' => array (1=>64, 70, 60, 61, 68, 63, 50, 32, 73, 89, 74, 53, 53, 49, 41, 24, ),
    '2 Makkabäer' => array (1=>36, 32, 40, 50, 27, 31, 42, 36, 29, 38, 38, 45, 26, 46, 39, ),
    '3 Makkabäer' => array (1=>29, 33, 30, 21, 51, 41, 23, ),
    '4 Makkabäer' => array (1=>35, 24, 21, 26, 38, 35, 23, 29, 32, 21, 27, 19, 27, 20, 32, 25, 24, 24, ),
    'Ode' => array (
      1=>array (0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, ),
      array (0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20, 21, 22, 23, 24, 25, 26, 27, 28, 29, 30, 31, 32, 33, 34, 35, 36, 37, 38, 39, 40, 41, 42, 43, ),
      array (0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, ),
      19,
      array (0, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20, ),
      array (0, 3, 4, 5, 6, 7, 8, 9, 10, ),
      array (0, 26, 27, 28, 29, 30, 31, 32, 33, 34, 35, 36, 37, 38, 39, 40, 41, 42, 43, 44, 45, ),
      array (0, 52, 53, 54, 55, 56, 57, 58, 59, 60, 61, 62, 63, 64, 65, 66, 67, 68, 69, 70, 71, 72, 73, 74, 75, 76, 77, 78, 79, 80, 81, 82, 83, 84, 85, 86, 87, 88, ),
      array (0, 46, 47, 48, 49, 50, 51, 52, 53, 54, 55, 68, 69, 70, 71, 72, 73, 74, 75, 76, 77, 78, 79, ),
      array (0, 1, 2, 3, 4, 5, 6, 7, 8, 9, ),
      array (0, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20),
      array (0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, ),
      array (0, 29, 30, 31, 32, ),
      array (0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20, 21, 22, 23, 24, 25, 26, 27, 28, 29, 30, 31, 32, 33, 34, 35, 36, 37, 38, 39, 40, 41, 42, 43, 44, 45, 46, ),
    ),
    'Weisheit' => array (1=>16, 24, 19, 20, 23, 25, 30, 21, 18, 21, 26, 27, 19, 31, 19, 29, 20, 25, 22, ),
    'Jesus Sirach' => array ('Prolog'=>array (0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20, 21, 22, 23, 24, 25, 26, 27, 28, 29, 30, 31, 32, 33, 34, 35, 36, ), 1=>30, 18, 31, 31, 15, 37, 36, 19, 18, 31, 34, 18, 26, 27, 20, 30, 32, 33, 30, 31, 28, 27, 27, 34, 26, 29, 30, 26, 28, 25, 31, 24, 33, 26, 24, 27, 31, 34, 35, 30, 27, 25, 33, 23, 26, 20, 25, 25, 16, 29, 30, ),
    'Psalm des Salomo' => array (
      1=>8,
      array (0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20, 21, 22, 23, 24, 25, 26, 27, 28, 29, 30, 31, 32, 33, 34, 35, 36, 37, ),
      array (0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, ),
      array (0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20, 21, 22, 23, 24, 25, ),
      array (0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, ),
      array (0, 1, 2, 3, 4, 5, 6, ),
      array (0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, ),
      array (0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20, 21, 22, 23, 24, 25, 26, 27, 28, 29, 30, 31, 32, 33, 34, ),
      array (0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, ),
      array (0, 1, 2, 3, 4, 5, 6, 7, 8, ),
      array (0, 1, 2, 3, 4, 5, 6, 7, 8, 9, ),
      array (0, 1, 2, 3, 4, 5, 6, ),
      array (0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, ),
      array (0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, ),
      array (0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, ),
      array (0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, ),
      array (0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20, 21, 22, 23, 24, 25, 26, 27, 28, 29, 30, 31, 32, 33, 34, 35, 36, 37, 38, 39, 40, 41, 42, 43, 44, 45, 46, ),
      array (0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, ),
    ),
    'Baruch' => array (1=>22, 35, 38, 37, 9, ),
    'Brief des Jeremia' => array (array (0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20, 21, 22, 23, 24, 25, 26, 27, 28, 29, 30, 31, 32, 33, 34, 35, 36, 37, 38, 39, 40, 41, 42, 43, 44, 45, 46, 47, 48, 49, 50, 51, 52, 53, 54, 55, 68, 69, 70, 71, 72, ), ),
    'Matthäus' => array (1=>25, 23, 17, 25, 48, 34, 29, 34, 38, 42, 30, 50, 58, 36, 39, 28, 27, 35, 30, 34, 46, 46, 39, 51, 46, 75, 66, 20, ),
    'Markus' => array (1=>45, 28, 35, 41, 43, 56, 37, 38, 50, 52, 33, 44, 37, 72, 47, 20, ),
    'Lukas' => array (1=>80, 52, 38, 44, 39, 49, 50, 56, 62, 42, 54, 59, 35, 35, 32, 31, 37, 43, 48, 47, 38, 71, 56, 53, ),
    'Johannes' => array (1=>51, 25, 36, 54, 47, 71, 53, 59, 41, 42, 57, 50, 38, 31, 27, 33, 26, 40, 42, 31, 25, ),
    'Apostelgeschichte' => array (1=>26, 47, 26, 37, 42, 15, 60, 40, 43, 48, 30, 25, 52, 28, 41, 40, 34, 28, 40, 38, 40, 30, 35, 27, 27, 32, 44, 31, ),
    'Römer' => array (1=>32, 29, 31, 25, 21, 23, 25, 39, 33, 21, 36, 21, 14, 23, 33, 27, ),
    '1 Korinther' => array (1=>31, 16, 23, 21, 13, 20, 40, 13, 27, 33, 34, 31, 13, 40, 58, 24, ),
    '2 Korinther' => array (1=>24, 17, 18, 18, 21, 18, 16, 24, 15, 18, 33, 21, 13, ),
    'Galater' => array (1=>24, 21, 29, 31, 26, 18, ),
    'Epheser' => array (1=>23, 22, 21, 32, 33, 24, ),
    'Philipper' => array (1=>30, 30, 21, 23, ),
    'Kolosser' => array (1=>29, 23, 25, 18, ),
    '1 Thessalonicher' => array (1=>10, 20, 13, 18, 28, ),
    '2 Thessalonicher' => array (1=>12, 17, 18, ),
    '1 Timotheus' => array (1=>20, 15, 16, 16, 25, 21, ),
    '2 Timotheus' => array (1=>18, 26, 17, 22, ),
    'Titus' => array (1=>16, 15, 15, ),
    'Philemon' => array (25, ),
    'Hebräer' => array (1=>14, 18, 19, 16, 14, 20, 28, 13, 28, 39, 40, 29, 25, ),
    'Jakobus' => array (1=>27, 26, 18, 17, 20, ),
    '1 Petrus' => array (1=>25, 25, 22, 19, 14, ),
    '2 Petrus' => array (1=>21, 22, 18, ),
    '1 Johannes' => array (1=>10, 29, 24, 21, 21, ),
    '2 Johannes' => array (13, ),
    '3 Johannes' => array (15, ),
    'Judas' => array (25, ),
    'Offenbarung' => array (1=>20, 29, 22, 11, 14, 17, 17, 13, 21, 11, 19, 18, 18, 20, 8, 21, 18, 24, 21, 15, 27, 21, ),
  );

  private static $teilworte = array (
    'abd' => 'obadja',
    'abdias' => 'obadja',
    'ac' => 'apostelgeschichte',
    'act' => 'apostelgeschichte',
    'acta' => 'apostelgeschichte',
    'acts' => 'apostelgeschichte',
    'add' => 'ad',
    'addition' => 'ad',
    'additional' => 'ad',
    'additions' => 'ad',
    'ag' => 'haggai',
    'agg' => 'haggai',
    'aggaeus' => 'haggai',
    'aggäus' => 'haggai',
    'am' => 'amos',
    'ambacum' => 'habakuk',
    'amo' => 'amos',
    'an' => '',
    'anfaenge' => 'genesis',
    'anfang' => 'genesis',
    'anfange' => 'genesis',
    'anfänge' => 'genesis',
    'ap' => 'offenbarung',
    'apc' => 'offenbarung',
    'apg' => 'apostelgeschichte',
    'apk' => 'offenbarung',
    'apoc' => 'offenbarung',
    'apocalypse' => 'offenbarung',
    'apocalypsis' => 'offenbarung',
    'apokalypse' => 'offenbarung',
    'apokalypsis' => 'offenbarung',
    'apostolorum' => 'apostelgeschichte',
    'at' => 'apostelgeschichte',
    'auszug' => 'exodus',
    'az' => 'asarja',
    'azar' => 'asarja',
    'azariah' => 'asarja',
    'ba' => 'baruch',
    'bar' => 'baruch',
    'begebenheiten' => 'chronik',
    'bemidbar' => 'numeri',
    'ben' => 'sirach',
    'bereschit' => 'genesis',
    'buch' => '',
    'cant' => 'hohelied',
    'canticle' => 'hohelied',
    'canticles' => 'hohelied',
    'canticorum' => 'hohelied',
    'canticum' => 'hohelied',
    'ch' => 'chronik',
    'chabakkuk' => 'habakuk',
    'chaggai' => 'haggai',
    'chr' => 'chronik',
    'chron' => 'chronik',
    'chronica' => 'chronik',
    'chronicles' => 'chronik',
    'cnt' => 'hohelied',
    'co' => 'korinther',
    'coh' => 'kohelet',
    'cohelet' => 'kohelet',
    'coheleth' => 'kohelet',
    'col' => 'kolosser',
    'collossians' => 'kolosser',
    'collossions' => 'kolosser',
    'colossenses' => 'kolosser',
    'colossians' => 'kolosser',
    'colossions' => 'kolosser',
    'cor' => 'korinther',
    'corinthians' => 'korinther',
    'corinthions' => 'korinther',
    'corinthios' => 'korinther',
    'cr' => 'chronik',
    'cro' => 'chronik',
    'ct' => 'hohelied',
    'dan' => 'daniel',
    'danijel' => 'daniel',
    'daniël' => 'daniel',
    'das' => '',
    'debarim' => 'deuteronomium',
    'der' => '',
    'des' => '',
    'deu' => 'deuteronomium',
    'deut' => 'deuteronomium',
    'deuteronomy' => 'deuteronomium',
    'die' => '',
    'dn' => 'daniel',
    'dritte' => '3',
    'dritter' => '3',
    'drittes' => '3',
    'dt' => 'deuteronomium',
    'dtjes' => 'jesaja',
    'dtn' => 'deuteronomium',
    'ebr' => 'hebräer',
    'ec' => 'kohelet',
    'ecc' => 'kohelet',
    'eccl' => 'kohelet',
    'eccle' => 'kohelet',
    'eccles' => 'kohelet',
    'ecclesiastes' => 'kohelet',
    'ecclesiasticus' => 'sirach',
    'eccli' => 'sirach',
    'ecl' => 'kohelet',
    'ef' => 'epheser',
    'efeser' => 'epheser',
    'ep' => 'brief',
    'eph' => 'epheser',
    'epheserbrief' => 'epheser',
    'ephesians' => 'epheser',
    'ephesions' => 'epheser',
    'ephesios' => 'epheser',
    'epist' => 'brief',
    'epistel' => 'brief',
    'epistle' => 'brief',
    'epistula' => 'brief',
    'ergaenzung' => 'ad',
    'ergaenzungen' => 'ad',
    'erganzung' => 'ad',
    'erganzungen' => 'ad',
    'ergänzung' => 'ad',
    'ergänzungen' => 'ad',
    'erste' => '1',
    'erster' => '1',
    'erstes' => '1',
    'erz' => 'esra',
    'esaias' => 'jesaja',
    'esajas' => 'jesaja',
    'esd' => 'esra',
    'esdrae' => 'esra',
    'esdras' => 'esra',
    'esdrä' => 'esra',
    'esr' => 'esra',
    'est' => 'ester',
    'esth' => 'ester',
    'esther' => 'ester',
    'eu' => 'evangelium',
    'euan' => 'evangelium',
    'euangelion' => 'evangelium',
    'euangelium' => 'evangelium',
    'ev' => 'evangelium',
    'evan' => 'evangelium',
    'evangelion' => 'evangelium',
    'ex' => 'exodus',
    'exo' => 'exodus',
    'exod' => 'exodus',
    'extra' => 'ad',
    'ez' => 'ezechiel',
    'eze' => 'ezechiel',
    'ezechiël' => 'ezechiel',
    'ezek' => 'ezechiel',
    'ezekiel' => 'ezechiel',
    'ezekiël' => 'ezechiel',
    'ezk' => 'ezechiel',
    'ezr' => 'esra',
    'ezra' => 'esra',
    'fassung' => '',
    'fassungen' => '',
    'fil' => 'philipper',
    'filem' => 'philemon',
    'filemon' => 'philemon',
    'filiper' => 'philipper',
    'filipper' => 'philipper',
    'filliper' => 'philipper',
    'fillipper' => 'philipper',
    'first' => '1',
    'flm' => 'philemon',
    'flp' => 'philipper',
    'fm' => 'philemon',
    'fourth' => '4',
    'fuenfte' => '5',
    'fuenfter' => '5',
    'fuenftes' => '5',
    'funfte' => '5',
    'funfter' => '5',
    'funftes' => '5',
    'fünfte' => '5',
    'fünfter' => '5',
    'fünftes' => '5',
    'g' => 'griechisch',
    'ga' => 'galater',
    'gal' => 'galater',
    'galatas' => 'galater',
    'galaterbrief' => 'galater',
    'galasians' => 'galater',
    'galatians' => 'galater',
    'galations' => 'galater',
    'galatter' => 'galater',
    'galatterbrief' => 'galater',
    'gallasions' => 'galater',
    'gallater' => 'galater',
    'gallatians' => 'galater',
    'gallations' => 'galater',
    'gallaterbrief' => 'galater',
    'gallatter' => 'galater',
    'gallatterbrief' => 'galater',
    'gdc' => 'richter',
    'gdt' => 'judit',
    'gen' => 'genesis',
    'ger' => 'jeremia',
    'gesetz' => 'mose',
    'gesetze' => 'mose',
    'gesetzes' => 'mose',
    'gesaenge' => 'hohelied',
    'gesang' => 'hohelied',
    'gesange' => 'hohelied',
    'gesänge' => 'hohelied',
    'giac' => 'jakobus',
    'giov' => 'johannes',
    'giuda' => 'judas',
    'gl' => 'joel',
    'gleichspruche' => 'sprichwörter',
    'gleichsprueche' => 'sprichwörter',
    'gleichsprüche' => 'sprichwörter',
    'gospel' => 'evangelium',
    'gr' => 'griechisch',
    'graecae' => 'griechisch',
    'graecum' => 'griechisch',
    'greek' => 'griechisch',
    'griechische' => 'griechisch',
    'griechisches' => 'griechisch',
    'gräcum' => 'griechisch',
    'gräcä' => 'griechisch',
    'gs' => 'josua',
    'gv' => 'johannes',
    'h' => 'hebräer',
    'ha' => 'habakuk',
    'hab' => 'habakuk',
    'habacuc' => 'habakuk',
    'habakkuk' => 'habakuk',
    'habbakkuk' => 'habakuk',
    'habbakuk' => 'habakuk',
    'hag' => 'haggai',
    'hagai' => 'haggai',
    'hb' => 'hebräer',
    'hbr' => 'hebräer',
    'hch' => 'apostelgeschichte',
    'he' => 'hebräer',
    'heb' => 'hebräer',
    'hebr' => 'hebräer',
    'hebraeer' => 'hebräer',
    'hebraeerbrief' => 'hebräer',
    'hebraeos' => 'hebräer',
    'hebraer' => 'hebräer',
    'hebraerbrief' => 'hebräer',
    'hebrews' => 'hebräer',
    'hebräerbrief' => 'hebräer',
    'hes' => 'ezechiel',
    'hesekiel' => 'ezechiel',
    'hesekiël' => 'ezechiel',
    'hg' => 'haggai',
    'hgg' => 'haggai',
    'hhld' => 'hohelied',
    'hi' => 'ijob',
    'hiob' => 'ijob',
    'hl' => 'hohelied',
    'hld' => 'hohelied',
    'hohe' => 'hohelied',
    'hohen' => 'hohelied',
    'hohes' => 'hohelied',
    'hos' => 'hosea',
    'hoschea' => 'hosea',
    'iac' => 'jakobus',
    'iacobi' => 'jakobus',
    'iacobus' => 'jakobus',
    'ib' => 'ijob',
    'ic' => 'richter',
    'idc' => 'richter',
    'ids' => 'judas',
    'idt' => 'judit',
    'idth' => 'judit',
    'ier' => 'jeremia',
    'ieremia' => 'jeremia',
    'ieremiae' => 'jeremia',
    'ieremias' => 'jeremia',
    'ieremiä' => 'jeremia',
    'ieremiou' => 'jeremia',
    'ies' => 'jesaja',
    'iesaia' => 'jesaja',
    'iesaias' => 'jesaja',
    'iesekiel' => 'ezechiel',
    'iesekiël' => 'ezechiel',
    'iezekiel' => 'ezechiel',
    'iezekiël' => 'ezechiel',
    'ij' => 'ijob',
    'il' => 'joel',
    'io' => 'johannes',
    'ioannes' => 'johannes',
    'ioannis' => 'johannes',
    'iob' => 'ijob',
    'ioe' => 'joel',
    'ioel' => 'joel',
    'ioh' => 'johannes',
    'iohannes' => 'johannes',
    'iohannis' => 'johannes',
    'ion' => 'jona',
    'iona' => 'jona',
    'ionannes' => 'johannes',
    'ionannis' => 'johannes',
    'ionas' => 'jona',
    'ios' => 'josua',
    'iosua' => 'josua',
    'iosue' => 'josua',
    'ioë' => 'joel',
    'ioël' => 'joel',
    'ir' => 'jeremia',
    'is' => 'jesaja',
    'isa' => 'jesaja',
    'isaia' => 'jesaja',
    'isaiah' => 'jesaja',
    'isaias' => 'jesaja',
    'iudas' => 'judas',
    'iudices' => 'richter',
    'iudicum' => 'richter',
    'j' => 'johannes',
    'jac' => 'jakobus',
    'jacobi' => 'jakobus',
    'jacobus' => 'jakobus',
    'jak' => 'jakobus',
    'jakobusbrief' => 'jakobus',
    'jam' => 'jakobus',
    'james' => 'jakobus',
    'jas' => 'jakobus',
    'jb' => 'ijob',
    'jd' => 'judas',
    'jdc' => 'richter',
    'jdg' => 'richter',
    'jds' => 'judas',
    'jdt' => 'judit',
    'jdth' => 'judit',
    'jecheskel' => 'ezechiel',
    'jeheskel' => 'ezechiel',
    'jehoschua' => 'josua',
    'jer' => 'jeremia',
    'jeremiae' => 'jeremia',
    'jeremiah' => 'jeremia',
    'jeremias' => 'jeremia',
    'jeremiä' => 'jeremia',
    'jeremiou' => 'jeremia',
    'jes' => 'jesaja',
    'jesaia' => 'jesaja',
    'jesaias' => 'jesaja',
    'jesajas' => 'jesaja',
    'jeschajahu' => 'jesaja',
    'jesekiel' => 'ezechiel',
    'jesekiël' => 'ezechiel',
    'jesus' => 'sirach',
    'jezekiel' => 'ezechiel',
    'jezekiël' => 'ezechiel',
    'jg' => 'richter',
    'jirmejahu' => 'jeremia',
    'jl' => 'joel',
    'jm' => 'jakobus',
    'jn' => 'johannes',
    'jo' => 'johannes',
    'joannes' => 'johannes',
    'joannis' => 'johannes',
    'job' => 'ijob',
    'joe' => 'joel',
    'joh' => 'johannes',
    'johannesevangelium' => 'johannes',
    'johannis' => 'johannes',
    'john' => 'johannes',
    'jon' => 'jona',
    'jonah' => 'jona',
    'jonannes' => 'johannes',
    'jonannis' => 'johannes',
    'jonas' => 'jona',
    'jos' => 'josua',
    'josh' => 'josua',
    'joshua' => 'josua',
    'josue' => 'josua',
    'joë' => 'joel',
    'joël' => 'joel',
    'jr' => 'jeremia',
    'js' => 'jesaja',
    'jud' => 'judas',
    'jude' => 'judas',
    'judg' => 'richter',
    'judges' => 'richter',
    'judices' => 'richter',
    'judicum' => 'richter',
    'judith' => 'judit',
    'k' => 'korinther',
    'kgs' => 'könige',
    'ki' => 'könige',
    'king' => 'könige',
    'kingdom' => 'kingdoms',
    'kings' => 'könige',
    'klg' => 'klagelieder',
    'klgl' => 'klagelieder',
    'koe' => 'könige',
    'koen' => 'könige',
    'koenige' => 'könige',
    'koh' => 'kohelet',
    'koheleth' => 'kohelet',
    'kol' => 'kolosser',
    'kolloser' => 'kolosser',
    'kollosser' => 'kolosser',
    'kolloserbrief' => 'kolosser',
    'kollosserbrief' => 'kolosser',
    'koloser' => 'kolosser',
    'koloserbrief' => 'kolosser',
    'kolosserbrief' => 'kolosser',
    'kon' => 'könige',
    'konige' => 'könige',
    'kor' => 'korinther',
    'korinter' => 'korinther',
    'korinterbrief' => 'korinther',
    'korintherbrief' => 'korinther',
    'kö' => 'könige',
    'kön' => 'könige',
    'l' => 'lateinisch',
    'lam' => 'klagelieder',
    'lamentationes' => 'klagelieder',
    'lamentations' => 'klagelieder',
    'lat' => 'lateinisch',
    'latein' => 'lateinisch',
    'lateinische' => 'lateinisch',
    'lateinisches' => 'lateinisch',
    'latin' => 'lateinisch',
    'lc' => 'lukas',
    'letter' => 'brief',
    'leu' => 'levitikus',
    'leuticus' => 'levitikus',
    'lev' => 'levitikus',
    'leviticus' => 'levitikus',
    'lied' => 'hohelied',
    'lieder' => 'hohelied',
    'lk' => 'lukas',
    'lm' => 'klagelieder',
    'luk' => 'lukas',
    'lukasevangelium' => 'lukas',
    'luke' => 'lukas',
    'lv' => 'levitikus',
    'lxx' => 'griechisch',
    'ma' => 'makkabäer',
    'mac' => 'makkabäer',
    'macabee' => 'makkabäer',
    'macabees' => 'makkabäer',
    'maccabee' => 'makkabäer',
    'maccabees' => 'makkabäer',
    'machabaeorum' => 'makkabäer',
    'machabäorum' => 'makkabäer',
    'makabaeer' => 'makkabäer',
    'makabaer' => 'makkabäer',
    'makabäer' => 'makkabäer',
    'makkabaeer' => 'makkabäer',
    'makkabaer' => 'makkabäer',
    'mal' => 'maleachi',
    'malachi' => 'maleachi',
    'malachias' => 'maleachi',
    'man' => 'manasse',
    'manase' => 'manasse',
    'manaseh' => 'manasse',
    'manases' => 'manasse',
    'manasseh' => 'manasse',
    'manasseh' => 'manasse',
    'manasses' => 'manasse',
    'manasses' => 'manasse',
    'mannasse' => 'manasse',
    'mannasseh' => 'manasse',
    'mannasses' => 'manasse',
    'mar' => 'markus',
    'mark' => 'markus',
    'markusevangelium' => 'markus',
    'mat' => 'matthäus',
    'mathaeus' => 'matthäus',
    'mathaus' => 'matthäus',
    'mathew' => 'matthäus',
    'mathäus' => 'matthäus',
    'matt' => 'matthäus',
    'mattaeus' => 'matthäus',
    'mattaus' => 'matthäus',
    'mattew' => 'matthäus',
    'matthaeus' => 'matthäus',
    'matthaeusevangelium' => 'matthäus',
    'matthaus' => 'matthäus',
    'matthausevangelium' => 'matthäus',
    'matthew' => 'matthäus',
    'matthäusevangelium' => 'matthäus',
    'mattäus' => 'matthäus',
    'mc' => 'markus',
    'mcc' => 'makkabäer',
    'mch' => 'micha',
    'melachim' => 'könige',
    'mi' => 'micha',
    'mic' => 'micha',
    'micah' => 'micha',
    'mich' => 'micha',
    'michaeas' => 'micha',
    'michäas' => 'micha',
    'mischle' => 'sprichwörter',
    'mk' => 'markus',
    'ml' => 'maleachi',
    'mo' => 'mose',
    'mos' => 'mose',
    'moses' => 'mose',
    'mt' => 'matthäus',
    'mz' => 'matthäus',
    'nachum' => 'nahum',
    'nah' => 'nahum',
    'namen' => 'exodus',
    'naum' => 'nahum',
    'nb' => 'numeri',
    'ne' => 'nehemia',
    'nechemja' => 'nehemia',
    'neh' => 'nehemia',
    'nehemiah' => 'nehemia',
    'nehemias' => 'nehemia',
    'nehemja' => 'nehemia',
    'nm' => 'numeri',
    'nu' => 'numeri',
    'num' => 'numeri',
    'numbers' => 'numeri',
    'ob' => 'obadja',
    'oba' => 'obadja',
    'obad' => 'obadja',
    'obadia' => 'obadja',
    'obadiah' => 'obadja',
    'obadias' => 'obadja',
    'obadjas' => 'obadja',
    'obd' => 'obadja',
    'od' => 'ode',
    'odae' => 'ode',
    'oden' => 'ode',
    'odes' => 'ode',
    'odä' => 'ode',
    'of' => '',
    'ofb' => 'offenbarung',
    'offb' => 'offenbarung',
    'offenb' => 'offenbarung',
    'or' => 'gebet',
    'oratio' => 'gebet',
    'os' => 'hosea',
    'osee' => 'hosea',
    'p' => 'petrus',
    'par' => 'chronik',
    'paraleipomena' => 'chronik',
    'paraleipomenon' => 'chronik',
    'paralipomena' => 'chronik',
    'paralipomenon' => 'chronik',
    'pe' => 'petrus',
    'pet' => 'petrus',
    'peter' => 'petrus',
    'petr' => 'petrus',
    'petri' => 'petrus',
    'petrusbrief' => 'petrus',
    'ph' => 'philipper',
    'phil' => 'philipper',
    'philem' => 'philemon',
    'phileman' => 'philemon',
    'philemen' => 'philemon',
    'philemin' => 'philemon',
    'philiman' => 'philemon',
    'philimen' => 'philemon',
    'philimin' => 'philemon',
    'philimon' => 'philemon',
    'philemonbrief' => 'philemon',
    'philemonem' => 'philemon',
    'philiper' => 'philipper',
    'philiperbrief' => 'philipper',
    'philipians' => 'philipper',
    'philipions' => 'philipper',
    'philippenses' => 'philipper',
    'philipperbrief' => 'philipper',
    'philippians' => 'philipper',
    'philippions' => 'philipper',
    'philleman' => 'philemon',
    'phillemen' => 'philemon',
    'phillemin' => 'philemon',
    'phillemon' => 'philemon',
    'philliman' => 'philemon',
    'phillimen' => 'philemon',
    'phillimin' => 'philemon',
    'phillimon' => 'philemon',
    'philliper' => 'philipper',
    'phillipians' => 'philipper',
    'phillipions' => 'philipper',
    'philliperbrief' => 'philipper',
    'phillipper' => 'philipper',
    'phillippians' => 'philipper',
    'phillippions' => 'philipper',
    'phillipperbrief' => 'philipper',
    'philm' => 'philemon',
    'phlip' => 'philipper',
    'phlm' => 'philemon',
    'phm' => 'philemon',
    'pr' => 'gebet',
    'pra' => 'gebet',
    'prayer' => 'gebet',
    'prazar' => 'asarja',
    'prd' => 'kohelet',
    'pred' => 'kohelet',
    'prediger' => 'kohelet',
    'preislied' => 'psalm',
    'preislieder' => 'psalm',
    'preisung' => 'psalm',
    'preisungen' => 'psalm',
    'priestertum' => 'levitikus',
    'prman' => 'manasse',
    'prou' => 'sprichwörter',
    'prouerbia' => 'sprichwörter',
    'prov' => 'sprichwörter',
    'proverbia' => 'sprichwörter',
    'proverbien' => 'sprichwörter',
    'proverbs' => 'sprichwörter',
    'prv' => 'sprichwörter',
    'ps' => 'psalm',
    'psa' => 'psalm',
    'psalmen' => 'psalm',
    'psalmi' => 'psalm',
    'psalms' => 'psalm',
    'psalter' => 'psalm',
    'ptr' => 'petrus',
    'qo' => 'kohelet',
    'qoh' => 'kohelet',
    'qohelet' => 'kohelet',
    'qoheleth' => 'kohelet',
    're' => 'könige',
    'reden' => 'deuteronomium',
    'reg' => 'könige',
    'reges' => 'könige',
    'regn' => 'könige',
    'regnorum' => 'könige',
    'regum' => 'könige',
    'rev' => 'offenbarung',
    'revelation' => 'offenbarung',
    'revelations' => 'offenbarung',
    'rg' => 'könige',
    'ri' => 'richter',
    'rm' => 'römer',
    'roem' => 'römer',
    'roemer' => 'römer',
    'roemerbrief' => 'römer',
    'rom' => 'römer',
    'romanos' => 'römer',
    'romans' => 'römer',
    'romer' => 'römer',
    'romerbrief' => 'römer',
    'rt' => 'rut',
    'rth' => 'rut',
    'ruckschau' => 'deuteronomium',
    'rueckschau' => 'deuteronomium',
    'ruth' => 'rut',
    'rv' => 'offenbarung',
    'röm' => 'römer',
    'römerbrief' => 'römer',
    'rückschau' => 'deuteronomium',
    's' => 'samuel',
    'sa' => 'samuel',
    'sach' => 'sacharja',
    'sacharia' => 'sacharja',
    'sacharias' => 'sacharja',
    'sacharjas' => 'sacharja',
    'sal' => 'psalm',
    'salomonis' => 'salomo',
    'sam' => 'samuel',
    'sang' => 'hohelied',
    'sap' => 'weisheit',
    'sapientia' => 'weisheit',
    'sb' => 'weisheit',
    'schemot' => 'exodus',
    'schemuel' => 'samuel',
    'schemuël' => 'samuel',
    'schmuel' => 'samuel',
    'schmuël' => 'samuel',
    'schofetim' => 'richter',
    'secharia' => 'sacharja',
    'secharja' => 'sacharja',
    'second' => '2',
    'sept' => 'griechisch',
    'septuagint' => 'griechisch',
    'septuaginta' => 'griechisch',
    'sir' => 'sirach',
    'sira' => 'sirach',
    'siracides' => 'sirach',
    'sm' => 'samuel',
    'so' => 'zefanja',
    'sof' => 'zefanja',
    'sol' => 'salomo',
    'solomon' => 'salomo',
    'song' => 'hohelied',
    'songs' => 'hohelied',
    'soph' => 'zefanja',
    'sophanias' => 'zefanja',
    'sophonias' => 'zefanja',
    'spr' => 'sprichwörter',
    'sprichwoerter' => 'sprichwörter',
    'sprichworter' => 'sprichwörter',
    'spruche' => 'sprichwörter',
    'sprueche' => 'sprichwörter',
    'sprüche' => 'sprichwörter',
    'st' => 'ad',
    'stuck' => 'ad',
    'stucke' => 'ad',
    'stueck' => 'ad',
    'stuecke' => 'ad',
    'stück' => 'ad',
    'stücke' => 'ad',
    'sus' => 'susanna',
    't' => 'timotheus',
    'tage' => 'chronik',
    'tb' => 'tobit',
    'tehillim' => 'psalm',
    'tesalonicher' => 'thessalonicher',
    'tesalonicherbrief' => 'thessalonicher',
    'tess' => 'thessalonicher',
    'tessalonicher' => 'thessalonicher',
    'tessalonicherbrief' => 'thessalonicher',
    'th' => 'thessalonicher',
    'the' => '',
    'thesalonicher' => 'thessalonicher',
    'thesalonicherbrief' => 'thessalonicher',
    'thess' => 'thessalonicher',
    'thessalonians' => 'thessalonicher',
    'thessalonions' => 'thessalonicher',
    'thesselonians' => 'thessalonicher',
    'thesselonicher' => 'thessalonicher',
    'thesselonions' => 'thessalonicher',
    'thessalonicenses' => 'thessalonicher',
    'thessalonicherbrief' => 'thessalonicher',
    'thesselonicherbrief' => 'thessalonicher',
    'third' => '3',
    'threni' => 'klagelieder',
    'tim' => 'timotheus',
    'timoteus' => 'timotheus',
    'timoteusbrief' => 'timotheus',
    'timotheum' => 'timotheus',
    'timotheusbrief' => 'timotheus',
    'timothy' => 'timotheus',
    'tit' => 'titus',
    'tito' => 'titus',
    'titum' => 'titus',
    'titusbrief' => 'titus',
    'tm' => 'timotheus',
    'to' => '',
    'tob' => 'tobit',
    'tobias' => 'tobit',
    'tora' => 'mose',
    'torah' => 'mose',
    'trjes' => 'jesaja',
    'ts' => 'thessalonicher',
    'tt' => 'titus',
    'versammler' => 'kohelet',
    'version' => '',
    'versionen' => '',
    'vierte' => '4',
    'vierter' => '4',
    'viertes' => '4',
    'von' => '',
    'vul' => 'lateinisch',
    'vulgata' => 'lateinisch',
    'wajjikra' => 'levitikus',
    'w' => 'weisheit',
    'we' => 'weisheit',
    'wehe' => 'klagelieder',
    'wei' => 'weisheit',
    'weis' => 'weisheit',
    'weish' => 'weisheit',
    'weisung' => 'mose',
    'weisungen' => 'mose',
    'wis' => 'weisheit',
    'wisdom' => 'weisheit',
    'ws' => 'weisheit',
    'wuestenzug' => 'numeri',
    'wustenzug' => 'numeri',
    'wüstenzug' => 'numeri',
    'za' => 'sacharja',
    'zach' => 'sacharja',
    'zacharia' => 'sacharja',
    'zacharias' => 'sacharja',
    'zacharja' => 'sacharja',
    'zc' => 'sacharja',
    'zch' => 'sacharja',
    'zec' => 'sacharja',
    'zech' => 'sacharja',
    'zechariah' => 'sacharja',
    'zef' => 'zefanja',
    'zefania' => 'zefanja',
    'zeitbegebenheiten' => 'chronik',
    'zep' => 'zefanja',
    'zeph' => 'zefanja',
    'zephania' => 'zefanja',
    'zephaniah' => 'zefanja',
    'zephanja' => 'zefanja',
    'zfanja' => 'zefanja',
    'zp' => 'zefanja',
    'zph' => 'zefanja',
    'zu' => 'ad',
    'zus' => 'ad',
    'zusaetze' => 'ad',
    'zusatz' => 'ad',
    'zusatze' => 'ad',
    'zusätze' => 'ad',
    'zweite' => '2',
    'zweiter' => '2',
    'zweites' => '2',
  );

  private static $abkürzungen = array (
    '1 ad brief korinther' => array (array ('1 korinther')),
    '1 ad brief thessalonicher' => array (array ('1 thessalonicher')),
    '1 ad brief timotheus' => array (array ('1 timotheus')),
    '1 ad korinther' => array (array ('1 korinther')),
    '1 ad thessalonicher' => array (array ('1 thessalonicher')),
    '1 ad timotheus' => array (array ('1 timotheus')),
    '1 brief johannes' => array (array ('1 johannes')),
    '1 brief korinther' => array (array ('1 korinther')),
    '1 brief petrus' => array (array ('1 petrus')),
    '1 brief thessalonicher' => array (array ('1 thessalonicher')),
    '1 brief timotheus' => array (array ('1 timotheus')),
    '1 dibre ha jamim' => array (array ('1 chronik')),
    '1 esra' => array (array ('esra'), array ('esra griechische ergänzung')),
    '1 kingdoms' => array (array ('1 samuel')),
    '1 ko' => array (array ('1 korinther'), array ('1 könige')),
    '1 m' => array (array ('genesis'), array ('1 makkabäer')),
    '1 mose' => array (array ('genesis')),
    '1 r' => array (array ('1 könige')),
    '1 the' => array (array ('1 thessalonicher')),
    '2 ad brief korinther' => array (array ('2 korinther')),
    '2 ad brief thessalonicher' => array (array ('2 thessalonicher')),
    '2 ad brief timotheus' => array (array ('2 timotheus')),
    '2 ad korinther' => array (array ('2 korinther')),
    '2 ad thessalonicher' => array (array ('2 thessalonicher')),
    '2 ad timotheus' => array (array ('2 timotheus')),
    '2 brief johannes' => array (array ('2 johannes')),
    '2 brief korinther' => array (array ('2 korinther')),
    '2 brief petrus' => array (array ('2 petrus')),
    '2 brief thessalonicher' => array (array ('2 thessalonicher')),
    '2 brief timotheus' => array (array ('2 timotheus')),
    '2 dibre ha jamim' => array (array ('2 chronik')),
    '2 esra' => array (array ('esra'), array ('esra lateinische ergänzung'), array ('nehemia')),
    '2 kingdoms' => array (array ('2 samuel')),
    '2 ko' => array (array ('2 korinther'), array ('2 könige')),
    '2 m' => array (array ('exodus'), array ('2 makkabäer')),
    '2 mose' => array (array ('exodus')),
    '2 r' => array (array ('2 könige')),
    '2 the' => array (array ('2 thessalonicher')),
    '3 brief johannes' => array (array ('3 johannes')),
    '3 esra' => array (array ('esra griechische ergänzung')),
    '3 kingdoms' => array (array ('1 könige')),
    '3 könige' => array (array ('1 könige')),
    '3 mose' => array (array ('levitikus')),
    '3 r' => array (array ('1 könige')),
    '3 samuel' => array (array ('1 könige')),
    '4 esra' => array (array ('esra lateinische ergänzung')),
    '4 kingdoms' => array (array ('2 könige')),
    '4 könige' => array (array ('2 könige')),
    '4 m' => array (array ('numeri'), array ('4 makkabäer')),
    '4 mose' => array (array ('numeri')),
    '4 r' => array (array ('2 könige')),
    '4 samuel' => array (array ('4 könige')),
    '5 esra' => array (array ('esra lateinische ergänzung')),
    '5 m' => array (array ('deuteronomium')),
    '5 mose' => array (array ('deuteronomium')),
    '6 esra' => array (array ('esra lateinische ergänzung')),
    'a ad daniel' => array (array ('daniel griechische version', '3', 'st3,')),
    'a ad ester' => array (array ('ester griechische version', '1', 'a')),
    'a daniel' => array (array ('daniel griechische version', 'st3,')),
    'a esra' => array (array ('esra griechische ergänzung')),
    'a ester' => array (array ('ester griechische version', '1', 'a')),
    'ab' => array (array ('obadja'), array ('habakuk')),
    'ad b daniel' => array (array ('susanna')),
    'ad b ester' => array (array ('ester griechische version', '3', 'b')),
    'ad brief epheser' => array (array ('epheser')),
    'ad brief galater' => array (array ('galater')),
    'ad brief hebräer' => array (array ('hebräer')),
    'ad brief kolosser' => array (array ('kolosser')),
    'ad brief korinther' => array (array ('1 korinther'), array ('2 korinther')),
    'ad brief philemon' => array (array ('philemon')),
    'ad brief philipper' => array (array ('philipper')),
    'ad brief römer' => array (array ('römer')),
    'ad brief thessalonicher' => array (array ('1 thessalonicher'), array ('2 thessalonicher')),
    'ad brief timotheus' => array (array ('1 timotheus'), array ('2 timotheus')),
    'ad brief titus' => array (array ('titus')),
    'ad c daniel' => array (array ('bel')),
    'ad c ester' => array (array ('ester griechische version', '4', 'c')),
    'ad d ester' => array (array ('ester griechische version', '5', 'd')),
    'ad e ester' => array (array ('ester griechische version', '8', 'e')),
    'ad epheser' => array (array ('epheser')),
    'ad esra' => array (array ('esra griechische ergänzung'), array ('esra lateinische ergänzung')),
    'ad ester' => array (array ('ester griechische version')),
    'ad ester f' => array (array ('ester griechische version', '10', 'f')),
    'ad esra griechisch' => array (array ('esra griechische ergänzung')),
    'ad esra lateinisch' => array (array ('esra lateinische ergänzung')),
    'ad galater' => array (array ('galater')),
    'ad hebräer' => array (array ('hebräer')),
    'ad kolosser' => array (array ('kolosser')),
    'ad korinther' => array (array ('1 korinther'), array ('2 korinther')),
    'ad philemon' => array (array ('philemon')),
    'ad philipper' => array (array ('philipper')),
    'ad psalm' => array (array ('psalm')),
    'ad römer' => array (array ('römer')),
    'ad thessalonicher' => array (array ('1 thessalonicher'), array ('2 thessalonicher')),
    'ad timotheus' => array (array ('1 timotheus'), array ('2 timotheus')),
    'ad titus' => array (array ('titus')),
    'addest' => array (array ('ester griechische version')),
    'addester' => array (array ('ester griechische version')),
    'addesth' => array (array ('ester griechische version')),
    'addesther' => array (array ('ester griechische version')),
    'apostelgeschichte lukas' => array (array ('apostelgeschichte')),
    'asarja gebet' => array (array ('daniel griechische version')),
    'asarja' => array (array ('daniel griechische version')),
    'b daniel' => array (array ('susanna')),
    'b esra' => array (array ('esra')),
    'b ester' => array (array ('ester griechische version', '3', 'b')),
    'bel et dr' => array (array ('bel')),
    'bel et draco' => array (array ('bel')),
    'brief epheser' => array (array ('epheser')),
    'brief galater' => array (array ('galater')),
    'brief hebräer' => array (array ('hebräer')),
    'brief jakobus' => array (array ('jakobus')),
    'brief jeremia' => array (array ('brief des jeremia')),
    'brief johannes' => array (array ('1 johannes'), array ('2 johannes'), array ('3 johannes')),
    'brief judas' => array (array ('judas')),
    'brief kolosser' => array (array ('kolosser')),
    'brief korinther' => array (array ('1 korinther'), array ('2 korinther')),
    'brief petrus' => array (array ('1 petrus'), array ('2 petrus')),
    'brief philemon' => array (array ('philemon')),
    'brief philipper' => array (array ('philipper')),
    'brief römer' => array (array ('römer')),
    'brief thessalonicher' => array (array ('1 thessalonicher'), array ('2 thessalonicher')),
    'brief timotheus' => array (array ('1 timotheus'), array ('2 timotheus')),
    'brief titus' => array (array ('titus')),
    'c daniel' => array (array ('bel')),
    'c ester' => array (array ('ester griechische version', '4', 'c')),
    'd ester' => array (array ('ester griechische version', '5', 'd')),
    'daniel gebet' => array (array ('daniel griechische version')),
    'daniel griechisch' => array (array ('daniel griechische version')),
    'dibre ha jamim i' => array (array ('1 chronik')),
    'dibre ha jamim ii' => array (array ('2 chronik')),
    'dibre ha jamim' => array (array ('1 chronik'), array ('2 chronik')),
    'dng' => array (array ('daniel griechische version')),
    'e ester' => array (array ('ester griechische version', '8', 'e')),
    'e' => array (array ('epheser')),
    'echa' => array (array ('klagelieder')),
    'ep' => array (array ('epheser')),
    'epier' => array (array ('brief des jeremia')),
    'epj' => array (array ('brief des jeremia')),
    'epjer' => array (array ('brief des jeremia')),
    'er rief' => array (array ('levitikus')),
    'es' => array (array ('esra'), array ('jesaja')),
    'esra griechisch' => array (array ('esra griechische ergänzung')),
    'esra i' => array (array ('esra griechische ergänzung')),
    'esra ii' => array (array ('esra')),
    'esra iii' => array (array ('esra griechische ergänzung')),
    'esra iv' => array (array ('esra lateinische ergänzung')),
    'esra lateinisch' => array (array ('esra lateinische ergänzung')),
    'esra v' => array (array ('esra lateinische ergänzung')),
    'esra vi' => array (array ('esra lateinische ergänzung')),
    'ester f' => array (array ('ester griechische version', '10', 'f')),
    'ester griechisch' => array (array ('ester griechische version')),
    'ester lateinisch' => array (array ('ester griechische version')),
    'evangelium johannes' => array (array ('johannes')),
    'evangelium lukas' => array (array ('lukas')),
    'evangelium markus' => array (array ('markus')),
    'evangelium matthäus' => array (array ('matthäus')),
    'ezra i' => array (array ('esra griechische ergänzung')),
    'ezra ii' => array (array ('esra')),
    'ezra iii' => array (array ('esra griechische ergänzung')),
    'ezra iv' => array (array ('esra lateinische ergänzung')),
    'ezra lateinisch' => array (array ('esra lateinische ergänzung')),
    'ezra v' => array (array ('esra lateinische ergänzung')),
    'ezra vi' => array (array ('esra lateinische ergänzung')),
    'g' => array (array ('galater')),
    'gebet manasse' => array (array ('ode', '12')),
    'gn' => array (array ('genesis'), array ('jona')),
    'hohelied salomo' => array (array ('hohelied')),
    'im anfang' => array (array ('genesis')),
    'in der wueste' => array (array ('numeri')),
    'in der wuste' => array (array ('numeri')),
    'in der wüste' => array (array ('numeri')),
    'iud' => array (array ('judas'), array ('richter')),
    'jc' => array (array ('jakobus'), array ('richter')),
    'jeremia klagelieder' => array (array ('klagelieder')),
    'jeremiabrief' => array (array ('brief des jeremia')),
    'johannes i' => array (array ('1 johannes')),
    'johannes ii' => array (array ('2 johannes')),
    'johannes iii' => array (array ('3 johannes')),
    'johannes offenbarung' => array (array ('offenbarung')),
    'johannesbrief' => array (array ('1 johannes'), array ('2 johannes'), array ('3 johannes')),
    'ko' => array (array ('kohelet'), array ('1 korinther'), array ('2 korinther'), array ('1 könige'), array ('2 könige')),
    'kohelet salomo' => array (array ('kohelet')),
    'könige i' => array (array ('1 samuel')),
    'könige ii' => array (array ('2 samuel')),
    'könige iii' => array (array ('1 könige')),
    'könige iv' => array (array ('2 könige')),
    'l' => array (array ('lukas')),
    'letjer' => array (array ('brief des jeremia')),
    'm' => array (array ('markus'), array ('matthäus'), array ('micha'), array ('maleachi'), array ('genesis'), array ('exodus'), array ('levitikus'), array ('numeri'), array ('deuteronomium'), array ('1 makkabäer'), array ('2 makkabäer'), array ('3 makkabäer'), array ('4 makkabäer')),
    'manasse' => array (array ('ode', '12')),
    'mose' => array (array ('genesis'), array ('exodus'), array ('levitikus'), array ('numeri'), array ('deuteronomium')),
    'of' => array (array ('offenbarung')),
    'pr' => array (array ('sprichwörter')),
    'pra' => array (array ('daniel griechische version')),
    'prm' => array (array ('ode', '12')),
    'prolog sirach' => array (array ('jesus sirach prolog')),
    'ps sal' => array (array ('psalm des salomo')),
    'psalm salomo' => array (array ('psalm des salomo')),
    'pssol' => array (array ('psalm des salomo')),
    'psssol' => array (array ('psalm des salomo')),
    'psir' => array (array ('jesus sirach prolog')),
    'pss' => array (array ('psalm'), array ('psalm des salomo')),
    'r i' => array (array ('1 samuel')),
    'r ii' => array (array ('2 samuel')),
    'r iii' => array (array ('1 könige')),
    'r iv' => array (array ('2 könige')),
    'r' => array (array ('römer')),
    'salomo weisheit' => array (array ('weisheit')),
    'salomo' => array (array ('hohelied')),
    'schir ha schirim' => array (array ('hohelied')),
    'sg' => array (array ('hohelied'), array ('weisheit')),
    'sip' => array (array ('jesus sirach prolog')),
    'sirach' => array (array ('jesus sirach')),
    'st' => array (array ('jakobus')),
    'the' => array (array ('1 thessalonicher'), array ('2 thessalonicher')),
    'to' => array (array ('Tobit')),
  );

  private static $pro_kapitel = array (
    'ad daniel' => array (
      '1' => array ('susanna'),
      '2' => array ('bel', 'st2,'),
      '3' => array ('daniel griechische version', '3', 'st3,'),
    ),
    'ad ester' => array (
      '1' => array ('ester griechische version', '2', 'st1,'),
      '2' => array ('ester griechische version', '4', 'st2,'),
      '3' => array ('ester griechische version', '4', 'st3,'),
      '4' => array ('ester griechische version', '5', 'st4,'),
      '5' => array ('ester griechische version', '8', 'st5, '),
      '6' => array ('ester griechische version', '1', 'st6,'),
      '7' => array ('ester griechische version', '10', 'st7,'),
    ),
    'baruch' => array (
      '6' => array ('brief des jeremia'),
    ),
    'daniel' => array (
      '13' => array ('susanna'),
      '14' => array ('bel'),
    ),
    'esra' => array (
      '11' => array ('nehemia', '1'),
      '12' => array ('nehemia', '2'),
      '13' => array ('nehemia', '3'),
      '14' => array ('nehemia', '4'),
      '15' => array ('nehemia', '5'),
      '16' => array ('nehemia', '6'),
      '17' => array ('nehemia', '7'),
      '18' => array ('nehemia', '8'),
      '19' => array ('nehemia', '9'),
      '20' => array ('nehemia', '10'),
      '21' => array ('nehemia', '11'),
      '22' => array ('nehemia', '12'),
      '23' => array ('nehemia', '13'),
    ),
    'psalm' => array (
      '151' => array ('psalm ergänzung', '151', ),
    ),
  );
}
?>
