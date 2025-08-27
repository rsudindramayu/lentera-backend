<?php

namespace App\Modules\BPJSVclaim\Services;

class RencanaKontrolService
{
    protected $baseUrl;
    protected $requestService;

    public function __construct()
    {
        $this->baseUrl = env('VCLAIM_URL') . 'RencanaKontrol';
        $this->requestService = new RequestService();
    }

    //List function dibuat pada tanggal 28 Juli 2025
    //Webservice ini lengkap sesuai dengan trustmark BPJS

    public function cariNomorSuratKontrol($noSuratKontrol)
    {
        $endPoint = $this->baseUrl . '/noSuratKontrol/' . $noSuratKontrol;
        $request = $this->requestService->sendRequest('GET', $endPoint);
        return $request;
    }

    public function cariSEP($sep)
    {
        $endPoint = $this->baseUrl . '/nosep/' . $sep;
        $request = $this->requestService->sendRequest('GET', $endPoint);
        return $request;
    }

    public function insertRencanaKontrol($params)
    {
        //params: noSEP, kodeDokter, poliKontrol, tglRencanaKontrol, user
        $endPoint = $this->baseUrl . '/insert';
        $setBody = ['request' => $params];
        $request = $this->requestService->sendRequest('POST', $endPoint, json_encode($setBody));
        return $request;
    }

    public function updateRencanaKontrol($params)
    {
        //params: noSuratKontrol, noSEP, kodeDokter, poliKontrol, tglRencanaKontrol, user
        $endPoint = $this->baseUrl . '/Update';
        $setBody = ['request' => $params];
        $request = $this->requestService->sendRequest('PUT', $endPoint, json_encode($setBody));
        return $request;
    }

    function hapusRencanaKontrol($params)
    {
        //params: noSuratKontrol, user
        $endPoint = $this->baseUrl . '/Delete';
        $setBody = ['request' => ['t_suratkontrol' => $params]];
        $request = $this->requestService->sendRequest('DELETE', $endPoint, json_encode($setBody));
        return $request;
    }

    public function insertSPRI($params)
    {
        //params: noKartu, kodeDokter, poliKontrol, tglRencanaKontrol, user
        $endPoint = $this->baseUrl . '/InsertSPRI';
        $setBody = ['request' => $params];
        $request = $this->requestService->sendRequest('POST', $endPoint, json_encode($setBody));
        return $request;
    }

    public function updateSPRI($params)
    {
        //params: noSPRI, kodeDokter, poliKontrol, tglRencanaKontrol, user

        $endPoint = $this->baseUrl . '/UpdateSPRI';
        $setBody = ['request' => $params];
        $request = $this->requestService->sendRequest('PUT', $endPoint, json_encode($setBody));
        return $request;
    }

    public function dataNomorSuratKontrolByKartu($params)
    {
        //params: noKartu, formatFilter, tglRencanaKontrol
        //formatFilter => 1 = tanggal terbit, 2 = tanggal rencana
        $bulan = date('m', strtotime($params['tglRencanaKontrol']));
        $tahun = date('Y', strtotime($params['tglRencanaKontrol']));
        $endPoint = $this->baseUrl . '/ListRencanaKontrol/Bulan/' . $bulan . '/Tahun/' . $tahun . '/Nokartu/' . $params['noKartu'] . '/filter/' . $params['formatFilter'];
        $request = $this->requestService->sendRequest('GET', $endPoint);
        return $request;
    }

    public function dataNomorSuratKontrol($params)
    {
        //params: tanggalAwal, tanggalAkhir, formatFilter
        //formatFilter => 1 = tanggal terbit, 2 = tanggal rencana
        $tanggalAwal = $params['tanggalAwal'];
        $tanggalAkhir = $params['tanggalAkhir'];
        $formatFilter = $params['formatFilter'];
        $endPoint = $this->baseUrl . '/ListRencanaKontrol/tglAwal/' . $tanggalAwal . '/tglAkhir/' . $tanggalAkhir . '/filter/' . $formatFilter;
        $request = $this->requestService->sendRequest('GET', $endPoint);
        return $request;
    }

    public function dataPoli($params)
    {
        //params: jenisKontrol, nomorReferensi, tglRencanaKontrol
        //jenisKontrol => 1 = SPRI, 2 = Rencana Kontrol
        //nomorReferensi => jika jenisKontrol = 1 maka noKartu, jika jenisKontrol = 2 maka SEP
        $jenisKontrol = $params['jenisKontrol'];
        $nomorReferensi = $params['nomorReferensi'];
        $tglRencanaKontrol = $params['tglRencanaKontrol'];
        $endPoint = $this->baseUrl . '/ListSpesialistik/JnsKontrol/' . $jenisKontrol . '/nomor/' . $nomorReferensi . '/TglRencanaKontrol/' . $tglRencanaKontrol;
        $request = $this->requestService->sendRequest('GET', $endPoint);
        return $request;
    }

    public function dataDokter($params)
    {
        //params: jenisKontrol, kodePoli, tglRencanaKontrol
        //jenisKontrol => 1 = SPRI, 2 = Rencana Kontrol
        $jenisKontrol = $params['jenisKontrol'];
        $kodePoli = $params['kodePoli'];
        $tglRencanaKontrol = $params['tglRencanaKontrol'];
        $endPoint = $this->baseUrl . '/JadwalPraktekDokter/JnsKontrol/' . $jenisKontrol . '/KdPoli/' . $kodePoli . '/TglRencanaKontrol/' . $tglRencanaKontrol;
        $request = $this->requestService->sendRequest('GET', $endPoint);
        return $request;
    }
}
