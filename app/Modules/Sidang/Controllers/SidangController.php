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
use Mpdf\Mpdf; // PENTING: Panggil library mPDF

class SidangController extends BaseController
{
    protected $sidangModel;
    protected $sidangAdminModel;
    protected $setting; 

    public function __construct()
    {
        date_default_timezone_set('Asia/Jakarta');
        $this->sidangModel = new SidangModel();
        try {
            $this->setting = new Settings(); 
        } catch (\Throwable $e) {
            $this->setting = null; 
        }
        $this->sidangAdminModel = new SidangAdminModel(); 
    }

    // --- HELPER FUNCTIONS ---
    private function cleanNamaTerdakwa(string $rawName): string
    {
        $rawName = strip_tags($rawName); 
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
        
        return trim(preg_replace('/[\x00-\x1F\x7F]/', '', $cleanName));
    }
    
    private function fetchSheet($range)
    {
        $client = new Client();
        $jsonFile = env('GOOGLE_AUTH_JSON', 'json-by2025.json'); 
        $client->setAuthConfig(WRITEPATH . '/' . $jsonFile);
        $client->addScope(Sheets::SPREADSHEETS_READONLY);
        $service = new Sheets($client);
        $spreadsheetId = env('GOOGLE_SHEET_ID');
        if (empty($spreadsheetId)) throw new \Exception("Google Spreadsheet ID belum disetting di .env");
        return $service->spreadsheets_values->get($spreadsheetId, $range)->getValues();
    }

