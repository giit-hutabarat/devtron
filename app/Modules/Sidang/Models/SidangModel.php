<?php

namespace App\Modules\Sidang\Models;

use CodeIgniter\Model;

class SidangModel extends Model
{
    protected $table = 'jadwal_sidang'; // Ganti sesuai nama tabel Anda
    protected $primaryKey = 'id';
     protected $useAutoIncrement = true;
    
    protected $allowedFields = [
        'tanggal_sidang', 
        'nomor_perkara',
        'nama_asli', 
        'nama_terdakwa', 
        'jpu', 
        'data_full',
    ];

    protected $useTimestamps = true; // Jika Anda menggunakan created_at/updated_at
}