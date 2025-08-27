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

    public function getPengguna($params)
    {
        // pagination
        $perPage = isset($params['limit']) ? (int)$params['limit'] : (int)getenv('DEFAULT_PAGINATE_LIMIT');
        $page    = isset($params['page'])  ? (int)$params['page']  : 1;
        $offset  = ($page - 1) * $perPage;

        // === BASE (tanpa select/order/group) ===
        $base = $this->penggunaModel->builder();         // pastikan model table= penguna (schema + alias jika perlu)
        // $base->from('lentera.pengguna pengguna');     // pakai ini kalau model tidak set table+alias

        $base->join('lentera.user_roles ur', 'ur.pengguna_id = pengguna.ID', 'left');
        $base->join('lentera.roles r',       'r.id = ur.role_id',          'left');
        $base->where('pengguna.STATUS !=', 0);

        // search
        if (!empty($params['search'])) {
            $term = trim($params['search']);

            $db = \Config\Database::connect();

            $likeNamaLengkap = $db->escape('%' . $db->escapeLikeString($term) . '%');

            $base->groupStart()
                ->like('pengguna.LOGIN', $term, 'both')
                ->orWhere("master.getNamaLengkapPegawai(pengguna.NIP) LIKE {$likeNamaLengkap}", null, false)
                ->groupEnd();
        }


        // === COUNT DISTINCT pengguna ===
        $count = clone $base;
        $total = (int) $count
            ->select('COUNT(DISTINCT pengguna.ID) AS total', false)
            ->get()->getRow('total');

        // === DATA LIST ===
        $list = clone $base;
        $list->select(
            'pengguna.ID AS idPengguna,
         pengguna.LOGIN AS userName,
         master.getNamaLengkapPegawai(pengguna.NIP) AS namaLengkap,
         (CASE WHEN pengguna.STATUS = 1 THEN "Aktif" ELSE "Tidak Aktif" END) AS status',
            false
        );
        $list->select("GROUP_CONCAT(DISTINCT r.nama_role ORDER BY r.nama_role SEPARATOR '||') AS roles_concat", false);
        $list->groupBy('pengguna.ID')->orderBy('pengguna.ID', 'ASC');

        $rows = $list->limit($perPage, $offset)->get()->getResultArray();

        if (empty($rows)) {
            return ['status' => false];
        }

        // parse roles
        $data = array_map(function ($row) {
            $row['roles'] = !empty($row['roles_concat'])
                ? array_values(array_filter(array_map('trim', explode('||', $row['roles_concat']))))
                : [];
            unset($row['roles_concat']);
            return $row;
        }, $rows);

        return [
            'status' => true,
            'data' => [
                'list'  => $data,
                'total' => $total,
                'page'  => $page,
                'limit' => $perPage,
            ],
        ];
    }



    public function getRolesWithPermissions(int $userId): array
    {
        $builder = $this->db->table('user_roles ur')
            ->select("
            r.id  AS role_id,
            r.nama_role,
            GROUP_CONCAT(
            DISTINCT TRIM(CONCAT(TRIM(f.`key`), '.', TRIM(m.`key`), '.', TRIM(p.action_key)))
            ORDER BY f.`key`, m.`key`, p.action_key
            SEPARATOR ','
            ) AS permissions
        ")
            ->join('roles r', 'r.id = ur.role_id', 'inner')
            ->join('role_permissions rp', 'rp.role_id = r.id', 'left')
            ->join('permissions p', 'p.id = rp.permission_id AND p.status = 1', 'left')
            ->join('modules m', 'm.id = p.module_id AND m.status = 1', 'left')
            ->join('features f', 'f.id = m.feature_id AND f.status = 1', 'left')
            ->where('ur.pengguna_id', $userId)
            ->where('r.status', 1)
            ->groupBy('r.id, r.nama_role');

        $rows = $builder->get()->getResultArray();

        $out = [];
        foreach ($rows as $row) {
            $perms = $row['permissions']
                ? array_values(array_filter(array_map('trim', explode(',', $row['permissions']))))
                : [];
            $out[] = [
                'id'          => (int)$row['role_id'],
                'name'        => $row['nama_role'],
                'permissions' => $perms,
            ];
        }
        return $out;
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
            'username' => $user['LOGIN'],
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
