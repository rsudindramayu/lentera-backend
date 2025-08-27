<?php

namespace App\Controllers\Api\V1;

use App\Services\RolePermissionService;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\RESTful\ResourceController;
use Config\Services;

class RolePermissionController extends ResourceController
{
    protected $rolePermissionService;
    protected $validation;

    public function __construct()
    {
        $this->rolePermissionService = new RolePermissionService();
        $this->validation = Services::validation();
    }

    public function getPermissionByRoleId($roleId): ResponseInterface
    {
        try {
            $result = $this->rolePermissionService->getPermissionByRoleId($roleId);
            if ($result['status']) {
                return $this->respond(
                    [
                        'status' => true,
                        'message' => 'Data ditemukan!',
                        'data' => $result['data']
                    ],
                    ResponseInterface::HTTP_OK
                );
            }
            return $this->respond(['status' => false, 'message' => 'Data tidak ditemukan!'], ResponseInterface::HTTP_NOT_FOUND);
        } catch (\Exception $e) {
            return $this->respond([
                'status' => false,
                'message' => $e->getMessage()
            ], ResponseInterface::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function syncRolePermissions()
    {
        $this->validation->setRules([
            'role_id' => 'required',
            'permission_ids' => 'permit_empty'
        ]);

        if (!$this->validation->withRequest($this->request)->run()) {
            return $this->respond([
                'status' => false,
                'message' => $this->validation->getErrors(),
            ], ResponseInterface::HTTP_BAD_REQUEST);
        }

        try {
            $params = (array) $this->request->getJSON();
            $result = $this->rolePermissionService->syncRolePermissions($params);
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