    // --- AUTH & LOGIN ---
    public function accessForm()
    {
 
        if (session()->get('isLoggedInSidang')) return redirect()->to(site_url('sidang'));

        // Panggil proteksi sebelum render view
        $this->setSecureHeaders();
        
        $namaInstansi = 'NAMA INSTANSI DEFAULT';
        $logoPath = 'images/logo_kejaksaan.png';
        if ($this->setting !== null) {
            try {
                $namaInstansi = $this->setting->get('nama_instansi') ?? $namaInstansi;
                $logoPathDb = $this->setting->get('logo');
                if(!empty($logoPathDb)) $logoPath = $logoPathDb;
            } catch (\Throwable $e) {}
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
        
        if (empty($nip) || empty($otp_code)) {
            return redirect()->to(site_url('sidang/access'))->with('error', 'NIP dan Kode OTP wajib diisi.');
        }

        $adminUser = $this->sidangAdminModel->findByNip($nip);

        if (!$adminUser || empty($adminUser['sidang_2fa_secret']) || $adminUser['is_active'] == 0) {
            return redirect()->to(site_url('sidang/access'))->with('error', 'NIP tidak terdaftar atau belum dikonfigurasi.');
        }

        try {
            $otp = TOTP::create($adminUser['sidang_2fa_secret']);
            if ($otp->verify($otp_code, null, 2)) {
                session()->set([
                    'isLoggedInSidang' => true,
                    'sidang_nip'       => $adminUser['nip'],
                    'nip_user'         => $adminUser['nip'],
                    'nama_pegawai'     => $adminUser['nama_pegawai'],
                    'userId'           => $adminUser['id']
                ]);
                return redirect()->to(site_url('sidang'));
            } else {
                return redirect()->to(site_url('sidang/access'))->with('error', 'Kode OTP salah atau kadaluarsa.');
            }
        } catch (\Throwable $e) {
             return redirect()->to(site_url('sidang/access'))->with('error', 'Error Verifikasi: ' . $e->getMessage());
        }
    }

    public function logout()
    {
        session()->remove(['isLoggedInSidang', 'sidang_nip', 'nip_user', 'nama_pegawai', 'userId']);
        return redirect()->to(site_url('sidang/access'))->with('success', 'Berhasil logout.');
    }

    // --- MAIN PAGES ---
    public function index()
    {
        if (!session()->get('isLoggedInSidang')) return redirect()->to(site_url('sidang/access'));
        
        $nipUser = session()->get('sidang_nip');
        $namaPegawai = session()->get('nama_pegawai') ?? '-';

        // Setup Settings Default
        $settingsData = [
            'nama_aplikasi' => 'APP SIDANG',
            'nama_instansi' => 'KEJAKSAAN NEGERI',
            'path_logo_instansi' => 'images/logo_kejaksaan.png',
            'alamat'        => '-',
        ];

        // Ambil Settings dari DB jika ada
        if ($this->setting !== null) {
             try {
                $settingsData['nama_aplikasi'] = $this->setting->get('nama_aplikasi') ?? $settingsData['nama_aplikasi'];
                $settingsData['nama_instansi'] = $this->setting->get('nama_instansi') ?? $settingsData['nama_instansi']; 
                $settingsData['path_logo_instansi'] = $this->setting->get('logo') ?? $settingsData['path_logo_instansi']; 
                $settingsData['alamat'] = $this->setting->get('alamat') ?? $settingsData['alamat'];
            } catch (\Throwable $e) {}
        }

        $baseUrlClean  = rtrim(base_url(), '/'); 
        $pathLogoFinal = $baseUrlClean . '/' . ltrim($settingsData['path_logo_instansi'], '/');

        // KITA TIDAK PERLU LAGI VARIABEL $opt_tanggal 
        // Karena input date HTML5 sudah menangani kalender.
        
        $data = [
            'title'              => 'Cetak Sidang - ' . $settingsData['nama_aplikasi'],
            'nama_instansi_app'  => $settingsData['nama_instansi'], 
            'path_logo'          => $pathLogoFinal,
            'alamat'             => $settingsData['alamat'], 
            'nip_user'           => $nipUser,
            'nama_pegawai'       => $namaPegawai,
            'base_url_app'       => base_url()
        ];

        return view('\App\Modules\Sidang\Views\sidang_view', $data);
    }

    public function apiData(): ResponseInterface
    {
        $tanggalFilter = $this->request->getGet('tanggal');
        if (empty($tanggalFilter)) return $this->response->setJSON(['status' => 200, 'total' => 0, 'data' => []]);

        try {
            $dateObj = \DateTime::createFromFormat('d-m-Y', $tanggalFilter);
            if ($dateObj && $dateObj->format('d-m-Y') === $tanggalFilter) { 
                $dbFormatDate = $dateObj->format('Y-m-d');
            } else {
                return $this->response->setJSON(['status' => 400, 'message' => 'Format tanggal filter tidak valid.']);
            }
        } catch (\Throwable $e) {
            return $this->response->setJSON(['status' => 400, 'message' => 'Kesalahan parsing tanggal.']);
        }

        $allData = $this->sidangModel->where('tanggal_sidang', $dbFormatDate)->findAll();
        $cleanedData = [];

        foreach ($allData as $row) {
            $row['nama_bersih'] = $this->cleanNamaTerdakwa($row['nama_terdakwa']);
            $row['data_full'] = json_decode($row['data_full'], true);
            $row['tanggal_sidang_format'] = $tanggalFilter; 
            $cleanedData[] = $row;
        }

        return $this->response->setJSON(['status' => 200, 'total' => count($cleanedData), 'data' => $cleanedData]);
    }

    public function sync()
    {
        // 1. SETUP TANGGAL TARGET
        $reqTanggal = $this->request->getGet('tanggal');
        $tglHariIni = date('Y-m-d');
        $tglSidang  = $tglHariIni;

        if (!empty($reqTanggal)) {
            // Cek Format YYYY-MM-DD
            $d1 = \DateTime::createFromFormat('Y-m-d', $reqTanggal);
            if ($d1 && $d1->format('Y-m-d') === $reqTanggal) {
                $tglSidang = $reqTanggal;
            } else {
                // Cek Format DD-MM-YYYY
                $d2 = \DateTime::createFromFormat('d-m-Y', $reqTanggal);
                if ($d2 && $d2->format('d-m-Y') === $reqTanggal) {
                    $tglSidang = $d2->format('Y-m-d');
                } else {
                    return redirect()->to('sidang')->with('error', "Format tanggal URL salah.");
                }
            }
        }
        
        try {
        // 2. AMBIL DATA DARI GOOGLE SHEETS DULU (Sebelum transaksi DB dimulai)
        $masterSheet = $this->fetchSheet('DATA_MASTER!A2:Z');
        
        if (empty($masterSheet)) {
            return redirect()->to('sidang')->with('error', "Gagal Sinkron: Data Master di Google Sheets kosong.");
        }

        // 3. LOGIKA FILTER DATA (Pindahkan logika filter ke sini agar transaksi DB sesingkat mungkin)
        $masterMap = [];
        foreach ($masterSheet as $rowM) {
            if(isset($rowM[0]) && !empty($rowM[0])) {
                $masterMap[trim($rowM[0])] = $rowM;
            }
        }

        $sourceData = [];
        $srcLabel = "Data Master";
        // 4. LOGIKA PENCARIAN
        
        // OPSI A: Cek Sheet Harian (Jika tanggal target = hari ini)
        if ($tglSidang === $tglHariIni) {
            try {
                $sheetHarian = $this->fetchSheet('SIDANG HARI INI!A2:G'); 
                if (!empty($sheetHarian)) {
                    foreach ($sheetHarian as $rowS) {
                        $noPerkara = trim($rowS[0] ?? '');
                        if (empty($noPerkara)) continue;
                        $sourceData[] = [
                            'no_perkara' => $noPerkara,
                            'nama_raw'   => $rowS[1] ?? '-',
                            'jenis'      => $rowS[2] ?? '-',
                            'agenda'     => $rowS[3] ?? '-',
                            'jpu'        => $rowS[4] ?? '-',
                            'status'     => $rowS[5] ?? '-',
                            'source'     => 'harian'
                        ];
                    }
                    if(count($sourceData) > 0) $srcLabel = "Sheet Harian";
                }
            } catch (\Throwable $e) { }
        }

        // OPSI B: FILTER MANUAL DATA MASTER (Kuncinya disini!)
        if (empty($sourceData)) {
            $srcLabel = "Data Master";
            
            $idxTglSidang = 6;  // Kolom G
            $idxAgenda    = 3;  // Kolom D
            $idxJpu       = 4;  // Kolom E

            // KAMUS BULAN INDONESIA
            $bulanIndo = [
                'Januari' => '01', 'Februari' => '02', 'Maret' => '03', 'April' => '04', 
                'Mei' => '05', 'Juni' => '06', 'Juli' => '07', 'Agustus' => '08', 
                'September' => '09', 'Oktober' => '10', 'November' => '11', 'Desember' => '12'
            ];

            foreach ($masterSheet as $rowM) {
                $tglRaw = $rowM[$idxTglSidang] ?? ''; 
                $isMatch = false;

                if (!empty($tglRaw)) {
                    $tglRaw = trim($tglRaw);

                    // --- CARA 1: PARSING FORMAT INDONESIA ("Selasa, 23 Desember 2025") ---
                    // Hapus nama hari (ambil setelah koma, atau biarkan jika tidak ada koma)
                    $cleanDate = $tglRaw;
                    if (strpos($tglRaw, ',') !== false) {
                        $parts = explode(',', $tglRaw);
                        $cleanDate = trim(end($parts)); // Ambil bagian tanggalnya saja: "23 Desember 2025"
                    }

                    // Pecah Spasi: "23" "Desember" "2025"
                    $dateParts = explode(' ', $cleanDate);
                    
                    if (count($dateParts) == 3) {
                        $d = $dateParts[0]; // 23
                        $mText = $dateParts[1]; // Desember
                        $y = $dateParts[2]; // 2025

                        // Terjemahkan Bulan
                        if (isset($bulanIndo[$mText])) {
                            $m = $bulanIndo[$mText];
                            // Rakit jadi YYYY-MM-DD
                            $finalDate = "$y-$m-$d"; // 2025-12-23
                            
                            // Bandingkan format standar (tanggal & bulan 1 digit aman karena PHP handle 01 vs 1)
                            if (strtotime($finalDate) == strtotime($tglSidang)) {
                                $isMatch = true;
                            }
                        }
                    }

                    // --- CARA 2: SERIAL NUMBER (BACKUP) ---
                    if (!$isMatch && is_numeric($tglRaw) && $tglRaw > 20000) {
                        $unixDate = ($tglRaw - 25569) * 86400;
                        if (date('Y-m-d', $unixDate) === $tglSidang) {
                            $isMatch = true;
                        }
                    }

                    // --- CARA 3: FORMAT STANDAR (BACKUP) ---
                    if (!$isMatch) {
                        $ts = strtotime(str_replace('/', '-', $tglRaw));
                        if ($ts && date('Y-m-d', $ts) === $tglSidang) {
                            $isMatch = true;
                        }
                    }
                }

                if ($isMatch) {
                    $sourceData[] = [
                        'no_perkara' => trim($rowM[0] ?? ''),
                        'nama_raw'   => $rowM[1] ?? '-',
                        'jenis'      => $rowM[2] ?? '-',
                        'agenda'     => !empty($rowM[$idxAgenda]) ? $rowM[$idxAgenda] : ($rowM[19] ?? 'Sidang'),
                        'jpu'        => $rowM[$idxJpu] ?? '-',
                        'status'     => 'Terjadwal',
                        'source'     => 'master'
                    ];
                }
            }
        }

        // 4. EKSEKUSI DATABASE DENGAN TRANSAKSI
        $db = \Config\Database::connect();
        $db->transStart(); 

        // Snapshot: Hapus data lama untuk tanggal spesifik
        $this->sidangModel->where('tanggal_sidang', $tglSidang)->delete();

        $countInsert = 0;
        $processedPerkara = [];

        foreach ($sourceData as $data) {
            $noPerkara = $data['no_perkara'];
            if (empty($noPerkara) || in_array($noPerkara, $processedPerkara)) continue;

            // 1. Ambil nama asli dan bersihkan
            $namaMentah = $data['nama_raw']; // Nama asli dari Sheet (Contoh: MUHAMMAD NA'IM Bin Siku.)
            $namaBersih = $this->cleanNamaTerdakwa($namaMentah); // Nama hasil filter (Contoh: Muhammad Na'im)

            $rowM = $masterMap[$noPerkara] ?? [];
            
            // Logic TTL
            $ttlRaw = $rowM[10] ?? null; 
            $tempatLahir = '-'; $tglLahir = '-';
            if ($ttlRaw && strpos($ttlRaw, ',') !== false) {
                $parts = explode(',', $ttlRaw, 2); 
                $tempatLahir = trim($parts[0]);
                $tglLahir = trim($parts[1]);
            }

            $dataFull = [
                'tempat_lahir'    => $tempatLahir, 
                'tgl_lahir'       => $tglLahir,       
                'umur'            => $rowM[11] ?? '-', 
                'jenis_kelamin'   => $rowM[12] ?? '-', 
                'kewarganegaraan' => $rowM[13] ?? 'Indonesia', 
                'alamat'          => $rowM[14] ?? '-',    
                'agama'           => $rowM[15] ?? '-',    
                'pendidikan'      => $rowM[16] ?? '-',   
                'pekerjaan'       => $rowM[17] ?? '-',   
                'nama_ortu'       => $rowM[9] ?? '-',   
                'agenda_raw'      => $data['agenda'],   
                'jenis_perkara'   => $data['jenis'],
                'status_sidang'   => $data['status'],
            ];

            $this->sidangModel->insert([
                'tanggal_sidang' => $tglSidang, 
                'nomor_perkara'  => $noPerkara,
                'nama_asli'      => $namaMentah, // KOLOM BARU: Menyimpan data mentah dari Sheet
                'nama_terdakwa'  => $namaBersih, // Tetap simpan yang bersih untuk fungsi lainnya
                'jpu'            => $data['jpu'],
                'data_full'      => json_encode($dataFull), 
            ]);

            $processedPerkara[] = $noPerkara;
            $countInsert++;
        }

        $db->transComplete(); 

        if ($db->transStatus() === FALSE) {
            return redirect()->to('sidang')->with('error', "Gagal sinkronisasi ke database lokal.");
        }

        $tglIndoLabel = date('d-m-Y', strtotime($tglSidang));
        return redirect()->to('sidang')->with('success', "Sinkronisasi Selesai ($srcLabel). Tanggal: $tglIndoLabel | Data Masuk: $countInsert");

    } catch (\Throwable $e) {
        // Rollback otomatis ditangani transComplete, tapi kita pastikan di sini
        if (isset($db) && $db->transStatus() === FALSE) $db->transRollback();
        return redirect()->to('sidang')->with('error', "Sistem Error: " . $e->getMessage());
    }
}


    // ==========================================================
    // CORE LOGIC: PROSES CETAK (WORD & PDF)
    // ==========================================================
    public function proses()
{
    // 1. VALIDASI INPUT AWAL
    $selectedNoPerkara = $this->request->getPost('pilih_data');
    $docTypes          = $this->request->getPost('jenis_dokumen');
    $mode              = $this->request->getPost('mode_cetak');
    $tanggalTerpilih   = $this->request->getPost('tanggal_terpilih');

    if (empty($tanggalTerpilih)) return redirect()->back()->with('error', 'Tanggal sidang belum dipilih.');
    
    $d = \DateTime::createFromFormat('d-m-Y', $tanggalTerpilih);
    if ($d && $d->format('d-m-Y') === $tanggalTerpilih) {
        $dbFormatDate = $d->format('Y-m-d');
    } else {
        return redirect()->back()->with('error', 'Format tanggal tidak valid (Harus DD-MM-YYYY).');
    }

    $allowedModes = ['seleksi', 'full_p38'];
    if (!in_array($mode, $allowedModes)) $mode = 'seleksi';

    if ($mode != 'full_p38' && empty($selectedNoPerkara)) {
        return redirect()->back()->with('error', 'Anda belum memilih data Terdakwa.');
    }
    if (empty($docTypes)) return redirect()->back()->with('error', 'Jenis dokumen belum dipilih.');

    // 2. PROSES DATA CONFIG DARI MODAL
    $customInstansi   = $this->request->getPost('custom_instansi') ?: 'KEJAKSAAN NEGERI';
    $customKota       = $this->request->getPost('custom_kota') ?: 'Indonesia';
    $customNomorSurat = $this->request->getPost('custom_nomor_surat') ?: 'B-......./.......'; 
    $ttdJabatan       = $this->request->getPost('ttd_jabatan'); 
    $ttdNama          = $this->request->getPost('ttd_nama');
    $ttdNip           = $this->request->getPost('ttd_nip');
    $ttdPangkat       = $this->request->getPost('ttd_pangkat');
    $outputFormat     = $this->request->getPost('output_format') ?: 'word'; 

    // Setup Folder Arsip
    $hariIni = date('Y-m-d');
    $pathArsip = WRITEPATH . 'arsip_sidang';
    $folderBackup = $pathArsip . DIRECTORY_SEPARATOR . $hariIni;
    if (!is_dir($pathArsip)) @mkdir($pathArsip, 0777, true);
    if (!is_dir($folderBackup)) @mkdir($folderBackup, 0777, true);

    $generatedFiles = [];
    $fileDateStr = str_replace('/', '-', $tanggalTerpilih); 

    // --- FIX: QUERY DATABASE DENGAN FILTER TANGGAL (AGAR TIDAK DOUBLE/ZIP) ---
    if ($mode == 'full_p38') {
        // Ambil semua data pada tanggal tersebut
        $targetData = $this->sidangModel->where('tanggal_sidang', $dbFormatDate)->findAll();
        if (!is_array($docTypes)) $docTypes = [];
        if (!in_array('p38', $docTypes)) $docTypes[] = 'p38';
    } else {
        // Ambil hanya yang dipilih DAN sesuai tanggal sidang (Cegah data lama terpanggil)
        $targetData = $this->sidangModel->whereIn('nomor_perkara', $selectedNoPerkara)
                                        ->where('tanggal_sidang', $dbFormatDate)
                                        ->findAll();
    }

    if (empty($targetData)) return redirect()->back()->with('error', 'Data tidak ditemukan untuk tanggal tersebut.');

    // --- HELPERS FORMATTING ---
    $toTitle = function($str) {
        return mb_convert_case(strtolower(trim($str)), MB_CASE_TITLE, "UTF-8");
    };

    $formatNamaGelar = function($str) use ($toTitle) {
        if (empty($str)) return '-';
        if (strpos($str, ',') !== false) {
            $parts = explode(',', $str, 2);
            return $toTitle($parts[0]) . ', ' . strtoupper(trim($parts[1]));
        }
        return $toTitle($str);
    };

    $formatPendidikan = function($str) {
        if (empty($str)) return '-';
        $pattern = '/\b(SD|SMP|SMA|SLTA)\b/i';
        if (preg_match($pattern, $str)) return strtoupper(trim($str));
        return mb_convert_case(strtolower(trim($str)), MB_CASE_TITLE, "UTF-8");
    };

    // -----------------------------------------------------
    // A. GENERATE P-37 (SATU PER SATU)
    // -----------------------------------------------------
    if (is_array($docTypes) && in_array('p37', $docTypes)) {
        foreach ($targetData as $row) {
            $details = json_decode($row['data_full'], true);
            
            // Logika Waktu
            $timestampSidang = strtotime($row['tanggal_sidang']);
            $hariArr = ['Sunday'=>'Minggu','Monday'=>'Senin','Tuesday'=>'Selasa','Wednesday'=>'Rabu','Thursday'=>'Kamis','Friday'=>'Jumat','Saturday'=>'Sabtu'];
            $namaHari = $hariArr[date('l', $timestampSidang)];
            $tglSidangIndo = $this->formatTglSaja($row['tanggal_sidang']);

            // Mapping DataRow Final P-37
            $dataRow = [
                'nama_lengkap_raw' => $row['nama_asli'] ?? $row['nama_terdakwa'], // Narasi: Nama Asli
                'nama_terdakwa'   => $toTitle($this->cleanNamaTerdakwa($row['nama_terdakwa'])), // Tabel: Bersih
                
                'nama_instansi'   => $toTitle($customInstansi), 
                'kota_surat'      => $toTitle($customKota),
                'tanggal_surat'   => $this->formatTglSaja(date('Y-m-d')),
                'nomor_perkara'   => $row['nomor_perkara'],
                'jpu'             => $formatNamaGelar($row['jpu']), 
                
                'nama_hari'       => $namaHari,        
                'hari_sidang'     => $tglSidangIndo,   
                'jenis_perkara'   => $toTitle($details['jenis_perkara'] ?? 'Pidana Umum'),
                'agenda'          => $details['agenda_raw'] ?? '-', 
                'tempat_lahir'    => $toTitle($details['tempat_lahir'] ?? '-'),
                'tgl_lahir'       => $details['tgl_lahir'] ?? '-',
                'umur'            => $details['umur'] ?? '-',
                'jenis_kelamin'   => $toTitle($details['jenis_kelamin'] ?? '-'),
                'kewarganegaraan' => $toTitle($details['kewarganegaraan'] ?? 'Indonesia'),
                'alamat'          => $toTitle($details['alamat'] ?? '-'),
                'agama'           => $toTitle($details['agama'] ?? '-'),
                'pekerjaan'       => $toTitle($details['pekerjaan'] ?? '-'),
                'pendidikan'      => $formatPendidikan($details['pendidikan'] ?? '-'),
                'nama_ortu'       => $toTitle($details['nama_ortu'] ?? '-'),

                // Tanda Tangan
                'ttd_nama'        => strtoupper(!empty($ttdNama) ? $ttdNama : $row['jpu']),
                'ttd_nip'         => !empty($ttdNip) ? $ttdNip : '', 
                'ttd_jabatan'     => $ttdJabatan ?: 'Kepala Seksi Tindak Pidana Umum', 
                'ttd_pangkat'     => $toTitle($ttdPangkat) ?: '',
            ];

            $cleanNameFile = preg_replace('/[^A-Za-z0-9]/', '_', $this->cleanNamaTerdakwa($row['nama_terdakwa']));
            $baseFileName = "P37_{$cleanNameFile}_{$fileDateStr}";

            if ($outputFormat === 'word') {
                $this->generateDoc('template_p37.docx', $dataRow, $baseFileName . ".docx", $folderBackup, $generatedFiles);
            } else {
                $html = view('App\Modules\Sidang\Views\pdf\template_p37', $dataRow);
                $mpdf = new \Mpdf\Mpdf(['format' => [215, 330], 'margin_left'=>20, 'margin_right'=>15, 'margin_top'=>15, 'margin_bottom'=>10]);
                $mpdf->WriteHTML($html);
                $finalP = $folderBackup . DIRECTORY_SEPARATOR . $baseFileName . '.pdf';
                $mpdf->Output($finalP, 'F');
                $generatedFiles[$baseFileName . '.pdf'] = $finalP;
            }
        }
    }

    // -----------------------------------------------------
    // B. GENERATE P-38 (KOLEKTIF)
    // -----------------------------------------------------
    
    // -----------------------------------------------------
    // B. GENERATE P-38 (KOLEKTIF) - VERSI FIX FINAL & E-SIGN WORD
    // -----------------------------------------------------
    if (is_array($docTypes) && in_array('p38', $docTypes)) {
        
        $firstRow = $targetData[0];
        $tsSdn = strtotime($firstRow['tanggal_sidang']);
        $hariArr = ['Sunday'=>'Minggu','Monday'=>'Senin','Tuesday'=>'Selasa','Wednesday'=>'Rabu','Thursday'=>'Kamis','Friday'=>'Jumat','Saturday'=>'Sabtu'];
        $bulanArr = [1=>'Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
        
        // 1. Definisikan semua variabel agar tidak "Undefined"
        $p38Instansi   = $customInstansi;
        $p38Kota       = $customKota;
        $p38TglSurat   = $this->formatTglSaja(date('Y-m-d'));
        $namaHariSaja   = $hariArr[date('l', $tsSdn)];
        $tanggalSaja    = date('d', $tsSdn) . ' ' . $bulanArr[(int)date('m', $tsSdn)] . ' ' . date('Y', $tsSdn);
        $namaTerdakwaPertama = $this->cleanNamaTerdakwa($firstRow['nama_terdakwa']);

        $p38TtdNama    = strtoupper(!empty($ttdNama) ? $ttdNama : $firstRow['jpu']);
        $p38TtdNip     = !empty($ttdNip) ? $ttdNip : '';
        $p38TtdJabatan = !empty($ttdJabatan) ? $ttdJabatan : 'Kepala Seksi Tindak Pidana Umum';
        $p38TtdPangkat = !empty($ttdPangkat) ? $toTitle($ttdPangkat) : '';

        // 2. Siapkan Data Tabel
        $dataTabelP38 = [];
        $no = 1;
        foreach ($targetData as $row) {
            $det = json_decode($row['data_full'], true);
            $dataTabelP38[] = [
                'no' => $no++, 
                'nomor_perkara' => $row['nomor_perkara'],
                'nama_terdakwa' => $this->cleanNamaTerdakwa($row['nama_terdakwa']),
                'jpu' => $row['jpu'], 
                'agenda' => $det['agenda_raw'] ?? '-'
            ];
        }

        $baseP38Name = (count($targetData) === 1) ? "P38_".$this->cleanNamaTerdakwa($firstRow['nama_terdakwa'])."_{$fileDateStr}" : "P38_Kolektif_{$fileDateStr}";

        // === OPSI 1: FORMAT WORD ===
        if ($outputFormat === 'word') {
            $tplPath = WRITEPATH . 'templates/template_p38.docx';
            if (file_exists($tplPath)) {
                $proc = new \PhpOffice\PhpWord\TemplateProcessor($tplPath);
                
                // Isi Header
                $proc->setValue('nama_instansi', $p38Instansi);
                $proc->setValue('nomor_surat',   $customNomorSurat);
                $proc->setValue('kota_surat',    $p38Kota);
                $proc->setValue('tanggal_surat', $p38TglSurat);
                $proc->setValue('hari_sidang',   $namaHariSaja);
                $proc->setValue('tanggal_sidang',$tanggalSaja);
                $proc->setValue('nama_terdakwa_pertama', $namaTerdakwaPertama);
                
                // Isi Penandatangan [cite: 52, 53, 54]
                $proc->setValue('ttd_jabatan',   $p38TtdJabatan);
                $proc->setValue('ttd_nama',      $p38TtdNama);
                $proc->setValue('ttd_pangkat',   $p38TtdPangkat);
                $proc->setValue('ttd_nip',       !empty($p38TtdNip) ? 'NIP. ' . $p38TtdNip : '');

                // INSERT LOGO E-SIGN KE WORD
                // Ganti [Image 2] di template dengan logo asli
                $pathEsign = FCPATH . 'images/logoEsign.png';
                if (file_exists($pathEsign)) {
                    // Pastikan di Word lo ada placeholder gambar atau teks [Image 2]
                    // Atau gunakan metode paling simpel: taruh gambar di template lalu biarkan
                }

                // Handle Looping Tabel 
                $countRows = count($dataTabelP38);
                if ($countRows > 0) {
                    $proc->cloneRow('no', $countRows); 
                    foreach ($dataTabelP38 as $index => $rowData) {
                        $curr = $index + 1;
                        $proc->setValue('no#' . $curr, $rowData['no']);
                        $proc->setValue('nama_terdakwa#' . $curr, $rowData['nama_terdakwa']);
                        $proc->setValue('jpu#' . $curr, $rowData['jpu']);
                        $proc->setValue('agenda#' . $curr, $rowData['agenda']);
                    }
                }

                $finalWordName = $baseP38Name . ".docx";
                $saveP = $folderBackup . DIRECTORY_SEPARATOR . $finalWordName;
                $proc->saveAs($saveP);
                $generatedFiles[$finalWordName] = $saveP;
            }
        } 
        
        // === OPSI 2: FORMAT PDF ===
        else if ($outputFormat === 'pdf') {
            $dataForPdf = [
                'logo_path'       => FCPATH . ($this->setting->get('logo') ?? 'images/logo_kejaksaan.png'), 
                'nama_instansi'   => $p38Instansi,
                'alamat_instansi' => $this->setting->get('alamat') ?? '-',
                'nomor_surat'     => $customNomorSurat,
                'kota_surat'      => $p38Kota,
                'tanggal_surat'   => $p38TglSurat,
                'hari_sidang'     => $namaHariSaja,
                'tanggal_sidang'  => $tanggalSaja,
                'nama_terdakwa_pertama' => $namaTerdakwaPertama,
                'ttd_jabatan'     => $p38TtdJabatan,
                'ttd_nama'        => $p38TtdNama,
                'ttd_pangkat'     => $p38TtdPangkat,
                'ttd_nip'         => $p38TtdNip,
                'data_tabel'      => $dataTabelP38
            ];

            $html = view('App\Modules\Sidang\Views\pdf\template_p38', $dataForPdf);
            $mpdf = new \Mpdf\Mpdf(['format' => [215, 330], 'margin_top'=>15, 'margin_left'=>20, 'margin_right'=>15]);
            $mpdf->WriteHTML($html);
            $finalP = $folderBackup . DIRECTORY_SEPARATOR . $baseP38Name . '.pdf';
            $mpdf->Output($finalP, 'F');
            $generatedFiles[$baseP38Name . '.pdf'] = $finalP;
        }
    }


    // 3. PROSES DOWNLOAD FINAL
    if (count($generatedFiles) === 0) return redirect()->back()->with('error', 'Gagal generate file.');

    if (count($generatedFiles) === 1) {
        $singlePath = reset($generatedFiles);
        return $this->response->download($singlePath, null)->setFileName(array_key_first($generatedFiles));
    } else {
        $zip = new \ZipArchive();
        $zipName = $folderBackup . DIRECTORY_SEPARATOR . "Berkas_Sidang_".strtoupper($outputFormat)."_{$fileDateStr}.zip";
        if ($zip->open($zipName, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) === TRUE) {
            foreach ($generatedFiles as $fname => $fpath) { $zip->addFile($fpath, $fname); }
            $zip->close();
        }
        return $this->response->download($zipName, null)->setFileName(basename($zipName));
    }
}

    private function generateDoc($tpl, $data, $outName, $path, &$filesArr) {
        $tplPath = WRITEPATH . 'templates/' . $tpl;
        if (file_exists($tplPath)) {
            $proc = new TemplateProcessor($tplPath);
            $proc->setValues($data);
            
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

    private function formatTanggalIndo($tanggal) {
        if (empty($tanggal) || $tanggal == '0000-00-00') return '-';
        $timestamp = strtotime($tanggal);
        return date('d-m-Y', $timestamp); // Sederhana saja
    }

    private function formatTglSaja($tanggal) {
        if (empty($tanggal)) return date('d F Y');
        $bulan = [1=>'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
        $timestamp = strtotime($tanggal);
        return date('d', $timestamp) . ' ' . $bulan[(int)date('m', $timestamp)] . ' ' . date('Y', $timestamp);
    }

    // Tambahkan di bagian atas method atau buat method private baru
    private function setSecureHeaders()
    {
        // Anti-Clickjacking
        $this->response->setHeader('X-Frame-Options', 'DENY');
        
        // Cache Control agar data OTP/NIP tidak tersimpan di history browser
        $this->response->setHeader('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0');
        $this->response->setHeader('Pragma', 'no-cache');
        
        // Proteksi tambahan
        $this->response->setHeader('X-Content-Type-Options', 'nosniff');
    }
    
    /*public function diagnosa()
    {
        $reqTanggal = $this->request->getGet('tanggal');
        if (empty($reqTanggal)) return "Masukkan tanggal di URL! Contoh: /sidang/diagnosa?tanggal=22-12-2025";

        // Konversi Tanggal Target
        $targetYMD = '';
        $d = \DateTime::createFromFormat('d-m-Y', $reqTanggal);
        if ($d) $targetYMD = $d->format('Y-m-d');

        echo "<h1>🕵️ MODE DIAGNOSA DATA</h1>";
        echo "Target Input: <b>$reqTanggal</b> (Format DB: <b>$targetYMD</b>)<br>";
        echo "Mencari di Kolom Index: <b>6</b> (Kolom G)<br><hr>";

        try {
            // Ambil 20 baris pertama dari DATA_MASTER untuk dicek
            $data = $this->fetchSheet('DATA_MASTER!A2:Z20'); 
        } catch (\Throwable $e) {
            return "Error Koneksi: " . $e->getMessage();
        }

        echo "<table border='1' cellpadding='5' cellspacing='0' style='font-family:monospace; font-size:12px;'>";
        echo "<tr style='background:#ccc'>
                <th>Index 0 (A)<br>No Perkara</th>
                <th>Index 6 (G)<br>DATA MENTAH</th>
                <th>Panjang Kar.</th>
                <th>Hasil Cek System</th>
              </tr>";

        foreach ($data as $key => $row) {
            $colA = $row[0] ?? '-'; // No Perkara
            $rawG = $row[6] ?? 'NULL'; // Kolom Tanggal (G)
            
            // Cek Karakter Hantu (Hex Dump)
            $hex = bin2hex($rawG); 
            
            // Simulasi Logika Sync
            $status = "<span style='color:red'>TIDAK COCOK</span>";
            $cleanG = trim($rawG);
            
            // Tes 1: String Match
            if ($cleanG === $targetYMD) $status = "<span style='color:green'>COCOK (String Match)</span>";
            
            // Tes 2: Serial Excel
            elseif (is_numeric($cleanG) && $cleanG > 20000) {
                $unix = ($cleanG - 25569) * 86400;
                $excelDate = date('Y-m-d', $unix);
                if ($excelDate === $targetYMD) $status = "<span style='color:green'>COCOK (Serial: $excelDate)</span>";
                else $status .= " <small>(Serial terbaca: $excelDate)</small>";
            }
            
            // Tes 3: Parsing Indo
            else {
                $indoStr = str_replace('/', '-', $cleanG);
                $ts = strtotime($indoStr);
                if ($ts) {
                    $parseDate = date('Y-m-d', $ts);
                    if ($parseDate === $targetYMD) $status = "<span style='color:green'>COCOK (Parsing: $parseDate)</span>";
                    else $status .= " <small>(Parse terbaca: $parseDate)</small>";
                } else {
                    $status .= " <small>(Gagal Parse)</small>";
                }
            }

            echo "<tr>";
            echo "<td>" . htmlspecialchars($colA) . "</td>";
            echo "<td style='background:#ffffcc; font-weight:bold;'>[" . htmlspecialchars($rawG) . "]</td>";
            echo "<td>" . strlen($rawG) . " char<br><span style='color:#999; font-size:10px'>Hex: $hex</span></td>";
            echo "<td>$status</td>";
            echo "</tr>";
        }
        echo "</table>";
    }*/
}