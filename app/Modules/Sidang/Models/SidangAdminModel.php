<?php

namespace App\Modules\Sidang\Models;

use CodeIgniter\Model;

class SidangAdminModel extends Model
{
    // Tabel khusus untuk menyimpan data otentikasi NIP akses Sidang
    protected $table = 'sidang_admins'; 
    protected $primaryKey = 'id';
    
    // Kolom yang diizinkan untuk diisi
    protected $allowedFields = [
        'nip',                      // Nomor Induk Pegawai (Identifier unik)
        'nama_pegawai',             // Nama Pegawai (untuk referensi)
        'sidang_2fa_secret',        // Secret Key TOTP (PENTING)
        'is_active',                // Flag apakah akses TOTP NIP ini aktif (0/1)
    ];

    protected $useTimestamps = true;
    protected $updatedField  = 'updated_at';
    protected $createdField  = 'created_at';

    /**
     * Cari admin sidang berdasarkan NIP
     * * @param string $nip
     * @return array|null
     */
    public function findByNip(string $nip)
    {
        return $this->where('nip', $nip)->first();
    }
}