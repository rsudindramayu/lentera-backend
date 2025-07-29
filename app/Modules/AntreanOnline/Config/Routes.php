<?php

use CodeIgniter\Router\RouteCollection;

$routes->group('antrean-online', ['namespace' => 'App\Modules\AntreanOnline\Controllers\Api\V1'], static function (RouteCollection $routes) {
    $routes->get('reservasi/summary', 'ReservasiController::getSummaryReservasi');
    $routes->get('reservasi', 'ReservasiController::getReservasi');
});
