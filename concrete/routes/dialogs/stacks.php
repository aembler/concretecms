<?php

defined('C5_EXECUTE') or die('Access Denied.');

/**
 * @var Concrete\Core\Application\Application $app
 * @var Concrete\Core\Routing\Router $router
 */

/*
 * Base path: /ccm/system/dialogs/stack
 * Namespace: Concrete\Controller\Dialog\Stack\
 */

$router->all('/delete', 'Delete::view');
$router->all('/delete/submit', 'Delete::submit');
$router->all('/duplicate', 'Duplicate::view');
$router->all('/duplicate/submit', 'Duplicate::submit');
