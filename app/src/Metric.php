<?php
namespace WebStatus;

class Metric
{
  private $data = [];
  private $name = '';
  private $max  = 200;

  public function __construct($name, $max = 200)
  {
    $this->name = $name;
    $this->max  = $max;
  }

  /**
   * Add a value in the stack
   *
   * @param int|float $value
   * @api
   */
  public function addValue($value)
  {
    while ($this->getCount() >= $this->max) {
      array_shift($this->data);
    }

    $this->data[] = $value;
  }

  /**
   * Get all data stack
   *
   * @return array
   * @api
   */
  public function getData()
  {
    return $this->data;
  }

  /**
   * Get metric name
   *
   * @return string
   * @api
   */
  public function getName()
  {
    return $this->name;
  }

  /**
   * Get average value
   *
   * @return float
   * @api
   */
  public function getAvg()
  {
    if (!$this->getCount()) {
      return (float)0;
    }

    return (float)array_sum($this->data) / $this->getCount();
  }

  /**
   * Get last value
   *
   * @return int|float
   * @api
   */
  public function getLast()
  {
    if (!$this->getCount()) {
      return 0;
    }

    return $this->data[$this->getCount() - 1];
  }

  /**
   * Get maximum value
   *
   * @return int|float
   * @api
   */
  public function getMax()
  {
    if (!$this->getCount()) {
      return 0;
    }

    return array_reduce(
      $this->data,
      function ($carry, $item) {
        return max($carry, $item);
      }, 
      0
    );
  }

  /**
   * Get minimum value
   *
   * @return int|float
   * @api
   */
  public function getMin()
  {
    if (!$this->getCount()) {
      return 0;
    }

    return array_reduce(
      $this->data,
      function ($carry, $item) {
        return min($carry, $item);
      },
      0
    );
  }

  /**
   * Get trends flag
   *
   * @param int|float $value
   * @param float $prctErr
   * 
   * @return int
   * 
   * @api
   */
  public function getTrend($value, $prctErr = 0.1)
  {
    if ($this->getCount() < 10) {
      return 0;
    }

    $avg = $this->getAvg();
    $mid = $avg * (1 - $prctErr);
    $high= $avg * (1 + $prctErr);
    if ($value <= $mid) {
      return -1;
    } elseif ($value <= $high) {
      return 0;
    }

    return 1;
  }

  /**
   * Get number of values
   *
   * @return int
   * @api
   */
  public function getCount()
  {
    return count($this->data);
  }

  /**
   * Get maximum number of values
   *
   * @return int
   * @api
   */
  public function getMaxItems()
  {
    return $this->max;
  }
}
