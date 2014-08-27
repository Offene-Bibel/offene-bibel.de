<?php
if ( ! defined( 'MEDIAWIKI' ) )
        die( 1 );
 
require_once( dirname( dirname( __FILE__ ) ) . '/includes/SkinTemplate.php');
require_once('offenebibel/headerFooter.php');
/**
 * OffeneBibel skin
 *
 * @file
 * @ingroup Skins
 * @version 1.2.0
 * @author Olaf Schmidt-Wischhöfer (olaf@offene-bibel.de)
 * @license http://www.gnu.org/copyleft/gpl.html GPL
 */

// initialize
if ( ! defined ('MEDIAWIKI')) {
  die ("This is a skins file for mediawiki and should not be viewed directly.\n");
}

// inherit main code from SkinTemplate, set the CSS and template filter
class SkinOffeneBibel extends SkinTemplate {
  var $useHeadElement = true;

  function initPage( OutputPage $out ) {
    parent::initPage( $out );
    $this->skinname  = 'offenebibel';
    $this->stylename = 'offenebibel';
    $this->template  = 'OffeneBibelTemplate';
  }
  function setupSkinUserCss( OutputPage $out ) {
    global $wgHandheldStyle;
    parent::setupSkinUserCss( $out );
    // Append to the default screen common & print styles...
    $out->addStyle( 'offenebibel/bootstrap.min.css', 'screen' );
    $out->addStyle( 'offenebibel/header_footer.css', 'screen' );
    $out->addStyle( 'offenebibel/main.css', 'screen' );
  }
}

class OffeneBibelTemplate extends QuickTemplate {
  /**
    * Template filter callback for this skin.
    * Takes an associative array of data set from a SkinTemplate-based
    * class, and a wrapper for MediaWiki's localization database, and
    * outputs a formatted page.
    */

