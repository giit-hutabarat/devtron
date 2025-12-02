<?php

namespace App\Modules\Masjid\Controllers\Api;
/*
IT Shop Purwokerto (Tokopedia, Shopee & Bukalapak)
Dibuat oleh: Hari Wicaksono, S.Kom
06-2022
*/

use App\Controllers\BaseControllerApi;
use App\Modules\Masjid\Models\AgamaquotesModel;
use App\Libraries\Settings;
use CodeIgniter\HTTP\ResponseInterface; // Tambahkan ini untuk 403 Forbidden

class AgamaQuotes extends BaseControllerApi
{
    protected $format       = 'json';
    protected $modelName    = AgamaquotesModel::class;
    protected $setting;

    public function __construct()
	{
        // 🔒 Otorisasi Level Admin
        // Hanya Admin (user_type 1) yang boleh mengakses semua fungsi CRUD di Controller ini.
        if (session()->get('user_type') == 2) {
            $response = service('response');
            $response->setStatusCode(ResponseInterface::HTTP_FORBIDDEN)
                     ->setJSON(['status' => false, 'message' => 'Akses Ditolak. Anda tidak memiliki hak untuk mengelola Quotes Agama.']);
            $response->send();
            exit(); 
        }
        
		//memanggil Model
		//$this->setting = new Settings();
        $this->setting = new \App\Libraries\Settings(); // Menginisiasi $setting
        $this->model = new \App\Modules\Masjid\Models\AgamaQuotesModel(); // Menginisiasi $model
	}

    // --- CRUD: GET ALL DATA (Dilindungi Otorisasi di Konstruktor) ---
    public function index()
    {
        return $this->respond(['status' => true, 'message' => lang('App.getSuccess'), 'data' => $this->model->findAll()], 200);
    }

    // --- CRUD: GET SINGLE DATA ---
    public function show($id = null)
    {
        return $this->respond(['status' => true, 'message' => lang('App.getSuccess'), 'data' => $this->model->find($id)], 200);
    }

    // --- PUBLIC DISPLAY ---
    // Method ini tidak dilindungi Konstruktor, tapi route-nya publik (Kelompok 2 di Routes.php), aman.
    public function display()
    {
        return $this->respond(['status' => true, 'message' => lang('App.getSuccess'), 'data' => $this->model->orderBy('id', 'DESC')->findAll()], 200);
    }

    // --- CRUD: CREATE DATA ---
    public function create()
    {
        $rules = [
            'isi_quotes' => [
                'rules'  => 'required',
                'errors' => []
            ],
            'suratriwayat' => [
                'rules'  => 'required',
                'errors' => []
            ],
        ];

        $input = $this->getRequestInput();

        if (!$this->validate($rules)) {
            $response = [
                'status' => false,
                'message' => lang('App.isRequired'),
                'data' => $this->validator->getErrors(),
            ];
            return $this->respond($response, 200);
        } else {
            $data = [
                'isi_quotes' => $input['isi_quotes'],
                'suratriwayat' => $input['suratriwayat'],
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

    // --- CRUD: UPDATE DATA ---
    public function update($id = NULL)
    {
        $rules = [
            'isi_quotes' => [
                'rules'  => 'required',
                'errors' => []
            ],
            'suratriwayat' => [
                'rules'  => 'required',
                'errors' => []
            ],
        ];

        $input = $this->getRequestInput();

        if (!$this->validate($rules)) {
            $response = [
                'status' => false,
                'message' => lang('App.updFailed'),
                'data' => $this->validator->getErrors(),
            ];
            return $this->respond($response, 200);
        } else {
            $data = [
                'isi_quotes' => $input['isi_quotes'],
                'suratriwayat' => $input['suratriwayat'],
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

    // --- CRUD: DELETE DATA ---
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
}