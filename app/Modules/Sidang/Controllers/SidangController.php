<?php

// NAMESPACE BARU: Mengikuti struktur HMVC yang sudah disepakati (App\Modules\Sidang)
namespace App\Modules\Sidang\Controllers; 

use App\Controllers\BaseController;
use App\Modules\Sidang\Models\SidangModel;
use App\Modules\Sidang\Models\SidangAdminModel; // Tambahkan import SidangAdminModel
use App\Libraries\Settings;
use Google\Client;
use Google\Service\Sheets;
use PhpOffice\PhpWord\TemplateProcessor;
// ✅ KOREKSI: Ganti Otp\Otp yang menyebabkan Class not found dengan library yang digunakan di AdminSetupController
use OTPHP\TOTP;
// Asumsi Anda memiliki UserModel di aplikasi utama untuk menyimpan data NIP dan Secret Key
use App\Models\UserModel; 

// Mengganti nama kelas menjadi SidangController agar konsisten dengan standar CI4
class SidangController extends BaseController
{
    protected $sidangModel;
    protected $sidangAdminModel;
    protected $setting;

    public function __construct()
    {
        // Load Model, Setting, dan UserModel
        $this->sidangModel = new SidangModel();
        $this->setting = new Settings(); 
        $this->sidangAdminModel = new SidangAdminModel(); // Inisialisasi UserModel untuk otentikasi
    }

    /**
     * Tampilkan Form Akses (NIP + OTP)
     */
    public function accessForm()
    {
        // Jika sudah login, langsung redirect ke index
        if (session()->get('isLoggedInSidang')) {
            return redirect()->to(site_url('sidang'));
        }

        $data = [
            'title' => 'Akses Menu Cetak Sidang',
            'nama_instansi' => $this->setting->info['nama_instansi'],
        ];
        // Pastikan path view sudah benar, mengarah ke modul HMVC
        return view('\App\Modules\Sidang\Views\access_form', $data); 
    }

    /**
     * Proses Verifikasi NIP dan Kode OTP
     */
    public function verifyOtp()
    {
        $nip = $this->request->getPost('nip'); 
        $otp_code = $this->request->getPost('otp_code');
        
        // Validasi input sederhana
        if (empty($nip) || empty($otp_code)) {
            return redirect()->back()->with('error', 'NIP dan Kode OTP wajib diisi.');
        }

       // 1. Cari Secret Key menggunakan Model BARU (SidangAdminModel)
        $adminUser = $this->sidangAdminModel->findByNip($nip);

        if (!$adminUser || empty($adminUser['sidang_2fa_secret']) || $adminUser['is_active'] == 0) {
            // NIP tidak dikenal, belum di-setup, atau tidak aktif
            return redirect()->back()->with('error', 'NIP tidak terdaftar untuk akses sidang atau belum dikonfigurasi. Hubungi Admin.');
        }

        $secret_key = $adminUser['sidang_2fa_secret'];

        // 2. Verifikasi Kode TOTP (MENGGUNAKAN LIBRARY BARU)
        try {
            // ✅ KOREKSI: Gunakan OTPHP\TOTP::create() dengan secret key yang tersimpan
            // Ini akan membuat objek TOTP dari kunci yang sudah ada.
            $otp = TOTP::create($secret_key); 

            // Verifikasi kode dengan jendela waktu yang diizinkan (misal: 1 jendela = 30 detik)
            if ($otp->verify($otp_code, null, 1)) {
            // KODE VALID!
            
            // 3. Buat Sesi Login Sidang Penuh
            session()->set([
                'isLoggedInSidang' => true, 
                'sidang_nip' => $adminUser['nip'],
                'userId' => $adminUser['id'] // Menyimpan ID dari tabel sidang_admins
            ]);
            return redirect()->to(site_url('sidang')); 

            } else {
                // KODE TIDAK VALID
                return redirect()->back()->with('error', 'Kode OTP tidak valid atau sudah kadaluarsa. Coba lagi.');
            }
        } catch (\Throwable $e) {
            // 🚨 TANGANI JIKA LIBRARY GAGAL DI-LOAD SAAT VERIFIKASI
             // (Ini akan terjadi jika masalah Autoloading Class TOTP muncul saat verifikasi)
             return redirect()->back()->with('error', 'Kesalahan Sistem Verifikasi: Class OTP gagal dimuat. Hubungi Admin. Pesan: ' . $e->getMessage());
        }
    }


