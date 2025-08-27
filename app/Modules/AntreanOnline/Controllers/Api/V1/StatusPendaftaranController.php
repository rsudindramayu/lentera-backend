<?php

namespace App\Modules\AntreanOnline\Controllers\Api\V1;

use App\Modules\AntreanOnline\Services\StatusPendaftaranService;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\RESTful\ResourceController;
use Config\Services;

class StatusPendaftaranController extends ResourceController
{
    protected $statusPendaftaraService;
    protected $validation;

    public function __construct()
    {
        $this->statusPendaftaraService = new StatusPendaftaranService();
        $this->validation = Services::validation();
    }

    public function updateStatusPendaftaran()
    {
        $this->validation->setRules([
            'tanggal' => 'required|valid_date[Y-m-d]',
            'kodeDokter' => 'required',
            'status' => 'required',
            'user' => 'required',
        ]);

        if (!$this->validation->withRequest($this->request)->run()) {
            return $this->respond([
                'status' => false,
                'message' => $this->validation->getErrors(),
            ], ResponseInterface::HTTP_BAD_REQUEST);
        }

        try {
            $body = $this->request->getBody();
            $data = json_decode($body, true);
            $request = $this->statusPendaftaraService->updateStatusPendaftaran($data);
            if ($request['status']) return $this->respond($request, ResponseInterface::HTTP_OK);
            return $this->respond($request, ResponseInterface::HTTP_NOT_FOUND);
        } catch (\Exception $e) {
            return $this->respond(['status' => false, 'message' => $e], ResponseInterface::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
