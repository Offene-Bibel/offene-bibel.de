<?php
include_once (dirname ( __FILE__ ) . '/OffeneBibel_abk.php');

class OfBi {
  var $ids = array ();
  var $content = array ();
  var $reverse = array ();
  var $versenumber = '';
  var $verse_id = '';
  var $backlinkprefix = '';
  var $in_poem = false;
  var $in_active_verse = false;
  var $from = false;
  var $to = false;

  # This regex matches the stuff inside a HTML <> tag.
  # It matches any character and " or ' quoted strings and handles \ correctly.
  #               [^"\]   \.           [^'\]   \.
  #   [^>"']   "(       |    )*+"   '(       |    )*+'
  # (        |                    |                    )*+
  const tag_content = '([^>"\']|"([^"\\\\]|\\\\.)*+"|\'([^\'\\\\]|\\\\.)*+\')*+';
  const regex_ich_du_er = 'ICH|DU|ER|MIR|DIR|IHM|MICH|DICH|IHN|(M|D|S)EIN(E[M|N|R|S]?)?';
  const regex_unser_euer = '(Uns|uns|Eu|eu)er\\s(GOTT|HERR)|(Unse|unse|Eu|eu)re[mn]\\s(GOTT|HERRN)|(Unse|unse|Eu|eu)res\\s(GOTTES|HERRN)';
  const regex_sonst = 'GOTTES|GOTT|[Dd]e[mns]\\sHERRN|([Dd]er\\s)?HERR|Jahwes|JAHWEs|JAHWES|Jahwe|JAHWE';
  
  function OfBi() {
    $GLOBALS ['wgHooks'] ['ParserBeforeTidy'] [] = array ($this, 'hook_ParserBeforeTidy');
    $GLOBALS ['wgHooks'] ['OutputPageBeforeHTML'][] = array ($this, 'hook_OutputPageBeforeHTML');
    $GLOBALS ['wgHooks'] ['EditPage::showEditForm:initial'][] = array ($this, 'hook_EditPage_showEditForm_initial');
    $GLOBALS ['wgHooks'] ['EditFormPreloadText'][] = array ($this, 'hook_EditFormPreloadText');
    $GLOBALS ['wgParser']->setHook ('yhwh' , array ($this, 'hook_yhwh'));
    $GLOBALS ['wgParser']->setFunctionHook ('chapternumber' , array ($this, 'functionhook_chapternumber'));
    $GLOBALS ['wgParser']->setFunctionHook ('bookname' , array ($this, 'functionhook_bookname'));
    $GLOBALS ['wgParser']->setFunctionHook ('nextchapter' , array ($this, 'functionhook_nextchapter'));
    $GLOBALS ['wgParser']->setFunctionHook ('previouschapter' , array ($this, 'functionhook_previouschapter'));
    $GLOBALS ['wgParser']->setFunctionHook ('firstchapter' , array ($this, 'functionhook_firstchapter'));
    $GLOBALS ['wgParser']->setFunctionHook ('lastchapter' , array ($this, 'functionhook_lastchapter'));
    $GLOBALS ['wgParser']->setFunctionHook ('versenumber' , array ($this, 'functionhook_versenumber'));
    $GLOBALS ['wgParser']->setHook ('versenumber' , array ($this, 'hook_versenumber'));
    $GLOBALS ['wgParser']->setHook ('poem' , array ($this, 'hook_poem'));
    $GLOBALS ['wgParser']->setHook ('ref' , array ($this, 'hook_ref'));
    $GLOBALS ['wgParser']->setHook ('references' , array ($this, 'hook_references'));
    $GLOBALS ['wgParser']->setFunctionHook ('selectchapter' , array ($this, 'functionhook_selectchapter'));
    $GLOBALS ['wgParser']->setHook ('selectchapter' , array ($this, 'hook_selectchapter'));
    $GLOBALS ['wgParser']->setHook ('versionlinks' , array ($this, 'hook_versionlinks'));
    $GLOBALS ['wgParser']->setHook ('activeverses' , array ($this, 'hook_activeverses'));
    $GLOBALS ['wgParser']->setFunctionHook ('syntax_status' , array ($this, 'functionhook_syntax_status'));
    $GLOBALS ['wgParser']->setHook ('syntax_status' , array ($this, 'hook_syntax_status'));
  }

