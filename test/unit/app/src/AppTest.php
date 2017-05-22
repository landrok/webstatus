<?php

namespace WebStatusTest;

use Exception;
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
      # WebStatus\App
      ['assertInstanceOf', '\WebStatus\History', 'getHistory'                    ],
      ['assertInstanceOf', '\WebStatus\Metric', 'getHistory', 'cpu'              ],
      ['assertInternalType', 'int', 'getFormattedMicrotime'                      ],
      ['assertEquals', 1000, 'getEstimatedFilesize', 10, 10, 1000                ],
      ['assertEquals', 0, 'getEstimatedFilesize', 10, 0, 1000                    ],
      ['expectException', Exception::class, 'read', 'not-existing-file.log'      ],
      ['assertEquals', 1000, 'transformValue', '1000'                            ],
      ['assertEquals', 1024, 'transformValue', '1K'                              ],
      ['assertEquals', 2306867, 'transformValue', '2.2M'                         ],
      ['assertEquals', 1181116006, 'transformValue', '1.1G'                      ],

      # WebStatus\App\FrameworkTrait
      ['assertInternalType', 'array', 'getConfig', 'global'                      ],
      ['assertInternalType', 'array', 'getConfig', 'routes'                      ],
      ['assertInternalType', 'array', 'getConfig', 'technologies'                ],
      ['assertEquals', $defaultGlobalWebAppConfig, 'getConfig', 'global', 'webapp'],
      [ 
        [ 
          ['assertEquals', null, 'setConfig', ['global', 'test', 'test'], 1], #1 Set a new option with a vector key
          ['assertEquals', 1, 'getConfig', 'global', 'test', 'test'        ]  #2 Get value
        ], null, null  
      ],
      [ 
        [ 
          ['assertEquals', null, 'setConfig', ['test'], 1], #1 Set a new option with a text key
          ['assertEquals', 1, 'getConfig', 'test'      ]  #2 Get value
        ], null, null  
      ],
      [ 
        [ 
          ['assertEquals', null, 'setConfig', ['global', 'webapp', 'title'], 'New title'], #1 Set a second level option
          ['assertEquals', 'New title', 'getConfig', 'global', 'webapp', 'title'        ]  #2 Get value
        ], null, null  
      ],
      ['assertEquals', null, 'getConfig', 'global', 'webapp', 'not-defined-config-option'],
      ['assertRegExp', '/^[a-zA-Z0-9_\/\.]+$/', 'getBaseUrl'                     ],
      ['assertEquals', null, 'getRouteKey', 'index'                              ],
      ['assertEquals', 'temperature', 'getRouteKey', 'status'                    ],
      ['assertEquals', null, 'getRequest'                                        ],
      ['assertInternalType', 'array', 'getRoute', 'status'                       ],
      ['assertEquals', null, 'getRoute'                                          ],
      ['assertRegExp', '/^[a-zA-Z0-9_\/\.]+index.php$/', 'getRouteUrl', 'index'  ],
      ['assertRegExp', '/^[a-zA-Z0-9_\/\.]+status.php\?id=temperature$/', 'getRouteUrl', 'status', 'temperature'],
      ['assertEquals', '1.0.0-dev', 'getVersion'                                 ],
      ['assertEquals', true, 'validateState', 1                                  ],
      ['assertEquals', false, 'validateState', 'anothervalue'                    ],
      ['assertEquals', 0, 'getFilemtime', '/no-file-here'                        ],
      ['assertInternalType', 'int', 'getFilemtime', __FILE__                     ],
      ['assertEquals', null, 'getComposer', 'not-existing-key'                   ],
      ['assertEquals', true, 'removeCache', 'config'                             ],
      ['assertEquals', null, 'removeCache', 'config-not-existing-cache-key'      ],
      ['expectException', Exception::class, 'loadIniFile', 'not-existing.ini'    ],

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
      ['assertEquals', 'Here is an IP: w.x.y.z.', 'ipToLocation', 'Here is an IP: 192.168.0.10.'],
      [
        [['assertEquals', null, 'setConfig', ['global', 'webapp', 'ip-hide'], 0], #1 Set ip-hide=0
         ['assertEquals', 
          'Here is an IP: <a href="http://ipv4.landrok.com/address/192.168.0.10">192.168.0.10</a>.', 
          'ipToLocation', 
          'Here is an IP: 192.168.0.10.'
          ]
        ],
         null,null
      ],
      
      ['assertEquals', '42B', 'formatFilesize', 42                               ],
      ['assertEquals', '1MB', 'formatFilesize', 1024 * 1024                      ],
    ];
  }

  /**
   * @dataProvider getScenarios
   */
  public function testScenario($asserts, $expected, $method, $param1 = null, $param2 = null, $param3 = null)
  {
    $app = new App('/index.php');

    if (!is_array($asserts)) {
      $asserts = [[$asserts, $expected, $method, $param1, $param2, $param3]];
    }

    $count = count($asserts);
    for ($i = 0; $i < $count; $i++) {
      $asserts[$i] = array_pad($asserts[$i], 6, null);
      list($assert, $expected, $method, $param1, $param2, $param3) = $asserts[$i];

      if ($assert == 'expectException') {
        $this->$assert($expected);
        $value = $app->$method($param1, $param2, $param3);
        
      } else {
        $value = $app->$method($param1, $param2, $param3);
        $this->$assert($expected, $value);
      }
    }
  }
}
