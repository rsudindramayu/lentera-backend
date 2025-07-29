<?php

namespace App\Modules\BPJSAntrol\Controllers\Api\V1;

use App\Modules\BPJSAntrol\Services\AntreanService;
use App\Modules\BPJSAntrol\Services\WebserviceBPJSService;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\RESTful\ResourceController;
use Config\Services;

class AntreanController extends ResourceController
{
    protected $validation;
    protected $antreanService;

    public function __construct()
    {
        $this->validation = Services::validation();
        $this->antreanService = new AntreanService();
    }

    public function antreanPerKodeBooking($kodebooking)
    {
        try {
            $request = $this->antreanService->antreanPerKodeBooking($kodebooking);
            if ($request['status']) return $this->respond($request, ResponseInterface::HTTP_OK);
            return $this->respond($request, ResponseInterface::HTTP_NOT_FOUND);
        } catch (\Exception $e) {
            return $this->respond(['status' => false, 'message' => $e], ResponseInterface::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function tambahAntrean()
    {
        $this->validation->setRules([
            "kodebooking" => 'required',
            "jenispasien" => 'required',
            // "nomorkartu" => 'required', dikosongkan apabila pasien NON JKN
            "nik" => 'required',
            "nohp" => 'required',
            "kodepoli" => 'required',
            "namapoli" => 'required',
            "pasienbaru" => 'required',
            "norm" => 'required',
            "tanggalperiksa" => 'required', //format yyyy-mm-dd
            "kodedokter" => 'required',
            "namadokter" => 'required',
            "jampraktek" => 'required',
            "jeniskunjungan" => 'required',
            // "nomorreferensi" => 'required', dikosongkan apabila NON JKN
            "nomorantrean" => 'required',
            "angkaantrean" => 'required',
            "estimasidilayani" => 'required', //format yyyy-mm-dd hh:mm:ss
            "sisakuotajkn" => 'required',
            "kuotajkn" => 'required',
            "sisakuotanonjkn" => 'required',
            "kuotanonjkn" => 'required',
            "keterangan" => 'required',
        ]);

        if (!$this->validator->withRequest($this->request)->run()) {
            return $this->respond([
                'status' => false,
                'message' => $this->validator->getErrors(),
            ], 400);
        }

        $body = $this->request->getBody();
        try {
            $data = json_decode($body, true);
            $request = $this->antreanService->tambahAntrean($data);
            if ($request['status']) return $this->respond($request, ResponseInterface::HTTP_NOT_FOUND);
            return $this->respond($request, ResponseInterface::HTTP_NOT_FOUND);
        } catch (\Exception $e) {
            return $this->respond(['status' => false, 'message' => $e], ResponseInterface::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function batalAntrean()
    {
        $this->validation->setRules([
            'kodebooking' => 'required',
            'keterangan' => 'required',
        ]);

        if (!$this->validation->withRequest($this->request)->run()) {
            return $this->respond([
                'status' => false,
                'message' => $this->validation->getErrors(),
            ], 400);
        }

        try {
            $body = $this->request->getBody();
            $data = json_decode($body, true);
            $request = $this->antreanService->batalAntrean($data);
            if ($request['status']) return $this->respond($request, ResponseInterface::HTTP_OK);
            return $this->respond($request, ResponseInterface::HTTP_NOT_FOUND);
        } catch (\Exception $e) {
            return $this->respond(['status' => false, 'message' => $e], ResponseInterface::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
