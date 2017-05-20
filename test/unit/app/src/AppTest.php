<?php

namespace WebStatusTest;

use PHPUnit\Framework\TestCase;
use PHPUnit_Framework_Constraint_IsType as PHPUnit_IsType;
use WebStatus\App as App;

class AppTest extends TestCase
{
  public function getScenarios()
  {
    $defaultGlobalWebAppConfig = [
      'title'         => 'RPi Home', 
      'label'         => 'RPi Home',
      'icon-class'    => 'music',
      'ip-hide'       => 1
    ];

    // Just a local helper, won't be used for scenarios
    $app = new App('/index.php');

    // scenario / expected / method / param1 / param2 / param3
    return [
      ['assertInstanceOf', '\WebStatus\History', 'getHistory'                    ],
      ['assertInstanceOf', '\WebStatus\Metric', 'getHistory', 'cpu'              ],
      ['assertInternalType', PHPUnit_IsType::TYPE_INT, 'getFormattedMicrotime'   ],
      ['assertEquals', 1000, 'getEstimatedFilesize', 10, 10, 1000                ],

      # WebStatus\App\FrameworkTrait
      ['assertInternalType', PHPUnit_IsType::TYPE_ARRAY, 'getConfig', 'global'   ],
      ['assertInternalType', PHPUnit_IsType::TYPE_ARRAY, 'getConfig', 'routes'   ],
      ['assertInternalType', PHPUnit_IsType::TYPE_ARRAY, 'getConfig', 'technologies'       ],
      ['assertEquals', $defaultGlobalWebAppConfig, 'getConfig', ['global', 'webapp']       ],
      ['assertEquals', null, 'getConfig', ['global', 'webapp', 'not-defined-config-option']],
      ['assertRegExp', '/^[a-zA-Z0-9_\/\.]+$/', 'getBaseUrl'                           ],
      ['assertEquals', null, 'getRouteKey', 'index'                              ],
      ['assertEquals', 'temperature', 'getRouteKey', 'status'                    ],
      ['assertEquals', null, 'getRequest'                                        ],
      ['assertInternalType', PHPUnit_IsType::TYPE_ARRAY, 'getRoute', 'status'    ],
      ['assertEquals', null, 'getRoute'                                          ],
      ['assertRegExp', '/^[a-zA-Z0-9_\/\.]+index.php$/', 'getRouteUrl', 'index'        ],
      ['assertRegExp', '/^[a-zA-Z0-9_\/\.]+status.php\?id=temperature$/', 'getRouteUrl', 'status', 'temperature'],
      ['assertEquals', '0.4.0-dev', 'getVersion'                                 ],
      ['assertEquals', true, 'validateState', 1                                  ],
      ['assertEquals', false, 'validateState', 'anothervalue'                    ],
      ['assertEquals', 0, 'getFilemtime', '/no-file-here'                        ],
      ['assertInternalType', PHPUnit_IsType::TYPE_INT, 'getFilemtime', __FILE__  ],

      # WebStatus\App\StatTrait
      ['assertInternalType', PHPUnit_IsType::TYPE_STRING, 'getOs'                ],
      ['assertInternalType', PHPUnit_IsType::TYPE_STRING, 'getKernel'            ],
      ['assertInternalType', PHPUnit_IsType::TYPE_STRING, 'getCpuTemperature'    ],
      ['assertInternalType', PHPUnit_IsType::TYPE_STRING, 'getUp'                ],
      ['assertInternalType', PHPUnit_IsType::TYPE_STRING, 'getStarted'           ],
      ['assertInternalType', PHPUnit_IsType::TYPE_FLOAT, 'getMemUsage'           ],
      ['assertInternalType', PHPUnit_IsType::TYPE_FLOAT, 'getSwapUsage'          ],
      ['assertInternalType', PHPUnit_IsType::TYPE_FLOAT, 'getCpuUsage'           ],
      ['assertInternalType', PHPUnit_IsType::TYPE_FLOAT, 'getDiskUsage'          ],
      ['assertInternalType', PHPUnit_IsType::TYPE_INT, 'getSocketNum'            ],
      ['assertInternalType', PHPUnit_IsType::TYPE_INT, 'getServerNum'            ],
      ['assertInternalType', PHPUnit_IsType::TYPE_FLOAT, 'getIn'                 ],
      ['assertInternalType', PHPUnit_IsType::TYPE_FLOAT, 'getOut'                ],
      ['assertInternalType', PHPUnit_IsType::TYPE_INT, 'getProcessNum'           ],
      ['assertInternalType', PHPUnit_IsType::TYPE_INT, 'getUserNum'              ],
      ['assertInternalType', PHPUnit_IsType::TYPE_INT, 'getIfNum'                ],
      ['assertInternalType', PHPUnit_IsType::TYPE_STRING, 'getStatusDate'        ],
      ['assertInternalType', PHPUnit_IsType::TYPE_STRING, 'getHostname'          ],
      ['assertInternalType', PHPUnit_IsType::TYPE_FLOAT, 'getLocalCpuUsage'      ],

      # WebStatus\App\TemplateTrait
      ['assertInstanceOf', 'Rain\Tpl', 'getTemplate'                             ],
      ['assertEquals', '<span class="label label-danger">Error</span>', 'bsLabel', 'danger', 'Error'],
      ['assertInternalType', PHPUnit_IsType::TYPE_STRING, 'getStatusLabel', 1, 'cpu'],
      ['assertInternalType', PHPUnit_IsType::TYPE_STRING, 'getStatusLabel', 70, 'cpu'],
      ['assertInternalType', PHPUnit_IsType::TYPE_STRING, 'getStatusLabel', 100, 'cpu'],
      ['assertInternalType', PHPUnit_IsType::TYPE_STRING, 'getNavbarMenus'       ],
      ['assertEquals', 'Here is an IP: ww.xx.yy.zz.', 'ipToLocation', 'Here is an IP: 192.168.0.10.'],
      ['assertEquals', '42B', 'formatFilesize', 42                               ],
      ['assertEquals', '1MB', 'formatFilesize', 1024 * 1024                      ],
    ];
  }

  /**
   * @dataProvider getScenarios
   */
  public function testScenario($assert, $expected, $method, $param1 = null, $param2 = null, $param3 = null)
  {
    $app = new App('/index.php');

    $value = $app->$method($param1, $param2, $param3);

    $this->$assert($expected, $value);
  }
}
