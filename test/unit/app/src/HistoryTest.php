<?php

namespace WebStatusTest;

use PHPUnit\Framework\TestCase;
use PHPUnit_Framework_Constraint_IsType as PHPUnit_IsType;
use WebStatus\App as App;

class HistoryTest extends TestCase
{
  public function getScenarios()
  {
    // scenario / expected / method / param1 / param2 / param3
    return [
      ['assertInternalType', PHPUnit_IsType::TYPE_ARRAY, 'getData'      ],
      ['assertInternalType', PHPUnit_IsType::TYPE_ARRAY, 'getStatus'    ],
      ['assertEquals', null, 'save'                                     ],
      ['assertInstanceOf', 'WebStatus\Metric', 'get', 'new-metric'      ],
    ];
  }

  /**
   * @dataProvider getScenarios
   */
  public function testScenario($assert, $expected, $method, $param1 = null, $param2 = null, $param3 = null)
  {
    $app = new App('/index.php');

    $value = $app->getHistory()->$method($param1, $param2, $param3);

    $this->$assert($expected, $value);
  }
}
