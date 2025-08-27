<?php

namespace App\Modules\AntreanOnline\Services;

use App\Modules\AntreanOnline\Models\StatusPendaftaranModel;

class StatusPendaftaranService
{
    protected $statusPendaftaranModel;

    public function __construct()
    {
        $this->statusPendaftaranModel = new StatusPendaftaranModel();
    }

    public function updateStatusPendaftaran($params)
    {
        $tanggal = $params['tanggal'];
        $kodeDokter = $params['kodeDokter'];
        $status = $params['status'];
        $user = $params['user'];

        $data = [
            'TANGGAL' => $tanggal,
            'DOKTER' => $kodeDokter,
            'STATUS' => $status,
            'OLEH' => $user,
        ];

        $builder = $this->statusPendaftaranModel;
        $isExist = $builder->where('TANGGAL', $tanggal)->where('DOKTER', $kodeDokter)->first();
        if ($isExist) {
            $builder->update($isExist['ID'], $data);
        } else {
            $builder->insert($data);
        }

        return ['status' => true, 'message' => 'Berhasil update status pendaftaran'];
    }
}
