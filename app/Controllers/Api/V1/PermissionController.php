<?php

namespace App\Controllers\Api\V1;

use App\Services\PermissionService;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\RESTful\ResourceController;
use Config\Services;

class PermissionController extends ResourceController
{
    protected $permissionService;
    protected $validation;

    public function __construct()
    {
        $this->permissionService = new PermissionService();
        $this->validation = Services::validation();
    }

    public function getPermissions(): ResponseInterface
    {
        try {
            $params = $this->request->getGet();
            $result = $this->permissionService->getPermissions($params);
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

    public function addPermission(): ResponseInterface
    {
        $this->validation->setRules([
            'action_key' => [
                'label'  => 'Nama akses',
                'rules'  => 'required|trim|regex_match[/^\S+$/]',
                'errors' => [
                    'required'    => '{field} wajib diisi.',
                    'regex_match' => '{field} tidak boleh mengandung spasi.',
                ],
            ],
            'module_id' => [
                'label'  => 'Modul',
                'rules'  => 'required',
                'errors' => [
                    'required' => '{field} wajib pilih fitur.',
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
            $result = $this->permissionService->create($params);
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

    public function updatePermission($id): ResponseInterface
    {
        try {
            $params = (array)$this->request->getJSON();
            $result = $this->permissionService->update($id, $params);
            if ($result['status']) {
                return $this->respond(['status' => true, 'message' => 'Data berhasil diupdate!']);
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
