<?php

namespace App\Modules\BPJSAntrol\Services;

use App\Modules\BPJSVclaim\Services\RequestService;
use DateTime;

class AntreanService
{
    protected $requestService;
    protected $baseUrl;

    public function __construct()
    {
        $this->baseUrl = env('ANTROL_URL') . 'antrean';
        $this->requestService = new RequestService();
    }

    public function antreanPerKodeBooking($kodebooking)
    {
        $endPoint = $this->baseUrl . '/pendaftaran/kodebooking/' . $kodebooking;
        $request = $this->requestService->sendAntreanRequest('GET', $endPoint);
        return $request;
    }

    public function tambahAntrean($params)
    {

        /* 
            List params:
            "kodebooking" => 'required',
            "jenispasien" => 'required',
            "nomorkartu" => 'required', dikosongkan apabila pasien NON JKN
            "nik" => 'required',
            "nohp" => 'required',
            "kodepoli" => 'required',
            "namapoli" => 'required',
            "pasienbaru" => 'required',
            "norm" => 'required',
            "tanggalperiksa" => 'required', format yyyy-mm-dd
            "kodedokter" => 'required',
            "namadokter" => 'required',
            "jampraktek" => 'required',
            "jeniskunjungan" => 'required',
            "nomorreferensi" => 'required', dikosongkan apabila NON JKN
            "nomorantrean" => 'required',
            "angkaantrean" => 'required',
            "estimasidilayani" => 'required', format yyyy-mm-dd hh:mm:ss
            "sisakuotajkn" => 'required',
            "kuotajkn" => 'required',
            "sisakuotanonjkn" => 'required',
            "kuotanonjkn" => 'required',
            "keterangan" => 'required',
        */

        $dateTime = new DateTime($params['estimasidilayani']);
        $milliseconds = $dateTime->getTimestamp() * 1000;
        $params['estimasidilayani'] = $milliseconds;
        $endPoint = $this->baseUrl . '/add';
        $request = $this->requestService->sendAntreanRequest('POST', $endPoint, json_encode($params));
        return $request;
    }

    public function batalAntrean($params)
    {
        //params: kodebooking, keterangan
        $endPoint = $this->baseUrl . '/batal';
        $request = $this->requestService->sendAntreanRequest('POST', $endPoint, json_encode($params));
        return $request;
    }
}
