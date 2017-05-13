<?php

namespace WebStatus;

class History
{
  private static $status = [];

  /**
   * Load all history
   */
  private static function loadHistory() {    
    if (is_readable(DATA_DIR . '/history.json')) {
      $history = json_decode(
        file_get_contents(DATA_DIR . '/history.json'),
        true
      );
    }

    if (!isset($history) || !is_array($history)) {
      $history = [];
    }

    array_walk($history, function ($values, $name) {
      self::add($values, $name);
    });
    
  }

  /**
   * Get one metric
   * 
   * @param string $name
   * 
   * @return \WebStatus\Metric
   */
  public static function get($name) {
    if (!count(self::$status)) {
      self::loadHistory();
    }

    if (!isset(self::$status[$name])) {
      self::$status[$name] = new Metric($name);
    }
    
    return self::$status[$name];
  }

  /**
   * Save data into data and cache dirs
   */
  public static function save() {
    /**
     * Write into the cache
     */
    if (is_writable(CACHE_DIR)) {
      file_put_contents(
        CACHE_DIR . '/history.php',
        "<?php\nreturn " . var_export(self::getData(), true) . ";"
      );
    }

    /**
     * Write into the data dir JSON
     */
    if (is_writable(DATA_DIR)) {
      file_put_contents(
        DATA_DIR . '/history.json',
        json_encode(self::getData())
      );
    }
  }

  /**
   * Add a new value for each metric
   * 
   * @param array $values
   */
  public static function add(array $values, $name) {
    if (!isset(self::$status[$name])) {
      self::$status[$name] = new Metric($name);
    }

    array_walk($values, function ($items) use ($name) {
      self::$status[$name]->addValue($items);
    });
  }

  /**
   * Get all data
   * [name][index], lightest mode
   * 
   * @return array
   */
  public static function getData() {
    if (!count(self::$status)) {
      self::loadHistory();
    }

    $data = [];

    array_walk(self::$status,
      function ($metric, $index) use (& $data) {
          $data[$index] = $metric->getData();
      }
    );

    return $data;
  }

  /**
   * Get history status
   * 
   * @return array
   */
  public static function getStatus() {
    if (!count(self::$status)) {
      self::loadHistory();
    }

    $dbSize = is_readable(DATA_DIR . '/history.json')
            ? filesize(DATA_DIR . '/history.json') : 0;
    $cacheSize = is_readable(CACHE_DIR . '/history.php')
            ? filesize(CACHE_DIR . '/history.php') : 0;

    $items = [];
    $max = 0;
    $num = 0;
    array_walk(self::$status,
      function ($metric, $index) use (& $items, & $max, & $num) {
          $items[$index] = $metric->getCount();
          if (!$max) {
            $max = $metric->getMaxItems();
            $num = $metric->getCount();
          }
      }
    );

    return [
      'dbSize'    => $dbSize,
      'cacheSize' => $cacheSize,
      'maxItems'  => $max,
      'numItems'  => $num,
      'items'     => self::$status
    ];
  }
}
