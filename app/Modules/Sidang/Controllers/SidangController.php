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
                    return redirect()->to('sidang')->with('error', "Format tanggal URL salah. Gunakan DD-MM-YYYY.");
                }
            }
        }

        // 2. SNAPSHOT: HAPUS DATA LAMA
        $this->sidangModel->where('tanggal_sidang', $tglSidang)->delete();

        // 3. AMBIL DATA MASTER
        try {
            $masterSheet = $this->fetchSheet('DATA_MASTER!A2:Z'); 
        } catch (\Throwable $e) {
            return redirect()->to('sidang')->with('error', "Gagal koneksi Google Sheet: " . $e->getMessage());
        }

        // Mapping Data Master
        $masterMap = [];
        foreach ($masterSheet as $rowM) {
            if(isset($rowM[0]) && !empty($rowM[0])) {
                $kunci = trim($rowM[0]); 
                $masterMap[$kunci] = $rowM;
            }
        }

        $countInsert = 0;
        $processedPerkara = [];
        $sourceData = [];
        $srcLabel = "";

        // 4. LOGIKA PENCARIAN (PRIORITAS: HARIAN -> MASTER)
        
        // STEP A: Cek Sheet Harian (Jika tanggal target = hari ini)
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

        // STEP B: Cek DATA MASTER (Jika data masih kosong)
        if (empty($sourceData)) {
            $srcLabel = "Data Master";
            
            // --- INDEX KOLOM DATA MASTER ---
            $idxTglSidang = 6;  // Kolom G (HARI SIDANG)
            $idxAgenda    = 3;  // Kolom D (AGENDA SIDANG)
            $idxJpu       = 4;  // Kolom E (JPU)
            // -------------------------------

            foreach ($masterSheet as $rowM) {
                $tglRaw = $rowM[$idxTglSidang] ?? '';
                $isMatch = false;

                if (!empty($tglRaw)) {
                    $tglRaw = trim($tglRaw);

                    // --- [FIX] LOGIKA "MATA DEWA" (3 CARA CEK) ---

                    // CARA 1: Cek jika data berupa ANGKA SERIAL EXCEL (Misal: 45648)
                    if (is_numeric($tglRaw) && $tglRaw > 20000) {
                        $unixDate = ($tglRaw - 25569) * 86400;
                        if (date('Y-m-d', $unixDate) === $tglSidang) {
                            $isMatch = true;
                        }
                    }
                    // CARA 2: Cek Format Text Standard (2025-12-22 atau 12/22/2025)
                    else {
                        $ts = strtotime($tglRaw);
                        if ($ts && date('Y-m-d', $ts) === $tglSidang) {
                            $isMatch = true;
                        }
                    }
                    // CARA 3: Cek Format Text Indo (22/12/2025 -> paksa jadi 22-12-2025)
                    if (!$isMatch) {
                        // INI YANG PALING PENTING BUAT KASUS LO
                        $tglIndo = str_replace('/', '-', $tglRaw); 
                        $ts2 = strtotime($tglIndo);
                        if ($ts2 && date('Y-m-d', $ts2) === $tglSidang) {
                            $isMatch = true;
                        }
                    }
                }

                if ($isMatch) {
                    $sourceData[] = [
                        'no_perkara' => trim($rowM[0] ?? ''),
                        'nama_raw'   => $rowM[1] ?? '-',
                        'jenis'      => $rowM[2] ?? '-',
                        // Fallback Agenda: Cek Index 3, kalau kosong cek Index 19
                        'agenda'     => !empty($rowM[$idxAgenda]) ? $rowM[$idxAgenda] : ($rowM[19] ?? 'Sidang'),
                        'jpu'        => $rowM[$idxJpu] ?? '-',
                        'status'     => 'Terjadwal',
                        'source'     => 'master'
                    ];
                }
            }
        }

        // 5. INSERT KE DATABASE
        foreach ($sourceData as $data) {
            $noPerkara = $data['no_perkara'];
            if (empty($noPerkara)) continue;
            if (in_array($noPerkara, $processedPerkara)) continue;

            $nama = $this->cleanNamaTerdakwa($data['nama_raw']);
            $rowM = $masterMap[$noPerkara] ?? [];
            
            $ttlRaw = $rowM[10] ?? null; 
            $tempatLahir = '-'; $tglLahir = '-';
            if ($ttlRaw) {
                if (strpos($ttlRaw, ',') !== false) {
                    $parts = explode(',', $ttlRaw, 2); 
                    $tempatLahir = trim($parts[0]);
                    $tglLahir = trim($parts[1]);
                } else {
                    $tempatLahir = $ttlRaw;
                }
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

            $saveData = [
                'tanggal_sidang' => $tglSidang, 
                'nomor_perkara'  => $noPerkara,
                'nama_terdakwa'  => $nama, 
                'jpu'            => $data['jpu'],
                'data_full'      => json_encode($dataFull), 
            ];

            $this->sidangModel->insert($saveData);
            $processedPerkara[] = $noPerkara;
            $countInsert++;
        }

        $tglIndoLabel = date('d-m-Y', strtotime($tglSidang));
        return redirect()->to('sidang')->with('success', "Sinkronisasi Selesai ($srcLabel). Tanggal: $tglIndoLabel | Data Masuk: $countInsert");
    }
    // ==========================================================
    // CORE LOGIC: PROSES CETAK (WORD & PDF)
    // ==========================================================
    public function proses()
    {
        // 1. VALIDASI INPUT
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

        // 2. PROSES DATA CONFIG
        $customInstansi   = $this->request->getPost('custom_instansi') ?: 'KEJAKSAAN NEGERI';
        $customKota       = $this->request->getPost('custom_kota') ?: 'Indonesia';
        $customNomorSurat = $this->request->getPost('custom_nomor_surat') ?: 'B-......./.......'; 
        $ttdJabatan       = $this->request->getPost('ttd_jabatan'); 
        $ttdNama          = $this->request->getPost('ttd_nama');
        $ttdNip           = $this->request->getPost('ttd_nip');
        $ttdPangkat       = $this->request->getPost('ttd_pangkat');
        $outputFormat     = $this->request->getPost('output_format') ?: 'word'; 

        // Setup Folder
        $hariIni = date('Y-m-d');
        $pathArsip = WRITEPATH . 'arsip_sidang';
        $folderBackup = $pathArsip . DIRECTORY_SEPARATOR . $hariIni;
        
        if (!is_dir($pathArsip)) @mkdir($pathArsip, 0777, true);
        if (!is_dir($folderBackup)) @mkdir($folderBackup, 0777, true);

        $generatedFiles = [];
        $targetData = [];
        $fileDateStr = str_replace('/', '-', $tanggalTerpilih); 

        // Query Database
        if ($mode == 'full_p38') {
            $targetData = $this->sidangModel->where('tanggal_sidang', $dbFormatDate)->findAll();
            if (!is_array($docTypes)) $docTypes = [];
            if (!in_array('p38', $docTypes)) $docTypes[] = 'p38';
        } else {
            $targetData = $this->sidangModel->whereIn('nomor_perkara', $selectedNoPerkara)->findAll();
        }

        if (empty($targetData)) return redirect()->back()->with('error', 'Data tidak ditemukan.');

        // -----------------------------------------------------
        // GENERATE P-37 (WORD ONLY)
        // -----------------------------------------------------
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
                    'TTD_PANGKAT'     => $ttdPangkat,
                    'NAMA_INSTANSI'   => $instansiValue,
                ];

                $cleanName = preg_replace('/[^A-Za-z0-9 \-]/', '', $this->cleanNamaTerdakwa($row['nama_terdakwa']));
                $cleanName = substr($cleanName, 0, 50);
                $fileNameP37 = "P37-{$cleanName}-{$fileDateStr}.docx";
                
                $this->generateDoc('template_p37.docx', $dataRow, $fileNameP37, $folderBackup, $generatedFiles);
            }
        }

        // -----------------------------------------------------
        // GENERATE P-38 (HYBRID: WORD OR PDF)
        // -----------------------------------------------------
        if (is_array($docTypes) && in_array('p38', $docTypes)) {
            
            $firstRow = $targetData[0];
            $p38Instansi   = strtoupper($customInstansi);
            $p38Kota       = $customKota;
            $p38TglSurat   = $this->formatTglSaja(date('Y-m-d'));
            
            $timestampSidang = strtotime($firstRow['tanggal_sidang']);
            $hariArr = ['Sunday' => 'Minggu', 'Monday' => 'Senin', 'Tuesday' => 'Selasa', 'Wednesday' => 'Rabu', 'Thursday' => 'Kamis', 'Friday' => 'Jumat', 'Saturday' => 'Sabtu'];
            $bulanArr = [1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
            
            $namaHariSaja   = $hariArr[date('l', $timestampSidang)];
            $tanggalSaja    = date('d', $timestampSidang) . ' ' . $bulanArr[(int)date('m', $timestampSidang)] . ' ' . date('Y', $timestampSidang);
            
            $p38TtdNama    = !empty($ttdNama) ? $ttdNama : $firstRow['jpu']; 
            $p38TtdNip     = !empty($ttdNip) ? $ttdNip : (session()->get('sidang_nip') ?? '-');
            $p38TtdJabatan = !empty($ttdJabatan) ? $ttdJabatan : 'PENUNTUT UMUM';
            $p38TtdPangkat = !empty($ttdPangkat) ? $ttdPangkat : '-';
            
            $namaTerdakwaPertama = $this->cleanNamaTerdakwa($firstRow['nama_terdakwa']);
            $cleanNameOne = substr(preg_replace('/[^A-Za-z0-9 \-]/', '', $namaTerdakwaPertama), 0, 50);
            $baseFileName = (count($targetData) === 1) ? "P38-{$cleanNameOne}-{$fileDateStr}" : "P38-Sidang-{$fileDateStr}";

            // === OPSI 1: FORMAT WORD ===
            if ($outputFormat === 'word') {
                $tableRows = [];
                $no = 1;
                foreach ($targetData as $row) {
                    $det = json_decode($row['data_full'], true);
                    $tableRows[] = [
                        'no'            => $no++,
                        'nomor_perkara' => $row['nomor_perkara'],
                        'nama_terdakwa' => $this->cleanNamaTerdakwa($row['nama_terdakwa']),
                        'jpu'           => $row['jpu'],
                        'status_sidang' => $det['status_sidang'] ?? '-',
                        'jenis_perkara' => $det['jenis_perkara'] ?? '-',
                        'agenda'        => $det['agenda_raw'] ?? '-'
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
                    $proc->setValue('hari_sidang', $namaHariSaja); 
                    $proc->setValue('tanggal_sidang', $tanggalSaja);
                    $proc->setValue('(HARI SIDANG)', $namaHariSaja);
                    $proc->setValue('(TANGGAL SIDANG)', $tanggalSaja);
                    $proc->setValue('nama_terdakwa_1', $namaTerdakwaPertama); 
                    $proc->setValue('TTD_NAMA', $p38TtdNama);
                    $proc->setValue('TTD_NIP', $p38TtdNip);
                    $proc->setValue('TTD_JABATAN', $p38TtdJabatan);
                    $proc->setValue('ttd_nama', $p38TtdNama);
                    $proc->setValue('ttd_nip', $p38TtdNip);
                    $proc->setValue('ttd_jabatan', $p38TtdJabatan);
                    $proc->setValue('TTD_PANGKAT', $p38TtdPangkat);

                    $countRows = count($tableRows);
                    $proc->cloneRow('no', $countRows); 
                    foreach ($tableRows as $index => $rowData) {
                        $rowIndex = $index + 1; 
                        $proc->setValue('no#' . $rowIndex, $rowData['no']);
                        $proc->setValue('nomor_perkara#' . $rowIndex, $rowData['nomor_perkara']);
                        $proc->setValue('nama_terdakwa#' . $rowIndex, $rowData['nama_terdakwa']);
                        $proc->setValue('jpu#' . $rowIndex, $rowData['jpu']);
                        $proc->setValue('status_sidang#' . $rowIndex, $rowData['status_sidang']);
                        $proc->setValue('jenis_perkara#' . $rowIndex, $rowData['jenis_perkara']);
                        $proc->setValue('agenda#' . $rowIndex, $rowData['agenda']);
                    }
                    /*
                    $proc->cloneRow('no_2', $countRows); 
                    foreach ($tableRows as $index => $rowData) {
                        $rowIndex = $index + 1; 
                        $proc->setValue('no_2#' . $rowIndex, $rowData['no']);
                        $proc->setValue('nomor_perkara_2#' . $rowIndex, $rowData['nomor_perkara']);
                        $proc->setValue('nama_terdakwa_2#' . $rowIndex, $rowData['nama_terdakwa']);
                        $proc->setValue('jpu_2#' . $rowIndex, $rowData['jpu']);
                        $proc->setValue('status_sidang_2#' . $rowIndex, $rowData['status_sidang']);
                        $proc->setValue('jenis_perkara_2#' . $rowIndex, $rowData['jenis_perkara']);
                    }
                        */

                    $finalName = $baseFileName . '.docx';
                    $saveP = $folderBackup . DIRECTORY_SEPARATOR . $finalName;
                    $proc->saveAs($saveP);
                    $generatedFiles[$finalName] = $saveP;
                }
            } 
            
            // === OPSI 2: FORMAT PDF (VIA MPDF) ===
            else if ($outputFormat === 'pdf') {
                
                // Ambil Path Logo untuk mPDF (Wajib path fisik, bukan URL)
                $logoSetting = $this->setting->get('logo') ?? 'images/logo_kejaksaan.png';
                $pathLogoFisik = FCPATH . $logoSetting;
                if(!file_exists($pathLogoFisik)) {
                    $pathLogoFisik = FCPATH . 'images/logo_kejaksaan.png';
                }

                $dataForPdf = [
                    'logo_path'       => $pathLogoFisik, 
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
                    'data_tabel'      => []
                ];

                $no = 1;
                foreach ($targetData as $row) {
                    $det = json_decode($row['data_full'], true);
                    $dataForPdf['data_tabel'][] = [
                        'no' => $no++,
                        'nomor_perkara' => $row['nomor_perkara'],
                        'nama_terdakwa' => $this->cleanNamaTerdakwa($row['nama_terdakwa']),
                        'jpu'           => $row['jpu'],
                        'agenda'        => $det['agenda_raw'] ?? '-',
                        'status_sidang' => $det['status_sidang'] ?? '-'
                    ];
                }

                try {
                    $html = view('App\Modules\Sidang\Views\pdf\template_p38', $dataForPdf);
                    
                    if (ob_get_length()) {
                        ob_end_clean(); 
                    }
                    
                    $mpdf = new Mpdf([
                        'mode' => 'utf-8', 
                        'format' => [215, 330], // Ukuran F4 / Folio (mm)
                        'orientation' => 'P',
                        'margin_top' => 15,
                        'margin_left' => 20,
                        'margin_right' => 15
                    ]);
                    $mpdf->WriteHTML($html);
                    
                    $finalName = $baseFileName . '.pdf';
                    $saveP = $folderBackup . DIRECTORY_SEPARATOR . $finalName;
                    $mpdf->Output($saveP, 'F');
                    
                    $generatedFiles[$finalName] = $saveP;
                } catch (\Throwable $e) {
                    return redirect()->back()->with('error', 'Gagal membuat PDF: ' . $e->getMessage());
                }
            }
        }

        // 3. DOWNLOAD
        $totalFiles = count($generatedFiles);
        if ($totalFiles === 0) return redirect()->back()->with('error', 'Gagal generate file.');

        if ($totalFiles === 1) {
            $singleFile = reset($generatedFiles);
            $downloadName = array_key_first($generatedFiles); 
            return $this->response->download($singleFile, null)->setFileName($downloadName);
        } else {
            $zip = new \ZipArchive();
            $ext = ($outputFormat === 'pdf') ? 'PDF' : 'WORD';
            $zipName = $folderBackup . DIRECTORY_SEPARATOR . "Berkas_Sidang_{$ext}_{$fileDateStr}.zip";
            
            if ($zip->open($zipName, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) === TRUE) {
                foreach ($generatedFiles as $fname => $fpath) {
                    if (file_exists($fpath)) $zip->addFile($fpath, $fname);
                }
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
}