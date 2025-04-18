<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Home::index');


$routes->post('/reset', 'StateController::resetState');
$routes->post('/event', 'Event::index');
$routes->get('/balance', 'AccountController::getAccountBalance');
