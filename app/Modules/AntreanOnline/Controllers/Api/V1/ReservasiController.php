<?php

namespace App\Modules\AntreanOnline\Controllers\Api\V1;

use CodeIgniter\RESTful\ResourceController;
use App\Modules\AntreanOnline\Services\ReservasiService;
use CodeIgniter\HTTP\ResponseInterface;

class ReservasiController extends ResourceController
{
    protected $reservasiService;

    public function __construct()
    {
        $this->reservasiService = new ReservasiService();
    }

    public function getReservasi()
    {
        try {
            $params = $this->request->getGet();
            $reservasi = $this->reservasiService->getData($params);
            if (!$reservasi['status']) {
                return $this->respond([
                    'status' => false,
                    'message' => 'Data reservasi tidak ditemukan!'
                ], ResponseInterface::HTTP_NOT_FOUND);
            }
            return $this->respond([
                'status' => true,
                'message' => 'Data reservasi ditemukan!',
                'data' => $reservasi['data']
            ], ResponseInterface::HTTP_OK);
        } catch (\Exception $e) {
            return $this->respond([
                'status' => false,
                'message' => $e->getMessage()
            ], ResponseInterface::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function getSummaryReservasi()
    {
        try {
            $params = $this->request->getGet();
            $data = $this->reservasiService->getSummaryReservasi($params);
            if (!$data['status']) {
                return $this->respond([
                    'status' => false,
                    'message' => $data['message']
                ], ResponseInterface::HTTP_NOT_FOUND);
            }

            return $this->respond([
                'status' => true,
                'message' => 'Data reservasi ditemukan!',
                'data' => $data['data']
            ], ResponseInterface::HTTP_OK);
        } catch (\Exception $e) {
            return $this->respond([
                'status' => false,
                'message' => $e->getMessage()
            ], ResponseInterface::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
