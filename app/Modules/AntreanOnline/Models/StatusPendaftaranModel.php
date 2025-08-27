<?php

namespace App\Modules\AntreanOnline\Models;

use CodeIgniter\Model;

class StatusPendaftaranModel extends Model
{
    protected $table            = 'lentera-antrol.status_pendaftaran';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'TANGGAL',
        'DOKTER',
        'STATUS',
        'OLEH',
        'CREATED_AT',
        'UPDATED_AT'
    ];

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'CREATED_AT';
    protected $updatedField  = 'UPDATED_AT';
    protected $DBGroup       = 'replikasi';
}