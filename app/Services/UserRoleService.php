<?php

namespace App\Services;

use App\Models\UserRoleModel;

class UserRoleService
{
    protected $userRoleModel;

    public function __construct()
    {
        $this->userRoleModel = new UserRoleModel();
    }

    public function getRoleByUserId($params)
    {
        // pagination
        $perPage = isset($params['limit']) ? (int)$params['limit'] : (int)getenv('DEFAULT_PAGINATE_LIMIT');
        $page    = isset($params['page'])  ? (int)$params['page']  : 1;
        $offset  = ($page - 1) * $perPage;

        //search value
        $search = isset($params['search']) ? $params['search'] : null;
        $userId = $params['user_id'];

        $builder = $this->userRoleModel;

        $builder->select('r.*');
        $builder->join('roles r', 'r.id = user_roles.role_id', 'inner');
        $builder->where('r.status', 1)->where('user_roles.pengguna_id', $userId);
        if ($search) $builder->like('nama_role', $search);

        $total = $builder->countAllResults(false);
        $data = $builder->limit($perPage, $offset)->get()->getResult();
        if (count($data) < 1) return ['status' => false];
        return [
            'status' => true,
            'data' => [
                'list' => $data,
                'total' => $total,
                'page' => $page,
                'limit' => $perPage,
            ]
        ];
    }

    public function synchUserRoles($params)
    {
        $userId = $params['pengguna_id'];
        $roleIds = array_values(array_filter((array) ($params['role_ids'] ?? []), 'is_numeric'));
        $this->userRoleModel->where('pengguna_id', $userId)->delete();
        foreach ($roleIds as $roleId) {
            $this->userRoleModel->insert(['pengguna_id' => $userId, 'role_id' => $roleId]);
        }
        return ['status' => true];
    }
}
