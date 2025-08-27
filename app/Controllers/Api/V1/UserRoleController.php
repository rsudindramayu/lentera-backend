<?php

namespace App\Controllers\Api\V1;

use App\Services\UserRoleService;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\RESTful\ResourceController;
use Config\Services;

class UserRoleController extends ResourceController
{
    protected $userRoleService;
    protected $validation;

    public function __construct()
    {
        $this->userRoleService = new UserRoleService();
        $this->validation = Services::validation();
    }

    public function getRoleByUserId()
    {
        //params: search, limit, page
        try {
            $params = $this->request->getGet();
            $result = $this->userRoleService->getRoleByUserId($params);
            if ($result['status']) {
                return $this->respond([
                    'status' => true,
                    'message' => 'Data ditemukan!',
                    'data' => $result['data']
                ], ResponseInterface::HTTP_OK);
            }
            return $this->respond(['status' => false, 'message' => 'Data tidak ditemukan!'], ResponseInterface::HTTP_NOT_FOUND);
        } catch (\Exception $e) {
            return $this->respond([
                'status' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    public function syncUserRoles()
    {
        $this->validation->setRules([
            'pengguna_id' => 'required',
            'role_ids' => 'permit_empty'
        ]);

        if (!$this->validation->withRequest($this->request)->run()) {
            return $this->respond([
                'status' => false,
                'message' => $this->validation->getErrors(),
            ], ResponseInterface::HTTP_BAD_REQUEST);
        }

        try {
            $params = (array) $this->request->getJSON();
            $result = $this->userRoleService->synchUserRoles($params);
            if ($result['status']) {
                return $this->respond([
                    'status' => true,
                    'message' => 'Data berhasil disimpan!',
                ], ResponseInterface::HTTP_CREATED);
            }

            return $this->respond([
                'status' => false,
                'message' => 'Data gagal disimpan!',
            ], ResponseInterface::HTTP_BAD_REQUEST);
        } catch (\Exception $e) {
            return $this->respond([
                'status' => false,
                'message' => $e->getMessage(),
            ], ResponseInterface::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
