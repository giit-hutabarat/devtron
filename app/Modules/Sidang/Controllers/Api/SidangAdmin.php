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
        // 1. SECURITY: Rate Limiting (Throttler)
        // Batasi: Maksimal 5 request per 60 detik per IP Address
        $throttler = Services::throttler();
        $ipAddress = $this->request->getIPAddress();
        
        if ($throttler->check(md5($ipAddress . 'save_admin'), 5, 60) === false) {
            return $this->respond([
                'status' => false,
                'message' => 'Terlalu banyak percobaan. Tunggu 1 menit lagi.',
            ], 429); // HTTP 429 Too Many Requests
        }

        // Endpoint: POST /api/admins/save
        $input = $this->getRequestInput();
        
        // 2. SECURITY: Sanitasi Input (Cegah XSS)
        // Hapus tag HTML berbahaya dari Nama Pegawai
        $namaRaw = $input['nama_pegawai'] ?? '';
        $namaClean = strip_tags(trim($namaRaw));
        
        // Pastikan NIP hanya angka (menghapus spasi/huruf iseng)
        $nipRaw = $input['nip'] ?? '';
        $nipClean = preg_replace('/[^0-9]/', '', $nipRaw);

        // 3. Validasi Ketat
        // NIP harus numeric dan tepat 18 digit (Standar NIP)
        $rules = [
            'nip' => [
                'label' => 'NIP',
                'rules' => 'required|numeric|exact_length[18]',
                'errors' => [
                    'numeric' => 'NIP harus berupa angka.',
                    'exact_length' => 'NIP harus berjumlah tepat 18 digit.'
                ]
            ],
            'nama_pegawai' => [
                'label' => 'Nama Pegawai',
                'rules' => 'required|min_length[3]|max_length[100]|string',
                'errors' => [
                    'string' => 'Nama mengandung karakter tidak valid.'
                ]
            ]
        ];

        // Override input data untuk validasi dengan data yang sudah dibersihkan
        $validationData = [
            'nip' => $nipClean,
            'nama_pegawai' => $namaClean
        ];

        if (!$this->validateData($validationData, $rules)) {
            return $this->respond([
                'status' => false,
                'message' => 'Validasi gagal: ' . implode(', ', $this->validator->getErrors()),
                'data' => $this->validator->getErrors(),
            ], ResponseInterface::HTTP_BAD_REQUEST);
        }

        try {
            // 4. Cek Duplikasi (Strict Create Mode)
            $existingUser = $this->model->where('nip', $nipClean)->first();

            if ($existingUser) {
                // SECURITY LOG: Mencatat percobaan duplikasi
                log_message('warning', "Percobaan daftar NIP duplikat [$nipClean] dari IP: $ipAddress");
                
                return $this->respond([
                    'status' => false,
                    'message' => 'Gagal: NIP Pegawai sudah terdaftar.',
                ], ResponseInterface::HTTP_BAD_REQUEST);
            }

            // --- INSERT DATA BARU ---
            $this->model->insert([
                'nip' => $nipClean,
                'nama_pegawai' => strtoupper($namaClean), // Standarisasi Huruf Besar
                'sidang_2fa_secret' => null, 
                'is_active' => 0
            ]);

            // SECURITY LOG: Mencatat sukses
            log_message('info', "Admin Sidang Baru [$nipClean] ditambahkan oleh IP: $ipAddress");

            return $this->respond([
                'status' => true,
                'message' => 'NIP Pegawai berhasil ditambahkan. Silakan Generate QR Code.',
            ], 200);

        } catch (\Throwable $e) {
            return $this->failServerError('Gagal menyimpan data: ' . $e->getMessage());
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