<?php
ini_set('display_errors', '1');

use Rain\Tpl;
use WebStatus\App;

if (!is_readable(dirname(__DIR__) . "/vendor/autoload.php")) {
  die('Run ./bin/install.sh');
}

require_once dirname(__DIR__) . "/vendor/autoload.php";

if (!isset($context, $id)) {
  $context = $_SERVER['SCRIPT_NAME'];
  $id = isset($_REQUEST['id']) ? $_REQUEST['id'] : null;
}

# Global constants
defined('APP_STARTIME')   || define('APP_STARTIME', microtime(true));
defined('APP_DIR')        || define('APP_DIR', __DIR__);
defined('APP_NAME')       || define('APP_NAME', 'WebStatus');
defined('APP_SCRIPTNAME') || define('APP_SCRIPTNAME', $context);
defined('DATA_DIR')       || define('DATA_DIR', '/dev/shm/webstatus');
defined('CFG_DIR')        || define('CFG_DIR', APP_DIR . '/config');
defined('CACHE_DIR')      || define('CACHE_DIR', APP_DIR . '/cache');

Tpl::configure([
  "tpl_dir"   => APP_DIR   . "/templates/",
  "cache_dir" => CACHE_DIR . "/templates/"
]);

$app = new App($context, $id);

$template = $app->getTemplate();

defined('BASEURL')        || define('BASEURL', $app->getBaseUrl());
defined('APP_VERSION')    || define('APP_VERSION', $app->getVersion());
