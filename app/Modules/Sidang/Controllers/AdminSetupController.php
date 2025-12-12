<?php

namespace App\Modules\Sidang\Controllers;

use App\Controllers\BaseController;
use App\Modules\Sidang\Models\SidangAdminModel;

// Import library yang dibutuhkan
use OTPHP\TOTP; 
// 💥 FIX: Gunakan semua class Builder V6
use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;


class AdminSetupController extends BaseController
{
    protected $sidangAdminModel;

    public function __construct()
    {
        // Inisialisasi Model
        try {
            $this->sidangAdminModel = new SidangAdminModel();
        } catch (\Throwable $e) {
            // Error handling model/DB
        }
    }
    
    // --- 1. Fungsi setupIndex: Dipanggil oleh rute 'setting/otp-sidang' ---
    public function index()
    {
        if (!$this->sidangAdminModel) {
            return redirect()->back()->with('error', 'Gagal memuat data administrasi. Cek koneksi database.');
        }

        $listAdmins = $this->sidangAdminModel->findAll();

        $data = [
            'title' => 'Manajemen Kunci OTP Pegawai Sidang',
            //'list_admins' => $listAdmins
        ];
        
        // Panggil view admin/setting_otp
        return view('\App\Modules\Sidang\Views\otp-sidang', $data);
    }
    
    // --- 2. Fungsi saveNip: Dipanggil oleh rute POST ---
    public function saveNip()
    {
        $input = $this->request->getPost();
        $validationRules = [
            'nip' => 'required|min_length[5]',
            'nama_pegawai' => 'required'
        ];
        if (empty($input['id'])) {
            $validationRules['nip'] .= '|is_unique[sidang_admins.nip]';
        } else{
            $validationRules['nip'] .= '|is_unique[sidang_admins.nip,id,' . $input['id'] . ']';
        }

        if (!$this->validate($validationRules)) {
            return redirect()->back()->withInput()->with('error', $this->validator->listErrors());
        }

        $data = [
            'nip' => $input['nip'],
            'nama_pegawai' => $input['nama_pegawai'],
            //'sidang_2fa_secret' => null, 
            //'is_active' => 0
        ];

        try {
            if (!empty($input['id'])) {
                //update : NIP dan NAMA
                $this->sidangAdminModel->update($input['id'], $data);
                $message = 'Data NIP berhasil diupdate.';
            } else {
                $data['sidang_2fa_secret'] = null;
                $data['is_active'] = 0;
                $this->sidangAdminModel->insert($data);
                $message = 'Data NIP berhasil ditambahkan. Lakukan "Generate QR Code" untuk mengaktifkan OTP.';
            }
            return redirect()->back()->with('success', $message);
        } catch (\Throwable $e) {
            log_message('error', 'Gagal Menambahkan Pegawai: ' . $e->getMessage());
            return redirect()->back()->withInput()->with('error', 'Gagal menyimpan data: ' . $e->getMessage());
        }
    }
    
    // --- 3. Fungsi generateQr: Dipanggil oleh rute 'setting/otp-sidang/generate/(:num)' ---
    public function generateQr(int $id)
    {
        // 1. Ambil data user dari database
        $adminUser = $this->sidangAdminModel->find($id);

        if (!$adminUser) {
            return $this->response->setStatusCode(404)->setJSON([
                'status' => 'false',
                'message' => 'NIP tidak ditemukan.',
            ]);
        }
        
        $secretKey = $adminUser['sidang_2fa_secret'];

        $currentCount = $adminUser['qr_regenerate_count'] ?? 0;
        $MAX_COUNT = 5;

        //algoritma cek batas regenerasi QR Code
        try{
            if ($currentCount >= $MAX_COUNT) {
            return $this->response->setStatusCode(429)->setJSON([
                'status' => 'false',
                'message' => "Batas maksimum regenerasi QR Code ({$MAX_COUNT}) telah tercapai. Silakan hubungi administrator.",
            ]);
        }
        $shouldGenerateNewKey = empty($secretKey) || $adminUser['is_active'] == 0;
         // generate secret key baru dan update counter
         if ($shouldGenerateNewKey) {
            $totp = TOTP::generate();
            $secretKey = $totp->getSecret();
        }

        // update status aktivasi dan counter regenerasi
        $this->sidangAdminModel->update($id, [
            'sidang_2fa_secret' => $secretKey,
            'is_active' => 1,
            'qr_regenerate_count' => $currentCount + 1,
        ]);

        // Label untuk aplikasi Authenticator
        $label = $adminUser['nip'] . ' - ' . $adminUser['nama_pegawai'];
        $issuer = 'TRON Sidang';
        
        $totp = TOTP::create($secretKey, 30); 
        $totp->setIssuer($issuer);
        $totp->setLabel($label);
            
        $provisioningUri = $totp->getProvisioningUri(); 

            // =========================================================
            // 💥 BLOCK IMPLEMENTASI CHILLERLAN/PHP-QRCODE
            // =========================================================
            
            // 1. Definisikan Opsi Rendering
            $options = new QROptions([
                // Pastikan PATH untuk gambar benar-benar kosong, 
                // agar output langsung berupa string binary PNG.
                'outputType' => QRCode::OUTPUT_IMAGE_PNG, 
                'eccLevel'   => QRCode::ECC_H, // ECC_H = High (Tingkat koreksi error tinggi)
                'scale'      => 5,             // Skala/Ukuran QR Code (5x pixel, menghasilkan ukuran ~250px)
                'imageBase64' => false,        // Kita akan encode Base64 secara manual
            ]);

            // 2. Render data URI menjadi string PNG biner
            $qrcodeBinary = (new QRCode($options))->render($provisioningUri);
            
            // 3. Ubah output PNG mentah menjadi Base64 URI untuk HTML
            $qrCodeImage = 'data:image/png;base64,' . base64_encode($qrcodeBinary);

            $adminUserUpdated = $this->sidangAdminModel->find($id);
           
            return $this->response->setJSON([
            'status' => 'true',
            'message' => 'QR Code berhasil dibuat.'. $adminUserUpdated['qr_regenerate_count'] . " dari {$MAX_COUNT}x.",
            'data' => [
                'nip' => $adminUser['nip'],
                'nama_pegawai' => $adminUser['nama_pegawai'],
                'secretKey' => $secretKey,
                'qrCodeImage' => $qrCodeImage,
            ]
        ]);
    } catch (\Throwable $e) {
            // Log Error untuk dibaca di server
            log_message('critical', 'QR GENERATION FAILED: ' . $e->getMessage());
            
            return $this->response->setStatusCode(500)->setJSON([
                'status' => 'false',
                'message' => 'Gagal membuat QR Code. Harap gunakan Kunci Manual. (Error: ' . $e->getMessage() . ')',
            ]);
        }         
    }
}