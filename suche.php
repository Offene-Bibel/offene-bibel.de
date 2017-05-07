<?php
  if (isset ($_GET ['suche']) && isset ($_GET ['in'])) {
    if ($_GET ['in'] == "volltext") {
      header ('Status: 302');
      header ('Location: /mediawiki/index.php?title=Spezial%3ASuche&profile=default&fulltext=Suche&search='.urlencode ($_GET ['suche']));
      die ();
    } elseif ($_GET ['in'] == "drupal") {
      header ('Status: 302');
      header ('Location: /search/?keys='.urlencode ($_GET ['suche']));
      die ();
    }
    header ('Status: 302');
    header ('Location: /mediawiki/index.php?title=Spezial%3ABibelstelle&abk='.urlencode ($_GET ['suche']));
    die ();
  }
  header ('Status: 302');
  header ('Location: /mediawiki/index.php?title=Spezial%3ABibelstelle');
  die ();
?>
