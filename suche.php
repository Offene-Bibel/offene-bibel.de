<?php
  if (isset ($_GET ['suche']) && isset ($_GET ['in'])) {
    if ($_GET ['in'] == "drupal") {
      header ('Status: 302');
      header ('Location: /search/?keys='.urlencode ($_GET ['suche']));
      die ();
    } elseif ($_GET ['in'] == "diskussion") {
      header ('Status: 302');
      header ('Location: /wiki/index.php?title=Spezial%3ASuche&ns1=1&ns101=1&redirs=1&fulltext=Erweiterte+Suche&search='.urlencode ($_GET ['suche']));
      die ();
    }
    header ('Status: 302');
    header ('Location: /wiki/index.php?title=Spezial%3ASuche&ns0=1&ns100=1&redirs=1&fulltext=Erweiterte+Suche&search='.urlencode ($_GET ['suche']));
    die ();
  }
  header ('Status: 302');
  header ('Location: /wiki/index.php?title=Spezial%3ASuche&ns0=1&ns100=1&redirs=1&fulltext=Erweiterte+Suche');
  die ();
?>
