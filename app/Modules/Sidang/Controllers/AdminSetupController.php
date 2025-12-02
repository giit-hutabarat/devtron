<?php

namespace App\Modules\Sidang\Controllers;

use App\Controllers\BaseController;
use App\Modules\Sidang\Models\SidangAdminModel;

// ✅ IMPORT LIBRARY OTP (spomky-labs/otphp)
use OTPHP\TOTP; 

// Import untuk QR Code Builder Anda (Endroid)
use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Writer\PngWriter;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel\ErrorCorrectionLevelHigh;
use Endroid\QrCode\Label\Label;

class AdminSetupController extends BaseController
{
    protected $sidangAdminModel;

    public function __construct()
    {
        // Inisialisasi Model
        try {
            $this->sidangAdminModel = new SidangAdminModel();
        } catch (\Throwable $e) {
            // Jika model gagal inisialisasi (misal DB error), ini akan ditangkap oleh Debugger CI4
        }
    }
    
    // --- 1. Fungsi setupIndex ---
    public function setupIndex()
    {
        $listAdmins = $this->sidangAdminModel->findAll();

        $data = [
            'title' => 'Manajemen Kunci OTP Pegawai Sidang',
            'list_admins' => $listAdmins
        ];
        return view('\App\Modules\Sidang\Views\admin\admin_nip_form', $data); 
    }

    // --- 2. Fungsi saveNip ---
    public function saveNip()
    {
        // Validasi dan sanitasi
        $nip = $this->request->getPost('nip');
        $nama = $this->request->getPost('nama');
        
        if (empty($nip) || empty($nama)) {
             return redirect()->back()->with('error', 'NIP dan Nama Pegawai wajib diisi.');
        }

        $existing = $this->sidangAdminModel->findByNip($nip);

        $dataSave = [
            'nip' => $nip,
            'nama_pegawai' => $nama,
            'is_active' => 0 // Awalnya tidak aktif
        ];

        if ($existing) {
            $this->sidangAdminModel->update($existing['id'], ['nama_pegawai' => $nama]);
            $message = 'Data NIP berhasil diupdate. Silakan klik "Generate QR Code".';
        } else {
            $this->sidangAdminModel->insert($dataSave, true);
            $message = 'NIP baru berhasil ditambahkan. Silakan klik "Generate QR Code" untuk konfigurasi.';
        }

        // Redirect kembali ke halaman daftar NIP
        return redirect()->to(site_url("admin/sidang/setup")) 
                         ->with('success', $message);
    }

        // --- 3. GENERATE Secret Key dan QR Code ---
    public function generateQr($id)
    {
        $adminUser = $this->sidangAdminModel->find($id);

        if (!$adminUser) {
            return redirect()->to(site_url('admin/sidang/setup'))->with('error', 'Pegawai tidak ditemukan.');
        }

        $qrCodeImage = '';
        $errorMessage = null;
        $secretKey = '';

        try {
            // 1. INJEKSI SECRET KEY KHUSUS YANG VALID BASE32
            // Karena TOTP::create() dan Base32::generate() gagal di-load, 
            // kita menggunakan library PHP-Auth (asumsi terinstall) atau membuat string valid.
            // UNTUK UJI COBA INI, KITA ASUMSIKAN CONSTRUCTOR DASAR BERHASIL:
            
            // Mencoba membuat objek TOTP secara default (ini akan gagal jika Base32 helper tidak terload)
            $otp = TOTP::create();
            
            if (!$otp || !($otp instanceof TOTP)) {
                 throw new \Exception("Gagal inisiasi OTP, mencoba fallback.");
            }
            
            $secretKey = $otp->getSecret(); // Secret Key yang dihasilkan adalah Base32 yang VALID

            // 2. SIMPAN SECRET KEY BARU ke database
            $this->sidangAdminModel->update($id, [
                'sidang_2fa_secret' => $secretKey,
                'is_active' => 1
            ]);

            // 3. BUAT URI dan QR CODE
            $otp->setLabel("{$adminUser['nip']} ({$adminUser['nama_pegawai']})")
                ->setIssuer('AksesSidang-TRON');

            $uri = $otp->getProvisioningUri();

            // 4. GENERATE QR CODE (Endroid)
            $result = Builder::create()->writer(new PngWriter())->data($uri)
                ->encoding(new Encoding('UTF-8'))
                ->errorCorrectionLevel(new ErrorCorrectionLevelHigh())
                ->size(300)->labelText('Pindai Sekarang')->build();
            
            $qrCodeImage = $result->getDataUri();

        } catch (\Throwable $e) {
            // 🚨 KETIKA LIBLARY OTP GAGAL (masalah Autoloading/Constructor)
            
            // 1. Ambil secret key terakhir dari DB (jika status Aktif)
            $adminUser = $this->sidangAdminModel->find($id); 
            $secretKey = $adminUser['sidang_2fa_secret'];
            
            // 2. Jika Secret Key KOSONG (Belum Konfigurasi), buat kunci yang panjangnya cukup (Minimal 32)
            if (empty($secretKey)) {
                
                // 🛑 PENTING: Karena kita tidak bisa membuat Base32 valid di sini, 
                // kita harus mengasumsikan masalah Autoloading.
                
                // Set status Belum Konfigurasi
                $this->sidangAdminModel->update($id, [
                    'is_active' => 0,
                    'sidang_2fa_secret' => null
                ]);
                
                return redirect()->back()->with('error', 'Gagal Total: ' . $e->getMessage() . '. Secret Key tidak dapat digenerate (Autoloading Error). NIP direset ke status Belum Konfigurasi.');
            }

            // 3. Set error message dan placeholder
            $errorMessage = 'Gagal membuat gambar QR Code: ' . $e->getMessage() . '. Harap gunakan Kunci Manual.';
            $qrCodeImage = 'data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7'; 
        }
        
        // --- AKHIR BLOK TRY-CATCH ---

        // 4. Tampilkan QR Code / Kunci Manual ke View
        $data = [
            'title' => 'Setup Kunci OTP Pegawai',
            'user' => $adminUser,
            'secretKey' => $secretKey, 
            'qrCodeImage' => $qrCodeImage,
            'errorMessage' => $errorMessage
        ];

        return view('\App\Modules\Sidang\Views\admin\admin_qr_view', $data); 
    }
}