    /**
     * HELPER: KONEKSI KE GOOGLE SHEET
     */
    private function fetchSheet($range)
    {
        // Kode ini sudah bagus, tidak perlu diubah.
        $client = new Client();
        $client->setAuthConfig(WRITEPATH . '/kredensial-google.json');
        $client->addScope(Sheets::SPREADSHEETS_READONLY);
        $service = new Sheets($client);
        
        // ⚠️ GANTI ID SHEET LO DISINI
        $spreadsheetId = '1Jlsbx5HxKzQDmfNFIBkw1TTmDBKXpIxKAtb_zhaLLfo'; 
        
        $response = $service->spreadsheets_values->get($spreadsheetId, $range);
        return $response->getValues();
    }

    /**
     * HALAMAN UTAMA (INDEX) - MEMERLUKAN FILTER 'sidang_auth'
     */
 public function index()
    {
        // Cek kembali apakah user sudah login. Walaupun sudah ada filter, cek ini bagus untuk redundancy.
        if (!session()->get('isLoggedInSidang')) {
             return redirect()->to(site_url('sidang/access'));
        }
        
        // Ambil Tanggal Unik dari Database buat Filter
        $queryTanggal = $this->sidangModel->select('tanggal_sidang')->distinct()->orderBy('tanggal_sidang', 'DESC')->findAll();
        
        $listTanggal = [];

        // ✅ KOREKSI LOGIC DAN SYNTAX UNTUK FORMATTING TANGGAL
        foreach ($queryTanggal as $row) {
            // Ambil tanggal mentah dari baris database
            $rawDate = $row['tanggal_sidang']; 

            // Pastikan tanggal TIDAK kosong atau bukan 0000-00-00
            if (!empty($rawDate) && $rawDate !== '0000-00-00') {
                try {
                    // Coba konversi dan format YYYY-MM-DD menjadi DD-MM-YYYY
                    $formattedDate = date('d-m-Y', strtotime($rawDate)); 
                    
                    // Tambahkan ke array hanya jika formatting berhasil
                    $listTanggal[] = $formattedDate; 
                    
                } catch (\Exception $e) {
                    // Jika terjadi error saat konversi (misalnya format data rusak), 
                    // lewati baris ini (tidak perlu ditambahkan ke $listTanggal)
                    continue; 
                }
            }
            // Tanggal 0000-00-00 atau kosong akan diabaikan (tidak masuk ke $listTanggal)
        }
        
        // Ambil 100 data terbaru (allData)
        // Note: Pastikan format tanggal di all_data sesuai dengan filter JS di View, 
        // atau format di View menggunakan data-attribute.
        $allData = $this->sidangModel->orderBy('tanggal_sidang', 'DESC')->findAll(100);

        // Kirim Data ke View 
        $data = [
            'title'         => 'Cetak Sidang - ' . $this->setting->info['nama_aplikasi'],
            'nama_instansi' => $this->setting->info['nama_instansi'],
            'alamat'        => $this->setting->info['alamat'],
            // ✅ Kirim array tanggal yang sudah diformat ke dropdown
            'opt_tanggal'   => $listTanggal, 
            'all_data'      => $allData, 
            // Tambahkan NIP yang sedang login ke data view
            'nip_user'      => session()->get('sidang_nip')
        ];

        // Pastikan path view sudah benar, mengarah ke modul HMVC
        return view('\App\Modules\Sidang\Views\sidang_view', $data);
    }
    /**
     * FITUR SINKRONISASI (SYNC) - MEMERLUKAN FILTER 'sidang_auth'
     */
    public function sync()
    {
        // ... (Kode sync() yang sudah ada, tidak ada perubahan logika inti) ...
        // 1. Ambil Data Mentah dari Google Sheet
        $sidangSheet = $this->fetchSheet('SIDANG HARI INI!A2:K');
        $masterSheet = $this->fetchSheet('DATA_MASTER!A2:Z');

        // 2. Buat Kamus Data Master (Kunci: Nomor Perkara / Index 0)
        $masterMap = [];
        if (!empty($masterSheet)) {
            foreach ($masterSheet as $mRow) {
                $key = isset($mRow[0]) ? trim($mRow[0]) : '';
                if ($key) $masterMap[$key] = $mRow;
            }
        }

        $countInsert = 0;
        $countUpdate = 0;

        // 3. Proses Simpan ke Database
        if (!empty($sidangSheet)) {
            foreach ($sidangSheet as $rowS) {
                // Bersihkan data
                $noPerkara = isset($rowS[0]) ? trim(preg_replace('/\s+/', ' ', $rowS[0])) : '';
                $nama      = isset($rowS[2]) ? trim(preg_replace('/\s+/', ' ', $rowS[2])) : '';
                
                // Filter Ghost Rows (Data Kosong/Sampah)
                if ($noPerkara == '' || strlen($noPerkara) < 5 || stripos($noPerkara, 'Nomor') !== false) continue;

                // Cari Biodata di Master
                $rowM = $masterMap[$noPerkara] ?? [];

                // Gabungkan Biodata ke dalam JSON
                $dataFull = [
                    'tempat_lahir'    => $rowM[3] ?? '-',
                    'tgl_lahir'       => $rowM[4] ?? '-',
                    'umur'            => $rowM[5] ?? '-',
                    'jenis_kelamin'   => $rowM[6] ?? '-',
                    'kewarganegaraan' => $rowM[7] ?? 'Indonesia',
                    'alamat'          => $rowM[8] ?? '-',
                    'agama'           => $rowM[9] ?? '-',
                    'pekerjaan'       => $rowM[10] ?? '-',
                    'pendidikan'      => $rowM[11] ?? '-',
                    'nama_ortu'       => $rowM[12] ?? '-',
                    'agenda_raw'      => $rowS[7] ?? '-', // Simpan agenda asli
                    'hari_raw'        => $rowS[6] ?? '-', // Simpan hari asli
                ];

                // Tanggal Sidang (Asumsi format string di sheet konsisten)
                $tglSidang = $rowS[6] ?? date('Y-m-d'); 

                // Cek apakah data sudah ada di DB?
                $existing = $this->sidangModel->where('nomor_perkara', $noPerkara)->first();

                $saveData = [
                    'tanggal_sidang' => $tglSidang, 
                    'nomor_perkara'  => $noPerkara,
                    'nama_terdakwa'  => $nama,
                    'jpu'            => $rowS[3] ?? '-',
                    'data_full'      => json_encode($dataFull), // Simpan biodata sbg JSON
                ];

                if ($existing) {
                    $this->sidangModel->update($existing['id'], $saveData);
                    $countUpdate++;
                } else {
                    $this->sidangModel->insert($saveData);
                    $countInsert++;
                }
            }
        }

        return redirect()->to('sidang')->with('success', "Sinkronisasi Selesai! Data Baru: $countInsert, Update: $countUpdate");
    }