  function hook_ParserBeforeTidy ( &$parser, &$text ) {
    # Search patterns...
    # (?<FOO>pattern) is a named capture. They can be referenced with \g{FOO} later on.
    # ++ is 1 or more, without backtracking.
    $patterns = array (
      '(?<Tag>(<' . self::tag_content . '>)++)',
      '(?<Normal>[^<>(){}[\]]++)',
      '(?<Headline>\(\((?<Headline1>[^)]++)\)\))',
      # This is the name-of-god replacement regex. It looks as follows:
      # (/PART1/PART2/PART3/)
      # PART1 is the default word to display, e.g. "|Gott|". It is optional.
      # PART2 is the possessive pronoun version, ich/du/er e.g. "unserem GOTT".
      # PART3 is the pronoun version, unser/euer/ihm/ihn/...
      '(?<JHWH>\(/(?<JHWH1>[^/)]++)/(?<JHWH2>[^/)]++)/((?<JHWH3>[^/)]++)/)?\))',
      '(?<OpeningRound>\()',
      '(?<ClosingRound>\))',
      '(?<OpeningCurly>\{)',
      '(?<ClosingCurly>\})',
      '(?<OpeningSquared>\[)',
      '(?<ClosingSquared>\])',
    );
    # ... and their replacements.
    $replace = array (
      'Tag' => array (),
      'Normal' => array (),
      'Headline' => array ('Lesefassung'=>'', ),
      'JHWH' => array ('Lesefassung'=>'', ),
      'OpeningRound' => array (
        'Studienfassung'=>'<span class="runde-klammer">(</span><span class="runde-klammer-inhalt">',
      ),
      'ClosingRound' => array (
        'Studienfassung'=>'</span><span class="runde-klammer">)</span>',
      ),
      'OpeningCurly' => array (
        'Studienfassung'=>'<span class="geschweifte-klammer">{</span><span class="geschweifte-klammer-inhalt">',
      ),
      'ClosingCurly' => array (
        'Studienfassung'=>'</span><span class="runde-klammer">}</span>',
      ),
      'OpeningSquared' => array (
        'Lesefassung'=>'<span class="eckige-klammer">[</span><span class="eckige-klammer-inhalt">',
        'Studienfassung'=>'<span class="eckige-klammer">[</span><span class="eckige-klammer-inhalt">',
      ),
      'ClosingSquared' => array (
        'Lesefassung'=>'</span><span class="eckige-klammer">]</span>',
        'Studienfassung'=>'</span><span class="eckige-klammer">]</span>',
      ),
    );

    # Search for occurences of our patterns in the text and save them to $matches..
    preg_match_all ('#' . implode ('|', $patterns) . '#u', $text, $matches, PREG_SET_ORDER);

    # $mode designates how the parser should react. The mode changes as we move through the file.
    # First it's 'Start' then changes to 'Lesefassung' and is finally 'Studienfassung'.
    $mode = 'Start';

    # Clear the $text because we are rebuilding it from scratch now.
    $text = '';

    # Go through our matches.
    foreach ($matches as $match) {
      if ($mode == 'Start') {
        if (mb_strpos ($match ['Tag'], 'id="Lesefassung"') !== false) {
          $mode = 'Lesefassung';
        }
      } elseif ($mode == 'Lesefassung') {
        if (mb_strpos ($match ['Tag'], 'id="Studienfassung"') !== false) {
          $mode = 'Studienfassung';
        }
      }
      foreach ($replace as $key=>$values) {
        if (! ($match[$key] == '')) { # Correct replacement ...
          if (array_key_exists ($mode, $values)) { # ... and correct mode
            if ($key == 'JHWH') {
              if ($match['JHWH3'] == '') {
                # Two parameters.
                $unser_euer = $match ['JHWH1'];
                $ich_du_er = $match ['JHWH2'];
              } else {
                # Three parameters.
                $unser_euer = $match ['JHWH2'];
                $ich_du_er = $match ['JHWH3'];
              }
              $regex_name = self::regex_ich_du_er . '|' . self::regex_unser_euer . '|' . self::regex_sonst;
              if (
                (
                  # PART1 is in "prefix|name|postfix" format
                  # (?<prefix> [^|]*+ ) \| (?<name> [^|]*+ ) \| (?<suffix> [^|]*+ )
                  preg_match ('#(?<prefix>[^|]*+)\\|(?<name>[^|]*+)\\|(?<suffix>[^|]*+)#u', $match ['JHWH1'], $matches_gemischt) === 1
                  ||
                  (
                     # PART1 is in "prefix spec postfix" format
                     # (?<prefix> .*? ) (?<name> \b ($regex_name) \b ) (?<suffix> .*+ )
                     preg_match ('#(?<prefix>.*?)(?<name>\\b(' . $regex_name . ')\\b)(?<suffix>.*+)#u', $match ['JHWH1'], $matches_gemischt) === 1
                     &&
                     # PART1 is in "( \b ($regex_name) \b ) .* (\b ($regex_name) \b )"
                     preg_match ('#(\\b(' . $regex_name . ')\\b).*(\\b(' . $regex_name . ')\\b)#u', $match ['JHWH1']) === 0
                  )
                )
                && preg_match ('#(?<prefix>.*?)(?<name>\\b(' . self::regex_ich_du_er . ')\\b)(?<suffix>.*+)#u', $ich_du_er, $matches_ich_du_er) === 1
                && preg_match ('#(\\b(' . $regex_name . ')\\b).*(\\b(' . $regex_name . ')\\b)#u', $ich_du_er) === 0
                && preg_match ('#(?<prefix>.*?)(?<name>\\b(' . self::regex_unser_euer . ')\\b)(?<suffix>.*+)#u', $unser_euer, $matches_unser_euer) === 1
                && preg_match ('#(\\b(' . $regex_name . ')\\b).*(\\b(' . $regex_name . ')\\b)#u', $unser_euer) === 0
              ) {
                $text .= '<span class="schalter"';
                $text .= ' data-prefix1="' . htmlspecialchars ($matches_ich_du_er ['prefix']) . '"';
                $text .= ' data-pattern1="' . htmlspecialchars ($matches_ich_du_er ['name']) . '"';
                $text .= ' data-suffix1="' . htmlspecialchars ($matches_ich_du_er ['suffix']) . '"';
                $text .= ' data-prefix2="' . htmlspecialchars ($matches_unser_euer ['prefix']) . '"';
                $text .= ' data-pattern2="' . htmlspecialchars (strtr ($matches_unser_euer ['name'], array ('HERRN'=>'Herrn', 'HERR'=>'Herr', 'GOTTES'=>'Herrn', 'rem GOTT'=>'rem Herrn', 'ren GOTT'=>'ren Herrn', 'GOTT'=>'Herr', ))) . '"';
                $text .= ' data-suffix2="' . htmlspecialchars ($matches_unser_euer ['suffix']) . '"';
                $text .= '>';
                $text .= $matches_gemischt ['prefix'];
                $text .= '<a class="name">⸂<span>';
                $text .= strtr ($matches_gemischt ['name'], array ('HERRN'=>'Herrn', 'HERR'=>'Herr', 'GOTTES'=>'Gottes', 'GOTT'=>'Gott', ));
                $text .= '</span>⸃</a>';
                $text .= $matches_gemischt ['suffix'];
                $text .= '</span>';
              } else {
                $text .= $match ['JHWH'];
              }
            } elseif ($key == 'Headline') {
              $text .= '<h5 class="zwischenueberschrift">(' . $match ['Headline1'] . ')</h5>';
            } else {
              $text .= $values [$mode];
            }
          } else {
            $text .= $match [$key];
          }
        }
      }
    }
    return true;
  }

