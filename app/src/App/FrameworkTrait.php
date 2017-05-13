<?php
namespace WebStatus\App;

trait FrameworkTrait
{
  private $configFiles = ['routes', 'global', 'technologies'];

  /**
   * Traverse a config file and return a subset
   * 
   * @param array|string $name
   * 
   * @return array|string|int|float
   */
  public function getConfig($vector, array $data = null) {
    if (is_array($vector) && !count($vector)) {
      return $data;
    }

    if (is_array($vector)) {
      $key = array_shift($vector);

      if (null === $data) {
        return isset($this->config[$key])
          ? $this->getConfig($vector, $this->config[$key]) : null;
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
   * Load all config files and init routes
   */
  protected function loadConfig() {
    foreach ($this->configFiles as $filename) {
      $this->config[$filename] = $this->loadIniFile($filename);
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
   * Load an ini file and cache it
   * 
   * @param string $filename
   * 
   * @return array
   */
  protected function loadIniFile($filename)
  {
    if (!is_readable(CFG_DIR . "$filename.ini.php")) {
      die(
        sprintf(
          '[ERROR] %s%s.ini.php is not readable.', 
          CFG_DIR ,
          $filename
        )
      );
    }

    $mTimeCache = is_readable(CACHE_DIR . "$filename.php")
      ? filemtime(CACHE_DIR . "$filename.php") : 0;
    $mTimeIni = filemtime(CFG_DIR . "$filename.ini.php");
    $mTimeCustomIni = is_readable(CFG_DIR . "$filename-custom.ini.php")
      ? filemtime(CFG_DIR . "$filename-custom.ini.php") : 0;

    # Load from cache
    if ($mTimeCache > max($mTimeIni, $mTimeCustomIni)) {
      return include (CACHE_DIR . "$filename.php");
    }

    # New configs must be loaded
    $config = parse_ini_file(CFG_DIR . "$filename.ini.php", true);

    if (is_readable(CFG_DIR . "$filename-custom.ini.php")) {
      $config += parse_ini_file(CFG_DIR . "$filename-custom.ini.php", true);
    }

    return $this->writeCache($filename, $config);
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
        CACHE_DIR . "$key.php",
        "<?php\nreturn " . var_export($data, true) . ";"
      );
    }

    return $data;
  }
}
