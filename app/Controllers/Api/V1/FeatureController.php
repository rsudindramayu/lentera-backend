<?php

namespace App\Controllers\Api\V1;

use App\Services\FeatureService;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\RESTful\ResourceController;
use Config\Services;

class FeatureController extends ResourceController
{
    protected $featureService;
    protected $validation;

    public function __construct()
    {
        $this->featureService = new FeatureService();
        $this->validation = Services::validation();
    }

    public function getFeatures(): ResponseInterface
    {
        try {
            $params = $this->request->getGet();
            $features = $this->featureService->getFeatures($params);
            if ($features['status']) {
                return $this->respond(['status' => true, 'message' => 'Data ditemukan!', 'data' => $features['data']], ResponseInterface::HTTP_OK);
            }

            return $this->respond(['status' => false, 'message' => 'Data tidak ditemukan!'], ResponseInterface::HTTP_NOT_FOUND);
        } catch (\Exception $e) {
            return $this->respond([
                'status' => false,
                'message' => $e->getMessage()
            ], ResponseInterface::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function getFeatureWithPermissions(): ResponseInterface
    {
        try {
            $params = $this->request->getGet();
            $result = $this->featureService->getFeatureWithPermissions($params);
            if ($result['status']) {
                return $this->respond(['status' => true, 'message' => 'Data ditemukan!', 'data' => $result['data']], ResponseInterface::HTTP_OK);
            }

            return $this->respond(['status' => false, 'message' => 'Data tidak ditemukan!'], ResponseInterface::HTTP_NOT_FOUND);
        } catch (\Exception $e) {
            return $this->respond([
                'status' => false,
                'message' => $e->getMessage()
            ], ResponseInterface::HTTP_INTERNAL_SERVER_ERROR);
        }
    }


    public function addFeature(): ResponseInterface
    {
        $this->validation->setRules([
            'key' => [
                'label'  => 'Nama fitur',
                'rules'  => 'required|trim|regex_match[/^\S+$/]|is_unique[features.key]',
                'errors' => [
                    'required'    => '{field} wajib diisi.',
                    'regex_match' => '{field} tidak boleh mengandung spasi.',
                    'is_unique'   => '{field} sudah ada, inputkan dengan nama yang berbeda.',
                ],
            ],
        ]);

        if (!$this->validation->withRequest($this->request)->run()) {
            return $this->respond([
                'status' => false,
                'message' => $this->validation->getErrors(),
            ], ResponseInterface::HTTP_BAD_REQUEST);
        }
        try {
            $params = (array)$this->request->getJSON();
            $result = $this->featureService->create($params);
            if ($result['status']) {
                return $this->respond(['status' => true, 'message' => 'Data berhasil disimpan!'], ResponseInterface::HTTP_CREATED);
            }

            return $this->respond(['status' => false, 'message' => 'Data gagal disimpan!'], ResponseInterface::HTTP_NOT_FOUND);
        } catch (\Exception $e) {
            return $this->respond([
                'status' => false,
                'message' => $e->getMessage()
            ], ResponseInterface::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function updateFeature($id): ResponseInterface
    {
        try {
            $params = (array)$this->request->getJSON();
            $result = $this->featureService->update($id, $params);
            if ($result['status']) {
                return $this->respond(['status' => true, 'message' => 'Data berhasil diupdate!'], ResponseInterface::HTTP_OK);
            }

            return $this->respond(['status' => false, 'message' => 'Data gagal diupdate!'], ResponseInterface::HTTP_NOT_FOUND);
        } catch (\Exception $e) {
            return $this->respond([
                'status' => false,
                'message' => $e->getMessage()
            ], ResponseInterface::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
