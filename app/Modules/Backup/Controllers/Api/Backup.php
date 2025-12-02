<?php

namespace App\Modules\Backup\Controllers\Api;
/*
IT Shop Purwokerto (Tokopedia, Shopee & Bukalapak)
Dibuat oleh: Hari Wicaksono, S.Kom
11-2021
*/

use App\Controllers\BaseControllerApi;
use App\Modules\Backup\Models\BackupModel;
use CodeIgniter\HTTP\ResponseInterface;
use Y0lk\SQLDumper\SQLDumper;
use Config\Database;

class Backup extends BaseControllerApi
{
    protected $format       = 'json';
    protected $modelName    = BackupModel::class;

    public function __construct()
    {
        // 🔒 Otorisasi Level Super Admin
        if (session()->get('user_type') != 1) {
            $response = service('response');
            $response->setStatusCode(ResponseInterface::HTTP_FORBIDDEN)
                     ->setJSON(['status' => false, 'message' => 'Akses Ditolak. Hanya Super Admin yang dapat mengelola pencadangan database.']);
            $response->send();
            exit(); 
        }
    }

    public function index()
    {
        return $this->respond(['status' => true, 'message' => lang('App.getSuccess'), 'data' => $this->model->findAll()], 200);
    }

    public function create()
    {
        // 🚨 PERBAIKAN KRITIS UNTUK SQL DUMPER (FIX: Access denied for user '')
        
        // 1. Dapatkan objek konfigurasi mentah (Config\Database)
        $dbConfigObj = config('Database'); 
        
        // 2. Akses properti default koneksi
        $db = $dbConfigObj->default;
        
        // 3. Simpan kredensial ke array (menggunakan array konfigurasi langsung, bukan properti koneksi)
        $db_config = [
            'hostname' => $db['hostname'],
            'username' => $db['username'],
            'password' => $db['password'],
            'database' => $db['database'],
            'port'     => $db['port'] ?? 3306,
        ];
        
        // 🚨 DEBUGGING LOG BARU 🚨
        log_message('critical', 'DEBUG: DB Hostname: ' . $db_config['hostname']);
        log_message('critical', 'DEBUG: DB Username: ' . $db_config['username']);
        
        $tanggal = date('Ymd-His');
        
        // Gunakan WRITEPATH untuk directory yang aman (memiliki izin tulis/write)
        $backupDir = WRITEPATH . 'backups' . DIRECTORY_SEPARATOR; 
        
        // Pastikan folder backups ada
        if (! is_dir($backupDir)) {
            mkdir($backupDir, 0777, true);
        }

        $namaFile = 'backup-' . $tanggal . '.sql';
        $pathFile = 'backups/'; // Path relatif untuk disimpan di DB

        try {
            // 4. Inisialisasi SQL Dumper dengan Argumen Posisi (FIX: array given error)
            $dumper = new SQLDumper(
                $db_config['hostname'],
                $db_config['username'],
                $db_config['password'],
                $db_config['database']
            );

            // 5. Konfigurasi Dump
            $dumper->allTables()
                ->withData(true)
                ->withDrop(true);

            // 6. Simpan File ke Disk (Menggunakan path absolut)
            $dumper->save($backupDir . $namaFile); // Simpan ke writable/backups/

            // 7. Log ke Tabel Database
            $data = [
                'file_name' => $namaFile,
                'file_path' => $pathFile . $namaFile, // Simpan path relatif untuk di-download
                'created_at' => date('Y-m-d H:i:s')
            ];
            $this->model->save($data);

            $response = [
                'status' => true,
                'message' => lang('App.saveSuccess'),
                'data' => [],
            ];
            return $this->respond($response, 200);

        } catch (\Exception $e) {
            // Log error ke file log CI4 untuk debugging
            log_message('critical', 'Gagal membuat backup. SQL Dumper Error: ' . $e->getMessage());
            
            $response = [
                'status' => false,
                'message' => 'Gagal membuat backup. Error: ' . $e->getMessage(),
                'data' => [],
            ];
            // Kembalikan status 500 agar Axios tahu ada masalah server
            return $this->respond($response, 500); 
        }
    }
    public function delete($id = null)
    {
        $hapus = $this->model->find($id);
        if ($hapus) {
            $filepath = WRITEPATH . $hapus['file_path']; // Gunakan WRITEPATH saat menghapus
            
			unlink($filepath);
            $this->model->delete($id);
            $response = [
                'status' => true,
                'message' => lang('App.delSuccess'),
                'data' => [],
            ];
            return $this->respond($response, 200);
        } else {
           $response = [
                'status' => false,
                'message' => lang('App.delFailed'),
                'data' => [],
            ];
            return $this->respond($response, 200);
        }
    }

    public function download()
    {
        // Endpoint ini harusnya mengembalikan file, tapi karena API, 
        // kita mengembalikan URL file yang akan diakses oleh frontend.
        
        // Menggunakan getRequestInput() dari BaseControllerApi untuk konsistensi
        $input = $this->getRequestInput();
        $id = $input['id'] ?? $this->request->getPost('id'); // Ambil dari input atau POST

        $backup = $this->model->find($id);
        
        if (!$backup) {
            return $this->respond(['status' => false, 'message' => 'File backup tidak ditemukan.'], 404);
        }

        $name = $backup['file_name'];
        $path = $backup['file_path'];
        // 🔑 Perbaikan path download: base_url() + path relatif
        $filePath = base_url($path); 

        $response = [
            'status' => true,
            'message' => lang('App.getSuccess'),
            'data' => ['filename' => $name, 'url' => $filePath],
        ];
        return $this->respond($response, 200);
    }
}