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
    public function sync()
    {
        // 1. Ambil Data Mentah dari Google Sheet
        try {
            // Asumsi: SIDANG HARI INI: Nomor Perkara, Nama Terdakwa, JPU, Tanggal Sidang
            $sidangSheet = $this->fetchSheet('SIDANG HARI INI!A2:D'); // A2 untuk skip header
            // Asumsi: DATA_MASTER: Nomor Perkara, Biodata Lengkap (Kolom E dst)
            $masterSheet = $this->fetchSheet('DATA_MASTER!A2:N'); // A2 untuk skip header, N asumsi kolom terakhir biodata
        } catch (\Throwable $e) {
            // 🛑 PENTING: Gagal koneksi Google Sheet
            log_message('error', 'Gagal Koneksi Google Sheet di Sync: ' . $e->getMessage());
            return redirect()->to('sidang')->with('error', "Gagal koneksi ke Google Sheet. Hubungi administrator. Pesan: " . $e->getMessage());
        }

        // 2. Buat Kamus Data Master (Kunci: Nomor Perkara / Index 0)
        $masterMap = [];
        if (!empty($masterSheet)) {
            foreach ($masterSheet as $mRow) {
                $key = isset($mRow[0]) ? trim($mRow[0]) : '';
                // Pastikan key tidak kosong dan tidak berisi header/sampah
                if ($key && stripos($key, 'nomor') === false) { 
                    $masterMap[$key] = $mRow;
                }
            }
        }

        $countInsert = 0;
        $countUpdate = 0;
        $countSkip = 0;

        // 3. Proses Simpan ke Database
        if (!empty($sidangSheet)) {
            foreach ($sidangSheet as $rowS) {
                // Asumsi mapping kolom dari SIDANG HARI INI:
                // [0] Nomor Perkara, [1] Nama Terdakwa, [2] JPU, [3] Tanggal Sidang (Format YYYY-MM-DD atau DD/MM/YYYY)

                // Bersihkan data
                $noPerkara = isset($rowS[0]) ? trim(preg_replace('/\s+/', ' ', $rowS[0])) : '';
                $nama      = isset($rowS[1]) ? trim(preg_replace('/\s+/', ' ', $rowS[1])) : '';
                $jpu       = isset($rowS[2]) ? trim($rowS[2]) : '-';
                $tglSidangRaw = isset($rowS[3]) ? trim($rowS[3]) : '';
                
                // Filter Ghost Rows (Data Kosong/Sampah)
                if ($noPerkara == '' || strlen($noPerkara) < 5 || stripos($noPerkara, 'Nomor') !== false) {
                    $countSkip++;
                    continue;
                }
                
                // Konversi Tanggal Sidang (Asumsi format YYYY-MM-DD atau D/M/YYYY)
                $tglSidang = date('Y-m-d'); 
                if (!empty($tglSidangRaw)) {
                    // Coba parsing YYYY-MM-DD
                    if (\DateTime::createFromFormat('Y-m-d', $tglSidangRaw) !== false) {
                        $tglSidang = $tglSidangRaw;
                    } 
                    // Coba parsing DD/MM/YYYY atau M/D/YYYY (format spreadsheet)
                    else {
                        $timestamp = strtotime($tglSidangRaw);
                        if ($timestamp !== false) {
                            $tglSidang = date('Y-m-d', $timestamp);
                        }
                    }
                }
                
                // Cari Biodata di Master
                $rowM = $masterMap[$noPerkara] ?? [];

                // 💥 KOREKSI PEMECANGAN DATA GABUNGAN
                // ASUMSI: Data gabungan "Tempat, Tanggal Lahir" ada di Index 3 ($rowM[3])
                
                $ttlRaw = $rowM[3] ?? null;
                $tempatLahir = '-';
                $tglLahir = '-';
                
                // Logic pemecah: Cari koma pertama (,)
                if ($ttlRaw && strpos($ttlRaw, ',') !== false) {
                    $parts = explode(',', $ttlRaw, 2); // Pecah maksimal 2 bagian
                    $tempatLahir = trim($parts[0]);
                    $tglLahir = trim($parts[1] ?? '-');
                } else {
                    // Jika tidak ada koma, asumsikan itu hanya Tempat Lahir
                    $tempatLahir = trim($ttlRaw ?? '-');
                }
                
                // Asumsi Mapping Kolom DATA_MASTER:
                // [0] No Perkara, [1] NAMA Terdakwa, [2] Alamat, [3] Tempat Lahir, [4] Tgl Lahir, [5] Umur, [6] JK, [7] Kewarganegaraan, [8] Alamat, [9] Agama, [10] Pekerjaan, [11] Pendidikan, [12] Nama Ortu, [13] Agenda Raw
                $dataFull = [
                    'tempat_lahir'    => $tempatLahir,
                    'tgl_lahir'       => $tglLahir,

                    // Perbaikan pemecahan TTL
                    'umur'            => $rowM[12] ?? '-',
                    'jenis_kelamin'   => $rowM[6] ?? '-',
                    'kewarganegaraan' => $rowM[7] ?? 'Indonesia',
                    'alamat'          => $rowM[8] ?? '-',
                    'agama'           => $rowM[9] ?? '-',
                    'pekerjaan'       => $rowM[10] ?? '-',
                    'pendidikan'      => $rowM[11] ?? '-',
                    'nama_ortu'       => $rowM[12] ?? '-',
                    'agenda_raw'      => $rowM[13] ?? '-', // Ambil Agenda dari Master jika ada
                ];

                // Cek apakah data sudah ada di DB?
                $existing = $this->sidangModel->where('nomor_perkara', $noPerkara)->first();

                $saveData = [
                    'tanggal_sidang' => $tglSidang, 
                    'nomor_perkara'  => $noPerkara,
                    'nama_terdakwa'  => $nama, // Ambil nama dari sheet sidang hari ini (lebih update)
                    'jpu'            => $jpu,
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

        // 4. Kumpulkan kembali semua tanggal unik dari database untuk update dropdown
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

        // 2. Setup Folder Backup
        $hariIni = date('Y-m-d');
        $pathArsip = WRITEPATH . 'arsip_sidang';
        $folderBackup = $pathArsip . DIRECTORY_SEPARATOR . $hariIni;

        if (!is_dir($pathArsip)) mkdir($pathArsip, 0777, true);
        if (!is_dir($folderBackup)) mkdir($folderBackup, 0777, true);

        $generatedFiles = [];
        $targetData = [];

        // 3. Ambil Data Target dari Database
        if ($mode == 'full_p38') {
            // Mode Tombol Hijau: Ambil semua data tanggal tsb
            $dbFormatDate = \DateTime::createFromFormat('d-m-Y', $tanggalTerpilih)->format('Y-m-d');
            $targetData = $this->sidangModel->where('tanggal_sidang', $dbFormatDate)->findAll();
            $docTypes = ['p38']; 
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
                'nama_terdakwa'   => $this->cleanNamaTerdakwa($row['nama_terdakwa']), 
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
    public function logout()
        {
            // Hapus sesi spesifik Modul Sidang
            session()->remove(['isLoggedInSidang', 'sidang_nip', 'userId']);
            
            // Redirect user kembali ke halaman login 2FA
            return redirect()->to(site_url('sidang/access'))->with('success', 'Anda berhasil keluar dari sesi sidang.');
        }
}