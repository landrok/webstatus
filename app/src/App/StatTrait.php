<?php
namespace WebStatus\App;

use WebStatus\History;

trait StatTrait
{
  /**
   * Get OS description
   * 
   * @return string
   * 
   * @api
   */
  public function getOs()
  {
    $this->readSplit('os');

    return $this->logs['os'][0];
  }

  /**
   * Get kernel version
   * 
   * @return string
   * 
   * @api
   */
  public function getKernel()
  {
    $this->readSplit('os');

    return isset($this->logs['os'][1]) 
               ? $this->logs['os'][1] : '-';
  }

  /**
   * Get CPU temperature
   * 
   * @return string
   * 
   * @api
   */
  public function getCpuTemperature()
  {
    $this->readSplit('os');

    return isset($this->logs['os'][2]) 
               ? $this->logs['os'][2] : '-';
  }

  /**
   * Get uptime
   * 
   * @return string
   * 
   * @api
   */
  public function getUp()
  {
    $this->readSplit('uptime', 'up');

    return isset($this->logs['uptime'][1]) 
               ? $this->logs['uptime'][1] : '-';
  }

  /**
   * Get started at
   * 
   * @return string
   * 
   * @api
   */
  public function getStarted()
  {
    $this->readSplit('uptime', 'up');

    $started = preg_replace(
      '/Started at\s+/', 
      '', 
      $this->logs['uptime'][0]
    );

    return preg_replace('/up\s\w+/', '', $started);
  }

  /**
   * Get memory usage
   * 
   * @return float
   * 
   * @api
   */
  public function getMemUsage()
  {
    return $this->getMemoryLine(1);
  }

  /**
   * Get swap usage
   * 
   * @return float
   * 
   * @api
   */
  public function getSwapUsage()
  {
    return $this->getMemoryLine(3);
  }

  /**
   * Get a memory usage ratio for a line of free command
   * 
   * @param int $line
   * @return float
   */
  protected function getMemoryLine($line)
  {
    $this->readSplit('memory');

    $total  = 0;
    $used   = 0;
    if (isset($this->logs['memory'][$line])) {
      $temp = preg_split('/\s+/', $this->logs['memory'][$line]);
      $total= isset($temp[1]) ? $this->transformValue($temp[1]) : 0;
      $used = isset($temp[2]) ? $this->transformValue($temp[2]) : 0;
    }

    return (float)($total > 0 ? round(100 * $used / $total, 2) : 0);
  }

  /**
   * Get CPU usage
   * 
   * @return float
   * 
   * @api
   */
  public function getCpuUsage()
  {
    return (float)$this->getHistory('cpu')->getLast();
  }

  /**
   * Get disk space usage
   * 
   * @return float
   * 
   * @api
   */
  public function getDiskUsage() 
  {
    $this->readSplit('hdd');

    if (!isset($this->logs['hdd'][1])) {
      return 0;
    }

    $diskStat = preg_split('/\s+/', $this->logs['hdd'][1]);

    return isset($diskStat[4]) ? (float)$diskStat[4] : 0;
  }

  /**
   * Get number of open sockets
   * 
   * @return int
   * 
   * @api
   */
  public function getSocketNum() 
  {
    $this->readSplit('tcp-sockets');

    return count($this->logs['tcp-sockets']) > 2 
        ? (count($this->logs['tcp-sockets']) - 2 ) : 0;
  }

  /**
   * Get number of listening servers
   * 
   * @return int
   * 
   * @api
   */
  public function getServerNum()
  {
    if (!isset($this->logs['servers'])) {
      $this->logs['servers'] = $this->read(DATA_DIR . '/servers.log');
    }

    return substr_count($this->logs['servers'], 'LISTEN');
  }

  /**
   * Get input traffic
   * 
   * @return float
   * 
   * @api
   */
  public function getIn()
  {
    return $this->getIfstatValue(2);

    $this->readSplit('ifstat', '\s+');
  }

  /**
   * Get output traffic
   * 
   * @return float
   * 
   * @api
   */
  public function getOut()
  {
    return $this->getIfstatValue(1);
  }

  /**
   * Get an ifstat value
   * 
   * @param int $index Starting from last value
   * @return float
   */
  protected function getIfstatValue($index)
  {
    $this->readSplit('ifstat', '\s+');

    while (isset($this->logs['ifstat'][count($this->logs['ifstat']) - 1]) 
      && $this->logs['ifstat'][count($this->logs['ifstat']) - 1] == ''
    ) {
      unset($this->logs['ifstat'][count($this->logs['ifstat']) - 1]);
    }

    $count = count($this->logs['ifstat']);

    return (float)(isset($this->logs['ifstat'][$count - $index]) 
           ? 1 * $this->logs['ifstat'][$count - $index] : 0);
  }

  /**
   * Get number of processes that matches name
   * 
   * @param string $name
   * 
   * @return int
   * 
   * @api
   */
  public function getProcessNum($name)
  {
    $this->readSplit('processes');

    return array_reduce(
      $this->logs['processes'], 
      function ($carry, $item) use ($name) {
        if (strpos($item, $name)) {
          $carry++;
        }
        return $carry;
      }, 0
    );
  }

  /**
   * Get number of connected users
   * 
   * @return int
   * 
   * @api
   */
  public function getUserNum()
  {
    $this->readSplit('users');

    return count($this->logs['users']) > 2 
         ? count($this->logs['users']) - 2 : 0;
  }

  /**
   * Get number of interfaces
   * 
   * @return int
   * 
   * @api
   */
  public function getIfNum()
  {
    if (!isset($this->logs['interfaces'])) {
      $this->logs['interfaces'] = $this->read(
        DATA_DIR . '/interfaces.log'
      );
    }

    return substr_count(
      $this->logs['interfaces'], 
      'Link encap:Ethernet'
    );
  }

  /**
   * Get last datetime for status
   * 
   * @return string
   * 
   * @api
   */
  public function getStatusDate()
  {
    if (!is_readable(DATA_DIR . '/status.log')) {
      return date("D M d H:i:s T Y", time());
    }

    return date("D M d H:i:s T Y", filemtime(DATA_DIR . '/status.log'));
  }

  /**
   * Get hostname
   * 
   * @return string
   * 
   * @api
   */
  public function getHostname()
  {
    if (!isset($this->logs['status'])) {
      $this->logs['status'] = $this->read(
        DATA_DIR . '/status.log'
      );
    }

    $xpt = explode('@', $this->logs['status']);

    return isset($xpt[1]) ? trim($xpt[1]) : gethostname();
  }


  /**
   * CPU Usage, not based on logged file
   * 
   * @return float
   * 
   * @api
   */
  public function getLocalCpuUsage()
  {
    $load = sys_getloadavg();
    $cpu = substr_count(
      shell_exec('cat /proc/cpuinfo'),
      'processor'
    );
    $cpu = $cpu > 0 ? $cpu : 1;
    return 100 * (float)$load[0] / $cpu;
  }

  protected function readSplit($item, $pattern = "\n")
  {
    if (!isset($this->logs[$item])) {
      $this->logs[$item] = preg_split(
        "/$pattern/", 
        $this->read(DATA_DIR . "/$item.log")
      );
    }
  }
}
