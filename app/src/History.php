<?php

namespace WebStatus;

class History
{
  private $status = [];

  /**
   * Load all history
   */
  private function loadHistory()
  {    
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
      $this->add($values, $name);
    });
  }

  /**
   * Get one metric
   * 
   * @param string $name
   * 
   * @return \WebStatus\Metric
   */
  public function get($name)
  {
    if (!count($this->status)) {
      $this->loadHistory();
    }

    if (!isset($this->status[$name])) {
      $this->status[$name] = new Metric($name);
    }
    
    return $this->status[$name];
  }

  /**
   * Save data into data and cache dirs
   * 
   * @api
   */
  public function save()
  {
    /**
     * Write into the cache
     */
    if (is_writable(CACHE_DIR)) {
      file_put_contents(
        CACHE_DIR . '/history.php',
        "<?php\nreturn " . var_export($this->getData(), true) . ";"
      );
    }

    /**
     * Write into the data dir JSON
     */
    if (is_writable(DATA_DIR)) {
      file_put_contents(
        DATA_DIR . '/history.json',
        json_encode($this->getData())
      );
    }
  }

  /**
   * Add a new value for each metric
   * 
   * @param array $values
   * @param string $name
   * 
   * @api
   */
  public function add(array $values, $name)
  {
    if (!isset($this->status[$name])) {
      $this->status[$name] = new Metric($name);
    }

    array_walk($values, function ($items) use ($name) {
      $this->status[$name]->addValue($items);
    });
  }

  /**
   * Get all data
   * [name][index], lightest mode
   * 
   * @return array
   * 
   * @api
   */
  public function getData()
  {
    if (!count($this->status)) {
      $this->loadHistory();
    }

    $data = [];

    array_walk($this->status,
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
   * 
   * @api
   */
  public function getStatus()
  {
    if (!count($this->status)) {
      $this->loadHistory();
    }

    $dbSize = is_readable(DATA_DIR . '/history.json')
            ? filesize(DATA_DIR . '/history.json') : 0;
    $cacheSize = is_readable(CACHE_DIR . '/history.php')
            ? filesize(CACHE_DIR . '/history.php') : 0;

    $max = 0;
    $num = 0;
    array_walk($this->status,
      function ($metric, $index) use (& $max, & $num) {
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
      'items'     => $this->status
    ];
  }
}
