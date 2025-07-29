<?php

use CodeIgniter\Router\RouteCollection;

$routes->group('bpjs-vclaim', ['namespace' => 'App\Modules\BPJSVclaim\Controllers\Api\V1'], static function (RouteCollection $routes) {
    // Rencana Kontrol
    $routes->group('rencana-kontrol', static function (RouteCollection $routes) {
        $routes->get('cari-nomor-surat-kontrol/(:any)', 'RencanaKontrolController::cariNomorSuratKontrol/$1');
        $routes->get('data-nomor-surat-kontrol', 'RencanaKontrolController::dataNomorSuratKontrol');
        $routes->get('data-nomor-surat-kontrol-by-kartu', 'RencanaKontrolController::dataNomorSuratKontrolByKartu');
        $routes->get('cari-sep/(:any)', 'RencanaKontrolController::cariSEP/$1');
        $routes->post('insert-rencana-kontrol', 'RencanaKontrolController::insertRencanaKontrol');
        $routes->post('insert-spri', 'RencanaKontrolController::insertSPRI');
        $routes->put('update-rencana-kontrol', 'RencanaKontrolController::updateRencanaKontrol');
        $routes->put('update-spri', 'RencanaKontrolController::updateSPRI');
        $routes->delete('hapus-rencana-kontrol', 'RencanaKontrolController::hapusRencanaKontrol');
        $routes->get('data-poli', 'RencanaKontrolController::dataPoli');
        $routes->get('data-dokter', 'RencanaKontrolController::dataDokter');
    });

    //Monitoring
    $routes->group('monitoring', static function (RouteCollection $routes) {
        $routes->get('data-history-pelayanan-peserta', 'MonitoringController::dataHistoryPelayananPeserta');
        $routes->get('data-kunjungan', 'MonitoringController::dataKunjungan');
        $routes->get('data-klaim', 'MonitoringController::dataKlaim');
        $routes->get('data-klaim-jaminan-jasa-raharja', 'MonitoringController::dataKlaimJaminanJasaRaharja');
    });

    //Peserta
    $routes->group('peserta', static function (RouteCollection $routes) {
        $routes->get('peserta-by-nik/(:any)', 'PesertaController::pesertaByNIK/$1');
        $routes->get('peserta-by-no-kartu/(:any)', 'PesertaController::pesertaByNoKartu/$1');
    });

    //Rujukan
    $routes->group('rujukan', static function (RouteCollection $routes) {
        $routes->get('rujukan-by-rujukan/(:any)', 'RujukanController::rujukanBerdasarkanNomorRujukan/$1');
        $routes->get('rujukan-by-no-kartu/(:any)', 'RujukanController::rujukanBerdasarkanNomorKartu/$1');
    });

    //SEP
    $routes->group('sep', static function (RouteCollection $routes) {
        $routes->get('list-data-update-tanggal-pulang', 'SEPController::listDataUpdateTanggalPulang');
        $routes->get('cari-sep/(:any)', 'SEPController::cariSEP/$1');
        $routes->get('insert-sep', 'SEPController::insertSEP');
    });
});
