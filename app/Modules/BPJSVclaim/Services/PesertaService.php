<?php

namespace App\Modules\BPJSVclaim\Services;

class PesertaService
{
    protected $baseUrl;
    protected $requestService;

    public function __construct()
    {
        $this->baseUrl = env('VCLAIM_URL') . 'Peserta';
        $this->requestService = new RequestService();
    }

    public function pesertaByNoKartu($noKartu)
    {
        $today = date('Y-m-d');
        $endPoint = $this->baseUrl . '/nokartu/' . $noKartu . '/tglSEP/' . $today;
        $request = $this->requestService->sendRequest('GET', $endPoint);
        return $request;
    }

    public function pesertaByNIK($nik)
    {
        $today = date('Y-m-d');
        $endPoint = $this->baseUrl . '/nik/' . $nik . '/tglSEP/' . $today;
        $request = $this->requestService->sendRequest('GET', $endPoint);
        return $request;
    }
}
