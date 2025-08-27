<?php

namespace App\Modules\PenjaminRs\Models;

use CodeIgniter\Model;

class DPJPModel extends Model
{
    protected $table            = 'penjamin_rs.dpjp';
    protected $primaryKey       = 'ID';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [];
}
