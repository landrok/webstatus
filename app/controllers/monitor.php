<?php

echo json_encode([
  'in'    => $app->getIn(),
  'out'   => $app->getOut(),
  'cpu'   => $app->getCpuUsage(),
  'mem'   => $app->getMemUsage(),
  'time'  => $app->getFormattedMicrotime()
]);
