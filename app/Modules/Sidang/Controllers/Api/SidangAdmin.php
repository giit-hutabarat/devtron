<?php

namespace App\Modules\Sidang\Controllers\Api;

use App\Controllers\BaseControllerApi;
use App\Modules\Sidang\Models\SidangAdminModel;
use CodeIgniter\HTTP\ResponseInterface;

// NOTE: Karena ini API, kita tidak perlu memanggil library QR Code di sini.
// Logic QR Code generation tetap di Controller WEB (AdminSetupController).

class SidangAdmin extends BaseControllerApi
{
    protected $format       = 'json';
    protected $modelName    = SidangAdminModel::class;

    public function __construct()
    {
        // Pengecekan Otorisasi (dianggap sudah ditangani oleh filter di Routes)
    }

    public function index()
    {
        // Endpoint: GET /api/sidang/admins
        // Mengambil semua data admin
        try {
            $data = $this->model->findAll();
            return $this->respond([
                "status" => true, 
                "message" => "Daftar Admin Sidang berhasil dimuat.", 
                "data" => $data
            ], 200);
        } catch (\Throwable $e) {
            return $this->failServerError('Gagal memuat data: ' . $e->getMessage());
        }
    }

    public function save()
    {
        // Endpoint: POST /api/sidang/admins/save
        $input = $this->getRequestInput();

        // Ambil data untuk disimpan
        $data = [
            'nip' => $input['nip'] ?? null,
            'nama_pegawai' => $input['nama_pegawai'] ?? null,
            'sidang_2fa_secret' => null, // Default secret kosong saat pertama kali daftar
            'is_active' => 0
        ];
        
        // Validasi dan Simpan
        if (!$this->validate(['nip' => 'required|is_unique[sidang_admins.nip]', 'nama_pegawai' => 'required'])) {
            return $this->respond([
                'status' => false,
                'message' => 'Validasi gagal',
                'data' => $this->validator->getErrors(),
            ], 200);
        }

        try {
            $this->model->save($data);
            return $this->respond([
                'status' => true,
                'message' => 'NIP Pegawai berhasil ditambahkan. Silakan Generate QR Code.',
            ], 200);
        } catch (\Throwable $e) {
            return $this->failServerError('Gagal menyimpan NIP: ' . $e->getMessage());
        }
    }
    
    public function toggle($id = null)
    {
        // Endpoint: PUT /api/sidang/admins/toggle/(:segment)
        $input = $this->getRequestInput();
        $admin = $this->model->find($id);

        if (!$admin) {
            return $this->failNotFound('Admin tidak ditemukan.');
        }

        $data = [
            'is_active' => $input['is_active'] ?? $admin['is_active']
        ];
        
        // Cek jika toggle ke ON (aktif) tapi secret key kosong
        if ($data['is_active'] == 1 && empty($admin['sidang_2fa_secret'])) {
             return $this->respond([
                'status' => false,
                'message' => 'Gagal mengaktifkan. Secret Key belum dibuat. Silakan Generate QR Code terlebih dahulu.',
            ], 200);
        }


        try {
            $this->model->update($id, $data);
            $message = ($data['is_active'] == 1) ? '2FA berhasil diaktifkan.' : '2FA berhasil dinonaktifkan.';
            
            return $this->respond([
                'status' => true,
                'message' => $message,
            ], 200);
        } catch (\Throwable $e) {
            return $this->failServerError('Gagal toggle status: ' . $e->getMessage());
        }
    }
}