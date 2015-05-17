<?php
/**
 * OffeneBibel skin
 *
 * @file
 * @ingroup Skins
 * @version 1.2.0
 * @author Olaf Schmidt-Wischhöfer (olaf@offene-bibel.de)
 * @license http://www.gnu.org/copyleft/gpl.html GPL
 */
if ( ! defined ('MEDIAWIKI')) {
  die ("This is a skins file for mediawiki and should not be viewed directly.\n");
}

$wgValidSkinNames['offenebibel'] = 'OffeneBibel';
 
require_once( $GLOBALS["IP"] . '/includes/skins/SkinTemplate.php');
require_once( $GLOBALS["IP"] . '/../static/server-side/headerFooter.php');


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
    $out->addStyle( '/static/css/lib/bootstrap.css', 'screen' );
    $out->addStyle( '/static/css/header_footer.css', 'screen' );
    $out->addStyle( 'OffeneBibel/main.css', 'screen' );
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
    <script data-main="/static/js/main.js" src="/static/js/lib/require.js"></script>
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
                   if (substr($key, 0, 5) === 'nstab')  {
                                echo '
                        <li id="', Sanitizer::escapeId( "ca-$key" ), '"';
                                if ( $tab['class'] ) {
                                        echo ' class="', htmlspecialchars($tab['class']), '"';
                                }
                                echo '><a href="', htmlspecialchars($tab['href']), '"',
                                        Xml::expandAttributes ($skin->tooltipAndAccesskeyAttribs('ca-'.$key)), '>',
                                        htmlspecialchars($tab['text']),
                                        '</a></li>';
                }
              }?>
              <?php
              foreach( $this->data['content_actions'] as $key => $tab ) {
                   if ($key === 'talk')  {
                                echo '
                        <li id="', Sanitizer::escapeId( "ca-$key" ), '"';
                                if ( $tab['class'] ) {
                                        echo ' class="', htmlspecialchars($tab['class']), '"';
                                }
                                echo '><a href="', htmlspecialchars($tab['href']), '"',
                                        Xml::expandAttributes ($skin->tooltipAndAccesskeyAttribs('ca-'.$key)), '>',
                                        htmlspecialchars($tab['text']),
                                        '</a></li>';
                }
              }?>
             <li class="dropdown">
               <a href="" class="dropdown-toggle" data-hover="dropdown" data-toggle="dropdown">Bearbeiten <span class="caret"></span></a>
                 <ul class="dropdown-menu pull-right">

              <?php
              foreach( $this->data['content_actions'] as $key => $tab ) {
                   if ($key !== 'talk' && substr($key, 0, 5) !== 'nstab')  {
                                echo '
                        <li id="', Sanitizer::escapeId( "ca-$key" ), '"';
                                if ( $tab['class'] ) {
                                        echo ' class="', htmlspecialchars($tab['class']), '"';
                                }
                                echo '><a href="', htmlspecialchars($tab['href']), '"',
                                        Xml::expandAttributes ($skin->tooltipAndAccesskeyAttribs('ca-'.$key)), '>',
                                        htmlspecialchars($tab['text']),
                                        '</a></li>';
                }
              }?>
                  </ul>
               </li>
               <li class="dropdown">
               <a href="" class="dropdown-toggle" data-hover="dropdown" data-toggle="dropdown"><?php $this->msg('toolbox') ?> <span class="caret"></span></a>
                 <ul class="dropdown-menu pull-right">
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
  
                    if(false && !empty( $this->data['nav_urls']['print']['href'] ) ) { ?>
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
               </li>
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
                  <form action="<?php echo $GLOBALS ['wgScriptPath']?>/index.php" method="get">
                    <input type="hidden" name="title" value="Spezial:Bibelstelle" />
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

              <noscript>
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
                <?php } ?>
              </div> <!-- p-personal -->
              </noscript>
            <?php } elseif ($bar == 'TOOLBOX') { ?>
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
            <?php } elseif ($bar !== 'TOOLBOX') { ?>
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
    </body></html>
    <?php
    wfRestoreWarnings();
  } // end of execute() method
} // end of class
