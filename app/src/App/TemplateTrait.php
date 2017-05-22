<?php
namespace WebStatus\App;

use Rain\Tpl;
use WebStatus\History;

trait TemplateTrait
{
  /**
   * Render global template
   * 
   * @param string $name
   * 
   * @return string
   */
  public function render($name)
  {
    $this->getTemplate()->assign([
      'history'    => $this->getHistory()->getStatus(),
      'app'        => $this,
      'tableClass' => 'table table-hover table-striped table-condensed'
    ]);

    return $this->getTemplate()->draw($name);
  }

  /**
   * Get main template instance
   * 
   * @return \Rain\Tpl
   * 
   * @api
   */
  public function getTemplate()
  {
    if (!$this->template) {
      $this->template = new Tpl();
    }

    return $this->template;
  }

  /**
   * Build a bootstrap label
   * 
   * @param string $class
   * @param string $content
   * 
   * @return string
   * 
   * @api
   */
  public function bsLabel($class, $content)
  {
    return sprintf(
      '<span class="label label-%s">%s</span>',
      $class,
      $content
    );
  }

  /**
   * Get a status label
   * 
   * @param float|int $value
   * @param string $def
   * 
   * @return string
   * 
   * @api
   */
  public function getStatusLabel($value, $def)
  {
    if ($value < $this->getConfig('global', 'thresholds', "$def.mid")) {
      return $this->bsLabel('success pull-right', 'OK');
    } elseif ($value < $this->getConfig('global', 'thresholds', "$def.high")) {
      return $this->bsLabel('warning pull-right', 'MID');
    }

    return $this->bsLabel('danger pull-right', 'HIGH');
  }

  /**
   * Get navbar menus
   * 
   * @return string
   * 
   * @api
   */
  public function getNavbarMenus()
  {
    return implode('',
      array_map(
        function ($def, $route) { 
          return $this->menuTemplater($def, $route);
        },
        $this->config['routes'],
        array_keys($this->config['routes'])
      )
    );
  }

  /**
   * Global dropdown menu drawer
   * Used before rendering the layout
   * 
   * @param array $def
   * @param string $route
   * 
   * @return string
   * 
   * @api
   */ 
  public function menuTemplater($def, $route)
  {
    $tpl = new Tpl();

    # 1st level
    $icon = '';
    if (isset($def['icon'])) {
      $icon = $def['icon'];
      unset($def['icon']);
    }

    $label = ucfirst($route);
    if (isset($def['label'])) {
      $label = $def['label'];
      unset($def['label']);
    }

    # No sub menu
    if (!count($def)) {
      $tpl->assign([
        'class'   => ($this->context == $route ? ' class="active"' : ''),
        'icon'    => $icon,
        'context' => $route,
        'id'      => "",
        'label'   => $label,
      'spaces'  => str_repeat(' ', 2 * 2)
      ]);

      return $tpl->draw('navbar.dropdown.li', true);
    }

    # Sub-menus
    $subMenu = '';
    array_walk($def, 
      function ($subLabel, $subRoute) use ($route, $tpl, $def, & $subMenu) {
        if (preg_match('/^(?!sub-header-|sub-icon-)sub-([a-z0-9\-]*)$/i', $subRoute, $matches)) {
          $subMenu .= $this->subMenuTemplater($def, $route, $matches[1], $subLabel);
        }
        # Sub-headers 
        elseif (preg_match('/^(sub-header-(.*))/i', $subRoute, $matches)) {
          if ($subMenu != '') {
            $tpl->assign('spaces', str_repeat(' ', 4 * 2));
            $subMenu .= $tpl->draw('navbar.dropdown.divider', true);
          }
          $tpl->assign('spaces', str_repeat(' ', 4 * 2));
          $tpl->assign('label', $def[$matches[1]]);
          $subMenu .= $tpl->draw('navbar.dropdown.header', true);
        }
      }
    );

    $tpl->assign([
      'active'  => ($this->context == $route ? ' active' : ''),
      'icon'    => $icon,
      'label'   => $label,
      'subMenu' => $subMenu,
      'spaces'  => str_repeat(' ', 3 * 2)
    ]);

    return $tpl->draw('navbar.dropdown.container', true);
  }

  /**
   * Submenu drowpdown drawer
   * 
   * @param array $def
   * @param string $route
   * @param string $subRoute
   * @param string $subLabel
   * 
   * @return string
   */ 
  protected function subMenuTemplater($def, $route, $subRoute, $subLabel)
  {
    $tpl = new Tpl();

    $icon = isset($def["sub-icon-$subRoute"])
                ? $def["sub-icon-$subRoute"] : '';

    $tpl->assign([
      'class'   => ($subRoute == $this->request ? ' class="active"' : ''),
      'icon'    => $icon,
      'context' => $route,
      'id'      => "?id=$subRoute",
      'label'   => $subLabel,
      'spaces'  => str_repeat(' ', 4 * 2),
    ]);

    return $tpl->draw('navbar.dropdown.li', true);
  }

  /**
   * Replace all IP with a link IP to location
   * 
   * @param string $content
   * 
   * @return string
   * 
   * @api
   */
  public function ipToLocation($content)
  {
    $ipPattern = '/(\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3})/';

    if (!$this->validateState($this->getConfig('global', 'webapp', 'ip-hide'))) {
      return preg_replace(
        $ipPattern,
        '<a href="http://ipv4.landrok.com/address/\1">\1</a>',
        $content
      );
    }

    return preg_replace($ipPattern, 'w.x.y.z', $content);
  }

  /**
   * Format a filesize
   * 
   * @param int $size
   * 
   * @return string
   * 
   * @api
   */
  public function formatFilesize($size)
  {
    $conf = [
      'P' => 4,
      'G' => 3,
      'M' => 2,
      'K' => 1
    ];

    foreach ($conf as $letter => $exp) {
      if ($size >= pow(1024, $exp)) {
        return round($size / pow(1024, $exp), 2) . "{$letter}B";
      }
    }

    return "{$size}B";
  }
}
