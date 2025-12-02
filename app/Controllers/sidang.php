<?php

namespace App\Controllers;

use App\Models\SidangModel;
use Google\Client;
use Google\Service\Sheets;
use PhpOffice\PhpWord\TemplateProcessor;
use App\Libraries\Settings; // Penting buat Layout Frontend

class Sidang extends BaseController
{
    protected $sidangModel;
    protected $setting;

    public function __construct()
    {
        // Load Model & Setting
        $this->sidangModel = new SidangModel();
        $this->setting = new Settings(); 
    }

    /**
     * HELPER: KONEKSI KE GOOGLE SHEET
     */
    private function fetchSheet($range)
    {
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
     * HALAMAN UTAMA (INDEX)
     * Mengambil data dari DATABASE (bukan Sheet langsung) biar cepat.
     */
    public function index()
    {
        // Ambil Tanggal Unik dari Database buat Filter
        $queryTanggal = $this->sidangModel->select('tanggal_sidang')->distinct()->orderBy('tanggal_sidang', 'DESC')->findAll();
        $listTanggal = array_column($queryTanggal, 'tanggal_sidang');

        // Ambil 100 data terbaru (biar gak berat loadnya)
        $allData = $this->sidangModel->orderBy('tanggal_sidang', 'DESC')->findAll(100);

        // Kirim Data ke View (Termasuk data Setting buat Frontend)
        $data = [
            'title'         => 'Cetak Sidang - ' . $this->setting->info['nama_aplikasi'],
            'nama_instansi' => $this->setting->info['nama_instansi'],
            'alamat'        => $this->setting->info['alamat'],
            'opt_tanggal'   => $listTanggal,
            'all_data'      => $allData
        ];

        return view('sidang_view', $data);
    }

    /**
     * FITUR SINKRONISASI (SYNC)
     * Menarik data dari Google Sheet -> Simpan ke Database Lokal
     */
    public function sync()
    {
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
     * FITUR PROSES CETAK
     * Mengambil data dari DATABASE -> Generate Word -> Download ZIP/File
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
                'nip_jpu'         => '...................',
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