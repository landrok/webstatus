<?php
ini_set('display_errors', '1');

use WebStatus\App;

if (!is_readable(dirname(__DIR__) . "/vendor/autoload.php")) {
  die('Run ./bin/install.sh');
}

require_once dirname(__DIR__) . "/vendor/autoload.php";

# Global constants
defined('APP_STARTIME') || define('APP_STARTIME', microtime(true));
defined('APP_DIR')      || define('APP_DIR', __DIR__);
defined('APP_NAME')     || define('APP_NAME', 'WebStatus');
defined('DATA_DIR')     || define('DATA_DIR', '/dev/shm/webstatus');
defined('CFG_DIR')      || define('CFG_DIR', APP_DIR . '/config');
defined('CACHE_DIR')    || define('CACHE_DIR', APP_DIR . '/cache');

$app = new App();
$template = $app->getTemplate();

defined('BASEURL')     || define('BASEURL', $app->getBaseUrl());
defined('APP_VERSION') || define('APP_VERSION', $app->getVersion());