  function hook_OutputPageBeforeHTML ( &$output, &$text ) {
    $text = preg_replace ('#((?>[\pZ\n\r]*)(</?p([\pZ\n\r]' . self::tag_content . ')?>(?>[\pZ\n\r]*))+)<span class="aktiv_ende"><span>&nbsp;</span>&nbsp;</span>#u', '<span class="aktiv_ende"><span>&nbsp;</span>&nbsp;</span>\1', $text);
    return true;
  }

  function hook_EditPage_showEditForm_initial ( &$editPage ) {
    $titleObj = $editPage->getArticle()->getTitle();
    if ($titleObj->getNamespace() == NS_MAIN) {
      list ($buchname, $kapitel) = OfBiAbk::buchnamekapitel ($titleObj->getText());
      if ($buchname !== false) {
        if ($kapitel !== false) {
          $editPage->editFormTextBeforeContent .= '<span id="seitentyp" class="kapitel"></span>';
        } else {
          $editPage->editFormTextBeforeContent .= '<span id="seitentyp" class="buch"></span>';
        }
      }
    } else if ($titleObj->getNamespace() == NS_TALK) {
      list ($buchname, $kapitel) = OfBiAbk::buchnamekapitel ($titleObj->getText());
      if ($buchname !== false) {
        if ($kapitel !== false) {
          $editPage->editFormTextBeforeContent .= '<span id="seitentyp" class="kapiteldiskussion"></span>';
        } else {
          $editPage->editFormTextBeforeContent .= '<span id="seitentyp" class="buchdiskussion"></span>';
        }
      }
    }
    return true;
  }

