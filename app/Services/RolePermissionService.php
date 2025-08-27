<?php

namespace App\Services;

use App\Models\RolePermissionModel;

class RolePermissionService
{
    protected $rolePermissionModel;
    public function __construct()
    {
        $this->rolePermissionModel = new RolePermissionModel();
    }

    public function getPermissionByRoleId($roleId)
    {
        //search value
        $builder = $this->rolePermissionModel;
        $builder->select('p.*');
        $builder->join('permissions p', 'p.id = role_permissions.permission_id', 'left');
        $builder->where('p.status', 1)->where('role_id', $roleId);

        $data = $builder->findAll();
        if (count($data) < 1) return ['status' => false];
        return [
            'status' => true,
            'data' => [
                'list' => $data,
            ]
        ];
    }

    public function syncRolePermissions($params)
    {
        $roleId = (int) $params['role_id'];
        $permissionIds = array_values(array_filter((array) ($params['permission_ids'] ?? []), 'is_numeric'));
        $this->rolePermissionModel->where('role_id', $roleId)->delete();
        foreach ($permissionIds as $permissionId) {
            $this->rolePermissionModel->insert(['role_id' => $roleId, 'permission_id' => $permissionId]);
        }
        return ['status' => true];
    }
}
