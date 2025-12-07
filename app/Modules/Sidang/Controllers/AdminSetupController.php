<?php

namespace App\Modules\Sidang\Controllers;

use App\Controllers\BaseController;
use App\Modules\Sidang\Models\SidangAdminModel;

// Import library yang dibutuhkan
use OTPHP\TOTP; 
// 💥 FIX: Gunakan semua class Builder V6
use Endroid\QrCode\Builder\Builder; 
use Endroid\QrCode\Writer\PngWriter; 
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel\ErrorCorrectionLevelHigh;
use Endroid\QrCode\Label\Font\NotoSans; 


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
    
    // --- 1. Fungsi setupIndex: Dipanggil oleh rute 'admin/sidang/setup' ---
    public function setupIndex()
    {
        if (!$this->sidangAdminModel) {
            return redirect()->back()->with('error', 'Gagal memuat data administrasi. Cek koneksi database.');
        }

        $listAdmins = $this->sidangAdminModel->findAll();

        $data = [
            'title' => 'Manajemen Kunci OTP Pegawai Sidang',
            'list_admins' => $listAdmins
        ];
        
        // Panggil view admin/setting_otp
        return view('\App\Modules\Setting\Views\setting_otp', $data);
    }
    
    // --- 2. Fungsi saveNip: Dipanggil oleh rute POST ---
    public function saveNip()
    {
        $input = $this->request->getPost();

        if (!$this->validate(['nip' => 'required|min_length[5]', 'nama_pegawai' => 'required'])) {
            return redirect()->back()->withInput()->with('error', $this->validator->listErrors());
        }

        $data = [
            'nip' => $input['nip'],
            'nama_pegawai' => $input['nama_pegawai'],
            'sidang_2fa_secret' => null, 
            'is_active' => 0
        ];

        try {
            if (!empty($input['id'])) {
                $this->sidangAdminModel->update($input['id'], $data);
                $message = 'Data NIP berhasil diupdate.';
            } else {
                $this->sidangAdminModel->insert($data);
                $message = 'Data NIP berhasil ditambahkan. Lakukan "Generate QR Code" untuk mengaktifkan OTP.';
            }
            return redirect()->back()->with('success', $message);
        } catch (\Throwable $e) {
            return redirect()->back()->withInput()->with('error', 'Gagal menyimpan data: ' . $e->getMessage());
        }
    }
    
    // --- 3. Fungsi generateQr: Dipanggil oleh rute 'setting/otp-sidang/generate/(:num)' ---
    public function generateQr(int $id)
    {
        // 1. Ambil data user dari database
        $adminUser = $this->sidangAdminModel->find($id);

        if (!$adminUser) {
            return redirect()->back()->with('error', 'NIP tidak ditemukan.');
        }
        
        $secretKey = $adminUser['sidang_2fa_secret'];

        try {
            if (empty($secretKey)) {
                $totp = TOTP::generate();
                $secretKey = $totp->getSecret();

                $this->sidangAdminModel->update($id, [
                    'sidang_2fa_secret' => $secretKey,
                    'is_active' => 1 
                ]);
            }

            // Label untuk aplikasi Authenticator
            $label = $adminUser['nip'] . ' - ' . $adminUser['nama_pegawai'];
            $issuer = 'TRON Sidang';
            
            $totp = TOTP::create($secretKey, 30); 
            $totp->setIssuer($issuer);
            $totp->setLabel($label);
            
            $provisioningUri = $totp->getProvisioningUri(); 

            // 💥 KOREKSI UTAMA: Menggunakan Builder Pattern V6
            // Pola yang benar untuk V6 adalah menggunakan withWriter(new PngWriter())
            $result = (new Builder())
                ->writer(new PngWriter()) 
                ->data($provisioningUri)
                ->encoding(new Encoding('UTF-8'))
                ->errorCorrectionLevel(new ErrorCorrectionLevelHigh())
                ->size(300)
                ->margin(10)
                ->labelText($label)
                ->labelFont(new NotoSans(18)) 
                ->build();

            // Render ke Base64 URI (menggunakan method yang benar untuk Builder)
            $qrCodeImage = $result->getDataUri();
            $errorMessage = '';

        } catch (\Throwable $e) {
            // Log Error untuk dibaca di server
            log_message('critical', 'QR GENERATION FAILED: ' . $e->getMessage());
            
            $errorMessage = 'Gagal membuat gambar QR Code: ' . $e->getMessage() . '. Harap gunakan Kunci Manual.';
            $qrCodeImage = 'data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7'; 
        }
        
        $data = [
            'title' => 'Setup Kunci OTP Pegawai',
            'user' => $adminUser,
            'secretKey' => $secretKey, 
            'qrCodeImage' => $qrCodeImage,
            'errorMessage' => $errorMessage
        ];
        
        return view('\App\Modules\Sidang\Views\admin\setup_qr', $data);
    }
}