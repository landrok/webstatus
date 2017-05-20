<?php

namespace WebStatusTest;

use PHPUnit\Framework\TestCase;
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
      ['assertInternalType', 'int', 'getFormattedMicrotime'   ],
      ['assertEquals', 1000, 'getEstimatedFilesize', 10, 10, 1000                ],

      # WebStatus\App\FrameworkTrait
      ['assertInternalType', 'array', 'getConfig', 'global'   ],
      ['assertInternalType', 'array', 'getConfig', 'routes'   ],
      ['assertInternalType', 'array', 'getConfig', 'technologies'       ],
      ['assertEquals', $defaultGlobalWebAppConfig, 'getConfig', ['global', 'webapp']       ],
      ['assertEquals', null, 'getConfig', ['global', 'webapp', 'not-defined-config-option']],
      ['assertRegExp', '/^[a-zA-Z0-9_\/\.]+$/', 'getBaseUrl'                     ],
      ['assertEquals', null, 'getRouteKey', 'index'                              ],
      ['assertEquals', 'temperature', 'getRouteKey', 'status'                    ],
      ['assertEquals', null, 'getRequest'                                        ],
      ['assertInternalType', 'array', 'getRoute', 'status'    ],
      ['assertEquals', null, 'getRoute'                                          ],
      ['assertRegExp', '/^[a-zA-Z0-9_\/\.]+index.php$/', 'getRouteUrl', 'index'  ],
      ['assertRegExp', '/^[a-zA-Z0-9_\/\.]+status.php\?id=temperature$/', 'getRouteUrl', 'status', 'temperature'],
      ['assertEquals', '0.4.0-dev', 'getVersion'                                 ],
      ['assertEquals', true, 'validateState', 1                                  ],
      ['assertEquals', false, 'validateState', 'anothervalue'                    ],
      ['assertEquals', 0, 'getFilemtime', '/no-file-here'                        ],
      ['assertInternalType', 'int', 'getFilemtime', __FILE__  ],

      # WebStatus\App\StatTrait
      ['assertInternalType', 'string', 'getOs'                ],
      ['assertInternalType', 'string', 'getKernel'            ],
      ['assertInternalType', 'string', 'getCpuTemperature'    ],
      ['assertInternalType', 'string', 'getUp'                ],
      ['assertInternalType', 'string', 'getStarted'           ],
      ['assertInternalType', 'float', 'getMemUsage'           ],
      ['assertInternalType', 'float', 'getSwapUsage'          ],
      ['assertInternalType', 'float', 'getCpuUsage'           ],
      ['assertInternalType', 'float', 'getDiskUsage'          ],
      ['assertInternalType', 'int', 'getSocketNum'            ],
      ['assertInternalType', 'int', 'getServerNum'            ],
      ['assertInternalType', 'float', 'getIn'                 ],
      ['assertInternalType', 'float', 'getOut'                ],
      ['assertInternalType', 'int', 'getProcessNum'           ],
      ['assertInternalType', 'int', 'getUserNum'              ],
      ['assertInternalType', 'int', 'getIfNum'                ],
      ['assertInternalType', 'string', 'getStatusDate'        ],
      ['assertInternalType', 'string', 'getHostname'          ],
      ['assertInternalType', 'float', 'getLocalCpuUsage'      ],

      # WebStatus\App\TemplateTrait
      ['assertInstanceOf', 'Rain\Tpl', 'getTemplate'                             ],
      ['assertEquals', '<span class="label label-danger">Error</span>', 'bsLabel', 'danger', 'Error'],
      ['assertInternalType', 'string', 'getStatusLabel', 1, 'cpu'],
      ['assertInternalType', 'string', 'getStatusLabel', 70, 'cpu'],
      ['assertInternalType', 'string', 'getStatusLabel', 100, 'cpu'],
      ['assertInternalType', 'string', 'getNavbarMenus'       ],
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
