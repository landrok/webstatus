<?php
namespace WebStatus\App;

use WebStatus\History;

trait StatTrait
{
  /**
   * Get OS description
   * 
   * @return string
   */
  public function getOs() {
    if (!isset($this->logs['os'])) {
      $this->logs['os'] = explode(
        "\n",
        $this->read(DATA_DIR . '/os.log')
      );
    }

    return $this->logs['os'][0];
  }

  /**
   * Get kernel version
   * 
   * @return string
   */
  public function getKernel() {
    if (!isset($this->logs['os'])) {
      $this->logs['os'] = explode(
        "\n",
        $this->read(DATA_DIR . '/os.log')
      );
    }

    return isset($this->logs['os'][1]) 
               ? $this->logs['os'][1] : '-';
  }

  /**
   * Get CPU temperature
   * 
   * @return string
   */
  public function getCpuTemperature() {
    if (!isset($this->logs['os'])) {
      $this->logs['os'] = explode(
        "\n",
        $this->read(DATA_DIR . '/os.log')
      );
    }

    return isset($this->logs['os'][2]) 
               ? $this->logs['os'][2] : '-';
  }

  /**
   * Get uptime
   * 
   * @return string
   */
  public function getUp() {
    if (!isset($this->logs['uptime'])) {
      $this->logs['uptime'] = explode(
        "up",
        $this->read(DATA_DIR . '/uptime.log')
      );
    }

    return isset($this->logs['uptime'][1]) 
               ? $this->logs['uptime'][1] : '-';
  }

  /**
   * Get started at
   * 
   * @return string
   */
  public function getStarted() {
    if (!isset($this->logs['uptime'])) {
      $this->logs['uptime'] = explode(
        "up",
        $this->read(DATA_DIR . '/uptime.log')
      );
    }

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
   * @return string
   */
  public function getMemUsage() {
    if (!isset($this->logs['memory'])) {
      $this->logs['memory'] = explode(
        "\n",
        $this->read(DATA_DIR . '/memory.log')
      );
    }

    $totalMem   = 0;
    $usedMem    = 0;
    if (isset($this->logs['memory'][1])) {
      $memTemp  = preg_split('/\s+/', $this->logs['memory'][1]);
      $totalMem = isset($memTemp[1]) ? $this->transformValue($memTemp[1]) : 0;
      $usedMem  = isset($memTemp[2]) ? $this->transformValue($memTemp[2]) : 0;
    }
    return $totalMem > 0
         ? round(100 * $usedMem / $totalMem, 2) : 0;
  }

  /**
   * Get swap usage
   * 
   * @return string
   */
  public function getSwapUsage() {
    if (!isset($this->logs['memory'])) {
      $this->logs['memory'] = explode(
        "\n",
        $this->read(DATA_DIR . '/memory.log')
      );
    }

    $totalSwap   = 0;
    $usedSwap    = 0;
    if (isset($this->logs['memory'][3])) {
      $swapTemp  = preg_split('/\s+/', $this->logs['memory'][3]);
      $totalSwap = isset($swapTemp[1]) ? $this->transformValue($swapTemp[1]) : 0;
      $usedSwap  = isset($swapTemp[2]) ? $this->transformValue($swapTemp[2]) : 0;
    }
    return $totalSwap > 0
         ? round(100 * $usedSwap / $totalSwap, 2) : 0;
  }

  /**
   * Get CPU usage
   * 
   * @return string
   */
  public function getCpuUsage() {
    return $this->getHistory('cpu')->getLast();
  }

  /**
   * Get disk space usage
   * 
   * @return float
   */
  public function getDiskUsage() {
    if (!isset($this->logs['hdd'])) {
      $this->logs['hdd'] = explode(
        "\n",
        $this->read(DATA_DIR . '/hdd.log')
      );
    }

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
   */
  public function getSocketNum() {
    if (!isset($this->logs['tcp-sockets'])) {
      $this->logs['tcp-sockets'] = explode(
        "\n",
        $this->read(DATA_DIR . '/tcp-sockets.log')
      );
    }

    return count($this->logs['tcp-sockets']) > 2 
        ? (count($this->logs['tcp-sockets']) - 2 ) : 0;
  }

  /**
   * Get number of listening servers
   * 
   * @return int
   */
  public function getServerNum() {
    if (!isset($this->logs['servers'])) {
      $this->logs['servers'] = $this->read(DATA_DIR . '/servers.log');
    }

    return substr_count($this->logs['servers'], 'LISTEN');
  }

  /**
   * Get input traffic
   * 
   * @return float
   */
  public function getIn() {
    if (!isset($this->logs['ifstat'])) {
      $this->logs['ifstat'] = preg_split(
        '/\s+/', 
        $this->read(DATA_DIR . '/ifstat.log')
      );
    }
    
    $in = 0;

    while (isset($this->logs['ifstat'][count($this->logs['ifstat']) - 1]) 
      && $this->logs['ifstat'][count($this->logs['ifstat']) - 1] == ''
    ) {
      unset($this->logs['ifstat'][count($this->logs['ifstat']) - 1]);
    }

    $count = count($this->logs['ifstat']);
    return isset($this->logs['ifstat'][$count - 2]) 
           ? 1 * $this->logs['ifstat'][$count - 2] : 0;

    return $in * 1;
  }

  /**
   * Get output traffic
   * 
   * @return float
   */
  public function getOut() {
    if (!isset($this->logs['ifstat'])) {
      $this->logs['ifstat'] = preg_split(
        '/\s+/', 
        $this->read(DATA_DIR . '/ifstat.log')
      );
    }
    
    $out= 0;

    while (isset($this->logs['ifstat'][count($this->logs['ifstat']) - 1]) 
      && $this->logs['ifstat'][count($this->logs['ifstat']) - 1] == ''
    ) {
      unset($this->logs['ifstat'][count($this->logs['ifstat']) - 1]);
    }

    $count = count($this->logs['ifstat']);
    return isset($this->logs['ifstat'][$count - 1]) 
           ? 1 * $this->logs['ifstat'][$count - 1] : 0;
  }

  /**
   * Get number of processes that matches name
   * 
   * @param string $name
   * @return int
   */
  public function getProcessNum($name) {
    if (!isset($this->logs['processes'])) {
      $this->logs['processes'] = explode(
        "\n", 
        $this->read(DATA_DIR . '/processes.log')
      );
    }

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
   */
  public function getUserNum() {
    if (!isset($this->logs['users'])) {
      $this->logs['users'] = explode(
        "\n", 
        $this->read(DATA_DIR . '/users.log')
      );
    }

    return count($this->logs['users']) > 2 
         ? count($this->logs['users']) - 2 : 0;
  }

  /**
   * Get number of interfaces
   * 
   * @return int
   */
  public function getIfNum() {
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
   */
  public function getStatusDate() {
    if (!is_readable(DATA_DIR . '/status.log')) {
      return date("D M d H:i:s T Y", time());
    }

    return date("D M d H:i:s T Y", filemtime(DATA_DIR . '/status.log'));
  }

  /**
   * Get hostname
   * 
   * @return string
   */
  public function getHostname() {
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
   */
  function getLocalCpuUsage() {
    $load = sys_getloadavg();
    $cpu = substr_count(
      shell_exec('cat /proc/cpuinfo'),
      'processor'
    );
    $cpu = $cpu > 0 ? $cpu : 1;
    return 100 * (float)$load[0] / $cpu;
  }
}
