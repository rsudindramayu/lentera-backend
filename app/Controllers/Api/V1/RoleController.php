<?php

namespace App\Controllers\Api\V1;

use App\Services\RoleService;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\RESTful\ResourceController;
use Config\Services;

class RoleController extends ResourceController
{
    protected $roleService;
    protected $validation;

    public function __construct()
    {
        $this->roleService = new RoleService();
        $this->validation = Services::validation();
    }

    public function getRoles()
    {
        try {
            //params: search, limit, page
            $params = $this->request->getGet();
            $result = $this->roleService->getRoles($params);

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

    public function createRole()
    {
        $this->validation->setRules([
            'nama_role' => [
                'rules' => 'required|is_unique[roles.nama_role]',
                'label' => 'Nama role',
                'errors' => [
                    'required' => '{field} harus diisi!',
                    'is_unique' => '{field} sudah ada!',
                ]
            ]
        ]);

        if (!$this->validation->withRequest($this->request)->run()) {
            return $this->respond([
                'status' => false,
                'message' => $this->validation->getErrors(),
            ], ResponseInterface::HTTP_BAD_REQUEST);
        }

        try {
            $params = (array) $this->request->getJSON();
            $result = $this->roleService->createRole($params);
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

    public function updateRole($id)
    {
        $this->validation->setRules([
            'nama_role' => [
                'rules' => 'required|is_unique[roles.nama_role,id,' . $id . ']',
                'label' => 'Nama role',
                'errors' => [
                    'required' => '{field} harus diisi!',
                    'is_unique' => '{field} sudah ada, inputkan dengan nama yang berbeda!',
                ]
            ]
        ]);

        if (!$this->validation->withRequest($this->request)->run()) {
            return $this->respond([
                'status' => false,
                'message' => $this->validation->getErrors(),
            ], ResponseInterface::HTTP_BAD_REQUEST);
        }

        try {
            $params = (array) $this->request->getJSON();
            $result = $this->roleService->updateRole($id, $params);
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

    public function deleteRole($id)
    {
        try {
            $result = $this->roleService->deleteRole($id);
            if ($result['status']) {
                return $this->respond([
                    'status' => true,
                    'message' => 'Data berhasil dihapus!',
                ], ResponseInterface::HTTP_CREATED);
            }

            return $this->respond([
                'status' => false,
                'message' => 'Data gagal dihapus!',
            ], ResponseInterface::HTTP_BAD_REQUEST);
        } catch (\Exception $e) {
            return $this->respond([
                'status' => false,
                'message' => $e->getMessage(),
            ], ResponseInterface::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
