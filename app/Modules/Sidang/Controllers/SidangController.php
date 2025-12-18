<?php

namespace App\Modules\Sidang\Controllers; 

use App\Controllers\BaseController;
use App\Modules\Sidang\Models\SidangModel;
use App\Modules\Sidang\Models\SidangAdminModel; 
use App\Libraries\Settings;
use Google\Client;
use Google\Service\Sheets;
use PhpOffice\PhpWord\TemplateProcessor;
use OTPHP\TOTP;
use App\Models\UserModel; 
use CodeIgniter\HTTP\ResponseInterface;

class SidangController extends BaseController
{
    protected $sidangModel;
    protected $sidangAdminModel;
    protected $setting; // Deklarasi properti

    public function __construct()
    {
        $this->sidangModel = new SidangModel();
        // 🛑 KOREKSI 1: Instansiasi Settings yang aman di constructor (Jika gagal, set ke null)
        try {
            $this->setting = new Settings(); 
        } catch (\Throwable $e) {
            $this->setting = null; 
        }
        $this->sidangAdminModel = new SidangAdminModel(); 
    }

    /**
     * Helper untuk membersihkan nama terdakwa dari Als, Bin, Alias, Dkk, dsb.
     */
    private function cleanNamaTerdakwa(string $rawName): string
    {
        $keywords = [' als ', ' alias ', ' bin ', ' dkk '];
        $cleanName = $rawName;
        $lowerName = strtolower($rawName);
        $foundPos = false;

        foreach ($keywords as $keyword) {
            $pos = strpos($lowerName, $keyword); 
            if ($pos !== false) {
                 if ($foundPos === false || $pos < $foundPos) {
                     $foundPos = $pos;
                 }
            }
        }
        
        if ($foundPos !== false) {
            $cleanName = substr($rawName, 0, $foundPos);
        }
        
        return trim($cleanName);
    }
    
    /**
     * HELPER: KONEKSI KE GOOGLE SHEET
     */
    private function fetchSheet($range)
    {
        $client = new Client();
        $client->setAuthConfig(WRITEPATH . '/json-by2025.json');
        $client->addScope(Sheets::SPREADSHEETS_READONLY);
        $service = new Sheets($client);
        
        // ⚠️ GANTI ID SHEET LO DISINI
        $spreadsheetId = '1Jlsbx5HxKzQDmfNFIBkw1TTmDBKXpIxKAtb_zhaLLfo'; 
        
        $response = $service->spreadsheets_values->get($spreadsheetId, $range);
        return $response->getValues();
    }


    public function accessForm()
    {
        if (session()->get('isLoggedInSidang')) {
            return redirect()->to(site_url('sidang'));
        }
        
        $namaInstansi = 'NAMA INSTANSI DEFAULT';
        $logoPath = 'images/logo_kejaksaan.png';
    if ($this->setting !== null) {
        // ✅ AKTIFKAN DAN KOREKSI INI
        try {
            //key dari general database
            $namaInstansi = $this->setting->get('nama_instansi') ?? $namaInstansi;
            
            //ambil logodb
            $logoPathDb = $this->setting->get('logo');
            if(!empty($logoPathDb)) {
                $logoPath = $logoPathDb;
            }
        } catch (\Throwable $e) {
            // Biarkan default jika error database saat get settings
        }
    }

        $data = [
            'title' => 'Akses Cetak Berkas Sidang',
            'nama_instansi' => $namaInstansi,
            'logo_instansi' => base_url(esc($logoPath)),
            
        ];
        return view('\App\Modules\Sidang\Views\access_form', $data); 
    }

