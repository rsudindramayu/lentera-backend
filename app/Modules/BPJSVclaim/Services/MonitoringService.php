<?php

namespace App\Modules\BPJSVclaim\Services;

class MonitoringService
{
    protected $requestService;
    protected $baseUrl;

    public function __construct()
    {
        $this->requestService = new RequestService();
        $this->baseUrl = env('VCLAIM_URL') . 'Monitoring';
    }

    public function dataHistoryPelayananPeserta($params)
    {
        //params: noKartu, tanggalAwal, tanggalAkhir

        $tanggalAwal = $params['tanggalAwal'];
        $tanggalAkhir = $params['tanggalAkhir'];
        $noKartu = $params['noKartu'];
        $endPoint = $this->baseUrl . '/HistoriPelayanan/NoKartu/' . $noKartu . '/tglMulai/' . $tanggalAwal . '/tglAkhir/' . $tanggalAkhir;
        $request = $this->requestService->sendRequest('GET', $endPoint);
        return $request;
    }

    public function dataKunjungan($params)
    {
        //params: tanggalSep, jenisPelayanan
        //jenisPelayanan => 1 = Rawat Inap, 2 = Rawat Jalan

        $tanggal = $params['tanggalSep'];
        $jenisPelayanan = $params['jenisPelayanan'];
        $endPoint = $this->baseUrl . '/Kunjungan/Tanggal/' . $tanggal . '/JnsPelayanan/' . $jenisPelayanan;
        $request = $this->requestService->sendRequest('GET', $endPoint);
        return $request;
    }

    public function dataKlaim($params)
    {
        //params: tanggalSep, jenisPelayanan
        //jenisPelayanan => 1 = Rawat Inap, 2 = Rawat Jalan

        $tanggalPulang = $params['tanggalPulang'];
        $jenisPelayanan = $params['jenisPelayanan'];
        $statusKlaim = $params['statusKlaim'];
        $endPoint = $this->baseUrl . '/Klaim/Tanggal/' . $tanggalPulang . '/JnsPelayanan/' . $jenisPelayanan . '/Status/' . $statusKlaim;
        $request = $this->requestService->sendRequest('GET', $endPoint);
        return $request;
    }

    public function dataKlaimJaminanJasaRaharja($params)
    {
        //params: tanggalAwal, tanggalAkhir, jenisPelayanan
        //jenisPelayanan => 1 = Rawat Inap, 2 = Rawat Jalan

        $tanggalAwal = $params['tanggalAwal'];
        $tanggalAkhir = $params['tanggalAkhir'];
        $jenisPelayanan = $params['jenisPelayanan'];
        $endPoint = $this->baseUrl . '/JasaRaharja/JnsPelayanan/' . $jenisPelayanan . '/tglMulai/' . $tanggalAwal . '/tglAkhir/' . $tanggalAkhir;
        $request = $this->requestService->sendRequest('GET', $endPoint);
        return $request;
    }
}