  function hook_EditFormPreloadText ( &$text, &$titleObj ) {
    if ($titleObj->getNamespace() == NS_MAIN) {
      list ($buchname, $kapitel) = OfBiAbk::buchnamekapitel ($titleObj->getText());
      if ($kapitel !== false) {
        $text = "{{Lesefassung}} ''(kommt später)''\n\n{{Studienfassung}}\n\n";
        foreach (OfBiAbk::versnummern ($buchname, $kapitel) as $versnummer) {
          $text .= "{{S|" . $versnummer . "}} \n\n";
        }

        $text .= "{{Bemerkungen}}\n\n{{Kapitelseite Fuß}}";
      }
      return true;
    } else if ($titleObj->getNamespace() == NS_TALK) {
      list ($buchname, $kapitel) = OfBiAbk::buchnamekapitel ($titleObj->getText());
      if ($kapitel !== false) {
        $text = "{{Checkliste Studienfassung\n";
        $text .= "|Alle Verse = \n";
        $text .= "|Alternativen = \n";
        $text .= "|Zweifelsfälle dokumentiert = \n";
        $text .= "|Bedeutung erläutert = \n";
        $text .= "|Textart = \n";
        $text .= "|Andere Kriterien = \n";
        $text .= "|Am Urtext überprüft = \n";
        $text .= "|Kommentare eingesehen = \n";
        $text .= "|Übersetzungsvergleich = \n";
        $text .= "|Endkorrektur = \n";
        $text .= "}}\n";
        $text .= "\n";
        $text .= "Hier dürfen Vorschläge, Rückfragen und andere Diskussionsbeiträge folgen:\n";
      }
      return true;
    }
    return false;
  }

  function hook_yhwh ($input, $args, $parser, $frame) {
    $text = '<span class="schalter"';
    if (isset ($args ['prefix1'])) {
      $text .= ' data-prefix1="' . htmlspecialchars ($args ['prefix1']) .'"';
    }
    if (isset ($args ['pattern1'])) {
      $text .= ' data-pattern1="' . htmlspecialchars ($args ['pattern1']) .'"';
    }
    if (isset ($args ['suffix1'])) {
      $text .= ' data-suffix1="' . htmlspecialchars ($args ['suffix1']) .'"';
    }
    if (isset ($args ['prefix2'])) {
      $text .= ' data-prefix2="' . htmlspecialchars ($args ['prefix2']) .'"';
    }
    if (isset ($args ['pattern2'])) {
      $text .= ' data-pattern2="' . htmlspecialchars ($args ['pattern2']) .'"';
    }
    if (isset ($args ['suffix2'])) {
      $text .= ' data-suffix2="' . htmlspecialchars ($args ['suffix2']) .'"';
    }
    $text .= '>';
    if (isset ($args ['prefix'])) {
      $text .= $parser->recursiveTagParse ($args ['prefix'], $frame);
    }
    $text .= '<a class="name"><span>';
    $text .= $parser->recursiveTagParse ($input, $frame);
    $text .= '</span></a>';
    if (isset ($args ['suffix'])) {
      $text .= $parser->recursiveTagParse ($args ['suffix'], $frame);
    }
    $text .= '</span>';
    return $text;
  }

