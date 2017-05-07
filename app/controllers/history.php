<?php

/**
 * Saves some history for monitoring
 */
include dirname(__DIR__) . '/bootstrap.php';

use WebStatus\History;

History::get('in')->addValue(round($app->getIn(), 2));
History::get('out')->addValue(round($app->getOut(), 2));
History::get('cpu')->addValue(round($app->getLocalCpuUsage(), 2));
History::get('mem')->addValue(round($app->getMemUsage(), 2));
History::get('swap')->addValue(round($app->getSwapUsage(), 2));
History::get('temp')->addValue((int)$app->getCpuTemperature());
History::get('sock')->addValue((int)$app->getSocketNum());

History::get('time')->addValue($app->getFormattedMicrotime());

History::save();
