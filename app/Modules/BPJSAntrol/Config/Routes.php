<?php

use CodeIgniter\Router\RouteCollection;

$routes->group('bpjs-antrol', ['namespace' => 'App\Modules\BPJSAntrol\Controllers\Api\V1'], static function (RouteCollection $routes) {
    //SEP
    $routes->group('antrean', static function (RouteCollection $routes) {
        $routes->get('antrean-per-kode-booking/(:any)', 'AntreanController::antreanPerKodeBooking/$1');
        $routes->post('tambah-antrean', 'AntreanController::tambahAntrean');
        $routes->delete('batal-antrean', 'AntreanController::batalAntrean');
    });
});
