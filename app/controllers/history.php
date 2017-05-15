<?php

/**
 * Saves some history for monitoring
 */
include dirname(__DIR__) . '/bootstrap.php';

$app->getHistory('in')->addValue(round($app->getIn(), 2));
$app->getHistory('out')->addValue(round($app->getOut(), 2));
$app->getHistory('cpu')->addValue(round($app->getLocalCpuUsage(), 2));
$app->getHistory('mem')->addValue(round($app->getMemUsage(), 2));
$app->getHistory('swap')->addValue(round($app->getSwapUsage(), 2));
$app->getHistory('temp')->addValue((int)$app->getCpuTemperature());
$app->getHistory('sock')->addValue((int)$app->getSocketNum());
$app->getHistory('time')->addValue($app->getFormattedMicrotime());

$app->getHistory()->save();