    public function verifyOtp()
    {
        $nip = $this->request->getPost('nip'); 
        $otp_code = $this->request->getPost('otp_code');
        
        // 1. Cek Input
        if (empty($nip) || empty($otp_code)) {
            return $this->response->setJSON(['status' => false, 'message' => 'NIP dan Kode OTP wajib diisi.']);
        }

        $adminUser = $this->sidangAdminModel->findByNip($nip);

        // 2. Cek User
        if (!$adminUser || empty($adminUser['sidang_2fa_secret']) || $adminUser['is_active'] == 0) {
            return redirect()->to(site_url('sidang/access'))->with('error', 'NIP tidak terdaftar untuk akses sidang atau belum dikonfigurasi.');
        }

        $secret_key = $adminUser['sidang_2fa_secret'];

        try {
            $otp = TOTP::create($secret_key);
            $window = 2;

            // 3. Verifikasi OTP
            if ($otp->verify($otp_code, null, $window)) {
                session()->set([
                    'isLoggedInSidang' => true, 
                    'sidang_nip' => $adminUser['nip'],
                    'userId' => $adminUser['id'] 
                ]);
                //return $this->response->setJSON([
                    //'status' => true, 
                    //'redirect' => site_url('sidang'), 
                    //'message' => 'Akses berhasil.'
                //]); 
                return redirect()->to(site_url('sidang'));

            } else {
                return redirect()->to(site_url('sidang/access'))->with('error', 'Kode OTP tidak valid atau sudah kadaluarsa. Coba lagi.');
            }
        } catch (\Throwable $e) {
            log_message('critical', 'Verifikasi OTP Gagal: ' . $e->getMessage());
             return redirect()->to(site_url('sidang/access'))->with('error', 'Terjadi kesalahan saat memverifikasi OTP. Hubungi administrator.');
        }
    }


    /**
     * HALAMAN UTAMA (INDEX) - MEMERLUKAN FILTER 'sidang_auth'
     */
    public function index()
    {
        // Cek redundan login
        if (!session()->get('isLoggedInSidang')) {
             return redirect()->to(site_url('sidang/access'));
        }
        $nipUser = session()->get('sidang_nip');
        $namaPegawai = '-';
        // Ambil nama pegawai dari sidang_admins
        $adminUser = $this->sidangAdminModel->where('nip', $nipUser)->first();
        if ($adminUser) {
            $namaPegawai = $adminUser['nama_pegawai'] ?? '-';
        }
        // 🛑 KOREKSI 2: Logic pengambilan Settings yang Aman di index()
        $settingsData = [
            'nama_aplikasi' => 'APP SIDANG',
            'nama_instansi' => 'INSTANSI ERROR',
            'path_logo_instansi' => 'images/logo_kejaksaan.png',
            'alamat'        => '-',
            'nip _user'      => session()->get('sidang_nip'),
        ];
        

        if ($this->setting !== null) {
             try {
                // Menggunakan method get() dan null coalescing untuk keamanan
                $settingsData['nama_aplikasi'] = $this->setting->get('nama_aplikasi') ?? $settingsData['nama_aplikasi'];
                $settingsData['nama_instansi'] = $this->setting->get('nama_instansi') ?? $settingsData['nama_instansi']; 
                $settingsData['path_logo_instansi'] = $this->setting->get('logo') ?? $settingsData['path_logo_instansi']; 
                $settingsData['alamat'] = $this->setting->get('alamat') ?? $settingsData['alamat'];
            } catch (\Throwable $e) {
                // Jika error terjadi saat GET (misalnya query database saat get), pakai default
            }
        }
        
        // 1. Ambil Semua Tanggal Unik yang Ada (untuk Dropdown)
        $queryTanggal = $this->sidangModel->select('tanggal_sidang')->distinct()->orderBy('tanggal_sidang', 'DESC')->findAll();
        
        $listTanggal = [];
        $latestDate = null; 

        foreach ($queryTanggal as $row) {
            $rawDate = $row['tanggal_sidang']; 

            if (!empty($rawDate) && $rawDate !== '0000-00-00') {
                try {
                    // Pastikan format tanggal aman sebelum diformat
                    if (\DateTime::createFromFormat('Y-m-d', $rawDate) !== false) {
                        $formattedDate = date('d-m-Y', strtotime($rawDate)); 
                        if ($latestDate === null) {
                            $latestDate = $rawDate; 
                        }
                        $listTanggal[] = $formattedDate; 
                    }
                } catch (\Exception $e) {
                    continue; 
                }
            }
        }
        
        $cleanedData = []; 

        // 2. Kirim Data ke View 
        $data = [
            'title'         => 'Cetak Sidang - ' . $settingsData['nama_aplikasi'],
            'nama_instansi_app' => $settingsData['nama_instansi'], 
            'path_logo_instansi' => $settingsData['path_logo_instansi'],         
            'alamat'        => $settingsData['alamat'], 

            'opt_tanggal'   => $listTanggal, 
            'all_data'      => $cleanedData, 


            'nip_user'      => $nipUser,
            'nama_pegawai'  => $namaPegawai,
            'selected_date' => null 
        ];

        return view('\App\Modules\Sidang\Views\sidang_view', $data);
    }
    
