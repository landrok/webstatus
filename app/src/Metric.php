<?php
namespace WebStatus;

class Metric
{
  private $data = [];
  private $name = '';
  private $max  = 10000;

  public function __construct($name, $max = 1440) {
    $this->name = $name;
    $this->max  = $max;
  }

  public function addValue($value) {
    while ($this->getCount() >= $this->max) {
      array_shift($this->data);
    }

    $this->data[] = $value;
  }

  public function getData() {
    return $this->data;
  }

  public function getName() {
    return $this->name;
  }

  public function getAvg() {
    if (!$this->getCount()) {
      return 0;
    }

    return array_sum($this->data) / $this->getCount();
  }

  public function getLast() {
    if (!$this->getCount()) {
      return 0;
    }

    return $this->data[$this->getCount() - 1];
  }

  public function getMax() {
    if (!$this->getCount()) {
      return 0;
    }

    return array_reduce($this->data, function ($carry, $item) {
      return max($carry, $item);
    }, 0);
  }

  public function getMin() {
    if (!$this->getCount()) {
      return 0;
    }

    return array_reduce($this->data, function ($carry, $item) {
      return min($carry, $item);
    }, 0);
  }

  public function getTrend($value, $prct_err = 0.1) {
    if ($this->getCount() < 10) {
      return 0;
    }

    $avg = $this->getAvg();
    $mid = $avg * (1 - $prct_err);
    $high= $avg * (1 + $prct_err);
    if ($value <= $mid) {
      return -1;
    } elseif ($value <= $high) {
      return 0;
    }

    return 1;
  }

  public function getCount() {
    return count($this->data);
  }

  public function getMaxItems() {
    return $this->max;
  }
}
