<?php

namespace App\Modules\Agenda\Controllers\Api;
/*
IT Shop Purwokerto (Tokopedia, Shopee & Bukalapak)
Dibuat oleh: Hari Wicaksono, S.Kom
11-2021
*/

use App\Controllers\BaseControllerApi;
use App\Modules\Agenda\Models\AgendaModel;
use App\Libraries\Settings;
use CodeIgniter\HTTP\ResponseInterface; // Tambahkan ini untuk 403 Forbidden

class Agenda extends BaseControllerApi
{
    protected $format       = 'json';
    protected $modelName    = AgendaModel::class;
    protected $setting;

    public function __construct()
	{
        // 🔒 Otorisasi Level Admin
        // Hanya Admin (user_type 1) yang boleh mengakses semua fungsi CRUD di Controller ini.
        if (session()->get('user_type') == 2) {
            $response = service('response');
            $response->setStatusCode(ResponseInterface::HTTP_FORBIDDEN)
                     ->setJSON(['status' => false, 'message' => 'Akses Ditolak. Anda tidak memiliki hak untuk mengelola Agenda.']);
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
        $bulan = date("n");
        $limit = $this->setting->info['limit_agenda'];
        return $this->respond(['status' => true, 'message' => lang('App.getSuccess'), 'data' => $this->model->where('MONTH(tgl_agenda)', $bulan)->orderBy('tgl_agenda', 'DESC')->findAll($limit)], 200);
    }

    public function create()
    {
        $rules = [
            'nama_agenda' => [
                'rules'  => 'required',
                'errors' => []
            ],
            'tempat_agenda' => [
                'rules'  => 'required',
                'errors' => []
            ],
            'tgl_agenda' => [
                'rules'  => 'required',
                'errors' => []
            ],
            'waktu' => [
                'rules'  => 'required',
                'errors' => []
            ],
        ];

        if ($this->request->getJSON()) {
            $json = $this->request->getJSON();
            $data = [
                'nama_agenda' => $json->nama_agenda,
                'tempat_agenda' => $json->tempat_agenda,
                'tgl_agenda' => $json->tgl_agenda,
                'waktu' => $json->waktu,
                'jenis_agenda' => 1,
            ];
        } else {
            // Menggunakan getRequestInput() dari BaseControllerApi untuk konsistensi
            $data = $this->getRequestInput(); 
            $data = [
                'nama_agenda' => $data['nama_agenda'],
                'tempat_agenda' => $data['tempat_agenda'],
                'tgl_agenda' => $data['tgl_agenda'],
                'waktu' => $data['waktu'],
                'jenis_agenda' => $data['jenis_agenda'] ?? 1,
            ];
        }

        if (!$this->validate($rules)) {
            $response = [
                'status' => false,
                'message' => lang('App.isRequired'),
                'data' => $this->validator->getErrors(),
            ];
            return $this->respond($response, 200);
        } else {
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
            'nama_agenda' => [
                'rules'  => 'required',
                'errors' => []
            ],
            'tempat_agenda' => [
                'rules'  => 'required',
                'errors' => []
            ],
            'tgl_agenda' => [
                'rules'  => 'required',
                'errors' => []
            ],
            'waktu' => [
                'rules'  => 'required',
                'errors' => []
            ],
        ];

        // Menggunakan getRequestInput() dari BaseControllerApi
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
                'nama_agenda' => $input['nama_agenda'],
                'tempat_agenda' => $input['tempat_agenda'],
                'tgl_agenda' => $input['tgl_agenda'],
                'waktu' => $input['waktu'],
                'jenis_agenda' => $input['jenis_agenda'] ?? 1,
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
}