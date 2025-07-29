<?php

namespace App\Services;

use App\Models\PenggunaModel;
use Config\Database;

class PenggunaService
{
    protected $penggunaModel;
    protected $db;

    public function __construct()
    {
        $this->penggunaModel = new PenggunaModel();
        $this->db = Database::connect();
    }

    public function getData($params)
    {

        // Select
        if (isset($params['select'])) {
            $this->penggunaModel->select($params['select']);
        }

        // Join
        if (isset($params['join']) && is_array($params['join'])) {
            foreach ($params['join'] as $join) {
                $table = $join['table'] ?? null;
                $condition = $join['condition'] ?? null;
                $type = $join['type'] ?? '';

                if ($table && $condition) {
                    $this->penggunaModel->join($table, $condition, $type);
                }
            }
        }

        // Where
        if (isset($params['where'])) {
            $this->penggunaModel->where($params['where']);
        }

        // Like
        if (isset($params['like'])) {
            foreach ($params['like'] as $key => $value) {
                $this->penggunaModel->like($key, $value);
            }
        }

        // Order By
        if (isset($params['orderBy'])) {
            foreach ($params['orderBy'] as $col => $dir) {
                $this->penggunaModel->orderBy($col, $dir);
            }
        }

        // Pagination
        if (!empty($params['paginate'])) {
            $perPage = $params['perPage'] ?? getenv('DEFAULT_PAGINATE_LIMIT');
            $group = $params['pagerGroup'] ?? 'default';

            $result = $this->penggunaModel->paginate($perPage, $group);
            $pager  = $this->penggunaModel->pager;

            return [
                'data' => $result,
                'pager' => $pager,
                'page' => [
                    'current' => $pager->getCurrentPage($group),
                    'perPage' => $pager->getPerPage($group),
                    'total' => $pager->getTotal($group),
                    'pageCount' => $pager->getPageCount($group),
                ],
            ];
        }

        // Limit
        if (isset($params['limit'])) {
            $this->penggunaModel->limit($params['limit']);
        }

        return $this->penggunaModel->findAll();
    }

    public function getRolesWithPermissions(int $userId): array
    {
        $builder = $this->db->table('user_roles ur')
            ->select('r.id role_id, r.nama_role, p.id AS permission_id, p.nama_permission')
            ->join('roles r', 'r.id = ur.role_id')
            ->join('role_permissions rp', 'rp.role_id = r.id', 'left')
            ->join('permissions p', 'p.id = rp.permission_id', 'left')
            ->where('ur.pengguna_id', $userId)
            ->where('r.status', 1)
            ->where('p.status', 1)
            ->orderBy('r.nama_role')
            ->orderBy('p.nama_permission');

        $results = $builder->get()->getResultArray();

        $roles = [];

        foreach ($results as $row) {
            $roleId = $row['role_id'];

            if (!isset($roles[$roleId])) {
                $roles[$roleId] = [
                    'id' => $roleId,
                    'name' => $row['nama_role'],
                    'permissions' => [],
                ];
            }

            if (!empty($row['permission_id'])) {
                $roles[$roleId]['permissions'][] = $row['nama_permission'];
            }
        }

        return array_values($roles);
    }

    public function validasiPengguna($username, $password)
    {
        $privateKey = env('PRIVATE_KEY');
        $hash = hash_hmac("sha256", $password, hash("sha256", $privateKey));
        $isUserExist = $this->penggunaModel->where('LOGIN', $username)->where('STATUS', 1)->first();
        if ($isUserExist) {
            if (password_verify($hash, $isUserExist['PASSWORD'])) {
                return $isUserExist;
            }
        } else {
            return false;
        }
    }

    public function getDataUser($user)
    {
        $dataUser = [
            'id' => $user['ID'],
            'namaLengkap' => "",
            'NIP' => $user['NIP'] ?? null,
        ];
        if (isset($user['NIP'])) {
            $resultNamaPegawai = $this->penggunaModel->select('master.getNamaLengkapPegawai("' . $user['NIP'] . '") as namaLengkapPegawai')->first()['namaLengkapPegawai'];
            $dataUser['namaLengkap'] = $resultNamaPegawai;
        }
        return $dataUser;
    }
}
