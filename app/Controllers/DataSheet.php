<?php

namespace App\Controllers;

use Google\Client;
use Google\Service\Sheets;

class DataSheet extends BaseController
{
    public function index()
    {
        try {
            $client = new Client();
            $credentialPath = WRITEPATH . '/json-by2025.json';
            $client->setAuthConfig($credentialPath);
            $client->addScope(Sheets::SPREADSHEETS_READONLY);
            $service = new Sheets($client);

            $spreadsheetId = '1Jlsbx5HxKzQDmfNFIBkw1TTmDBKXpIxKAtb_zhaLLfo'; // ⚠️ GANTI INI
            $range = 'SIDANG HARI INI!A1:G100'; // ⚠️ GANTI INI (NamaTab!Range)

            $response = $service->spreadsheets_values->get($spreadsheetId, $range);
            $values = $response->getValues();

            if (empty($values)) {
                // Kirim data kosong jika tidak ada
                return $this->response->setJSON([
                    'status' => false,
                    'message' => 'Tidak ada data ditemukan.',
                    'headers' => [],
                    'rows' => []
                ]);
            }

            // --- PROSES DATA BIAR RAPI ---
            
            // 1. Ambil baris pertama sebagai Header
            $headerRow = array_shift($values);
            
            // 2. Buat data header untuk v-data-table Vuetify
            $headersForVue = [];
            foreach ($headerRow as $header) {
                $headersForVue[] = [
                    'text'  => $header, // Teks yg tampil di header tabel
                    'value' => strtolower(str_replace(' ', '_', $header)) // Kunci unik
                ];
            }

            // 3. Ubah sisa baris data (values) jadi array of objects
            $rowsForVue = [];
            foreach ($values as $row) {
                
                // ✅ KOREKSI KRITIS: FILTER BARIS KOSONG
                // Cek apakah kolom-kolom utama (Nomor Perkara/Nama) ada isinya
                // Asumsi: Kolom A (Index 0) atau Kolom C (Index 2) harus ada data.
                $kolomA_noPerkara = $row[0] ?? ''; 
                $kolomC_nama = $row[2] ?? '';

                // Jika kolom utama kosong atau terlalu pendek, LOMPATI baris ini (Ghost Row)
                if (empty(trim($kolomA_noPerkara)) && empty(trim($kolomC_nama))) {
                    continue; 
                }
                
                // Tambahan: Pastikan baris yang tersisa memiliki setidaknya 3 kolom agar tidak ada error index.
                if (count($row) < 3) {
                    continue;
                }
                
                $newRow = [];
                foreach ($headerRow as $index => $header) {
                    $key = strtolower(str_replace(' ', '_', $header));
                    // Pastikan data ada, kalau nggak, kasih string kosong
                    $newRow[$key] = $row[$index] ?? ''; 
                }
                $rowsForVue[] = $newRow;
            }
            
            // 4. Siapkan data final
            $data = [
                'status'  => true,
                'message' => 'Data berhasil diambil',
                'headers' => $headersForVue,
                'rows'    => $rowsForVue
            ];

            // 5. KIRIM SEBAGAI JSON
            return $this->response->setJSON($data);

        } catch (\Exception $e) {
            // Tangani error
            return $this->response->setStatusCode(500)->setJSON([
                'status'  => false,
                'message' => 'Error: ' . $e->getMessage(),
                'headers' => [],
                'rows'    => []
            ]);
        }
    }
}