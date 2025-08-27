<?php

namespace App\Modules\AntreanOnline\Controllers\Api\V1;

use App\Modules\AntreanOnline\Services\DisplayReservasiService;
use CodeIgniter\RESTful\ResourceController;
use CodeIgniter\HTTP\ResponseInterface;
use Config\Services;

class DisplayReservasiController extends ResourceController
{
    protected $displayService;
    protected $validation;

    public function __construct()
    {
        $this->displayService = new DisplayReservasiService();
        $this->validation = Services::validation();
    }

    public function getDataDisplayReservasi()
    {
        $this->validation->setRules([
            'tanggalKunjungan' => 'required',
        ]);

        if (!$this->validation->withRequest($this->request)->run()) {
            return $this->respond([
                'status' => false,
                'message' => $this->validation->getErrors(),
            ], ResponseInterface::HTTP_BAD_REQUEST);
        }

        try {
            $params = $this->request->getGet();
            $request = $this->displayService->getDataDisplayReservasi($params);
            if ($request['status']) return $this->respond([
                'status' => true,
                'message' => 'Data reservasi ditemukan!',
                'data' => $request['data']
            ], ResponseInterface::HTTP_OK);
            return $this->respond([
                'status' => false,
                'message' => 'Data reservasi tidak ditemukan!',
            ], ResponseInterface::HTTP_NOT_FOUND);
        } catch (\Exception $e) {
            return $this->respond([
                'status' => false,
                'message' => $e->getMessage(),
            ], ResponseInterface::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function getPengunjung()
    {
        $this->validation->setRules([
            'tanggalKunjungan' => 'required',
            'dokter' => 'required',
            'ruangan' => 'required',
            'caraBayar' => 'required',
            'page' => 'required',
            'limit' => 'required',
        ]);

        if (!$this->validation->withRequest($this->request)->run()) {
            return $this->respond([
                'status' => false,
                'message' => $this->validation->getErrors(),
            ], ResponseInterface::HTTP_BAD_REQUEST);
        }

        try {
            $params = $this->request->getGet();
            $request = $this->displayService->getPengunjung($params);
            if ($request['status']) return $this->respond([
                'status' => true,
                'message' => 'Data pendaftaran ditemukan!',
                'data' => $request['data']
            ], ResponseInterface::HTTP_OK);
            return $this->respond([
                'status' => false,
                'message' => 'Data pendaftaran tidak ditemukan!',
            ], ResponseInterface::HTTP_NOT_FOUND);
        } catch (\Exception $e) {
            return $this->respond([
                'status' => false,
                'message' => $e->getMessage(),
            ], ResponseInterface::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
