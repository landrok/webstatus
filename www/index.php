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

# System
$tpl->assign('cpuTemperatureLabel', $app->getStatusLabel(
  $app->getCpuTemperature(),
  $app->getConfig(['global', 'thresholds', 'temp.mid']),
  $app->getConfig(['global', 'thresholds', 'temp.high'])
));
$tpl->assign('temperatureUrl', 'status.php?id=temperature');

# Uptime
$tpl->assign('uptimeUrl', 'status.php?id=uptime');

# Memory
$tpl->assign('memTrend', History::get('mem')->getTrend($app->getMemUsage()));
$tpl->assign('memUsageLabel', $app->getStatusLabel(
  $app->getMemUsage(),
  $app->getConfig(['global', 'thresholds', 'mem.mid']),
  $app->getConfig(['global', 'thresholds', 'mem.high'])
));
$tpl->assign('memUrl', 'status.php?id=memory');

# SWAP
$tpl->assign('swapUsageLabel', $app->getStatusLabel(
  $app->getSwapUsage(),
  $app->getConfig(['global', 'thresholds', 'swap.mid']),
  $app->getConfig(['global', 'thresholds', 'swap.high'])
));

# CPU
$tpl->assign('cpuTrend', History::get('cpu')->getTrend(
  $app->getCpuUsage()
));
$tpl->assign('cpuUsageLabel', $app->getStatusLabel(
  $app->getCpuUsage(),
  $app->getConfig(['global', 'thresholds', 'cpu.mid']),
  $app->getConfig(['global', 'thresholds', 'cpu.high'])
));
$tpl->assign('usageUrl', 'status.php?id=top');

# Disk
$tpl->assign('diskUsageLabel', $app->getStatusLabel(
  $app->getDiskUsage(),
  $app->getConfig(['global', 'thresholds', 'disk.mid']),
  $app->getConfig(['global', 'thresholds', 'disk.high'])
));
$tpl->assign('diskUrl', 'status.php?id=hdd');

# Sockets
$tpl->assign('socketUrl', 'status.php?id=tcp-sockets');

# Servers
$tpl->assign('serverUrl', 'status.php?id=servers');

# Processes
$patterns = explode(
  '|', 
  $app->getConfig(['global', 'cron', 'processes.pattern'])
);

$processList = [];

if (count($patterns)) {
  foreach ($patterns as $pattern) {
    $count = $app->getProcessNum($pattern);
    $processList[] = [
      'width' => '35%',
      'pattern' => $pattern,
      'label-class'=> $count ? 'success' : 'danger',
      'label-text' => $count ? 'UP' : 'DOWN',
      'count'      => $count
    ];
  }
}
$tpl->assign('processList', $processList);
$tpl->assign('processUrl', 'status.php?id=processes');

# users
$tpl->assign('userUrl', 'status.php?id=users');

# Interfaces
$tpl->assign('ifUrl', 'status.php?id=interfaces');
$tpl->assign('ifstatUrl', 'status.php?id=ifstat');

$template->assign('html', $tpl->draw('index.tables', true));
$app->render('layout');
