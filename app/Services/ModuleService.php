<?php

namespace App\Services;

use App\Models\ModuleModel;

class ModuleService
{
    protected $moduleModel;
    public function __construct()
    {
        $this->moduleModel = new ModuleModel();
    }

    public function getModules($params)
    {
        $idFeature = $params['idFeature'] ?? null;
        $namaModule = $params['searchModule'] ?? null;
        $modules =  $this->moduleModel;
        if ($idFeature) {
            $modules = $modules->where('feature_id', $idFeature);
        }
        if ($namaModule) {
            $modules = $modules->like('key', $namaModule);
        }
        $result = $modules->findAll();
        return ['status' => count($result) > 0, 'data' => $result];
    }

    public function create($data)
    {
        $this->moduleModel->insert($data);
        $isSuccess = $this->moduleModel->getInsertID();
        return ['status' => isset($isSuccess), 'data' => $isSuccess];
    }

    public function update($id, $data)
    {
        $this->moduleModel->update($id, $data);
        return ['status' => true];
    }
}
