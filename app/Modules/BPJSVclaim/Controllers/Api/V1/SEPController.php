<?php

namespace App\Modules\BPJSVclaim\Controllers\Api\V1;

use App\Modules\BPJSVclaim\Services\SEPService;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\RESTful\ResourceController;
use Config\Services;

class SEPController extends ResourceController
{
    protected $validator;
    protected $sepService;

    public function __construct()
    {
        $this->validator = Services::validation();
        $this->sepService = new SEPService();
    }

    public function listDataUpdateTanggalPulang()
    {
        $this->validator->setRules([
            'bulan' => 'required',
            'tahun' => 'required',
        ]);

        if (!$this->validator->withRequest($this->request)->run()) {
            return $this->respond([
                'status' => false,
                'message' => $this->validator->getErrors(),
            ], 400);
        }

        $body = $this->request->getBody();
        $params = json_decode($body, true);
        try {
            $request = $this->sepService->listDataUpdateTanggalPulang($params);
            if ($request['status']) return $this->respond($request, ResponseInterface::HTTP_OK);
            return $this->respond($request, ResponseInterface::HTTP_NOT_FOUND);
        } catch (\Exception $e) {
            return $this->respond(['status' => false, 'message' => $e->getMessage()], ResponseInterface::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function cariSEP($sep)
    {
        try {
            $result = $this->sepService->cariSEP($sep);
            if ($result['status']) return $this->respond($result, 200);
            return $this->respond($result, 404);
        } catch (\Exception $e) {
            return $this->respond([
                'status' => false,
                'message' => $e->getMessage()
            ], ResponseInterface::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function insertSEP()
    {
        try {
            $body = $this->request->getBody();
            $params = json_decode($body, true);
            $request = $this->sepService->insertSEP($params);
            if ($request['status']) return $this->respond($request, ResponseInterface::HTTP_OK);
            return $this->respond($request, ResponseInterface::HTTP_NOT_FOUND);
        } catch (\Exception $e) {
            return $this->respond(['status' => false, 'message' => $e], ResponseInterface::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