  function functionhook_chapternumber ($parser, $pagename = '') {
    list ($buchname, $kapitel) = OfBiAbk::buchnamekapitel ($pagename);
    return $kapitel === false ? '' : $kapitel;
  }

  function functionhook_bookname ($parser, $pagename = '') {
    list ($buchname, $kapitel) = OfBiAbk::buchnamekapitel ($pagename);
    return $buchname === false ? '' : $buchname;
  }

  function functionhook_nextchapter ($parser, $bookname = '', $chapternumber = '') {
    $newchapternumber = OfBiAbk::naechstes_kapitel ($bookname, $chapternumber);
    return $newchapternumber === false ? '' : $newchapternumber;
  }

  function functionhook_previouschapter ($parser, $bookname = '', $chapternumber = '') {
    $newchapternumber = OfBiAbk::voriges_kapitel ($bookname, $chapternumber);
    return $newchapternumber === false ? '' : $newchapternumber;
  }

  function functionhook_firstchapter ($parser, $bookname = '') {
    $chapternumber = OfBiAbk::erstes_kapitel ($bookname);
    return $chapternumber === false ? '' : $chapternumber;
  }

  function functionhook_lastchapter ($parser, $bookname = '') {
    $chapternumber = OfBiAbk::letztes_kapitel ($bookname);
    return $chapternumber === false ? '' : $chapternumber;
  }

  function functionhook_versenumber ($parser, $versenumber = '', $id = '', $backlinkprefix = '') {
    return $parser->recursiveTagParse ('<versenumber id="' . htmlspecialchars ($id) . '" backlinkprefix="' . htmlspecialchars ($backlinkprefix) . '">' . $versenumber . '</versenumber>');
  }

  function hook_versenumber ($input, $args, $parser, $frame) {
    $parser->disableCache();

    $output = '';
    $this->versenumber = trim ($parser->recursiveTagParse ($input, $frame));
    $this->backlinkprefix = isset ($args ['backlinkprefix']) ? $args ['backlinkprefix'] : '';
    $this->verse_id = isset ($args ['id']) ? $args ['id'] : '';
    $in_active_verse_new = false;

    if ($this->versenumber !== '') {
      if ($this->get_from () != 0 && strnatcasecmp ($this->versenumber, $this->get_from ()) >= 0) {
        if ($this->get_to () != 0 && strnatcasecmp ($this->versenumber, $this->get_to ()) <= 0) {
          $in_active_verse_new = true;
        }
      }

      if ( ! $in_active_verse_new && $this->in_active_verse) {
        $output .= '<span class="aktiv_ende"><span>&nbsp;</span>&nbsp;</span>';
      }
      if ($in_active_verse_new && ! $this->in_active_verse) {
        $output .= '<span class="vor_aktiv"></span>';
      }
      $output .= '<span class="vor_vers"';
      if ($this->verse_id != '') {
        $output .= ' id="' . htmlspecialchars ($this->verse_id) . '"';
      }
      $output .= '></span>';
      if ($in_active_verse_new && ! $this->in_active_verse) {
        $output .= '<span class="pfeil"></span>';
        $output .= '<span class="aktiv_anfang"><span>&nbsp;</span></span>';
      }
      if ($in_active_verse_new) {
        $output .= '<span class="aktiv">';
      }
      $output .= '<sup class="versnummer">';
      $output .= $this->versenumber;
      $output .= '</sup>';
      if ($in_active_verse_new) {
        $output .= '</span>';
      }
    }
     $this->in_active_verse = $in_active_verse_new;
    return $output;
  }

