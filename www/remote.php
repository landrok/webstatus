<?php

include dirname(__DIR__) . '/app/bootstrap.php';

if ($app->getConfig(['global', 'remote', 'remote.server']) != 1) {
  exit;
}

if (isset($_FILES, $_FILES['data'], $_FILES['data']['name'])
  && $_FILES['data']['name'] == 'webstatus.tar.gz') {
  echo date('Y-m-d H:i:s') . " OK\n";
  $data = file_get_contents($_FILES['data']['tmp_name']);
  file_put_contents(CACHE_DIR . '/webstatus.tar.gz', $data);
}
