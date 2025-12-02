<?php

namespace App\Modules\Galeri\Controllers\Api;
/*
IT Shop Purwokerto (Tokopedia, Shopee & Bukalapak)
Dibuat oleh: Hari Wicaksono, S.Kom
11-2021
*/

use App\Controllers\BaseControllerApi;
use App\Modules\Galeri\Models\GaleriModel;
use CodeIgniter\HTTP\ResponseInterface;

class Galeri extends BaseControllerApi
{
    protected $format       = 'json';
    protected $modelName    = GaleriModel::class;

        public function __construct()
    {
        // 🔒 Otorisasi Level Admin
        // Hanya Admin (user_type 1) yang boleh mengakses semua fungsi CRUD di Controller ini.
        if (session()->get('user_type') == 2) {
            $response = service('response');
            $response->setStatusCode(ResponseInterface::HTTP_FORBIDDEN)
                     ->setJSON(['status' => false, 'message' => 'Akses Ditolak. Anda tidak memiliki hak untuk mengelola galeri.']);
            $response->send();
            exit(); 
        }

        // Asumsi BaseControllerApi sudah menangani inisialisasi yang dibutuhkan.
    }

    public function index()
    {
        return $this->respond(['status' => true, 'message' => lang('App.getSuccess'), 'data' => $this->model->findAll()], 200);
    }

    public function show($id = null)
    {
        return $this->respond(['status' => true, 'message' => lang('App.getSuccess'), 'data' => $this->model->find($id)], 200);
    }

    // --- PUBLIC DATA (Display) ---
    // Method ini tidak dilindungi Konstruktor, tapi route-nya juga publik. Tetap aman.
    public function display()
    {
        return $this->respond(['status' => true, 'message' => lang('App.getSuccess'), 'data' => $this->model->where('status', 1)->findAll()], 200);
    }

    public function create()
    {
        $rules = [
            'label' => [
                'rules'  => 'required',
                'errors' => []
            ],
            'deskripsi' => [
                'rules'  => 'required',
                'errors' => []
            ],
            'image_url' => [
                'rules'  => 'required',
                'errors' => []
            ],
            'status' => [
                'rules'  => 'required',
                'errors' => []
            ],
        ];


        if ($this->request->getJSON()) {
            $json = $this->request->getJSON();
            $data = [
                'label' => $json->label,
                'deskripsi' => $json->deskripsi,
                'image_url' => $json->image_url,
                'status' => $json->status,
            ];
        } else {
            $data = [
                'label' => $this->request->getPost('label'),
                'deskripsi' => $this->request->getPost('deskripsi'),
                'image_url' => $this->request->getPost('image_url'),
                'status' => $this->request->getPost('status'),
            ];
        }
                    $input = $this->getRequestInput(); // Dari BaseControllerApi
                    
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
            'label' => [
                'rules'  => 'required',
                'errors' => []
            ],
            'deskripsi' => [
                'rules'  => 'required',
                'errors' => []
            ],
        ];

        if ($this->request->getJSON()) {
            $json = $this->request->getJSON();
            $image = $json->image_url;
            if ($image != '') {
                $data = [
                    'label' => $json->label,
                    'deskripsi' => $json->deskripsi,
                    'image_url' => $image,
                ];
            } else {
                $data = [
                    'label' => $json->label,
                    'deskripsi' => $json->deskripsi,
                ];
            }
        } else {
            $input = $this->request->getRawInput();
            $image = $input->image_url;
            if ($image != '') {
                $data = [
                    'label' => $input->label,
                    'deskripsi' => $input->deskripsi,
                    'image_url' => $image,
                ];
            } else {
                $data = [
                    'label' => $input->label,
                    'deskripsi' => $input->deskripsi,
                ];
            }
        }

        if (!$this->validate($rules)) {
            $response = [
                'status' => false,
                'message' => lang('App.updFailed'),
                'data' => $this->validator->getErrors(),
            ];
            return $this->respond($response, 200);
        } else {
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
        $image = $hapus['image_url'];
        if ($hapus) {
            unlink($image);
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

    public function upload()
    {
        //$id = $this->request->getVar('id');
        $image = $this->request->getFile('image');
        $fileName = $image->getRandomName();
        if ($image !== "") {
            $path = "images/";
            $moved = $image->move($path, $fileName);
            if ($moved) {
                return $this->respond(["status" => true, "message" => lang('App.imgSuccess'), "data" => ["url" => $path . $fileName]], 200);
            } else {
                return $this->respond(["status" => false, "message" => lang('App.imgFailed'), "data" => []], 200);
            }
        } else {
            $response = [
                'status' => false,
                'message' => lang('App.uploadFailed'),
                'data' => []
            ];
            return $this->respond($response, 200);
        }
    }

    public function setAktif($id = NULL)
    {
        if ($this->request->getJSON()) {
            $json = $this->request->getJSON();
            $status = $json->status;
            $data = [
                'status' => $status,
            ];
        } else {
            $input = $this->request->getRawInput();
            $status = $input['status'];
            $data = [
                'status' => $status,
            ];
        }

        if ($data > 0) {
            $this->model->update($id, $data);
            $response = [
                'status' => true,
                'message' => lang('App.updSuccess'),
                'data' => []
            ];
            return $this->respond($response, 200);
        } else {
            $response = [
                'status' => false,
                'message' => lang('App.updFailed'),
                'data' => []
            ];
            return $this->respond($response, 200);
        }
    }
}
