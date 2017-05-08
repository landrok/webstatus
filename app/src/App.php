<?php
namespace WebStatus;

use Rain\Tpl;

class App
{
  const VERSION = '0.2.1';

  use App\FrameworkTrait;
  use App\StatTrait;
  use App\TemplateTrait;

  protected $logs = [];
  protected $template;
  protected $context;
  protected $request;
  protected $routes = [];

  public function __construct() {
    Tpl::configure([
      "tpl_dir"    => APP_DIR . "/templates/",
      "cache_dir"  => CACHE_DIR . "templates/"
    ]);

    $this->loadConfig();

    // Context  
    $this->context = !preg_match(
        '@.*/(.*)\.php@i',
        $_SERVER['SCRIPT_NAME'],
        $matches
      )
      ? 'index' : $matches[1];

    $this->request = isset(
        $_REQUEST['id'], 
        $this->getRoute($this->context)[$_REQUEST['id']]
      )
      ? $_REQUEST['id'] 
      : $this->getDefaultRoute($this->context);
  }

  /**
   * Get Web Status version
   * 
   * @return string
   */
  public function getVersion() {
    return static::VERSION;
  }

  public function getRoute($name) {
    if (isset($this->routes[$name])) {
      return $this->routes[$name];
    }
  }

  public function getRequest() {
    return $this->request;
  }

  public function getBaseUrl() {
    $baseUrl = isset($_SERVER['SCRIPT_NAME']) 
           ? dirname($_SERVER['SCRIPT_NAME']) : '';

    if (substr($baseUrl, strlen($baseUrl) - 1) != '/') {
      $baseUrl .= '/';
    }
    
    return $baseUrl;
  }

  public function getDefaultRoute($name) {
    if (isset($this->routes[$name][0])) {
      return array_keys($this->routes[$name])[0];
    }
  }

  /**
   * Get JS-formatted microtime
   *
   * @return int
   */
  function getFormattedMicrotime() {
    $time = number_format(round(microtime(true), 3), 3, '.', '');
    return 1 * str_replace('.', '', $time);
  }

  /**
   * Get estimated filesize
   *
   * @param int $size Current filesize
   * @param int $num Current number of elements
   * @param int $max Maximum number of elements
   * 
   * @return int
   */
  function getEstimatedFilesize($size, $num, $max) {
    if ($num <= 0) {
      return 0;
    }

    return intval($size * $max / $num);
  }

  /**
   * Read a data file
   * 
   * @return string
   */
  public function read($filename) {
    if (is_readable($filename)) {
      return file_get_contents($filename);
    }

    die(sprintf('File %s is not readable.', $filename));
  }

  /**
   * Transform a value suffixed by K, M, G
   * eg. 9K => 9000
   *   2.2M => 2200000
   * 
   * @param string $string
   * 
   * @return int|float
   */
  private function transformValue($string) {
    switch (substr($string, strlen($string) - 1)) {
      case 'K':
        return 1000 * str_replace('K', '', $string);
      case 'M':
        return 1000000 * str_replace('M', '', $string);
      case 'G':
        return 1000000000 * str_replace('G', '', $string);
      default:
        return 1 * $string;
    }
  }
}
