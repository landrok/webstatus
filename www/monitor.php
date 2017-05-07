<?php

require dirname(__DIR__) . '/app/bootstrap.php';

/**
 * Monitor
 */
use Rain\Tpl;
use WebStatus\History;

// Get some data to update chart
if (isset($_REQUEST['data'])) {
  include APP_DIR . '/controllers/monitor.php';
  return;
}

$tpl = new Tpl();
$template->assign('html', $tpl->draw('monitor.charts', true));

$template->assign('norefresh', true);
$template->assign('footerLibraries', sprintf(
  '<script>var appHistory = %s;</script>
<script src="%s/asset/highcharts/highcharts.js"></script>
<script src="%s/asset/app/monitor.js"></script>',
  json_encode(History::getData()),
  BASEURL,
  BASEURL
));


# Print result
$app->render('layout');
