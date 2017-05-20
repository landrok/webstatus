<?php

namespace WebStatusTest;

use PHPUnit\Framework\TestCase;
use WebStatus\App as App;

class MetricTest extends TestCase
{
  public function getScenarios()
  {
    // scenario / expected / method / param1 / param2 / param3
    return [
      ['assertEquals', 180, 'getMaxItems'                               ],
      ['assertEquals', 'cpu', 'getName'                                 ],
      ['assertInternalType', 'array', 'getData'                         ],
      ['assertInternalType', 'float', 'getAvg'                          ],
      ['assertInternalType', 'numeric', 'getLast'                       ],
      ['assertInternalType', 'numeric', 'getMax'                        ],
      ['assertInternalType', 'numeric', 'getMin'                        ],
      ['assertInternalType', 'int', 'getTrend'                          ],
    ];
  }

  /**
   * @dataProvider getScenarios
   */
  public function testScenario($assert, $expected, $method, $param1 = null, $param2 = null, $param3 = null)
  {
    $app = new App('/index.php');

    $value = $app->getHistory('cpu')->$method($param1, $param2, $param3);

    $this->$assert($expected, $value);
  }
}
