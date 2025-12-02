<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateSidangAdminsTable extends Migration
{
    public function up()
    {
        // 1. Definisikan Kolom-kolom Tabel
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'nip' => [
                'type'           => 'VARCHAR',
                'constraint'     => '18',
                'unique'         => true, // NIP harus unik
            ],
            'nama_pegawai' => [
                'type'           => 'VARCHAR',
                'constraint'     => '255',
            ],
            'sidang_2fa_secret' => [ // Kolom KUNCI untuk Secret Key TOTP
                'type'           => 'VARCHAR',
                'constraint'     => '100',
                'null'           => true, // Bisa null jika NIP belum di-setup
            ],
            'is_active' => [
                'type'           => 'TINYINT',
                'constraint'     => 1,
                'default'        => 0, // Default 0 (belum aktif)
            ],
            'created_at' => [
                'type'           => 'DATETIME',
                'null'           => true,
            ],
            'updated_at' => [
                'type'           => 'DATETIME',
                'null'           => true,
            ],
        ]);

        // 2. Tentukan Primary Key
        $this->forge->addKey('id', true); 
        // 3. Buat Tabel
        $this->forge->createTable('sidang_admins');
    }

    public function down()
    {
        // Untuk rollback (menghapus tabel)
        $this->forge->dropTable('sidang_admins');
    }
}