    /**
     * API: Mengambil data sidang full.
     */
    public function apiData(): ResponseInterface
    {
        $tanggalFilter = $this->request->getGet('tanggal');
        
        $query = $this->sidangModel->orderBy('tanggal_sidang', 'ASC');

        if (empty($tanggalFilter)) {
            return $this->response->setJSON(['status' => 200, 'total' => 0, 'data' => []]);
        }

        // 🛑 KOREKSI 3: Konversi tanggal dari DD-MM-YYYY ke Database YYYY-MM-DD
        $dbFormatDate = null;
        try {
            // Tanggal masuk DD-MM-YYYY dari JS
            $dateObj = \DateTime::createFromFormat('d-m-Y', $tanggalFilter);
            
            if ($dateObj && $dateObj->format('d-m-Y') === $tanggalFilter) { 
                $dbFormatDate = $dateObj->format('Y-m-d');
            } else {
                return $this->response->setJSON(['status' => 400, 'message' => 'Format tanggal filter tidak valid.']);
            }
            $query->where('tanggal_sidang', $dbFormatDate);
        } catch (\Throwable $e) {
            return $this->response->setJSON(['status' => 400, 'message' => 'Kesalahan parsing tanggal.']);
        }


        $allData = $query->findAll();
        $cleanedData = [];

        foreach ($allData as $row) {
            $row['nama_bersih'] = $this->cleanNamaTerdakwa($row['nama_terdakwa']);
            $row['data_full'] = json_decode($row['data_full'], true);
            $row['tanggal_sidang_format'] = $tanggalFilter; 

            $cleanedData[] = $row;
        }

        return $this->response->setJSON(['status' => 200, 'total' => count($cleanedData), 'data' => $cleanedData]);
    }


