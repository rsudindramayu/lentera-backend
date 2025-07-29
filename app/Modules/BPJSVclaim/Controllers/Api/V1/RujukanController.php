<?php

namespace App\Modules\BPJSVclaim\Controllers\Api\V1;

use App\Modules\BPJSVclaim\Services\RujukanService;
use CodeIgniter\HTTP\ResponsableInterface;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\RESTful\ResourceController;
use Config\Services;

class RujukanController extends ResourceController
{
    protected $validation;
    protected $rujukanService;

    public function __construct()
    {
        $this->validation = Services::validation();
        $this->rujukanService = new RujukanService();
    }

    public function rujukanBerdasarkanNomorKartu($noKartu)
    {
        try {
            $request = $this->rujukanService->rujukanBerdasarkanNomorKartu($noKartu);
            if ($request['status']) return $this->respond($request, ResponseInterface::HTTP_OK);
            return $this->respond($request, ResponseInterface::HTTP_NOT_FOUND);
        } catch (\Exception $e) {
            return $this->respond(['status' => false, 'result' => $e], ResponseInterface::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function rujukanBerdasarkanNomorRujukan($noRujukan)
    {
        try {
            $request = $this->rujukanService->rujukanBerdasarkanNomorRujukan($noRujukan);
            if ($request['status']) return $this->respond($request, ResponseInterface::HTTP_OK);
            return $this->respond($request, ResponseInterface::HTTP_NOT_FOUND);
        } catch (\Exception $e) {
            return $this->respond(['status' => false, 'result' => $e], ResponseInterface::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
