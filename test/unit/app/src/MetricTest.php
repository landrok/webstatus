<?php

namespace WebStatusTest;

use PHPUnit\Framework\TestCase;
use PHPUnit_Framework_Constraint_IsType as PHPUnit_IsType;
use WebStatus\App as App;

class MetricTest extends TestCase
{
  public function getScenarios()
  {
    // scenario / expected / method / param1 / param2 / param3
    return [
      ['assertEquals', 180, 'getMaxItems'                               ],
      ['assertEquals', 'cpu', 'getName'                                 ],
      ['assertInternalType', PHPUnit_IsType::TYPE_ARRAY, 'getData'      ],
      ['assertInternalType', PHPUnit_IsType::TYPE_FLOAT, 'getAvg'       ],
      ['assertInternalType', PHPUnit_IsType::TYPE_NUMERIC, 'getLast'    ],
      ['assertInternalType', PHPUnit_IsType::TYPE_NUMERIC, 'getMax'     ],
      ['assertInternalType', PHPUnit_IsType::TYPE_NUMERIC, 'getMin'     ],
      ['assertInternalType', PHPUnit_IsType::TYPE_INT, 'getTrend'       ],
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