    /**
     * FITUR SINKRONISASI (SYNC) - MEMERLUKAN FILTER 'sidang_auth'
     * 💥 LOGIC DIUBAH: Mengambil data dari SIDANG HARI INI dan DATA_MASTER Google Sheets
     */
/**
     * FITUR SINKRONISASI (SYNC) - KUNCI MATCH & RANGE SHEET KOREKSI TOTAL
     */
    public function sync()
    {
        // 1. Ambil Data Mentah dari Google Sheet
        try {
            // 🛑 KOREKSI 1: SIDANG HARI INI range diubah dari A2:D menjadi A2:F
            $sidangSheet = $this->fetchSheet('SIDANG HARI INI!A2:F'); 
            
            // 🛑 KOREKSI 2: DATA_MASTER range diubah dari A2:M menjadi A2:R (Koreksi sebelumnya)
            $masterSheet = $this->fetchSheet('DATA_MASTER!A2:R'); 
        } catch (\Throwable $e) {
            log_message('error', 'Gagal Koneksi Google Sheet di Sync: ' . $e->getMessage());
            return redirect()->to('sidang')->with('error', "Gagal koneksi ke Google Sheet. Hubungi administrator. Pesan: " . $e->getMessage());
        }

        // 2. Buat Kamus Data Master (masterMap)
        $masterMap = [];
        foreach ($masterSheet as $rowM) {
            // Index 0 adalah NOMOR PERKARA
            $masterMap[$rowM[0]] = $rowM;
        }

        $countInsert = 0;
        $countUpdate = 0;
        $countSkip = 0;

        // 3. Proses Simpan ke Database
        if (!empty($sidangSheet)) {
            foreach ($sidangSheet as $rowS) {
                // Pengambilan data dari SIDANG HARI INI (Index 0 sampai 5)
                $noPerkara     = $rowS[0] ?? null;
                $namaRaw       = $rowS[1] ?? '-';
                $jenisPerkara  = $rowS[2] ?? '-';
                $agendaRaw     = $rowS[3] ?? '-';
                $jpu           = $rowS[4] ?? '-'; // Kolom E
                $statusSidang  = $rowS[5] ?? '-'; // Kolom F
                
                // Asumsi: Tanggal Sidang adalah hari ini (dari sheet SIDANG HARI INI)
                $tglSidang = date('Y-m-d'); 
                
                // Filter Ghost Rows dan Clean Nama
                if (empty($noPerkara)) continue;
                $nama = $this->cleanNamaTerdakwa($namaRaw);
                
                // Cari Biodata di Master
                $rowM = $masterMap[$noPerkara] ?? [];

                // LOGIC PEMECahan Tempat, Tanggal Lahir (KOLOM K / Index 10 di DATA_MASTER)
                $ttlRaw = $rowM[10] ?? null; 
                $tempatLahir = '-';
                $tglLahir = '-';
                
                if ($ttlRaw && strpos($ttlRaw, ',') !== false) {
                    $parts = explode(',', $ttlRaw, 2); 
                    $tempatLahir = trim($parts[0]);
                    $tglLahir = trim($parts[1] ?? '-');
                } else {
                    $tempatLahir = trim($ttlRaw ?? '-');
                    $tglLahir = '-';
                }

                // 💥 KOREKSI TOTAL PEMETAAN KOLOM DATA_MASTER BERDASARKAN INDEX A-R
                $dataFull = [
                    // Hasil Pemecahan Gabungan (KOLOM K / Index 10)
                    'tempat_lahir'    => $tempatLahir, 
                    'tgl_lahir'       => $tglLahir,       
                    
                    // Kolom L / Index 11
                    'umur'            => $rowM[11] ?? '-', 
                    
                    // Kolom M / Index 12
                    'jenis_kelamin'   => $rowM[12] ?? '-', 
                    
                    // Kolom N / Index 13
                    'kewarganegaraan' => $rowM[13] ?? 'Indonesia', 
                    
                    // Kolom O / Index 14
                    'alamat'          => $rowM[14] ?? '-',    
                    
                    // Kolom P / Index 15
                    'agama'           => $rowM[15] ?? '-',    
                    
                    // Kolom Q / Index 16 (PENDIDIKAN)
                    'pendidikan'      => $rowM[16] ?? '-',   
                    
                    // Kolom R / Index 17 (PEKERJAAN)
                    'pekerjaan'       => $rowM[17] ?? '-',   
                    
                    // Kolom J / Index 9 (NAMA ORANG TUA)
                    'nama_ortu'       => $rowM[9] ?? '-',   
                    
                    // Ambil agenda dari SIDANG HARI INI
                    'agenda_raw'      => $agendaRaw,   
                    
                    // Tambahan kolom dari SIDANG HARI INI
                    'jenis_perkara'   => $jenisPerkara,
                    'status_sidang'   => $statusSidang,
                ];

                // 🛑 KOREKSI 3: Cek apakah data sudah ada di DB berdasarkan No Perkara DAN Nama Terdakwa
                $existing = $this->sidangModel
                                 ->where('nomor_perkara', $noPerkara)
                                 ->where('nama_terdakwa', $nama) // Match Key baru
                                 ->first(); 

                $saveData = [
                    'tanggal_sidang' => $tglSidang, 
                    'nomor_perkara'  => $noPerkara,
                    'nama_terdakwa'  => $nama, 
                    'jpu'            => $jpu,
                    // Simpan data_full dengan semua info biodata dan info sidang tambahan
                    'data_full'      => json_encode($dataFull), 
                ];

                if ($existing) {
                    // Jika data sudah ada (cocok No. Perkara & Nama), lakukan UPDATE
                    $this->sidangModel->update($existing['id'], $saveData);
                    $countUpdate++;
                } else {
                    // Jika tidak cocok, lakukan INSERT baru
                    $this->sidangModel->insert($saveData);
                    $countInsert++;
                }
            }
        }

        $this->updateTanggalSidangDropdown();

        return redirect()->to('sidang')->with('success', "Sinkronisasi Selesai! Data Baru: $countInsert, Update: $countUpdate, Skip: $countSkip");
    }
    /**
     * FITUR PROSES CETAK - MEMERLUKAN FILTER 'sidang_auth'
     */
    public function proses()
    {
        // 1. Ambil Input User
        $selectedNoPerkara = $this->request->getPost('pilih_data');
        $docTypes = $this->request->getPost('jenis_dokumen');
        $mode = $this->request->getPost('mode_cetak');
        $tanggalTerpilih = $this->request->getPost('tanggal_terpilih');

        // 2. AMBIL DATA KONFIGURASI DARI MODAL (View)
        $customInstansi = $this->request->getPost('custom_instansi') ?: 'KEJAKSAAN NEGERI';
        $customKota     = $this->request->getPost('custom_kota') ?: 'Indonesia';
        $ttdJabatan     = $this->request->getPost('ttd_jabatan'); 
        $ttdNama        = $this->request->getPost('ttd_nama');
        $ttdNip         = $this->request->getPost('ttd_nip');

        // Validasi Tanggal (Wajib ada untuk semua mode)
        if (empty($tanggalTerpilih)) {
            return redirect()->back()->with('error', 'Tanggal sidang belum dipilih.');
        }

        // 3. Setup Folder Backup
        $hariIni = date('Y-m-d');
        $pathArsip = WRITEPATH . 'arsip_sidang';
        $folderBackup = $pathArsip . DIRECTORY_SEPARATOR . $hariIni;

        if (!is_dir($pathArsip)) mkdir($pathArsip, 0777, true);
        if (!is_dir($folderBackup)) mkdir($folderBackup, 0777, true);

        $generatedFiles = [];
        $targetData = [];

        // Konversi tanggal UI (d-m-Y) ke DB (Y-m-d)
        $dbFormatDate = \DateTime::createFromFormat('d-m-Y', $tanggalTerpilih)->format('Y-m-d');

        // ==========================================================
        // 🔥 LOGIC BARU: CONTROLLER IS THE BOSS
        // ==========================================================

        // Cek apakah P-38 dipilih?
        $isP38 = (is_array($docTypes) && in_array('p38', $docTypes));
        
        // Jika tombol Hijau (full_p38) ATAU Checkbox P-38 dicentang
        if ($mode == 'full_p38' || $isP38) {

            // PAKSA AMBIL SEMUA DATA (Abaikan checklist manual)
            $targetData = $this->sidangModel->where('tanggal_sidang', $dbFormatDate)->findAll();
            
            // Jika masuk lewat tombol hijau, pastikan doctypes di-set minimal p38
            if ($mode == 'full_p38') {
                $docTypes = ['p38']; 
            }
            
        } else {
            // Mode Normal (Hanya P-37 atau lainnya) -> WAJIB CHECKLIST
            if(empty($selectedNoPerkara)) return redirect()->back()->with('error', 'Pilih minimal satu data Terdakwa!');
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

            // LOGIKA PENENTUAN PEJABAT TTD (Fitur Modal Tadi)
            // 1. Jika User input di Modal -> Pakai input Modal
            // 2. Jika Kosong -> Pakai Default (JPU perkara tersebut)
            $finalTtdNama = !empty($ttdNama) ? $ttdNama : $row['jpu'];
            $finalTtdNip  = !empty($ttdNip) ? $ttdNip : (session()->get('sidang_nip') ?? '-');
            $finalTtdJabatan = !empty($ttdJabatan) ? $ttdJabatan : 'PENUNTUT UMUM'; // Default jabatan

            // FORMAT TANGGAL
            // JSON: "tanggal_sidang": "2025-12-18" -> Jadi: "Kamis, 18 Desember 2025"
            $hariSidangIndo = $this->formatTanggalIndo($row['tanggal_sidang']);
            
            // Tanggal Surat (Hari Ini)
            $tglSuratIndo = $this->formatTglSaja(date('Y-m-d'));

            $dataRow = [

                // DATA INSTANSI & KOP (Dari Modal)
                'nama_instansi'   => strtoupper($customInstansi),

                // --- 2. DATA UTAMA (Dari Tabel DB Langsung) ---
                'nomor_perkara'   => $row['nomor_perkara'],
                'nama_terdakwa'   => $this->cleanNamaTerdakwa($row['nama_terdakwa']),
                'jpu'             => $row['jpu'], // JPU Asli (untuk bagian "Menghadap Kepada")
                'hari_sidang'     => $hariSidangIndo, // Hasil convert helper

                // --- 3. DETAIL BIODATA (Dari JSON data_full) ---
                // Menggunakan null coalescing (??) agar tidak error jika data kosong
                'tempat_lahir'    => $details['tempat_lahir'] ?? '-',
                'tgl_lahir'       => $details['tgl_lahir'] ?? '-',
                'umur'            => $details['umur'] ?? '-',
                'jenis_kelamin'   => $details['jenis_kelamin'] ?? '-',
                'kewarganegaraan' => $details['kewarganegaraan'] ?? 'Indonesia',
                'alamat'          => $details['alamat'] ?? '-',
                'agama'           => $details['agama'] ?? '-',
                'pekerjaan'       => $details['pekerjaan'] ?? '-',
                'pendidikan'      => $details['pendidikan'] ?? '-',
                'nama_ortu'       => $details['nama_ortu'] ?? '-',
                                
                // --- 4. AGENDA ---
                // Di template tertulis: "perkara tindak Pidana ${agenda}"
                // JSON punya 'agenda_raw' ("Tuntutan") dan 'jenis_perkara' ("Lain-Lain")
                // Pilih salah satu yang cocok untuk template.
                'jenis_perkara'   => $details['jenis_perkara'] ?? 'Pidana Umum', // Default jika kosong
                'agenda'          => $details['agenda_raw'] ?? '-',

                // --- 5. TANDA TANGAN (Footer) ---
                // PENTING: Template harus diubah variabelnya agar fitur Ganti Pejabat jalan
                'kota_surat'      => $customKota,
                'tanggal_surat'   => $tglSuratIndo, // Isinya cuma tanggal: "18 Desember 2025"

                'ttd_nama'        => $finalTtdNama,     // Variabel baru untuk TTD
                'ttd_nip'         => $finalTtdNip,      // Variabel baru untuk NIP TTD
                'ttd_jabatan'     => $finalTtdJabatan,  // Variabel baru untuk Jabatan

                // Backup jika template belum diubah (masih pakai ${nip_jpu})
                'nip_jpu'         => $finalTtdNip,
            ];

            // Bersihkan nama file
            $cleanName = preg_replace('/[^A-Za-z0-9 \-]/', '', $row['nama_terdakwa']);
            $cleanName = substr($cleanName, 0, 50);

            if (is_array($docTypes) && in_array('p37', $docTypes)) {
                $this->generateDoc('template_p37.docx', $dataRow, "P37_{$cleanName}.docx", $folderBackup, $generatedFiles);
            }
            if (is_array($docTypes) && in_array('p38', $docTypes)) {
                $this->generateDoc('template_p38.docx', $dataRow, "P38_{$cleanName}.docx", $folderBackup, $generatedFiles);
            }
        }

        // 5. Packing & Download
        $totalFiles = count($generatedFiles);
        if ($totalFiles === 0) return redirect()->back()->with('error', 'Gagal generate file.');

        if ($totalFiles === 1) {
            $singleFile = reset($generatedFiles);
            return $this->response->download($singleFile, null)->setFileName(basename($singleFile));
        } 
        else {
            $zip = new \ZipArchive();
            $zipName = $folderBackup . DIRECTORY_SEPARATOR . 'Berkas_Sidang_' . time() . '.zip';

            if ($zip->open($zipName, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) === TRUE) {
                foreach ($generatedFiles as $fname => $fpath) {
                    if (file_exists($fpath)) $zip->addFile($fpath, $fname);
                }
                $zip->close();
            }
            
            return $this->response->download($zipName, null);
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
    public function logout()
        {
            // Hapus sesi spesifik Modul Sidang
            session()->remove(['isLoggedInSidang', 'sidang_nip', 'userId']);
            
            // Redirect user kembali ke halaman login 2FA
            return redirect()->to(site_url('sidang/access'))->with('success', 'Anda berhasil keluar dari sesi sidang.');
        }


        // helper format tanggal dan hari di indonesia 
        
    // HELPER: Format Tanggal Indonesia (Hari, d F Y)
    private function formatTanggalIndo($tanggal)
    {
        if (empty($tanggal) || $tanggal == '0000-00-00') return '-';
        
        $hari = [
            'Sunday' => 'Minggu', 'Monday' => 'Senin', 'Tuesday' => 'Selasa',
            'Wednesday' => 'Rabu', 'Thursday' => 'Kamis', 'Friday' => 'Jumat', 'Saturday' => 'Sabtu'
        ];
        $bulan = [
            1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
            'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
        ];

        $timestamp = strtotime($tanggal);
        $namaHari = $hari[date('l', $timestamp)];
        $tgl = date('d', $timestamp);
        $bln = $bulan[(int)date('m', $timestamp)];
        $thn = date('Y', $timestamp);

        return "$namaHari, $tgl $bln $thn";
    }

    // HELPER: Format Tanggal Saja (d F Y) untuk TTD
    private function formatTglSaja($tanggal)
    {
        if (empty($tanggal)) return date('d F Y');
        $bulan = [
            1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
            'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
        ];
        $timestamp = strtotime($tanggal);
        return date('d', $timestamp) . ' ' . $bulan[(int)date('m', $timestamp)] . ' ' . date('Y', $timestamp);
    }

    // HELPER BARU: Update cache tanggal unik di index()
    private function updateTanggalSidangDropdown()
    {
        // Fungsi ini dipanggil setelah sync. 
        // Saat ini, kita biarkan kosong karena index() mengambil data langsung dari DB.
        // Jika di masa depan Anda ingin membuat caching, logic-nya ada di sini.
        return true;
    }
}