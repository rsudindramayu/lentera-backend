<?php

namespace App\Modules\BPJSVclaim\Services;

class RujukanService
{
    protected $baseUrl;
    protected $requestService;

    public function __construct()
    {
        $this->baseUrl = env('VCLAIM_URL') . 'Rujukan';
        $this->requestService = new RequestService();
    }

    public function rujukanBerdasarkanNomorKartu($noKartu)
    {
        $endPoint = $this->baseUrl . '/Peserta/' . $noKartu;
        $request = $this->requestService->sendRequest('GET', $endPoint);
        return $request;
    }

    public function rujukanBerdasarkanNomorRujukan($noRujukan)
    {
        $endPoint = $this->baseUrl . '/' . $noRujukan;
        $request = $this->requestService->sendRequest('GET', $endPoint);
        return $request;
    }
}
