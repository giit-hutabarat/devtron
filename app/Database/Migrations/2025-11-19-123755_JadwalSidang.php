<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class JadwalSidang extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'tanggal_sidang' => [
                'type'       => 'DATE',
            ],
            'nomor_perkara' => [
                'type'       => 'VARCHAR',
                'constraint' => '100',
            ],
            'nama_terdakwa' => [
                'type'       => 'VARCHAR',
                'constraint' => '255',
            ],
            'jpu' => [
                'type'       => 'VARCHAR',
                'constraint' => '255',
            ],
            'data_full' => [ // Kita simpan biodata lengkap (alamat, dll) dalam format JSON
                'type' => 'TEXT',
                'null' => true,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('jadwal_sidang');
    }

    public function down()
    {
        $this->forge->dropTable('jadwal_sidang');
    }
}