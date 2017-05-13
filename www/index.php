<?php

require dirname(__DIR__) . '/app/bootstrap.php';

/**
 * Home
 * Global status summary
 */
use Rain\Tpl;
use WebStatus\History;

$tpl = new Tpl();

$tpl->assign('tableClass', 'table table-hover table-striped table-condensed');
$tpl->assign('app', $app);

# Memory
$tpl->assign('memTrend', History::get('mem')->getTrend($app->getMemUsage()));

# CPU
$tpl->assign('cpuTrend', History::get('cpu')->getTrend(
  $app->getCpuUsage()
));

# Processes
$patterns = explode(
  '|', 
  $app->getConfig(['global', 'cron', 'processes.pattern'])
);

$tpl->assign('patterns', $patterns);

$template->assign('html', $tpl->draw('index.tables', true));

$app->render('layout');
