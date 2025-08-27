<?php

namespace App\Services;

use App\Models\PermissionModel;

class PermissionService
{
    protected $permissionModel;

    public function __construct()
    {
        $this->permissionModel = new PermissionModel();
    }

    public function getPermissions($params)
    {
        $idModule = $params['idModule'] ?? null;
        $namaPermission = $params['searchPermission'] ?? null;

        $permissions = $this->permissionModel;

        if ($idModule) {
            $permissions = $permissions->where('module_id', $idModule);
        }
        if ($namaPermission) {
            $permissions = $permissions->like('action_key', $namaPermission);
        }
        $result =  $this->permissionModel->findAll();

        return ['status' => count($result) > 0, 'data' => $result];
    }

    public function create($params)
    {
        $this->permissionModel->insert($params);
        $isSuccess = $this->permissionModel->getInsertID();
        return ['status' => isset($isSuccess), 'data' => $isSuccess];
    }

    public function update($id, $params)
    {
        $this->permissionModel->update($id, $params);
        return ['status' => true];
    }
}
