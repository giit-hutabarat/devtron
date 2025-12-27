<?php

namespace App\Modules\Sidang\Controllers\Api;

use App\Controllers\BaseControllerApi;
use App\Modules\Sidang\Models\SidangAdminModel;
use CodeIgniter\HTTP\ResponseInterface;
use Config\Services; // Import Services untuk Throttler

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
        try {
            $data = $this->model->orderBy('updated_at', 'DESC')->findAll();
            return $this->respond([
                "status" => true, 
                "message" => "Daftar Admin Sidang berhasil dimuat.", 
                "data" => $data
            ], 200);
        } catch (\Throwable $e) {
            return $this->failServerError('Gagal memuat data: ' . $e->getMessage());
        }
    }

    /**
     * SAVE ADMIN (CREATE ONLY) - HIGH SECURITY
     * Fitur: Anti-Spam, XSS Cleaning, Strict Validation
     */
    public function save()
{
    // 1. SECURITY: Rate Limiting
    $throttler = \Config\Services::throttler();
    $ipAddress = $this->request->getIPAddress();
    
    if ($throttler->check(md5($ipAddress . 'save_admin'), 5, 60) === false) {
        return $this->respond([
            'status' => false,
            'message' => 'Terlalu banyak percobaan. Tunggu 1 menit lagi.',
            'csrf_hash' => csrf_hash(),
        ], 429);
    }

    $input = $this->getRequestInput();
    
    // 2. SECURITY: Sanitasi
    $namaClean = strip_tags(trim($input['nama_pegawai'] ?? ''));
    $nipClean = preg_replace('/[^0-9]/', '', $input['nip'] ?? '');

    // 3. Validasi
    $rules = [
        'nip' => [
            'rules' => 'required|numeric|exact_length[18]',
            'errors' => ['exact_length' => 'NIP harus 18 digit.']
        ],
        'nama_pegawai' => ['rules' => 'required|min_length[3]|max_length[100]']
    ];

    $validationData = ['nip' => $nipClean, 'nama_pegawai' => $namaClean];

    if (!$this->validateData($validationData, $rules)) {
        return $this->respond([
            'status' => false,
            'message' => 'Validasi gagal: ' . implode(', ', $this->validator->getErrors()),
            'csrf_hash' => csrf_hash(),
        ], 400);
    }

    try {
        // 4. Cek Duplikasi
        $existingUser = $this->model->where('nip', $nipClean)->first();
        if ($existingUser) {
            return $this->respond([
                'status' => false,
                'message' => 'Gagal: NIP Pegawai sudah terdaftar.',
                'csrf_hash' => csrf_hash(),
            ], 400);
        }

        // 5. EKSEKUSI INSERT (Hanya Sekali)
        $inserted = $this->model->insert([
            'nip'               => $nipClean,
            'nama_pegawai'      => strtoupper($namaClean),
            'sidang_2fa_secret' => null, 
            'is_active'         => 0
        ]);

        if ($inserted) {
            log_message('info', "Admin Sidang Baru [$nipClean] ditambahkan oleh IP: $ipAddress");
            return $this->respond([
                'status' => true,
                'message' => 'NIP Pegawai berhasil ditambahkan. Silakan Generate QR Code.',
                'csrf_hash' => csrf_hash(), // Sinkronisasi untuk Interceptor JS
            ], 200);
        }

    } catch (\Throwable $e) {
        return $this->respond([
            'status' => false,
            'message' => 'Gagal sistem: ' . $e->getMessage(),
            'csrf_hash' => csrf_hash(),
        ], 500);
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
        
        if ($data['is_active'] == 1 && empty($admin['sidang_2fa_secret'])) {
             return $this->respond([
                'status' => false,
                'message' => 'Gagal mengaktifkan. Secret Key belum dibuat. Silakan Generate QR Code terlebih dahulu.',
            ], ResponseInterface::HTTP_PRECONDITION_FAILED);
        }

        try {
            $this->model->update($id, $data);
            $message = ($data['is_active'] == 1) ? '2FA berhasil diaktifkan.' : '2FA berhasil dinonaktifkan.';
            
            return $this->respond([
                'status' => true,
                'message' => $message,
            ], ResponseInterface::HTTP_OK);
        } catch (\Throwable $e) {
            return $this->failServerError('Gagal toggle status: ' . $e->getMessage());
        }
    }

    public function delete($id = null)
    {
        // Endpoint: DELETE /api/sidang/admins/delete/(:segment)
        if (!$this->model->find($id)) {
            return $this->failNotFound('Admin tidak ditemukan.');
        }

        try {
            $this->model->delete($id);
            return $this->respond([
                'status' => true,
                'message' => 'NIP Pegawai berhasil dihapus permanen.',
            ], ResponseInterface::HTTP_OK);
        } catch (\Throwable $e) {
            return $this->failServerError('Gagal menghapus NIP: ' . $e->getMessage());
        }
    }
}