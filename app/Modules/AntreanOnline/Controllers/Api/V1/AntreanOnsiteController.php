<?php

namespace App\Modules\AntreanOnline\Controllers\Api\V1;

use App\Modules\AntreanOnline\Services\AntreanOnsiteService;
use CodeIgniter\RESTful\ResourceController;
use Config\Services;
use CodeIgniter\HTTP\ResponseInterface;

class AntreanOnsiteController extends ResourceController
{
    protected $antreanOnsiteService;
    protected $validation;

    public function __construct()
    {
        $this->antreanOnsiteService = new AntreanOnsiteService();
        $this->validation = Services::validation();
    }

    public function getDataPasien($keyPasien)
    {
        try {
            $response = $this->antreanOnsiteService->getDataPasien($keyPasien);
            if ($response['status']) return $this->respond([
                'status' => true,
                'message' => 'Data pasien ditemukan!',
                'data' => $response['data']
            ], ResponseInterface::HTTP_OK);
            return $this->respond([
                'status' => false,
                'message' => 'Data pasien tidak ditemukan!'
            ], ResponseInterface::HTTP_NOT_FOUND);
        } catch (\Exception $e) {
            return $this->respond([
                'status' => false,
                'message' => $e->getMessage()
            ], ResponseInterface::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function getHistoryPendaftaran($norm)
    {
        try {
            $response = $this->antreanOnsiteService->getHistoryPendaftaran($norm);
            if ($response['status']) return $this->respond([
                'status' => true,
                'message' => 'History pasien ditemukan!',
                'data' => [
                    'list' => $response['data']
                ]
            ], ResponseInterface::HTTP_OK);
            return $this->respond([
                'status' => false,
                'message' => 'response pasien tidak ditemukan!'
            ], ResponseInterface::HTTP_NOT_FOUND);
        } catch (\Exception $e) {
            return $this->respond([
                'status' => false,
                'message' => $e->getMessage()
            ], ResponseInterface::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function getReservasi($norm)
    {
        try {
            $response = $this->antreanOnsiteService->getReservasi($norm);
            if ($response['status']) return $this->respond([
                'status' => true,
                'message' => 'Reservasi pasien ditemukan!',
                'data' => [
                    'list' => $response['data']
                ]
            ], ResponseInterface::HTTP_OK);
            return $this->respond([
                'status' => false,
                'message' => 'Reservasi pasien tidak ditemukan!'
            ], ResponseInterface::HTTP_NOT_FOUND);
        } catch (\Exception $e) {
            return $this->respond([
                'status' => false,
                'message' => $e->getMessage()
            ], ResponseInterface::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function getPoliklinik()
    {
        try {
            $response = $this->antreanOnsiteService->getPoliklinik();
            if ($response['status']) return $this->respond([
                'status' => true,
                'message' => 'Data poliklinik ditemukan!',
                'data' => [
                    'list' => $response['data']
                ]
            ], ResponseInterface::HTTP_OK);
            return $this->respond([
                'status' => false,
                'message' => 'Data poliklinik tidak ditemukan!'
            ], ResponseInterface::HTTP_NOT_FOUND);
        } catch (\Exception $e) {
            return $this->respond([
                'status' => false,
                'message' => $e->getMessage()
            ], ResponseInterface::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function getJadwalDokter()
    {
        $this->validation->setRules([
            'tanggal' => 'required|valid_date[Y-m-d]',
            'kodePoli' => 'required',
        ]);
        if (!$this->validation->withRequest($this->request)->run()) return $this->respond([
            'status' => false,
            'message' => $this->validation->getErrors()
        ], ResponseInterface::HTTP_BAD_REQUEST);
        try {
            $params = $this->request->getGet();
            $response = $this->antreanOnsiteService->getJadwalDokter($params);
            if ($response['status']) return $this->respond([
                'status' => true,
                'message' => 'Jadwal dokter ditemukan!',
                'data' => $response['data']
            ], ResponseInterface::HTTP_OK);
            return $this->respond([
                'status' => false,
                'message' => 'Jadwal dokter tidak ditemukan!'
            ], ResponseInterface::HTTP_NOT_FOUND);
        } catch (\Exception $e) {
            return $this->respond([
                'status' => false,
                'message' => $e->getMessage()
            ], ResponseInterface::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
