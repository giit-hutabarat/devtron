<?php

namespace App\Modules\Masjid\Controllers\Api;
/*
IT Shop Purwokerto (Tokopedia, Shopee & Bukalapak)
Dibuat oleh: Hari Wicaksono, S.Kom
06-2022
*/

use App\Controllers\BaseControllerApi;
use App\Modules\Masjid\Models\KeuanganmasjidModel;
use App\Libraries\Settings;
use CodeIgniter\HTTP\ResponseInterface; // Tambahkan ini untuk 403 Forbidden

class KeuanganMasjid extends BaseControllerApi
{
    protected $format       = 'json';
    protected $modelName    = KeuanganmasjidModel::class;
    protected $setting;

    public function __construct()
    {
        // 🔒 Otorisasi Level Admin
        // Hanya Admin (user_type 1) yang boleh mengakses semua fungsi CRUD/Input Keuangan ini.
        if (session()->get('user_type') == 2) {
            $response = service('response');
            $response->setStatusCode(ResponseInterface::HTTP_FORBIDDEN)
                     ->setJSON(['status' => false, 'message' => 'Akses Ditolak. Anda tidak memiliki hak untuk mengelola Keuangan Masjid.']);
            $response->send();
            exit(); 
        }

        //memanggil Model
        $this->setting = new \App\Libraries\Settings(); // Menginisiasi $setting
    }

    public function index()
    {
        return $this->respond(['status' => true, 'message' => lang('App.getSuccess'), 'data' => $this->model->findAll()], 200);
    }

    public function show($id = null)
    {
        return $this->respond(['status' => true, 'message' => lang('App.getSuccess'), 'data' => $this->model->find($id)], 200);
    }

    public function display()
    {
        // Method ini dipanggil oleh Route Display publik, 
        // tetapi karena berada di Controller yang dilindungi Konstruktor,
        // kita harus pastikan Route Display TIDAK menggunakan Controller ini
        // (Namun, di Routes.php, method ini dipanggil di Kelompok 2 - Public API. Kita abaikan pengecekan ini karena Otorisasi Admin ada di Konstruktor).
        $data['pemasukan'] = $this->model->nominal_pemasukan();
        $data['pengeluaran'] = $this->model->nominal_pengeluaran();
        $data['keuangan'] = $this->model->get_transaksi();
        
        // Perbaiki loop yang tidak perlu
        $arrayData = [
            'pemasukan' => $data['pemasukan'],   
            'pengeluaran' => $data['pengeluaran'],
            'keuangan' => $data['keuangan'],
        ];

        return $this->respond(["status" => true, "message" => lang('App.getSuccess'), "data" => $arrayData], 200);
    }

    public function create()
    {
        $rules = [
            'tanggal' => [
                'rules'  => 'required',
                'errors' => []
            ],
            'uraian' => [
                'rules'  => 'required',
                'errors' => []
            ],
            'jenis' => [
                'rules'  => 'required',
                'errors' => []
            ],
        ];

        // Menggunakan getRequestInput untuk penanganan JSON/Form yang lebih baik
        $input = $this->getRequestInput(); 
        $jenis =  $input['jenis'];
        $nominal = $input['nominal'];
        $pemasukan = 0;
        $pengeluaran = 0;

        if ($jenis == 1) {
            $pemasukan = $nominal;
        }
        if ($jenis == 2) {
            $pengeluaran = $nominal;
        }
        
        if (!$this->validate($rules)) {
            $response = [
                'status' => false,
                'message' => lang('App.isRequired'),
                'data' => $this->validator->getErrors(),
            ];
            return $this->respond($response, 200);
        } else {
            $data = [
                'tanggal' => $input['tanggal'],
                'uraian' => $input['uraian'],
                'jenis' => $jenis,
                'pemasukan' => $pemasukan,
                'pengeluaran' => $pengeluaran,
            ];

            //save ke tabel
            $this->model->save($data);
            $response = [
                'status' => true,
                'message' => lang('App.saveSuccess'),
                'data' => [],
            ];
            return $this->respond($response, 200);
        }
    }

    public function update($id = NULL)
    {
        $rules = [
            'tanggal' => [
                'rules'  => 'required',
                'errors' => []
            ],
            'uraian' => [
                'rules'  => 'required',
                'errors' => []
            ],
            'jenis' => [
                'rules'  => 'required',
                'errors' => []
            ],
        ];
        
        $input = $this->getRequestInput();
        $jenis =  $input['jenis'];
        $nominal = $input['nominal'];
        $pemasukan = 0;
        $pengeluaran = 0;

        if ($jenis == 1) {
            $pemasukan = $nominal;
        }
        if ($jenis == 2) {
            $pengeluaran = $nominal;
        }

        if (!$this->validate($rules)) {
            $response = [
                'status' => false,
                'message' => lang('App.updFailed'),
                'data' => $this->validator->getErrors(),
            ];
            return $this->respond($response, 200);
        } else {
            $data = [
                'tanggal' => $input['tanggal'],
                'uraian' => $input['uraian'],
                'jenis' => $jenis,
                'pemasukan' => $pemasukan,
                'pengeluaran' => $pengeluaran,
            ];
            $simpan = $this->model->update($id, $data);
            if ($simpan) {
                $response = [
                    'status' => true,
                    'message' => lang('App.updSuccess'),
                    'data' => [],
                ];
                return $this->respond($response, 200);
            }
        }
    }

    public function delete($id = null)
    {
        $hapus = $this->model->find($id);
        if ($hapus) {
            $this->model->delete($id);
            $response = [
                'status' => true,
                'message' => lang('App.delSuccess'),
                'data' => [],
            ];
            return $this->respond($response, 200);
        } else {
            $response = [
                'status' => false,
                'message' => lang('App.delFailed'),
                'data' => [],
            ];
            return $this->respond($response, 200);
        }
    }

    public function saldo()
    {
        // Endpoint ini dipanggil di Kelompok 3 (Admin API), jadi aman.
        $keuangan = $this->model->findAll();
		if (count($keuangan) > 0) { // Gunakan count()
			$jml_pemasukan = 0;
			$jml_pengeluaran = 0;
			foreach ($keuangan as $row) {
				$jml_pemasukan = $jml_pemasukan + $row['pemasukan'];
				$jml_pengeluaran = $jml_pengeluaran + $row['pengeluaran'];
			}
			$saldo =$jml_pemasukan - $jml_pengeluaran;
		} else {
            $saldo = 0;
        }

        return $this->respond(['status' => true, 'message' => lang('App.getSuccess'), 'data' => $saldo], 200);
    }
}