    /**
     * FITUR PROSES CETAK - MEMERLUKAN FILTER 'sidang_auth'
     */
    public function proses()
    {
        // ... (Kode proses() yang sudah ada, tidak ada perubahan logika inti) ...
        
        // 1. Ambil Input User
        $selectedNoPerkara = $this->request->getPost('pilih_data');
        $docTypes = $this->request->getPost('jenis_dokumen');
        $mode = $this->request->getPost('mode_cetak');
        $tanggalTerpilih = $this->request->getPost('tanggal_terpilih');

        // 2. Setup Folder Backup
        $hariIni = date('Y-m-d');
        $pathArsip = WRITEPATH . 'arsip_sidang';
        $folderBackup = $pathArsip . DIRECTORY_SEPARATOR . $hariIni;

        // Buat folder kalau belum ada
        if (!is_dir($pathArsip)) mkdir($pathArsip, 0777, true);
        if (!is_dir($folderBackup)) mkdir($folderBackup, 0777, true);

        $generatedFiles = [];
        $targetData = [];

        // 3. Ambil Data Target dari Database
        if ($mode == 'full_p38') {
            // Mode Tombol Hijau: Ambil semua data tanggal tsb
            $targetData = $this->sidangModel->where('tanggal_sidang', $tanggalTerpilih)->findAll();
            $docTypes = ['p38']; // Paksa cuma P38
        } else {
            // Mode Tombol Kuning: Ambil sesuai checklist
            if(empty($selectedNoPerkara)) return redirect()->back()->with('error', 'Pilih data dulu!');
            if(empty($docTypes)) return redirect()->back()->with('error', 'Pilih jenis dokumen!');
            
            $targetData = $this->sidangModel->whereIn('nomor_perkara', $selectedNoPerkara)->findAll();
        }

        if (empty($targetData)) {
            return redirect()->back()->with('error', 'Data tidak ditemukan di database. Coba Sync dulu!');
        }

        // 4. Generate Word
        foreach ($targetData as $row) {
            // Decode JSON biodata
            $details = json_decode($row['data_full'], true);

            $dataRow = [
                'nomor_perkara'   => $row['nomor_perkara'],
                'nama_terdakwa'   => $row['nama_terdakwa'],
                'jpu'             => $row['jpu'],
                'hari_sidang'     => $row['tanggal_sidang'], 
                'agenda'          => $details['agenda_raw'] ?? '-',
                'tanggal_surat'   => date('d F Y'), 

                // Data Biodata
                'tempat_lahir'    => $details['tempat_lahir'] ?? '-',
                'tgl_lahir'       => $details['tgl_lahir'] ?? '-',
                'umur'            => $details['umur'] ?? '-',
                'jenis_kelamin'   => $details['jenis_kelamin'] ?? '-',
                'kewarganegaraan' => $details['kewarganegaraan'] ?? '-',
                'alamat'          => $details['alamat'] ?? '-',
                'agama'           => $details['agama'] ?? '-',
                'pekerjaan'       => $details['pekerjaan'] ?? '-',
                'pendidikan'      => $details['pendidikan'] ?? '-',
                'nama_ortu'       => $details['nama_ortu'] ?? '-',
                // AMBIL NIP DARI SESSION
                'nip_jpu'         => session()->get('sidang_nip') ?? '...................',
            ];

            // Bersihkan nama file
            $cleanName = preg_replace('/[^A-Za-z0-9 \-]/', '', $row['nama_terdakwa']);
            $cleanName = substr($cleanName, 0, 50);

            // Generate P-37
            if (in_array('p37', $docTypes)) {
                $this->generateDoc('template_p37.docx', $dataRow, "P37_{$cleanName}.docx", $folderBackup, $generatedFiles);
            }
            // Generate P-38
            if (in_array('p38', $docTypes)) {
                $this->generateDoc('template_p38.docx', $dataRow, "P38_{$cleanName}.docx", $folderBackup, $generatedFiles);
            }
        }

        // 5. Packing & Download
        $totalFiles = count($generatedFiles);
        if ($totalFiles === 0) return redirect()->back()->with('error', 'Gagal generate file.');

        // Kalau cuma 1 file -> Download Langsung
        if ($totalFiles === 1) {
            $singleFile = reset($generatedFiles);
            return $this->response->download($singleFile, null)->setFileName(basename($singleFile));
        } 
        // Kalau banyak -> ZIP
        else {
            $zip = new \ZipArchive();
            $zipName = $folderBackup . DIRECTORY_SEPARATOR . 'Berkas_Sidang_' . time() . '.zip';

            if ($zip->open($zipName, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) === TRUE) {
                foreach ($generatedFiles as $fname => $fpath) {
                    if (file_exists($fpath)) $zip->addFile($fpath, $fname);
                }
                $zip->close();
            }
            
            if (file_exists($zipName)) {
                return $this->response->download($zipName, null);
            } else {
                return redirect()->back()->with('error', 'Gagal membuat file ZIP.');
            }
        }
    }

    // Helper Generate Doc
    private function generateDoc($tpl, $data, $outName, $path, &$filesArr) {
        $tplPath = WRITEPATH . 'templates/' . $tpl;
        if (file_exists($tplPath)) {
            $proc = new TemplateProcessor($tplPath);
            $proc->setValues($data);
            
            // Auto numbering nama file biar gak bentrok
            $finalName = $outName;
            $c = 1;
            while(file_exists($path . DIRECTORY_SEPARATOR . $finalName)) {
                $finalName = pathinfo($outName, PATHINFO_FILENAME) . "_($c)." . pathinfo($outName, PATHINFO_EXTENSION);
                $c++;
            }
            
            $saveP = $path . DIRECTORY_SEPARATOR . $finalName;
            $proc->saveAs($saveP);
            $filesArr[$finalName] = $saveP;
        }
    }
}