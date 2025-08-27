<?php

namespace App\Modules\PenjaminRs\Services;

use App\Modules\PenjaminRs\Models\DPJPModel;

class DPJPService
{
    protected $dpjpModel;

    public function __construct()
    {
        $this->dpjpModel = new DPJPModel();
    }

    public function searchDPJP($namaDokter)
    {
        $dpjp =  $this->dpjpModel->select('md.ID idDokter, dpjp.DPJP_PENJAMIN idPenjamin, mp.NIP, mp.NAMA namaDokter')
            ->join('master.dokter md', 'md.ID = dpjp.DPJP_RS', 'left')
            ->join('master.pegawai mp', 'mp.NIP = md.NIP', 'left')
            ->where('mp.NAMA LIKE', '%' . $namaDokter . '%')
            ->where('dpjp.STATUS', 1)->where('md.STATUS', 1)->where('mp.STATUS', 1)
            ->findAll();

        if (empty($dpjp)) return ['status' => false];
        return [
            'status' => true,
            'data' => $dpjp
        ];
    }
}