  function hook_poem ($input, $args, $parser, $frame) {
    if (! $this->in_poem) {
      $this->in_poem = true;
      $text = $parser->recursiveTagParse ('<p class="poem">' . trim ($input) . '</p>', $frame);
      $this->in_poem = false;

      $offset = 0;
      $output = '';
      while (preg_match ('#(([^<]++|<br([\pZ\n\r]' . self::tag_content . ')?>)*+)<' . self::tag_content . '>#u', $text, $matches, 0, $offset) === 1) {
        $output .= $this->replace_newlines ($matches [1]);
        $output .= substr ($matches [0], strlen ($matches [1]));
        $offset += strlen ($matches [0]);
      }
      $output .= $this->replace_newlines (substr ($text, $offset));
    } else {
      $output = htmlspecialchars ('<poem>') . $parser->recursiveTagParse ($input, $frame) . htmlspecialchars ('</poem>');
    }

    return $output;
  }

  function replace_newlines ($text) {
    $text = preg_replace ("#(\n|\r|\r\n)#u", "<br />\r\n", $text);
    $text = preg_replace ('#(<br[\pZ\n\r]*+/?>)+#u', '<br />', $text);
    $text = preg_replace ("#\r\n<br />#u", "\r\n", $text);
    return $text;
  }

  function hook_ref ($input, $args, $parser, $frame) {
    $id = $this->register_id ($input, $args, $parser, $frame);

    $html_title = strip_tags ($this->content [$id], '<br><br/><p><p/><div><div/>');
    $html_title = preg_replace ("#<[^>]*+>#u", " ", $html_title);
    $html_title = trim (preg_replace ("#(\n|\r|\r\n)+#u", " ", $html_title));
    if (mb_strlen ($html_title) > 150) {
      $html_title = mb_substr ($html_title, 0, mb_strpos ($html_title, ' ', 150)) . ' …';
    }

    $html_href = '#note_' . $id;
    $backlink_id = count ($this->reverse [$id]) + 1;
    $html_id = 'reference_' . $id . '_' . $backlink_id;

    if ($this->versenumber == 0) {
      if ($backlink_id == 1) {
        $this->reverse [$id] [$html_id] = 'Zurück zum Text: ' . $id;
      } else {
        $this->reverse [$id] [$html_id] = $id . '-' . $backlink_id;
      }
    } else {
      if ($backlink_id == 1) {
        $this->reverse [$id] [$this->verse_id] = 'Zurück zu ' . $this->backlinkprefix . $this->versenumber;
      } else {
        $this->reverse [$id] [$this->verse_id] = 'zu ' . $this->backlinkprefix . $this->versenumber;
      }
    }

    $result = '<sup class="reference">〈';
    $result .= '<a href="' . htmlspecialchars ($html_href) . '" ';
    $result .= 'id="' . htmlspecialchars ($html_id) . '" ';
    $result .= 'title="' . htmlspecialchars ($html_title) . '">';
    $result .= htmlspecialchars ($id);
    $result .= '</a>';
    $result .= '〉</sup>';
    return $result;
  }

  function register_id ($input, $args, $parser, $frame) {
    if (isset ($args ['name']) && isset ($this->verse_ids [$args ['name']])) {
      return $this->verse_ids [$args ['name']];
    }

    $id = '';
    for ($c = count ($this->reverse); $c >= 0; $c = floor ($c/26) - 1) {
      $id = chr (ord ('a') + $c % 26) . $id;
    }

    $content = $parser->recursiveTagParse ($input, $frame);

    $this->reverse [$id] = array ();
    $this->content [$id] = $content;

    if (isset ($args ['name'])) {
      $this->verse_ids [$args ['name']] = $id;
    }

    return $id;
  }

  function hook_references ($input, $args, $parser, $frame) {
    $text = '<table class="references">';
    foreach ($this->content as $id=>$content) {
        $text .= $this->show_note ($id, $content);
    }
    $text .= '</table>';
    $this->content = array ();
    return $text;
  }

