<?php
namespace WebStatus\App;

use Exception;

trait FrameworkTrait
{
  private $configFiles = ['routes', 'global', 'technologies'];
  private $composer;
  private $version;
  private $routes = [];
  private $okStates = ['yes', 'ok', 'on', '1', 1, 'true'];

  /**
   * Traverse a config file and return a subset
   * 
   * @param array|string $name
   * 
   * @return array|string|int|float
   * 
   * @api
   */
  public function getConfig($vector, array $data = null)
  {
    if (is_array($vector) && !count($vector)) {
      return $data;
    }

    if (is_array($vector)) {
      $key = array_shift($vector);

      if (null === $data) {
        return isset($this->config[$key])
          ? $this->getConfig($vector, $this->config[$key])
          : null;
      }

      if (isset($data[$key])) {
        return count($vector)
          ? $this->getConfig($vector, $data[$key])
          : $data[$key];
      }

      return;
    }

    if (isset($this->config[$vector])) {
      return $this->config[$vector];
    }
  }

  /**
   * Get base URL
   * 
   * @return string
   * 
   * @api
   */
  public function getBaseUrl()
  {
    $baseUrl = dirname(APP_SCRIPTNAME);

    if (substr($baseUrl, strlen($baseUrl) - 1) != '/') {
      $baseUrl .= '/';
    }

    return $baseUrl;
  }

  /**
   * Get first route key
   * 
   * @param string $name
   * 
   * @return string
   * 
   * @api
   */
  public function getRouteKey($name)
  {
    if (isset($this->routes[$name]) && count($this->routes[$name])) {
      return array_keys($this->routes[$name])[0];
    }
  }

  /**
   * Get request
   * 
   * @return string
   * 
   * @api
   */
  public function getRequest()
  {
    return $this->request;
  }

  /**
   * Get route label
   * 
   * @param string $name
   * 
   * @return string
   * 
   * @api
   */
  public function getRoute($name)
  {
    if (isset($this->routes[$name])) {
      return $this->routes[$name];
    }
  }

  /**
   * Build a route URL
   *
   * @param string $context
   * @param string $request
   *
   * @return string
   *
   * @api
   */
  public function getRouteUrl($context, $request = null)
  {
    if (null === $request) {
      return sprintf(
        '%s%s.php',
        $this->getBaseUrl(),
        $context
      );
    }

    return sprintf(
      '%s%s.php?id=%s',
      $this->getBaseUrl(),
      $context,
      $request
    );
  }

  /**
   * Get application version from a composer definition
   * 
   * @return string
   * 
   * @api
   */
  public function getVersion()
  {
    if (null === $this->version) {
      $this->version = $this->getComposer('version');
    }

    return $this->version;
  }

  /**
   * Get a composer key value
   * 
   * @param string $key
   * 
   * @return mixed
   * 
   * @api
   */
  public function getComposer($key)
  {
    if (!is_array($this->composer)) {
      $this->composer = json_decode(
        $this->read(dirname(APP_DIR) . '/composer.json'),
        true
      );
    }

    if (isset($this->composer[$key])) {
      return $this->composer[$key];
    }
  }

  /**
   * Validate an option value
   * 
   * @param string|int|float $value
   * 
   * @return bool
   * 
   * @api
   */
  public function validateState($value)
  {
    return in_array($value, $this->okStates);
  }

  /**
   * Load all config files and init routes
   */
  protected function loadConfig()
  {
    $max = 0;
    foreach ($this->configFiles as $filename) {
      $max = max(
        $max,
        $this->getFilemtime(CFG_DIR . "/$filename.ini.php"),
        $this->getFilemtime(CFG_DIR . "/$filename-custom.ini.php")
      );
    }

    if ($this->getFilemtime(CACHE_DIR . "/config.php") < $max) {
      $this->loadConfigFiles();
    } else {
      $this->config = include CACHE_DIR . "/config.php";
    }

    #1st level
    array_walk($this->config['routes'], function($value, $key) {
      if (preg_match('/^(?!icon|label|header|sub-)([a-z0-9\-\_]*)$/i', $key, $matches)) {
        #2nd level
        array_walk(
          $value, 
          function($value, $index) use ($key) {
            if (preg_match('/^(?!sub-header-|sub-icon-)sub-([a-z0-9\-\_]*)$/i', $index, $matches)) {
              $this->routes[$key][$matches[1]] = $this->config['routes'][$key]["sub-{$matches[1]}"];
            }
          }
        );
      }
    });
  }

  /**
   * Load all defined config files
   */
  protected function loadConfigFiles()
  {
    array_walk(
      $this->configFiles,
      function ($filename) {
        $this->config[$filename] = $this->loadIniFile($filename);
      }
    );

    $this->writeCache('config', $this->config);
  }

  /**
   * Get last modified time
   * 
   * @param string $path
   * 
   * @return int
   */
  public function getFilemtime($path)
  {
    return is_readable($path) ? filemtime($path) : 0;
  }

  /**
   * Load an ini file and cache it
   * 
   * @param string $filename
   * 
   * @return array
   * 
   * @throws \Exception
   */
  protected function loadIniFile($filename)
  {
    if (!is_readable(CFG_DIR . "/$filename.ini.php")) {
      throw new Exception (
        sprintf(
          '[ERROR] %s/%s.ini.php is not readable.', 
          CFG_DIR ,
          $filename
        )
      );
    }

    $config = parse_ini_file(CFG_DIR . "/$filename.ini.php", true);

    if (is_readable(CFG_DIR . "/$filename-custom.ini.php")) {
      $config = array_merge(
        $config,
        parse_ini_file(CFG_DIR . "/$filename-custom.ini.php", true)
      );
    }

    return $config;
  }

  /**
   * Write cache
   * 
   * @param string $key
   * @param array  $data
   * 
   * @return array $data
   */
  protected function writeCache($key, array $data)
  {
    if (is_writable(CACHE_DIR)) {
      file_put_contents(
        CACHE_DIR . "/$key.php",
        "<?php\nreturn " . var_export($data, true) . ";"
      );
    }

    return $data;
  }
}
