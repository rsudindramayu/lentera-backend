<?php

namespace App\Services;

use App\Models\RoleModel;

class RoleService
{
    protected $roleModel;

    public function __construct()
    {
        $this->roleModel = new RoleModel();
    }

    public function getRoles($params)
    {
        // pagination
        $perPage = isset($params['limit']) ? (int)$params['limit'] : (int)getenv('DEFAULT_PAGINATE_LIMIT');
        $page    = isset($params['page'])  ? (int)$params['page']  : 1;
        $offset  = ($page - 1) * $perPage;

        //search value
        $search = isset($params['search']) ? $params['search'] : null;

        $builder = $this->roleModel;

        $builder->select('*');
        $builder->where('status', 1);
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

    public function createRole($data)
    {
        $this->roleModel->insert($data);
        $isSuccess = $this->roleModel->getInsertID();
        return ['status' => isset($isSuccess), 'data' => $isSuccess];
    }

    public function updateRole($id, $data)
    {
        $role = $this->roleModel->where('id', $id)->find();
        if (!$role) return ['status' => false];
        $this->roleModel->update($id, $data);
        return ['status' => true];
    }

    public function deleteRole($id)
    {
        $role = $this->roleModel->where('id', $id)->find();
        if (!$role) return ['status' => false];
        $this->roleModel->delete($id);
        return ['status' => true];
    }
}
