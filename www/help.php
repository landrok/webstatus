<?php

include dirname(__DIR__) . '/app/bootstrap.php';

/*
 * Print a list of icon options.
 * It's an help for customizing the appearance
 */
use Rain\Tpl;
$tpl = new Tpl();

$tpl->assign('tableClass', 'table table-hover table-striped table-condensed');
$tpl->assign(
  'icons', 
  explode(
    "\n",
    $app->read(CFG_DIR . 'icon-values.php')
  )
);

# Build content
$template->assign('html', $tpl->draw('help', true));

$app->render('layout');