  function show_note ($id, $content) {
    $backlinks = array ();
    foreach ($this->reverse [$id] as $anchor=>$linktext) {
      $backlinks [] = '<a href="#' . htmlspecialchars ($anchor) . '">' . htmlspecialchars ($linktext) . '</a>';
    }
    $text = '<tr><td class="note_id">';
    $text .= htmlspecialchars ($id);
    $text .= '</td><td class="note" id="note_' . htmlspecialchars ($id) . '">';
    $text .= $content;
    $text .= '<span class="backlinks"> (' . implode (' / ', $backlinks) . ')</span>';
    $text .= '</td></tr>';
    return $text;
  }

  function functionhook_selectchapter ($parser, $name = '-', $chapter = 0) {
    return $parser->recursiveTagParse ('<selectchapter name="' . htmlspecialchars ($name) . '" chapter="' . htmlspecialchars ($chapter) . '" />');
  }

  function hook_selectchapter ($input, $args, $parser, $frame) {
    $text = '';
    if (isset ($args ['name']) && isset ($args ['chapter'])) {
      $text .= '<div class="kapitelwahl">';

      $text .= '<form action="' . htmlspecialchars ($GLOBALS ['wgScriptPath']) . '">';
      $text .= '<input type="submit" id="submitbutton1" value="Geh zu" class="zelle" /> ';
      $text .= '<label for="buch" class="zelle">&nbsp;Buch:&nbsp;</label>';
      $text .= '<span class="zelle">';
      $text .= '<select name="title" id="buch" onchange="this.form.submit()">';

      $current_bookname = $args ['name'];
      $options = OfBiAbk::buchnamen_alphabetisch ($current_bookname);
      foreach ($options as $display=>$option) {
        $text .= $this->make_option ($option, $display, $current_bookname == $display);
      }
      $text .= '</select>';
      $text .= '</span>';
      $text .= '</form>';

      if (OfBiAbk::erstes_kapitel ($args ['name']) !== false) {
        $text .= '<form action="' . htmlspecialchars ($GLOBALS ['wgScriptPath']) . '">';
        $text .= '<input type="submit" id="submitbutton2" value="Geh zu" onload="" class="zelle" /> ';
        $text .= '<label for="kapitel" class="zelle">&nbsp;Kapitel:&nbsp;</label>';
        $text .= '<span class="zelle">';

        $text .= '<select name="title" id="kapitel" onchange="this.form.submit()">';
        if (intval (trim ($args ['chapter'])) == 0) {
          $text .= $this->make_option ($args ['name'], '(auswählen)', true);
        }

        $chapternumber = OfBiAbk::erstes_kapitel ($args ['name']);
        do {
          $option = htmlspecialchars ($args ['name'] . ' ' . $chapternumber);
          $titleObj = Title::makeTitle( NS_MAIN, $option);
          if ($titleObj->exists ()) {
            $text .= $this->make_option ($option, $chapternumber, $chapternumber == trim ($args ['chapter']));
          } else {
            $text .= $this->make_option ($option, '(' . $chapternumber . ')', $chapternumber == trim ($args ['chapter']));
          }
          $text .= '</option>';
          $chapternumber = OfBiAbk::naechstes_kapitel ($args ['name'], $chapternumber);
        } while ($chapternumber !== false);
        $text .= '</select>';

        if (OfBiAbk::voriges_kapitel ($args ['name'], $args ['chapter']) !== false) {
          $titleObj = Title::makeTitle( NS_MAIN, $args ['name'] . ' ' . OfBiAbk::voriges_kapitel ($args ['name'], $args ['chapter']));
          $text .= ' <a href="' . htmlspecialchars ($titleObj->getLocalURL ()) . '"';
          $text .= ' title="' . htmlspecialchars ($args ['name'] . ' ' . OfBiAbk::voriges_kapitel ($args ['name'], $args ['chapter'])) . '"';
          if (! $titleObj->exists ()) {
            $text .= ' class="new"';
          }
          $text .= '>←</a> ';
        }

        if (OfBiAbk::naechstes_kapitel ($args ['name'], $args ['chapter']) !== false) {
          $titleObj = Title::makeTitle( NS_MAIN, $args ['name'] . ' ' . OfBiAbk::naechstes_kapitel ($args ['name'], $args ['chapter']));
          $text .= ' <a href="' . htmlspecialchars ($titleObj->getLocalURL ()) . '"';
          $text .= ' title="' . htmlspecialchars ($args ['name'] . ' ' . OfBiAbk::naechstes_kapitel ($args ['name'], $args ['chapter'])) . '"';
          if (! $titleObj->exists ()) {
            $text .= ' class="new"';
          }
          $text .= '>→</a> ';
        }

        $text .= '</span>';
        $text .= '</form>';
      }

      $text .= '<script type="text/javascript"> ';
      $text .= 'document.getElementById ("submitbutton1").style.display = "none"; ';
      $text .= 'document.getElementById ("submitbutton2").style.display = "none"; ';
      $text .= '</script>';

      $text .= '</div>';
    }
    return $text;
  }

