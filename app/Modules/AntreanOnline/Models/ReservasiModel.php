<?php

namespace App\Modules\AntreanOnline\Models;

use CodeIgniter\Model;

class ReservasiModel extends Model
{
    protected $table            = 'reservasi';
    protected $primaryKey       = 'ID';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $DBGroup          = 'aplikasi';
    protected $allowedFields    = [
        'STATUS'
    ];
}
