<?php

use CodeIgniter\Router\RouteCollection;

$routes->group('penjamin-rs', ['namespace' => 'App\Modules\PenjaminRs\Controllers\Api\V1'], static function (RouteCollection $routes) {
    $routes->get('dpjp', 'DPJPController::searchDPJP');
});
