<?php

namespace App\Modules\BPJSVclaim\Controllers\Api\V1;

use App\Modules\BPJSVclaim\Services\RencanaKontrolService;
use App\Modules\BPJSVclaim\Services\RequestService;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\RESTful\ResourceController;
use Config\Services;

class RencanaKontrolController extends ResourceController
{
    protected $validator;
    protected $rencanaKontrolService;

    public function __construct()
    {
        $this->validator = Services::validation();
        $this->rencanaKontrolService = new RencanaKontrolService();
    }

    public function cariNomorSuratKontrol($noSuratKontrol)
    {
        try {
            $request = $this->rencanaKontrolService->cariNomorSuratKontrol($noSuratKontrol);
            if ($request['status']) return $this->respond($request, ResponseInterface::HTTP_OK);
            return $this->respond($request, ResponseInterface::HTTP_NOT_FOUND);
        } catch (\Exception $e) {
            return $this->respond(['status' => false, 'message' => $e], ResponseInterface::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function cariSEP($sep)
    {
        try {
            $request = $this->rencanaKontrolService->cariSEP($sep);
            if ($request['status']) return $this->respond($request, ResponseInterface::HTTP_OK);
            return $this->respond($request, ResponseInterface::HTTP_NOT_FOUND);
        } catch (\Exception $e) {
            return $this->respond(['status' => false, 'message' => $e], ResponseInterface::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function insertRencanaKontrol()
    {
        $this->validator->setRules([
            'noSEP' => 'required',
            'kodeDokter' => 'required',
            'poliKontrol' => 'required',
            'tglRencanaKontrol' => 'required',
            'user' => 'required',
        ]);

        if (!$this->validator->withRequest($this->request)->run()) {
            return $this->respond([
                'status' => false,
                'message' => $this->validator->getErrors(),
            ], 400);
        }

        $body = $this->request->getBody();
        $data = json_decode($body, true);
        try {
            $request = $this->rencanaKontrolService->insertRencanaKontrol($data);
            if ($request['status']) return $this->respond($request, ResponseInterface::HTTP_OK);
            return $this->respond($request, ResponseInterface::HTTP_NOT_FOUND);
        } catch (\Exception $e) {
            return $this->respond(['status' => false, 'message' => $e], ResponseInterface::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function updateRencanaKontrol()
    {

        $this->validator->setRules([
            'noSuratKontrol' => 'required',
            'noSEP' => 'required',
            'kodeDokter' => 'required',
            'poliKontrol' => 'required',
            'tglRencanaKontrol' => 'required',
            'user' => 'required',
        ]);

        if (!$this->validator->withRequest($this->request)->run()) {
            return $this->respond([
                'status' => false,
                'message' => $this->validator->getErrors(),
            ], 400);
        }

        $body = $this->request->getBody();
        $data = json_decode($body, true);

        try {
            $request = $this->rencanaKontrolService->updateRencanaKontrol($data);
            if ($request['status']) return $this->respond($request, ResponseInterface::HTTP_OK);
            return $this->respond($request, ResponseInterface::HTTP_NOT_FOUND);
        } catch (\Exception $e) {
            return $this->respond(['status' => false, 'message' => $e], ResponseInterface::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    function hapusRencanaKontrol()
    {
        $this->validator->setRules([
            'noSuratKontrol' => 'required',
            'user' => 'required',
        ]);
        if (!$this->validator->withRequest($this->request)->run()) {
            return $this->respond([
                'status' => false,
                'message' => $this->validator->getErrors(),
            ], 400);
        }
        $body = $this->request->getBody();
        $data = json_decode($body, true);
        try {
            $request = $this->rencanaKontrolService->hapusRencanaKontrol($data);
            if ($request['status']) return $this->respond($request, ResponseInterface::HTTP_OK);
            return $this->respond($request, ResponseInterface::HTTP_NOT_FOUND);
        } catch (\Exception $e) {
            return $this->respond(['status' => false, 'message' => $e], ResponseInterface::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function insertSPRI()
    {
        $this->validator->setRules([
            'noKartu' => 'required',
            'kodeDokter' => 'required',
            'poliKontrol' => 'required',
            'tglRencanaKontrol' => 'required',
            'user' => 'required',
        ]);

        if (!$this->validator->withRequest($this->request)->run()) {
            return $this->respond([
                'status' => false,
                'message' => $this->validator->getErrors(),
            ], 400);
        }

        $body = $this->request->getBody();
        $data = json_decode($body, true);
        try {
            $request = $this->rencanaKontrolService->insertSPRI($data);
            if ($request['status']) return $this->respond($request, ResponseInterface::HTTP_OK);
            return $this->respond($request, ResponseInterface::HTTP_NOT_FOUND);
        } catch (\Exception $e) {
            return $this->respond(['status' => false, 'message' => $e], ResponseInterface::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function updateSPRI()
    {
        $this->validator->setRules([
            'noSPRI' => 'required',
            'kodeDokter' => 'required',
            'poliKontrol' => 'required',
            'tglRencanaKontrol' => 'required',
            'user' => 'required',
        ]);

        if (!$this->validator->withRequest($this->request)->run()) {
            return $this->respond([
                'status' => false,
                'message' => $this->validator->getErrors(),
            ], 400);
        }

        $body = $this->request->getBody();
        $data = json_decode($body, true);
        try {
            $request = $this->rencanaKontrolService->updateSPRI($data);
            if ($request['status']) return $this->respond($request, ResponseInterface::HTTP_OK);
            return $this->respond($request, ResponseInterface::HTTP_NOT_FOUND);
        } catch (\Exception $e) {
            return $this->respond(['status' => false, 'message' => $e], ResponseInterface::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function dataNomorSuratKontrolByKartu()
    {
        //formatFilter => 1 = tanggal terbit, 2 = tanggal rencana
        $this->validator->setRules([
            'noKartu' => 'required',
            'formatFilter' => 'required',
            'tglRencanaKontrol' => 'required',
        ]);
        if (!$this->validator->withRequest($this->request)->run()) {
            return $this->respond([
                'status' => false,
                'message' => $this->validator->getErrors(),
            ], 400);
        }
        $params = $this->request->getGet();
        try {
            $request = $this->rencanaKontrolService->dataNomorSuratKontrolByKartu($params);
            if ($request['status']) return $this->respond($request, ResponseInterface::HTTP_OK);
            return $this->respond($request, ResponseInterface::HTTP_NOT_FOUND);
        } catch (\Exception $e) {
            return $this->respond(['status' => false, 'message' => $e], ResponseInterface::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function dataNomorSuratKontrol()
    {
        //formatFilter => 1 = tanggal terbit, 2 = tanggal rencana

        $this->validator->setRules([
            'tanggalAwal' => 'required',
            'tanggalAkhir' => 'required',
            'formatFilter' => 'required',
        ]);

        if (!$this->validator->withRequest($this->request)->run()) {
            return $this->respond([
                'status' => false,
                'message' => $this->validator->getErrors(),
            ], 400);
        }

        $params = $this->request->getGet();
        try {
            $request = $this->rencanaKontrolService->dataNomorSuratKontrol($params);
            if ($request['status']) return $this->respond($request, ResponseInterface::HTTP_OK);
            return $this->respond($request, ResponseInterface::HTTP_NOT_FOUND);
        } catch (\Exception $e) {
            return $this->respond(['status' => false, 'message' => $e], ResponseInterface::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function dataPoli()
    {
        //jenisKontrol => 1 = SPRI, 2 = Rencana Kontrol
        //nomorReferensi => jika jenisKontrol = 1 maka noKartu, jika jenisKontrol = 2 maka SEP

        $this->validator->setRules([
            'jenisKontrol' => 'required',
            'nomorReferensi' => 'required',
            'tglRencanaKontrol' => 'required',
        ]);

        if (!$this->validator->withRequest($this->request)->run()) {
            return $this->respond([
                'status' => false,
                'message' => $this->validator->getErrors(),
            ], 400);
        }

        $params = $this->request->getGet();
        try {
            $request = $this->rencanaKontrolService->dataPoli($params);
            if ($request['status']) return $this->respond($request, ResponseInterface::HTTP_OK);
            return $this->respond($request, ResponseInterface::HTTP_NOT_FOUND);
        } catch (\Exception $e) {
            return $this->respond(['status' => false, 'message' => $e], ResponseInterface::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function dataDokter()
    {
        //jenisKontrol => 1 = SPRI, 2 = Rencana Kontrol

        $this->validator->setRules([
            'jenisKontrol' => 'required',
            'kodePoli' => 'required',
            'tglRencanaKontrol' => 'required',
        ]);

        if (!$this->validator->withRequest($this->request)->run()) {
            return $this->respond([
                'status' => false,
                'message' => $this->validator->getErrors(),
            ], 400);
        }

        $params = $this->request->getGet();
        try {
            $request = $this->rencanaKontrolService->dataDokter($params);
            if ($request['status']) return $this->respond($request, ResponseInterface::HTTP_OK);
            return $this->respond($request, ResponseInterface::HTTP_NOT_FOUND);
        } catch (\Exception $e) {
            return $this->respond(['status' => false, 'message' => $e], ResponseInterface::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
