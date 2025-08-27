<?php

namespace App\Services;

use App\Models\FeatureModel;

class FeatureService
{
    protected $featureModel;

    public function __construct()
    {
        $this->featureModel = new FeatureModel();
    }

    public function getFeatures($params)
    {
        $namaFeature = $params['searchFeature'] ?? null;
        $features = $this->featureModel;
        if ($namaFeature) {
            $features = $features->like('key', $namaFeature);
        }
        $result = $features->orderBy('id', 'ASC')->findAll();
        return ['status' => count($result) > 0, 'data' => $result];
    }

    public function getFeatureWithPermissions(array $params = []): array
    {
        //params : search
        $q               = trim($params['search'] ?? '');
        $limit           = (int)($params['limit'] ?? 0);
        $page            = max(1, (int)($params['page'] ?? 1));
        $offset          = $limit ? ($page - 1) * $limit : 0;
        $includeInactive = (bool)($params['include_inactive'] ?? false);

        $builder = $this->featureModel->db->table('features f');

        // SELECT columns
        $builder->select(
            'f.id AS feature_id, f.`key` AS feature_key, f.deskripsi AS feature_desc,' .
                'm.id AS module_id, m.`key` AS module_key, m.deskripsi AS module_desc,' .
                'p.id AS permission_id, p.action_key, p.deskripsi AS permission_desc'
        );

        // LEFT JOIN + filter status di join ON agar tetap LEFT
        $joinModuleStatus     = $includeInactive ? '' : ' AND m.status = 1';
        $joinPermissionStatus = $includeInactive ? '' : ' AND p.status = 1';

        $builder->join('modules m', 'm.feature_id = f.id' . $joinModuleStatus, 'left');
        $builder->join('permissions p', 'p.module_id = m.id' . $joinPermissionStatus, 'left');

        // Feature aktif saja
        $builder->where('f.status', 1);

        // Pencarian
        if ($q !== '') {
            $builder->groupStart()
                ->like('f.`key`', $q)
                ->orLike('f.deskripsi', $q)
                ->orLike('m.`key`', $q)
                ->orLike('m.deskripsi', $q)
                ->orLike('p.action_key', $q)
                ->orLike('p.deskripsi', $q)
                ->groupEnd();
        }

        // order by
        $builder->orderBy('f.`key`', 'ASC')
            ->orderBy('m.`key`', 'ASC')
            ->orderBy('p.action_key', 'ASC');

        // Paging
        if ($limit > 0) {
            $builder->limit($limit, $offset);
        }

        $rows = $builder->get()->getResultArray();

        // Map flat rows → nested array
        $features = [];
        foreach ($rows as $row) {
            $fid = (int) $row['feature_id'];

            if (!isset($features[$fid])) {
                $features[$fid] = [
                    'id'        => $fid,
                    'key'       => $row['feature_key'],
                    'deskripsi' => $row['feature_desc'],
                    'modules'   => [], // keyed by module_id sementara
                ];
            }

            // Module bisa null kalau belum ada
            $mid = $row['module_id'];
            if ($mid !== null) {
                $mid = (int) $mid;
                if (!isset($features[$fid]['modules'][$mid])) {
                    $features[$fid]['modules'][$mid] = [
                        'id'          => $mid,
                        'key'         => $row['module_key'],
                        'deskripsi'   => $row['module_desc'],
                        'permissions' => [],
                    ];
                }

                // Permission bisa null
                $pid = $row['permission_id'];
                if ($pid !== null) {
                    $features[$fid]['modules'][$mid]['permissions'][] = [
                        'id'           => (int) $pid,
                        'action_key'   => $row['action_key'],
                        'deskripsi' => $row['permission_desc'],
                    ];
                }
            }
        }

        // Ubah modules dari map → array numerik
        $data = [];
        foreach ($features as $f) {
            if (!empty($f['modules'])) {
                $f['modules'] = array_values($f['modules']);
            } else {
                $f['modules'] = [];
            }
            $data[] = $f;
        }

        return ['status' => true, 'data' => ['list' => $data]];
    }

    public function create($data)
    {
        $this->featureModel->insert($data);
        $isSuccess = $this->featureModel->getInsertID();
        return ['status' => isset($isSuccess), 'data' => $isSuccess];
    }

    public function update($id, $data)
    {
        $this->featureModel->update($id, $data);
        return ['status' => true];
    }
}