  function make_option ($value, $text, $active) {
    if ($active) {
      return '<option value="' . htmlspecialchars ($value) . '" selected="selected">' . htmlspecialchars ($text) . '</option>';
    } else {
      return '<option value="' . htmlspecialchars ($value) . '">' . htmlspecialchars ($text) . '</option>';
    }
  }

  function hook_versionlinks ($input, $args, $parser, $frame) {
    if ($this->get_from () != 0) {
      $output = '<p>';
      $output .= '[[#l' . intval ($this->get_from ()) . '|↓ <span class="aktiv_link">Lesefassung (Vers ' . intval ($this->get_from ()) . ')</span>]], ';
      $output .= '[[#s' . intval ($this->get_from ()) . '|↓ <span class="aktiv_link">Studienfassung (Vers ' . intval ($this->get_from ()) . ')</span>]]';
      $output .= '</p>';
    } else {
      $output = '<p>';
      $output .= '[[#Lesefassung|↓ Lesefassung]], ';
      $output .= '[[#Studienfassung|↓ Studienfassung]]';
      $output .= '</p>';
    }
    return $parser->recursiveTagParse ($output, $frame);
  }

  function hook_activeverses ($input, $args, $parser, $frame) {
    if (isset ($args ['from'])) {
      $this->from = intval ($args ['from']);
    }
    if (isset ($args ['to'])) {
      $this->to = intval ($args ['to']);
    }
  }

  function get_from () {
    if ($this->from === false) {
      return $GLOBALS ['wgRequest']->getText ('von', 0);
    } else {
      return $this->from;
    }
  }

  function get_to () {
    if ($this->to === false) {
      return $GLOBALS ['wgRequest']->getText ('bis', 0);
    } else {
      return $this->to;
    }
  }

  function functionhook_syntax_status ($parser, $name = '-', $chapter = 0) {
    return $parser->recursiveTagParse ('<syntax_status name="' . htmlspecialchars ($name) . '" chapter="' . htmlspecialchars ($chapter) . '"/>');
  }

  function hook_syntax_status ($input, $args, $parser, $frame) {
    $dbr = wfGetDB(DB_SLAVE);
    $result = $dbr->select('parse_errors', array('error_occurred', 'error_string'),
    	array(
	  'pageid=' . $parser->getTitle()->getArticleID(),
	  'revid=' . $parser->getRevisionId()
	)
      );
    if($result->numRows() > 1) {
      return 'Datenbankfehler, ' . $result->numRows() . ' Eintraege fuer diese Seite gefunden, 0 oder 1 erwartet. PageID: ' . $parser->getTitle()->getArticleID() . ' RevID: ' . $parser->getRevisionId();
    }
    if($result->numRows() == 0) {
      return 'Noch kein Ergebnis verfuegbar. PageID: ' . $parser->getTitle()->getArticleID() . ' RevID: ' . $parser->getRevisionId();
    }
    $row = $result->fetchRow();
    if($row['error_occurred']) {
      return 'Fehler vorhanden:\n' . $row['error_string'] . 'PageID: ' . $parser->getTitle()->getArticleID() . ' RevID: ' . $parser->getRevisionId();
    }
    else {
      return 'Keine Fehler.' . 'PageID: ' . $parser->getTitle()->getArticleID() . ' RevID: ' . $parser->getRevisionId();
    }
  }
}
?>
