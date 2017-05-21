<?php
namespace WebStatus;

use Exception;
use Rain\Tpl;

class App
{
  use App\FrameworkTrait;
  use App\StatTrait;
  use App\TemplateTrait;

  protected $logs = [];
  protected $template;
  protected $context;
  protected $request;
  protected $history;

  /**
   * @param string $script
   * @param string $id
   */
  public function __construct($script, $request = null)
  {
    Tpl::configure([
      "tpl_dir"    => APP_DIR . "/templates/",
      "cache_dir"  => CACHE_DIR . "/templates/"
    ]);

    $this->loadConfig();

    $this->context = !preg_match(
        '@.*/(.*)\.php@i',
        $script,
        $matches
      )
      ? 'index' : $matches[1];

    $this->request = null !== $request
        && isset($this->getRoute($this->context)[$request])
      ? $request
      : $this->getRouteKey($this->context);

    $this->history = new History();
  }

  /**
   * Get history instance
   * 
   * @param string $name
   * 
   * @return \WebStatus\History|\WebStatus\Metric
   */
  public function getHistory($name = null)
  {
    if (null === $name) {
      return $this->history;
    }

    return $this->history->get($name);
  }

  /**
   * Get JS-formatted microtime
   *
   * @return int
   * 
   * @api
   */
  public function getFormattedMicrotime()
  {
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
   * 
   * @api
   */
  public function getEstimatedFilesize($size, $num, $max)
  {
    if ($num <= 0) {
      return 0;
    }

    return intval($size * $max / $num);
  }

  /**
   * Read a data file
   * 
   * @param string $path
   * 
   * @return string
   * 
   * @throws \Exception
   *
   * @api
   */
  public function read($path)
  {
    if (is_readable($path)) {
      return file_get_contents($path);
    }

    throw new Exception(
      sprintf('File "%s" is not readable.', $path)
    );
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
  protected function transformValue($string)
  {
    switch (substr($string, strlen($string) - 1)) {
      case 'K':
        return 1024 * str_replace('K', '', $string);
      case 'M':
        return 1024 * 1024 * str_replace('M', '', $string);
      case 'G':
        return 1024 * 1024 * 1024 * str_replace('G', '', $string);
      default:
        return 1 * (float)$string;
    }
  }
}
