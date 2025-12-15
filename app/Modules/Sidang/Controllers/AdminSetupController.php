<?php

namespace App\Modules\Sidang\Controllers;

use App\Controllers\BaseController;
use App\Modules\Sidang\Models\SidangAdminModel;

// Import library yang dibutuhkan
use OTPHP\TOTP; 
use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;


class AdminSetupController extends BaseController
{
    protected $sidangAdminModel;

    public function __construct()
    {
        try {
            $this->sidangAdminModel = new SidangAdminModel();
        } catch (\Throwable $e) {
            // Error handling model/DB
        }
    }
    
    // --- 1. Fungsi index: Menampilkan View ---
    public function index()
    {
        if (!$this->sidangAdminModel) {
            return redirect()->back()->with('error', 'Gagal memuat data administrasi. Cek koneksi database.');
        }

        $data = [
            'title' => 'Manajemen Kunci OTP Pegawai Sidang',
        ];
        
        return view('\App\Modules\Sidang\Views\otp-sidang', $data);
    }
    
    // --- 2. Fungsi saveNip: Handle AJAX POST untuk Tambah/Update NIP ---
    public function saveNip()
    {
        $input = $this->request->getPost();
        $validationRules = [
            'nip' => 'required|min_length[5]',
            'nama_pegawai' => 'required'
        ];
        
        if (empty($input['id'])) {
            $validationRules['nip'] .= '|is_unique[sidang_admins.nip]';
        } else {
            // Update mode: abaikan NIP saat ini
            $validationRules['nip'] .= '|is_unique[sidang_admins.nip,id,' . $input['id'] . ']';
        }

        // KOREKSI: Hanya satu blok validasi, dan harus return JSON untuk AJAX
        if (!$this->validate($validationRules)) {
            return $this->response->setStatusCode(400)->setJSON([
                'status' => 'false',
                'message' => 'Validasi Gagal: ' . $this->validator->listErrors(),
                'errors' => $this->validator->getErrors(),
            ]);
        }
        
        $data = [
            'nip' => $input['nip'],
            'nama_pegawai' => $input['nama_pegawai'],
        ];

        try {
            if (!empty($input['id'])) {
                $this->sidangAdminModel->update($input['id'], $data);
                $message = 'Data NIP berhasil diupdate.';
            } else {
                // Insert Baru
                $data['sidang_2fa_secret'] = null;
                $data['is_active'] = 0;
                $this->sidangAdminModel->insert($data);
                $message = 'Data NIP berhasil ditambahkan. Lakukan "Generate QR Code" untuk mengaktifkan OTP.';
            }

            // Berhasil simpan: Return JSON dan CSRF Hash baru
            return $this->response->setJSON([
                'status' => 'true',
                'message' => $message,
                'csrf_hash' => csrf_hash(), // Wajib kirim hash baru
            ]);

        } catch (\Throwable $e) {
            log_message('error', 'Gagal Menambahkan Pegawai: ' . $e->getMessage());
            return $this->response->setStatusCode(500)->setJSON([
                'status' => 'false',
                'message' => 'Gagal menyimpan data: ' . $e->getMessage(),
            ]);
        }
    }
    
    // --- 3. Fungsi generateQr: Handle AJAX untuk Generate/Tampilkan QR Code ---
    public function generateQr(int $id)
    {
        $adminUser = $this->sidangAdminModel->find($id);
        if (!$adminUser) {
            return $this->response->setStatusCode(404)->setJSON(['status' => 'false', 'message' => 'NIP tidak ditemukan.']);
        }
        
        $secretKey = $adminUser['sidang_2fa_secret'];
        $currentCount = $adminUser['qr_regenerate_count'] ?? 0;
        $MAX_COUNT = 5;
        $isRegenerate = false; 

        try{
            // 2. Cek Batas Maksimal Regenerasi (5x)
            if ($currentCount >= $MAX_COUNT && !empty($secretKey)) {
                return $this->response->setStatusCode(429)->setJSON([
                    'status' => 'false',
                    'message' => "Batas maksimal generate QR Code ({$MAX_COUNT}) telah tercapai. Harap hapus NIP dan daftarkan ulang.",
                ]);
            }
            
            // 3. Logika Generate Key Baru: HANYA jika (key kosong) ATAU (status nonaktif)
            $isGenerateNewKey = empty($secretKey) || $adminUser['is_active'] == 0; 
            
            if ($isGenerateNewKey) {
                $totp = TOTP::generate();
                $secretKey = $totp->getSecret(); // Key baru
                $isRegenerate = true;
            } 

            // 4. Update DB HANYA jika ada Regenerasi/Setup Awal
            if ($isGenerateNewKey) {
                $this->sidangAdminModel->update($id, [
                    'sidang_2fa_secret' => $secretKey, // Simpan key penuh
                    'is_active' => 1,
                    'qr_regenerate_count' => $currentCount + 1, // Tambah hitungan
                ]);
            }
            // Jika HANYA menampilkan ulang, tidak ada update ke DB

            // 5. Setup QR Code (Gunakan $secretKey terbaru/lama)
            $label = $adminUser['nip'] . ' - ' . $adminUser['nama_pegawai'];
            $issuer = 'TRON Sidang';
            $totp = TOTP::create($secretKey, 30); 
            $totp->setIssuer($issuer);
            $totp->setLabel($label);
            $provisioningUri = $totp->getProvisioningUri(); 

            // 6. Render QR Code
            $options = new QROptions([
                'outputType' => QRCode::OUTPUT_IMAGE_PNG, 
                'eccLevel'   => QRCode::ECC_H, 
                'scale'      => 5,             
                'imageBase64' => false,        
            ]);
            $qrcodeBinary = (new QRCode($options))->render($provisioningUri);
            $qrCodeImage = 'data:image/png;base64,' . base64_encode($qrcodeBinary);

            $adminUserUpdated = $this->sidangAdminModel->find($id); // Ambil data terbaru
            $message = $isRegenerate ? 
                       "QR Code berhasil dibuat/diperbarui. Batas saat ini: " . $adminUserUpdated['qr_regenerate_count'] . " dari {$MAX_COUNT}x." :
                       "QR Code ditampilkan. Kunci tidak diubah.";

            return $this->response->setJSON([
                'status' => 'true',
                'message' => $message,
                'data' => [
                    'nip' => $adminUser['nip'],
                    'nama_pegawai' => $adminUser['nama_pegawai'],
                    'secretKey' => $secretKey,
                    'qrCodeImage' => $qrCodeImage,
                ]
            ]);
        } catch (\Throwable $e) {
            log_message('critical', 'QR GENERATION FAILED: ' . $e->getMessage());
            return $this->response->setStatusCode(500)->setJSON([
                'status' => 'false',
                'message' => 'Gagal membuat QR Code. Harap gunakan Kunci Manual. (Error: ' . $e->getMessage() . ')',
            ]);
        }         
    }
}