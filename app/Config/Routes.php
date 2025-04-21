<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->post('/reset', 'StateController::resetState');
$routes->post('/event', 'AccountController::handleAccountOperationEvent');
$routes->get('/balance', 'AccountController::getAccountBalance');
