<?php

namespace App\Modules\BPJSVclaim\Controllers\Api\V1;

use App\Modules\BPJSVclaim\Services\PesertaService;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\RESTful\ResourceController;
use Config\Services;

class PesertaController extends ResourceController
{
    protected $validator;
    protected $pesertaService;

    public function __construct()
    {
        $this->validator = Services::validation();
        $this->pesertaService = new PesertaService();
    }

    public function pesertaByNoKartu($noKartu)
    {
        try {
            $request = $this->pesertaService->pesertaByNoKartu($noKartu);
            if ($request['status']) return $this->respond($request, ResponseInterface::HTTP_OK);
            return $this->respond($request, ResponseInterface::HTTP_NOT_FOUND);
        } catch (\Exception $e) {
            return $this->respond(['status' => false, 'message' => $e], ResponseInterface::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
    public function pesertaByNIK($nik)
    {
        try {
            $request = $this->pesertaService->pesertaByNIK($nik);
            if ($request['status']) return $this->respond($request, ResponseInterface::HTTP_OK);
            return $this->respond($request, ResponseInterface::HTTP_NOT_FOUND);
        } catch (\Exception $e) {
            return $this->respond(['status' => false, 'message' => $e], ResponseInterface::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
