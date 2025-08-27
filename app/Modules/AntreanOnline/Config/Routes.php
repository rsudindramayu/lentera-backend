<?php

use CodeIgniter\Router\RouteCollection;

$routes->group('antrean-online', ['namespace' => 'App\Modules\AntreanOnline\Controllers\Api\V1'], static function (RouteCollection $routes) {

    $routes->group('', ['filter' => 'jwt-auth'], static function (RouteCollection $routes) {
        //Reservasi
        $routes->get('reservasi/summary', 'ReservasiController::getSummaryReservasi');
        $routes->get('reservasi', 'ReservasiController::getReservasi');
        $routes->delete('reservasi', 'ReservasiController::batalkanReservasi');
        $routes->post('reservasi/batalkan', 'ReservasiController::batalkanReservasiMasal');

        //Display reservasi
        $routes->get('display-reservasi', 'DisplayReservasiController::getDataDisplayReservasi');
        $routes->get('display-reservasi/pasien-terdaftar', 'DisplayReservasiController::getPengunjung');

        //Antrean onsite
        $routes->get('antrean-onsite/pasien/(:any)', 'AntreanOnsiteController::getDataPasien/$1');
        $routes->get('antrean-onsite/history/(:any)', 'AntreanOnsiteController::getHistoryPendaftaran/$1');
        $routes->get('antrean-onsite/reservasi/(:any)', 'AntreanOnsiteController::getReservasi/$1');
        $routes->get('antrean-onsite/jadwal-dokter', 'AntreanOnsiteController::getJadwalDokter');
        $routes->get('antrean-onsite/poliklinik', 'AntreanOnsiteController::getPoliklinik');

        //Status pendaftaran
        $routes->put('status-pendaftaran', 'StatusPendaftaranController::updateStatusPendaftaran');
    });
});
