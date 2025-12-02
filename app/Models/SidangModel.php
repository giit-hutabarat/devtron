<?php

namespace App\Models;

use CodeIgniter\Model;

class SidangModel extends Model
{
    protected $table            = 'jadwal_sidang';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $allowedFields    = ['tanggal_sidang', 'nomor_perkara', 'nama_terdakwa', 'jpu', 'data_full'];
    protected $useTimestamps    = true;
}