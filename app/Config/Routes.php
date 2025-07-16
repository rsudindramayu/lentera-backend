<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
//API
$routes->group('api/v1', ['namespace' => 'App\Controllers\Api\V1'], static function (RouteCollection $routes) {
    $routes->post('auth/signin', 'AuthController::signIn');
    $routes->post('auth/refresh-token', 'AuthController::refreshToken');
    $routes->post('auth/signout', 'AuthController::signOut');
});
