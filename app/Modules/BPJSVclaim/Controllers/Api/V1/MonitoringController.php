<?php

namespace App\Modules\BPJSVclaim\Controllers\Api\V1;

use App\Modules\BPJSVclaim\Services\MonitoringService;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\RESTful\ResourceController;
use Config\Services;

class MonitoringController extends ResourceController
{
    protected $validator;
    protected $monitoringService;

    public function __construct()
    {
        $this->validator = Services::validation();
        $this->monitoringService = new MonitoringService();
    }

    public function dataHistoryPelayananPeserta()
    {
        $this->validator->setRules([
            'noKartu' => 'required',
            'tanggalAwal' => 'required',
            'tanggalAkhir' => 'required'
        ]);

        if (!$this->validator->withRequest($this->request)->run()) {
            return $this->respond([
                'status' => false,
                'message' => $this->validator->getErrors(),
            ], 400);
        }

        $params = $this->request->getGet();
        try {
            $request = $this->monitoringService->dataHistoryPelayananPeserta($params);
            if ($request['status']) return $this->respond($request, ResponseInterface::HTTP_OK);
            return $this->respond($request, ResponseInterface::HTTP_NOT_FOUND);
        } catch (\Exception $e) {
            return $this->respond(['status' => false, 'message' => $e], ResponseInterface::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function dataKunjungan()
    {
        $this->validator->setRules([
            'tanggalSep' => 'required',
            'jenisPelayanan' => 'required'
        ]);

        if (!$this->validator->withRequest($this->request)->run()) {
            return $this->respond([
                'status' => false,
                'message' => $this->validator->getErrors(),
            ], 400);
        }

        $params = $this->request->getGet();
        try {
            $request = $this->monitoringService->dataKunjungan($params);
            if ($request['status']) return $this->respond($request, ResponseInterface::HTTP_OK);
            return $this->respond($request, ResponseInterface::HTTP_NOT_FOUND);
        } catch (\Exception $e) {
            return $this->respond(['status' => false, 'message' => $e], ResponseInterface::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function dataKlaim()
    {
        //jenisPelayanan => 1 = Rawat Inap, 2 = Rawat Jalan
        //statusKlaim => 1 = proses verifikasi, 2 = pending verifikasi, 3 = klaim
        $this->validator->setRules([
            'tanggalPulang' => 'required',
            'jenisPelayanan' => 'required',
            'statusKlaim' => 'required'
        ]);

        if (!$this->validator->withRequest($this->request)->run()) {
            return $this->respond([
                'status' => false,
                'message' => $this->validator->getErrors(),
            ], 400);
        }

        $params = $this->request->getGet();
        try {
            $request = $this->monitoringService->dataKlaim($params);
            if ($request['status']) return $this->respond($request, ResponseInterface::HTTP_OK);
            return $this->respond($request, ResponseInterface::HTTP_NOT_FOUND);
        } catch (\Exception $e) {
            return $this->respond(['status' => false, 'message' => $e], ResponseInterface::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function dataKlaimJaminanJasaRaharja()
    {
        //jenisPelayanan => 1 = Rawat Inap, 2 = Rawat Jalan
        $this->validator->setRules([
            'jenisPelayanan' => 'required',
            'tanggalAwal' => 'required',
            'tanggalAkhir' => 'required'
        ]);

        if (!$this->validator->withRequest($this->request)->run()) {
            return $this->respond([
                'status' => false,
                'message' => $this->validator->getErrors(),
            ], 400);
        }

        $params = $this->request->getGet();
        try {
            $request = $this->monitoringService->dataKlaimJaminanJasaRaharja($params);
            if ($request['status']) return $this->respond($request, ResponseInterface::HTTP_OK);
            return $this->respond($request, ResponseInterface::HTTP_NOT_FOUND);
        } catch (\Exception $e) {
            return $this->respond(['status' => false, 'message' => $e], ResponseInterface::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
