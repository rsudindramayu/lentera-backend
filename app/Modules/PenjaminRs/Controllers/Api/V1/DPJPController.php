<?php

namespace App\Modules\PenjaminRs\Controllers\Api\V1;

use App\Modules\PenjaminRs\Services\DPJPService;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\RESTful\ResourceController;

class DPJPController extends ResourceController
{
    protected $DPJPService;
    public function __construct()
    {
        $this->DPJPService = new DPJPService();
    }

    public function searchDPJP()
    {
        try {
            $namaDokter = $this->request->getGet('namaDokter');

            if (!$namaDokter) return $this->respond([
                'status' => false,
                'message' => 'Nama dokter tidak boleh kosong!'
            ]);

            $response = $this->DPJPService->searchDPJP($namaDokter);
            if ($response['status'] == false) return $this->respond([
                'status' => false,
                'message' => 'DPJP tidak ditemukan!'
            ], ResponseInterface::HTTP_NOT_FOUND);
            return $this->respond([
                'status' => true,
                'message' => 'DPJP ditemukan!',
                'data' => $response['data']
            ], ResponseInterface::HTTP_OK);
        } catch (\Exception $e) {
            return
                $this->respond([
                    'status' => false,
                    'message' => $e->getMessage()
                ], ResponseInterface::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
