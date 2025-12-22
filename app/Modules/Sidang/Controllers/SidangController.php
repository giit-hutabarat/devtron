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
        // 1. Hapus tag HTML berbahaya (Security)
        $rawName = strip_tags($rawName); 
        
        // 2. Logic Bisnis Anda (Als, Bin, dkk)
        $keywords = [' als ', ' alias ', ' bin ', ' dkk '];
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
        } else {
            $cleanName = $rawName;
        }
        
        // Hapus karakter aneh non-printable
        return trim(preg_replace('/[\x00-\x1F\x7F]/', '', $cleanName));
    }
    
    private function fetchSheet($range)
    {
        $client = new Client();
        // Gunakan env() untuk memanggil rahasia
        $jsonFile = env('GOOGLE_AUTH_JSON', 'json-by2025.json'); 
        $client->setAuthConfig(WRITEPATH . '/' . $jsonFile);
        
        $client->addScope(Sheets::SPREADSHEETS_READONLY);
        $service = new Sheets($client);
        
        $spreadsheetId = env('GOOGLE_SHEET_ID'); // Ambil dari env
        
        if (empty($spreadsheetId)) {
            throw new \Exception("Google Spreadsheet ID belum disetting di .env");
        }
        
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
                    'isLoggedInSidang' => true,           // Penanda Login Utama
                    'sidang_nip'       => $adminUser['nip'], // (Legacy) Tetap simpan buat controller ini
                    'nip_user'         => $adminUser['nip'], // (PENTING) Buat deteksi di Home.php
                    'nama_pegawai'     => $adminUser['nama_pegawai'], // (PENTING) Buat sapaan di Home.php
                    'userId'           => $adminUser['id']
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
            'nip_user'      => session()->get('sidang_nip'),
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

                        // --- NEW LOGIC WORKING DAYS --//
                        $timestamp = strtotime($rawDate);
                        $hariKe = date('N', $timestamp);//
                        
                        // jika hari sabtu / minggu (6-7)
                        if ($hariKe >= 6){
                            continue;

                        }
                        $formattedDate = date('d-m-Y', $timestamp); 
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


        // 1. Siapkan Default Value
        $namaInstansiApp = $settingsData['nama_instansi'] ?? 'INSTANSI ERROR';
        $pathLogoDb      = $settingsData['path_logo_instansi'] ?? 'images/default_logo.png';
        
        // 2. Logic Manipulasi Path Logo (Membersihkan Slash URL)
        // Kita lakukan di sini agar View tinggal terima jadi
        $baseUrlClean  = rtrim(base_url(), '/'); 
        $pathLogoFinal = $baseUrlClean . '/' . ltrim($pathLogoDb, '/');

        // 2. Kirim Data ke View 
        $data = [
            'title'         => 'Cetak Sidang - ' . ($settingsData['nama_aplikasi'] ?? 'APP'),
            // Data Bersih untuk View
            'nama_instansi_app'  => $namaInstansiApp, 
            'path_logo'          => $pathLogoFinal, // View tidak perlu mikir path lagi
            'alamat'             => $settingsData['alamat'] ?? '-', 

            'opt_tanggal'        => $listTanggal, 
            'all_data'           => [], // Default kosong

            'nip_user'           => $nipUser,
            'nama_pegawai'       => $namaPegawai,
            'selected_date'      => null,

            // Kirim base_url explicit untuk JavaScript
            'base_url_app'       => base_url()
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

    public function proses()
    {
        // --- 1. SECURITY VALIDATION ---
        if (!$this->validate([
            'tanggal_terpilih' => 'required|valid_date[d-m-Y]',
            'mode_cetak'       => 'required|in_list[seleksi,full_p38,p37_seleksi]',
            // Pastikan pilih_data adalah array jika dikirim
            'pilih_data'       => 'permit_empty', 
        ])) {
            return redirect()->back()->with('error', 'Data input tidak valid / manipulasi terdeteksi.');
        }
        // 1. AMBIL INPUT DASAR
        $selectedNoPerkara = $this->request->getPost('pilih_data');
        $docTypes = $this->request->getPost('jenis_dokumen');
        $mode = $this->request->getPost('mode_cetak');
        $tanggalTerpilih = $this->request->getPost('tanggal_terpilih');

        // 2. DATA MODAL KONFIGURASI
        $customInstansi   = $this->request->getPost('custom_instansi') ?: 'KEJAKSAAN NEGERI';
        $customKota       = $this->request->getPost('custom_kota') ?: 'Indonesia';
        $customNomorSurat = $this->request->getPost('custom_nomor_surat') ?: 'B-......./.......'; 
        $ttdJabatan       = $this->request->getPost('ttd_jabatan'); 
        $ttdNama          = $this->request->getPost('ttd_nama');
        $ttdNip           = $this->request->getPost('ttd_nip');

        if (empty($tanggalTerpilih)) return redirect()->back()->with('error', 'Tanggal sidang belum dipilih.');

        // 3. SETUP FOLDER
        $hariIni = date('Y-m-d');
        $pathArsip = WRITEPATH . 'arsip_sidang';
        $folderBackup = $pathArsip . DIRECTORY_SEPARATOR . $hariIni;
        if (!is_dir($pathArsip)) mkdir($pathArsip, 0777, true);
        if (!is_dir($folderBackup)) mkdir($folderBackup, 0777, true);

        $generatedFiles = [];
        $targetData = [];
        $dbFormatDate = \DateTime::createFromFormat('d-m-Y', $tanggalTerpilih)->format('Y-m-d');
        
        // Format Tanggal untuk Nama File
        $fileDateStr = str_replace('/', '-', $tanggalTerpilih); 

        // 4. LOGIC PENGAMBILAN DATA
        $isP38 = (is_array($docTypes) && in_array('p38', $docTypes));
        
        if ($mode == 'full_p38') {
            $targetData = $this->sidangModel->where('tanggal_sidang', $dbFormatDate)->findAll();
            if (!is_array($docTypes)) $docTypes = [];
            if (!in_array('p38', $docTypes)) $docTypes[] = 'p38';
        } else {
            if(empty($selectedNoPerkara)) return redirect()->back()->with('error', 'Pilih minimal satu data Terdakwa!');
            if(empty($docTypes)) return redirect()->back()->with('error', 'Pilih jenis dokumen!');
            $targetData = $this->sidangModel->whereIn('nomor_perkara', $selectedNoPerkara)->findAll();
        }

        if (empty($targetData)) return redirect()->back()->with('error', 'Data tidak ditemukan di database.');

        // ==========================================================
        // PROSES 1: GENERATE P-37 (Satu per satu)
        // ==========================================================
        if (is_array($docTypes) && in_array('p37', $docTypes)) {
            foreach ($targetData as $row) {
                $details = json_decode($row['data_full'], true);
                
                $finalTtdNama = !empty($ttdNama) ? $ttdNama : $row['jpu'];
                $finalTtdNip  = !empty($ttdNip) ? $ttdNip : (session()->get('sidang_nip') ?? '-');
                $finalTtdJabatan = !empty($ttdJabatan) ? $ttdJabatan : 'PENUNTUT UMUM';
                
                $hariSidangIndo = $this->formatTanggalIndo($row['tanggal_sidang']);
                $tglSuratIndo   = $this->formatTglSaja(date('Y-m-d'));
                $instansiValue  = strtoupper($customInstansi);

                $dataRow = [
                    'nama_instansi'   => $instansiValue,
                    'kota_surat'      => $customKota,
                    'tanggal_surat'   => $tglSuratIndo,
                    'nomor_perkara'   => $row['nomor_perkara'],
                    'nama_terdakwa'   => $this->cleanNamaTerdakwa($row['nama_terdakwa']),
                    'jpu'             => $row['jpu'],
                    'hari_sidang'     => $hariSidangIndo,
                    'jenis_perkara'   => $details['jenis_perkara'] ?? 'Pidana Umum',
                    'agenda'          => $details['agenda_raw'] ?? '-', 
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
                    'ttd_nama'        => $finalTtdNama,
                    'ttd_nip'         => $finalTtdNip,
                    'ttd_jabatan'     => $finalTtdJabatan,
                    'TTD_NAMA'        => $finalTtdNama,
                    'TTD_NIP'         => $finalTtdNip,
                    'TTD_JABATAN'     => $finalTtdJabatan,
                    'NAMA_INSTANSI'   => $instansiValue,
                ];

                $cleanName = preg_replace('/[^A-Za-z0-9 \-]/', '', $this->cleanNamaTerdakwa($row['nama_terdakwa']));
                $cleanName = substr($cleanName, 0, 50);
                $fileNameP37 = "P37-{$cleanName}-{$fileDateStr}.docx";
                
                $this->generateDoc('template_p37.docx', $dataRow, $fileNameP37, $folderBackup, $generatedFiles);
            }
        }

        // ==========================================================
        // PROSES 2: GENERATE P-38 (Satu file Tabel)
        // ==========================================================
        if (is_array($docTypes) && in_array('p38', $docTypes)) {
            
            $tableRows = [];
            $no = 1;

            $firstRow = $targetData[0];
            $p38Instansi   = strtoupper($customInstansi);
            $p38Kota       = $customKota;
            $p38TglSurat   = $this->formatTglSaja(date('Y-m-d'));
            

            // LOGIC PECAH HARI DAN TANGGAL
            $timestampSidang = strtotime($firstRow['tanggal_sidang']);
            // Array Helper Hari & Bulan
            $hariArr = [
                'Sunday' => 'Minggu', 'Monday' => 'Senin', 'Tuesday' => 'Selasa',
                'Wednesday' => 'Rabu', 'Thursday' => 'Kamis', 'Friday' => 'Jumat', 'Saturday' => 'Sabtu'
            ];
            $bulanArr = [
                1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
                'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
            ];

            // Variabel Terpisah
            $namaHariSaja   = $hariArr[date('l', $timestampSidang)]; // Contoh: "Senin"
            $tanggalSaja    = date('d', $timestampSidang) . ' ' . $bulanArr[(int)date('m', $timestampSidang)] . ' ' . date('Y', $timestampSidang); // Contoh: "15 Desember 2025"
            $formatLengkap  = "$namaHariSaja, $tanggalSaja"; // Contoh: "Senin, 15 Desember 2025"

            // END LOGIC PISAH HARI DAN TANGGAL
            
            $p38TtdNama    = !empty($ttdNama) ? $ttdNama : $firstRow['jpu']; 
            $p38TtdNip     = !empty($ttdNip) ? $ttdNip : (session()->get('sidang_nip') ?? '-');
            $p38TtdJabatan = !empty($ttdJabatan) ? $ttdJabatan : 'PENUNTUT UMUM';
            $namaTerdakwaPertama = $this->cleanNamaTerdakwa($firstRow['nama_terdakwa']);

            foreach ($targetData as $row) {
                $det = json_decode($row['data_full'], true);
                
                // --- PERBAIKAN 1: UNCOMMENT AGENDA ---
                $tableRows[] = [
                    'no'            => $no++,
                    'nomor_perkara' => $row['nomor_perkara'],
                    'nama_terdakwa' => $this->cleanNamaTerdakwa($row['nama_terdakwa']),
                    'jpu'           => $row['jpu'],
                    'status_sidang' => $det['status_sidang'] ?? '-',
                    'jenis_perkara' => $det['jenis_perkara'] ?? '-',
                    'agenda'        => $det['agenda_raw'] ?? '-' // <--- INI SUDAH DIAKTIFKAN
                ];
            }

            $tplPath = WRITEPATH . 'templates/template_p38.docx';
            if (file_exists($tplPath)) {
                $proc = new TemplateProcessor($tplPath);

                $proc->setValue('nomor_surat', $customNomorSurat);
                $proc->setValue('NOMOR_SURAT', $customNomorSurat);
                $proc->setValue('nama_instansi', $p38Instansi);
                $proc->setValue('NAMA_INSTANSI', $p38Instansi);
                $proc->setValue('kota_surat', $p38Kota);
                $proc->setValue('tanggal_surat', $p38TglSurat);
                
                // --- PERBAIKAN FINAL DISINI ---
                // 1. Jika di Word pakai ${hari_sidang}, isinya cuma "Senin"
                $proc->setValue('hari_sidang', $namaHariSaja); 
                
                // 2. Jika di Word pakai ${tanggal_sidang}, isinya "15 Desember 2025"
                $proc->setValue('tanggal_sidang', $tanggalSaja);
                
                // 3. Handle Teks Manual (HARI SIDANG) & (TANGGAL SIDANG)
                $proc->setValue('(HARI SIDANG)', $namaHariSaja);
                $proc->setValue('(TANGGAL SIDANG)', $tanggalSaja);

                $proc->setValue('nama_terdakwa_1', $namaTerdakwaPertama); 

                $proc->setValue('TTD_NAMA', $p38TtdNama);
                $proc->setValue('TTD_NIP', $p38TtdNip);
                $proc->setValue('TTD_JABATAN', $p38TtdJabatan);
                $proc->setValue('ttd_nama', $p38TtdNama);
                $proc->setValue('ttd_nip', $p38TtdNip);
                $proc->setValue('ttd_jabatan', $p38TtdJabatan);

                //cetak lembar kedua
                $countRows = count($tableRows);
                $proc->cloneRow('no', $countRows); // Clone baris kosong dulu sejumlah data

                foreach ($tableRows as $index => $rowData) {
                    $rowIndex = $index + 1; // PHPWord index mulai dari 1 (untuk replace)
                    
                    // Replace variabel per baris (format: variabel#1, variabel#2, dst)
                    $proc->setValue('no#' . $rowIndex, $rowData['no']);
                    $proc->setValue('nomor_perkara#' . $rowIndex, $rowData['nomor_perkara']);
                    $proc->setValue('nama_terdakwa#' . $rowIndex, $rowData['nama_terdakwa']);
                    $proc->setValue('jpu#' . $rowIndex, $rowData['jpu']);
                    $proc->setValue('status_sidang#' . $rowIndex, $rowData['status_sidang']);
                    $proc->setValue('jenis_perkara#' . $rowIndex, $rowData['jenis_perkara']);
                    $proc->setValue('agenda#' . $rowIndex, $rowData['agenda']);
                }

                // =================================================
                // 2. PROSES TABEL HALAMAN 2 (Lampiran)
                //    Variabel BARU (Pake _2): ${no_2}, ${nomor_perkara_2}, dll
                // =================================================
                $proc->cloneRow('no_2', $countRows); // Clone berdasarkan variabel baru

                foreach ($tableRows as $index => $rowData) {
                    $rowIndex = $index + 1; 
                    // Isi kolom tabel Halaman 2 (Perhatikan akhiran _2)
                    $proc->setValue('no_2#' . $rowIndex, $rowData['no']);
                    $proc->setValue('nomor_perkara_2#' . $rowIndex, $rowData['nomor_perkara']);
                    $proc->setValue('nama_terdakwa_2#' . $rowIndex, $rowData['nama_terdakwa']);
                    $proc->setValue('jpu_2#' . $rowIndex, $rowData['jpu']);
                    $proc->setValue('status_sidang_2#' . $rowIndex, $rowData['status_sidang']);
                    $proc->setValue('jenis_perkara_2#' . $rowIndex, $rowData['jenis_perkara']);
                }

                // Naming Convention P-38
                $finalNameP38 = "";
                if (count($targetData) === 1) {
                    $cleanNameOne = preg_replace('/[^A-Za-z0-9 \-]/', '', $namaTerdakwaPertama);
                    $cleanNameOne = substr($cleanNameOne, 0, 50);
                    $finalNameP38 = "P38-{$cleanNameOne}-{$fileDateStr}.docx";
                } else {
                    $finalNameP38 = "P38-Sidang-{$fileDateStr}.docx";
                }

                $baseName = $finalNameP38;
                $c = 1;
                while(file_exists($folderBackup . DIRECTORY_SEPARATOR . $baseName)) {
                    $baseName = pathinfo($finalNameP38, PATHINFO_FILENAME) . "_($c)." . pathinfo($finalNameP38, PATHINFO_EXTENSION);
                    $c++;
                }

                $saveP = $folderBackup . DIRECTORY_SEPARATOR . $baseName;
                $proc->saveAs($saveP);
                $generatedFiles[$baseName] = $saveP;
            }
        }

        // 5. DOWNLOAD
        $totalFiles = count($generatedFiles);
        if ($totalFiles === 0) return redirect()->back()->with('error', 'Gagal generate file.');

        if ($totalFiles === 1) {
            $singleFile = reset($generatedFiles);
            $downloadName = array_key_first($generatedFiles); 
            return $this->response->download($singleFile, null)->setFileName($downloadName);
        } else {
            $zip = new \ZipArchive();
            $zipName = $folderBackup . DIRECTORY_SEPARATOR . 'Berkas_Sidang_' . $fileDateStr . '.zip';
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
        // Hapus SEMUA sesi yang kita buat tadi
        session()->remove(['isLoggedInSidang', 'sidang_nip', 'nip_user', 'nama_pegawai', 'userId']);
        
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