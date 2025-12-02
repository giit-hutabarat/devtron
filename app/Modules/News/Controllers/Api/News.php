<?php

namespace App\Modules\News\Controllers\Api;
/*
IT Shop Purwokerto (Tokopedia, Shopee & Bukalapak)
Dibuat oleh: Hari Wicaksono, S.Kom
11-2021
*/

use App\Controllers\BaseControllerApi;
use App\Modules\News\Models\NewsModel;
use App\Libraries\Settings;
use CodeIgniter\HTTP\ResponseInterface; // Tambahkan ini untuk 403 Forbidden

class News extends BaseControllerApi
{
    protected $format       = 'json';
    protected $modelName    = NewsModel::class;
    protected $setting;
    protected $model; // Deklarasikan model di sini jika tidak otomatis

    public function __construct()
	{
        // 🔒 Otorisasi Level Admin
        // Karena Controller ini menangani SEMUA endpoint CRUD yang sensitif (sesuai Routes.php),
        // kita amankan di Konstruktor. Hanya Admin (user_type 1) yang boleh.
        if (session()->get('user_type') == 2) {
            $response = service('response');
            $response->setStatusCode(ResponseInterface::HTTP_FORBIDDEN)
                     ->setJSON(['status' => false, 'message' => 'Akses Ditolak. Anda tidak memiliki hak untuk mengelola berita.']);
            $response->send();
            exit(); 
        }

		//memanggil Model
		$this->setting = new Settings();
        // Anda mungkin perlu inisialisasi Model di sini jika tidak menggunakan $modelName di BaseControllerApi
        // $this->model = new NewsModel(); 
	}

    // --- ADMIN CRUD: GET ALL DATA (Dilindungi Otorisasi di Konstruktor) ---
    public function index()
    {
        return $this->respond(['status' => true, 'message' => lang('App.getSuccess'), 'data' => $this->model->findAll()], 200);
    }

    // --- ADMIN CRUD: GET SINGLE DATA ---
    public function show($id = null)
    {
        return $this->respond(['status' => true, 'message' => lang('App.getSuccess'), 'data' => $this->model->find($id)], 200);
    }

    // --- PUBLIC DISPLAY: NEWS ---
    // Route ini berada di group PUBLIK (TIDAK ADA FILTER), aman.
    public function news()
    {
        return $this->respond(['status' => true, 'message' => lang('App.getSuccess'), 'data' => $this->model->where('jenis_news', '1')->orderBy('id', 'DESC')->findAll()], 200);
    }

    // --- PUBLIC DISPLAY: INFO ---
    public function info()
    {
        $limit = $this->setting->info['limit_info'];
        return $this->respond(['status' => true, 'message' => lang('App.getSuccess'), 'data' => $this->model->where('jenis_news', '2')->orderBy('id', 'DESC')->findAll($limit)], 200);
    }

    // --- PUBLIC DISPLAY: MASJID ---
    public function masjid()
    {
        $limit = $this->setting->info['limit_info'];
        return $this->respond(['status' => true, 'message' => lang('App.getSuccess'), 'data' => $this->model->where('jenis_news', '3')->orderBy('id', 'DESC')->findAll($limit)], 200);
    }
    
    // --- Helper yang tidak relevan dengan CRUD dihapus (e.g. where)
    public function where($kriteria = null)
    {
        // PENTING: Jika method ini tidak digunakan untuk CRUD, biarkan saja.
        // Jika digunakan untuk data publik, tidak perlu diotentikasi.
        $query = $this->model->where(['kriteria' => $kriteria])->findAll();
        $data = array();
        foreach ($query as $row) {
            $data[] = $row['nama_kriteria'];
        }
        return $this->respond([
            'status' => true,
            'message' => lang('App.getSuccess'),
            'data' => $data
        ], 200);
    }


    // --- ADMIN CRUD: CREATE DATA (Dilindungi Otorisasi di Konstruktor) ---
    public function create()
    {
        $rules = [
            'tgl_news' => [ 'rules'  => 'required', 'errors' => [] ],
            'jenis_news' => [ 'rules'  => 'required', 'errors' => [] ],
            'text_news' => [ 'rules'  => 'required', 'errors' => [] ],
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
                'tgl_news' => $input['tgl_news'],
                'text_news' => $input['text_news'],
                'jenis_news' => $input['jenis_news'],
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

    // --- ADMIN CRUD: UPDATE DATA ---
    public function update($id = NULL)
    {
        $rules = [
            'tgl_news' => [ 'rules'  => 'required', 'errors' => [] ],
            'jenis_news' => [ 'rules'  => 'required', 'errors' => [] ],
            'text_news' => [ 'rules'  => 'required', 'errors' => [] ],
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
                'tgl_news' => $input['tgl_news'],
                'text_news' => $input['text_news'],
                'jenis_news' => $input['jenis_news'],
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

    // --- ADMIN CRUD: DELETE DATA ---
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