  public function execute() {
    global $wgRequest;

    $skin = $this->data ['skin'];

    // suppress warnings to prevent notices about missing indexes in $this->data
    wfSuppressWarnings();

    $this->html( 'headelement' );
    ?>
    <script data-main="<?php echo htmlspecialchars($skin->getSkinStylePath("js/main.js"));?>" src="<?php echo htmlspecialchars($skin->getSkinStylePath("js/vendor/require.js"));?>"></script>
    <?php echo getOfBiHeader();?>

    <div id="globalWrapper">
      <div id="column-content">
        <div id="content">
          <a name="top" id="top"></a>

          <h1 id="firstHeading"><?php $this->html('title'); ?></h1>
          <div id="bodyContent">

            <h3 id="siteSub"><?php $this->msg('tagline') ?></h3>

            <div id="contentSub"><?php $this->html('subtitle') ?></div>

            <?php if( $this->data['undelete'] ) { ?><div id="contentSub2"><?php $this->html('undelete') ?></div><?php } ?>
            <?php if( $this->data['newtalk'] ) { ?><div class="usermessage"><?php $this->html('newtalk') ?></div><?php } ?>
            <?php if( $this->data['showjumplinks'] ) { ?><div id="jump-to-nav"><?php $this->msg('jumpto') ?> <a href="#column-one"><?php $this->msg('jumptonavigation') ?></a>, <a href="#searchInput"><?php $this->msg('jumptosearch') ?></a></div><?php } ?>

            <!-- start content -->
            <?php $this->html('bodytext') ?>
            <?php if( $this->data['catlinks'] ) { $this->html('catlinks'); } ?>
            <!-- end content -->

            <?php if($this->data['dataAfterContent']) { $this->html ('dataAfterContent'); } ?>
          </div> <!-- bodyContent -->
          <div class="visualClear"></div>

          <div id="footer"><?php echo getOfBiFooter(); ?></div> <!-- footer -->

        </div> <!-- content -->

        <div id="p-cactions" class="portlet">
          <h5><?php $this->msg('views') ?></h5> <!-- Page Toolbar Label/Caption [optional] -->
          <div class="pBody">
            <ul id="p-cactions-ul">
              <script type="text/javascript">
                if (navigator.cookieEnabled) {
                  document.write ('<ul id="show-sidebar" title="Seitenleiste einblenden" style="display: none; float: left;"><li><a href=\'javascript: document.getElementById ("column-content-large").id = "column-content"; document.getElementById ("show-sidebar").style.display = "none"; document.getElementById ("column-one").style.display = "block"; void(document.cookie = "sidebar=true; path=/; domain=offene-bibel.de");\'><span style="font-weight: bold;">»</span> Seitenleiste</a><li></ul>');
                }
              </script>
              <?php
              foreach( $this->data['content_actions'] as $key => $tab ) {
                                echo '
                        <li id="', Sanitizer::escapeId( "ca-$key" ), '"';
                                if ( $tab['class'] ) {
                                        echo ' class="', htmlspecialchars($tab['class']), '"';
                                }
                                echo '><a href="', htmlspecialchars($tab['href']), '"',
                                        Xml::expandAttributes ($skin->tooltipAndAccesskeyAttribs('ca-'.$key)), '>',
                                        htmlspecialchars($tab['text']),
                                        '</a></li>';
              }?>
            </ul>
          </div> <!-- pBody -->
        </div> <!-- p-cactions -->
      </div> <!-- column-content -->
      <div id="column-one">
        <div id="one">
          <script type="text/javascript">
            if (navigator.cookieEnabled) {
              document.write ('<div id="hide-sidebar" class="cactions" title="Seitenleiste ausblenden" style="position: absolute; margin:0; right: 0%; text-align: center; border: 1px solid #E4F0DE; border-right: none; border-top: none; vertical-align: middle; font-size: 125%; line-height: 1em; width: 1em;"><a href=\'javascript: document.getElementById ("column-content").id = "column-content-large"; document.getElementById ("show-sidebar").style.display = "inline"; document.getElementById ("column-one").style.display = "none"; void (document.cookie = "sidebar=false; path=/; domain=.offene-bibel.de");\'><span style="font-weight: bold;">«</span></a></div>');
              if (document.cookie.indexOf ('sidebar=false') >= 0) {
                document.getElementById ("column-content").id = "column-content-large";
                document.getElementById ("show-sidebar").style.display = "inline";
              }
            }
          </script>

          <?php foreach ($this->data['sidebar'] as $bar => $cont) { ?>
            <?php if ($bar == 'SEARCH') {?>
              <div id="p-biblepassage" class="portlet">
                <h5><label for="biblepassage">Bibelstelle aufschlagen</label></h5>
                <div class="pBody">
                  <form action="<?php echo $GLOBALS ['wgScriptPath']?>" method="get">
                    <input type="hidden" name="title" value="Special:Bibelstelle_aufschlagen" />
                    <div style="display: table; width: 100%;"><span style="display: table-row;">
                      <span style="display: table-cell; text-align: left; vertical-align: middle; width: 6em;"><input type="submit" value="Gehe zu:" style="width: 5em;" /></span>
                      <span style="display: table-cell; vertical-align: middle;"><input type="text" name="abk" id="biblepassage" value="" style="width: 100%;" /></span>
                    </span></div>
                  </form>
                </div> <!-- pBody -->
              </div> <!-- p-biblepassage -->

              <div id="p-search" class="portlet">
                <h5><label for="suche">Volltextsuche</label></h5>
                <div class="pBody">
                  <form action="/suche.php" method="get">
                    <div style="display: table; width: 100%;"><span style="display: table-row;">
                      <span style="display: table-cell; text-align: left; vertical-align: middle; width: 6em;"><input type="submit" value="Suche:" style="width: 5em;" /></span>
                      <span style="display: table-cell; vertical-align: middle;"><input id="suche" name="suche" type="text" style="width: 100%;" /></span>
                    </span></div>
                    <div style="display: table; width: 100%; margin: 0.3em 0 0 0;"><span style="display: table-row;">
                      <label for="in" style="display: table-cell; vertical-align: middle;">in&nbsp;</label>
                      <select id="in" name="in" style="display: table-cell; vertical-align: middle; width: 100%;">
                        <option value="wiki">Übersetzungen/Kommentaren</option>
                        <option value="diskussion">Diskussionsseiten</option>
                        <option value="drupal">News, Blogs und Forum</option>
                      </select>
                    </span></div>
                  </form>
                </div> <!-- pBody -->
              </div> <!-- p-search -->

              <div class="portlet" id="p-personal">
                <?php
                if (isset ($this->data['personal_urls'] ['logout'])) { 
                  echo '<h5>'.$this->data['personal_urls'] ['userpage'] ['text'].'</h5>';
                  echo '<div class="pBody">';
                  echo '<ul>';
                  foreach ($this->data['personal_urls'] as $key => $item) {
                    echo '<li id="'.Sanitizer::escapeId( "pt-$key" ).'"';
                    if ($item ['active']) {
                      echo ' class="active"';
                    }
                    echo '><a href="'.htmlspecialchars($item['href']).'"'.Xml::expandAttributes ($skin->tooltipAndAccesskeyAttribs('pt-'.$key));
                    if (!empty ($item ['class'])) {
                      echo ' class="'.htmlspecialchars($item ['class']).'"';
                    }
                    echo '>';
                    if ($key=='userpage') {
                      echo 'Eigene Benutzerseite';
                    } else {
                      echo htmlspecialchars($item['text']);
                    }
                    echo '</a></li>';
                  }
                  echo '</ul>';
                  echo '</div> <!-- pBody -->';
                } else {
                  ?>
                  <h5>Benutzeranmeldung</h5>
                  <div class="pBody">
                    <form action="/?destination=wiki/?title=<?php echo htmlspecialchars ($this->text ('title')); ?>"  accept-charset="UTF-8" method="post" id="user-login-form">
                      <div>
                        <div class="form-item" id="edit-name-wrapper">
                          <label for="edit-name">Benutzername: <span class="form-required" title="Dieses Feld wird benötigt.">*</span></label>
                          <input type="text" maxlength="60" name="name" id="edit-name" size="15" value="" class="form-text required" style="width:100%" />
                        </div> <!-- form-item -->
                        <div class="form-item" id="edit-pass-wrapper">
                          <label for="edit-pass">Passwort: <span class="form-required" title="Dieses Feld wird benötigt.">*</span></label>
                          <input type="password" name="pass" id="edit-pass"  maxlength="60"  size="15"  class="form-text required" style="width:100%" />
                        </div> <!-- form-item -->
                        <input type="submit" name="op" id="edit-submit-1" value="Anmelden"  class="form-submit" style="width:100%; margin: 0.3em 0 0 0;" />
                        <div class="item-list">
                          <ul>
                            <li class="first"><a href="/user/register" title="Ein neues Benutzerkonto erstellen.">Registrieren</a></li>
                            <li class="last"><a href="/user/password" title="Ein neues Passwort per E-Mail anfordern.">Neues Passwort anfordern</a></li>
                          </ul>
                        </div> <!-- item-list -->
                        <input type="hidden" name="form_build_id" id="form-e4ba62c993ea5ae8a0b7d7d351f43df7" value="form-e4ba62c993ea5ae8a0b7d7d351f43df7"  />
                        <input type="hidden" name="form_id" id="edit-user-login-block" value="user_login_block"  />
                      </div>
                    </form>
                  </div> <!-- pBody -->
                <?php } ?>
              </div> <!-- p-personal -->
            <?php } elseif ($bar == 'TOOLBOX') { ?>
              <div class="portlet" id="p-tb">
                <h5><?php $this->msg('toolbox') ?></h5>
                <div class="pBody">
                  <ul>
                    <?php
                    if( $this->data['notspecialpage'] ) { ?>
                      <li id="t-whatlinkshere"><a href="<?php
                      echo htmlspecialchars($this->data['nav_urls']['whatlinkshere']['href'])
                      ?>"<?php echo Xml::expandAttributes ($skin->tooltipAndAccesskeyAttribs('t-whatlinkshere')) ?>><?php $this->msg('whatlinkshere') ?></a></li>
                      <?php
                      if( $this->data['nav_urls']['recentchangeslinked'] ) { ?>
                        <li id="t-recentchangeslinked"><a href="<?php
                        echo htmlspecialchars($this->data['nav_urls']['recentchangeslinked']['href'])
                        ?>"<?php echo Xml::expandAttributes ($skin->tooltipAndAccesskeyAttribs('t-recentchangeslinked')) ?>><?php $this->msg('recentchangeslinked') ?></a></li>
                      <?php
                      }
                    }

                    if ( isset( $this->data['nav_urls']['trackbacklink'] ) ) { ?>
                      <li id="t-trackbacklink"><a href="<?php
                      echo htmlspecialchars($this->data['nav_urls']['trackbacklink']['href'])
                      ?>"<?php echo Xml::expandAttributes ($skin->tooltipAndAccesskeyAttribs('t-trackbacklink')) ?>><?php $this->msg('trackbacklink') ?></a></li>
                    <?php
                    }

                    if( $this->data['feeds'] ) { ?>
                      <li id="feedlinks"><?php foreach($this->data['feeds'] as $key => $feed) {
                      ?><span id="feed-<?php echo Sanitizer::escapeId($key) ?>"><a href="<?php
                      echo htmlspecialchars($feed['href']) ?>"<?php echo Xml::expandAttributes ($skin->tooltipAndAccesskeyAttribs('feed-'.$key)) ?>><?php echo htmlspecialchars($feed['text'])?></a>&nbsp;</span>
                      <?php } ?>
                      </li>
                    <?php
                    }
  
                    foreach( array( 'contributions', 'blockip', 'emailuser', 'upload', 'specialpages' ) as $special ) {
                      if( $this->data['nav_urls'][$special] ) {
                        ?><li id="t-<?php echo $special ?>"><a href="<?php echo htmlspecialchars($this->data['nav_urls'][$special]['href'])
                        ?>"<?php echo Xml::expandAttributes ($skin->tooltipAndAccesskeyAttribs('t-'.$special)) ?>><?php $this->msg($special) ?></a></li>
                      <?php
                      }
                    }
  
                    if( !empty( $this->data['nav_urls']['print']['href'] ) ) { ?>
                      <li id="t-print"><a href="<?php echo htmlspecialchars($this->data['nav_urls']['print']['href'])
                      ?>"<?php echo Xml::expandAttributes ($skin->tooltipAndAccesskeyAttribs('t-print')) ?>><?php $this->msg('printableversion') ?></a></li><?php
                    }
  
                    if( !empty( $this->data['nav_urls']['permalink']['href'] ) ) { ?>
                      <li id="t-permalink"><a href="<?php echo htmlspecialchars($this->data['nav_urls']['permalink']['href'])
                      ?>"<?php echo Xml::expandAttributes ($skin->tooltipAndAccesskeyAttribs('t-permalink')) ?>><?php $this->msg('permalink') ?></a></li><?php
                    } elseif( $this->data['nav_urls']['permalink']['href'] === '' ) { ?>
                      <li id="t-ispermalink"<?php echo $skin->tooltip('t-ispermalink') ?>><?php $this->msg('permalink') ?></li><?php
                    }
  
                    wfRunHooks( 'MonoBookTemplateToolboxEnd', array( &$this ) );
                  ?>
                  </ul>
                </div> <!-- pBody -->
              </div> <!-- p-tb -->
            <?php } elseif ($bar == 'LANGUAGES') { ?>
              <?php if( $this->data['language_urls'] ) { ?>
                <div id="p-lang" class="portlet">
                  <h5><?php $this->msg('otherlanguages') ?></h5>
                  <div class="pBody">
                    <ul>
                      <?php foreach( $this->data['language_urls'] as $langlink ) { ?>
                        <li class="<?php echo htmlspecialchars($langlink['class'])?>"><?php
                        ?><a href="<?php echo htmlspecialchars($langlink['href']) ?>"><?php echo $langlink['text'] ?></a></li>
                      <?php } ?>
                    </ul>
                  </div> <!-- pBody -->
                </div> <!-- p-lang -->
              <?php } ?>
            <?php } elseif ($bar !== 'Rubriken') { ?>
              <div class='portlet' id='p-<?php echo Sanitizer::escapeId( $bar ) ?>'<?php echo $skin->tooltip('p-'.$bar) ?>>
                <h5><?php $out = wfMsg( $bar ); if( wfEmptyMsg( $bar, $out ) ) echo $bar; else echo $out; ?></h5>
                <div class='pBody'>
                  <ul>
                    <?php foreach ($cont as $key => $val) { ?>
                      <li id="<?php echo Sanitizer::escapeId( $val['id'] ) ?>"<?php
                        if( $val['active'] ) { ?> class="active" <?php }
                        ?>><a href="<?php echo htmlspecialchars( $val['href'] ) ?>"<?php echo Xml::expandAttributes ($skin->tooltipAndAccesskeyAttribs($val['id'])) ?>><?php echo htmlspecialchars( $val['text'] ) ?></a></li>
                    <?php } ?>
                  </ul>
                </div> <!-- pBody -->
              </div> <!-- p-... -->
            <?php } ?>
          <?php } ?>
        </div> <!-- one -->
      </div> <!-- column-one -->
    </div> <!-- global-wrapper -->

    <!-- scripts and debugging information -->
    <?php $this->html('bottomscripts'); /* JS call to runBodyOnloadHook */ ?>
    <?php $this->html('reporttime') ?>
    <?php if ( $this->data['debug'] ): ?>
      <!-- Debug output:
      <?php $this->text( 'debug' ); ?>
      -->
    <?php endif; ?>

<div id='Ersatzlesungen'></div>

<script>
  function htmlspecialchars (text) {
    var elem = document.createElement ("span");
    elem.appendChild (document.createTextNode (text || ''));
    return elem.innerHTML;
  }

  function Ersatzlesung (number) {
    this.number = number;

    this.pattern = function (obj, number) {
      return htmlspecialchars (obj.getAttribute ("data-pattern" + number));
    }
    this.prefix = function (obj) {
      return htmlspecialchars (obj.getAttribute ("data-prefix" + this.number));
    }
    this.suffix = function (obj) {
      return htmlspecialchars (obj.getAttribute ("data-suffix" + this.number));
    }

    this.span_name = function (ersatzlesung) {
      return "<a class=name>⸂<span>" + htmlspecialchars (ersatzlesung) + "</span>⸃</a>";
    }

    this.isGenitiv = function (obj) {
      return /^(?:[Uu]nseres|[Ee]ures) Herrn$/.test (this.pattern (obj, 2));
    }

    this.ersatzlesung = function (obj) {
      return this.span_name (this.pattern (obj, this.number));
    }

    this.replaceName = function (obj) {
      obj.innerHTML =  this.prefix (obj) + this.ersatzlesung (obj) + this.suffix (obj);
    }
  }

  ErsatzlesungName.prototype = new Ersatzlesung (1);
  function ErsatzlesungName (name) {
    this.name = name;
    this.ersatzlesung = function (obj) {
       if (this.isGenitiv (obj)) {
        return this.span_name (this.name) + "s";
      } else {
        return this.span_name (this.name);
      }
    }
  }

  ErsatzlesungGott.prototype = new Ersatzlesung (1);
  function ErsatzlesungGott () {
    this.ersatzlesung = function (obj) {
      if (this.isGenitiv (obj)) {
        return this.span_name ("Gottes");
      } else {
        return this.span_name ("Gott");
      }
    }
  }

  ErsatzlesungUnserGott.prototype = new Ersatzlesung (2);
  function ErsatzlesungUnserGott () {
    this.ersatzlesung = function (obj) {
      if (this.isGenitiv (obj)) {
        return this.span_name (this.pattern (obj, 2).replace (/ Herrn$/, " Gottes"));
      } else {
        return this.span_name (this.pattern (obj, 2).replace (/ Herrn$/, " Gott").replace (/ Herr$/, " Gott"));
      }
    }
  }

  ErsatzlesungDer.prototype = new Ersatzlesung (2);
  function ErsatzlesungDer (name, r) {
    this.name = name;
    this.r = r;
    this.ersatzlesung = function (obj) {
      if ("DU" == this.pattern (obj, 1)) {
        return this.span_name (this.name + this.r);
      } else {
        return this.span_name (
            this.pattern (obj, 2)
            .replace(/^(?:unser|euer) /, "der ").replace(/^(?:Unser|Euer) /, "Der ")
            .replace (/^(?:unsere|eure)([nms]) /, "de$1 ").replace (/^(?:Unsere|Eure)([nms]) /, "De$1 ")
            .replace (/ Herrn$/, " " + this.name + "n").replace (/ Herr$/, " " + this.name)
        );
      }
    }
  }

  ErsatzlesungHebräisch.prototype = new Ersatzlesung (2);
  function ErsatzlesungHebräisch (name) {
    this.name = name;
    this.ersatzlesung = function (obj) {
      if (this.isGenitiv (obj)) {
        return "von " + this.span_name (this.name);
      } else {
        return this.span_name (this.name);
      }
    }
  }

  function ErsatzlesungOriginal () {
    this.replaceName = function (obj) {
      obj.innerHTML = obj.getAttribute ("data-original");
    }
  }

  Ersatzlesungen = {
    "gemischt" : new ErsatzlesungOriginal,
    "‎יהוה" : new ErsatzlesungHebräisch ("‎יהוה"),
    "JHWH" : new ErsatzlesungName ("JHWH"),
    "Jahwe" : new ErsatzlesungName ("Jahwe"),
    "Jaho" : new ErsatzlesungName ("Jaho"),

    "Gott" : new ErsatzlesungGott,
    "der Herr" : new ErsatzlesungDer ("Herr", ""),
    "der Ewige" : new ErsatzlesungDer ("Ewige", "r"),
    "Ich/Du/Er" : new Ersatzlesung (1),
    "Ich-Bin-Da" : new ErsatzlesungName ("Ich-Bin-Da"),

    "Unser/Euer Gott" : new ErsatzlesungUnserGott,
    "Unser/Euer Herr" : new Ersatzlesung (2),
    "Adonai" : new ErsatzlesungHebräisch ("Adonai"),
    "Ha-Schem" : new ErsatzlesungHebräisch ("Ha-Schem"),
  };
  var Ersatzlesung = "gemischt";

  function replaceAllNames (neuerName) {
    var schalterArr = document.getElementsByClassName ("schalter");
    for (var i = 0; i < schalterArr.length; i++) {
      Ersatzlesungen [neuerName].replaceName (schalterArr [i]);
    }
    close ();

    document.getElementById (Ersatzlesung).style.fontWeight="normal";
    Ersatzlesung = neuerName.replace (/[\0\s\f\n\r\t\v]/, "_");
    setJavascriptFunctions();
  }

  function setOriginalName () {
    var schalterArr = document.getElementsByClassName ("schalter");
    var i = schalterArr.length;
    while (i--) {
      schalterArr [i].setAttribute ("data-original", schalterArr [i].innerHTML);
    }
  }

  function setJavascriptFunctions () {
    var nameArr = document.getElementsByClassName ("name");
    var i = nameArr.length;
    while (i--) {
      nameArr [i].setAttribute ("id", "ersatzlesung" + i);
      nameArr [i].setAttribute ("href", "Javascript:showErsatzlesungen('ersatzlesung" + i + "')");
      nameArr [i].setAttribute ("title", "Hier steht im Urtext der Gottesname JHWH. Für weitere Information und zum Ändern der Ersatzlesung bitte klicken.");
    }
  }

  function showErsatzlesungen (id) {
    var el = document.getElementById ("Ersatzlesungen");
    if (el.style.visibility == "visible") {
      close ();
    } else {
      var obj = document.getElementById (id);
      var left = obj.offsetLeft;
      var top = obj.offsetTop;
      for (var parent = obj.offsetParent; parent.tagName != "BODY"; parent = parent.offsetParent) {
        left += parent.offsetLeft;
        top += parent.offsetTop;
      }

      var maxleft = document.getElementsByTagName("html")[0].offsetWidth - document.getElementById ("Ersatzlesungen").offsetWidth - 10;
      if (maxleft <= 0) {
        el.style.left = "0";
        left = 0;
      } else if (maxleft < left) {
        el.style.left = maxleft + "px";
      } else {
        el.style.left = left + "px";
      }

      el.style.top = (top + obj.offsetHeight) + "px";
      el.style.visibility = "visible";
      document.getElementById ("beginErsatzlesungen").focus();
    }
    document.getElementById (Ersatzlesung).style.fontWeight="bold";
  }

  function changeLink (ersatzlesung) {
    return "<p><a id=\""  + htmlspecialchars (ersatzlesung.replace (/[\0\s\f\n\r\t\v]/, "_")) + "\" href='Javascript:replaceAllNames(\""  + htmlspecialchars (ersatzlesung) + "\")'>" + htmlspecialchars (ersatzlesung) + "</a></p>";
  }

  function close () {
    el = document.getElementById ("Ersatzlesungen");
    el.style.visibility = "hidden";
    el.style.left = 0;
    el.style.top = 0;
  }

  (function () {
    var html = "<p><span id=beginErsatzlesungen tabindex=-1>Ersatzlesung auswählen:</span>";
    html += "<div>";
    var count = 0;
    for (var ersatzlesung in Ersatzlesungen) {
      if (count++ >= 5) {
        html += "</div><div>";
        count = 1;
      }
      html += changeLink (ersatzlesung);
    }
    html += "</div>";

    html += "<p>Hier steht im Urtext der Gottesname <a href=/wiki/?title=JHWH>JHWH</a>,<br/>dessen genaue Aussprache unbekannt ist und<br/>der im Christentum und Judentum meistens<br/>durch eine Ersatzlesung wiedergegeben wird.";

    html += "<p><a href='Javascript:close()'>schließen</a>";

    document.getElementById ("Ersatzlesungen").innerHTML = html;

    setOriginalName();
    setJavascriptFunctions();
  })();
</script>

    </body></html>
    <?php
    wfRestoreWarnings();
  } // end of execute() method
} // end of class
