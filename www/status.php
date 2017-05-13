<?php

include dirname(__DIR__) . '/app/bootstrap.php';

use Rain\Tpl;
$tpl = new Tpl();

# request
$request = $app->getRequest();

$tmp = $app->read(DATA_DIR . "/$request.log");
$tpl->assign('title', $app->getRoute('status')[$request]);
$tpl->assign('content', $app->ipToLocation($tmp));

# Build content
$template->assign('html', $tpl->draw('com.logs', true));

$app->render('layout');
