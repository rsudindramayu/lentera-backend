<?php

namespace App\Controllers\Api\V1;

use App\Services\PenggunaService;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\RESTful\ResourceController;
use Config\Services;

class PenggunaController extends ResourceController
{
    protected $penggunaService;
    protected $validation;

    public function __construct()
    {
        $this->penggunaService = new PenggunaService();
        $this->validation = Services::validation();
    }

    public function getPengguna(): ResponseInterface
    {
        try {
            //params: search, limit, page, sortBy, sortDir
            $params = $this->request->getGet();
            $result = $this->penggunaService->getPengguna($params);

            if ($result['status']) {
                return $this->respond([
                    'status' => true,
                    'data' => $result['data']
                ]);
            }

            return $this->respond([
                'status' => false,
                'message' => 'Data tidak ditemukan!',
            ]);
        } catch (\Exception $e) {
            return $this->respond([
                'status' => false,
                'message' => $e->getMessage()
            ], ResponseInterface::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
