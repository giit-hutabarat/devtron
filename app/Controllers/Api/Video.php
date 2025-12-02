<?php

namespace App\Modules\Video\Controllers\Api;
/*
IT Shop Purwokerto (Tokopedia, Shopee & Bukalapak)
Dibuat oleh: Hari Wicaksono, S.Kom
06-2022
*/

use App\Controllers\BaseControllerApi;
use App\Modules\Video\Models\VideoModel;
use App\Libraries\Settings;

class Video extends BaseControllerApi
{
    protected $format       = 'json';
    protected $modelName    = VideoModel::class;

    public function __construct()
    {
        //memanggil Model
        $this->setting = new Settings();
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
        if ($this->setting->info['video_youtube'] == 'no') {
            $data = $this->model->where(['source' => 1, 'status' => 1])->orderBy('upload_time', 'DESC')->findAll();
            $result = array();
            foreach ($data as $video) {
                $alamat = base_url() . '/' . $video['video_url'];
                array_push($result, array($alamat));
            }
        } else {
            $result = [];
        }

        return $this->respond(['status' => true, 'message' => lang('App.getSuccess'), 'data' => $result], 200);
    }

    public function create()
    {
        $rules = [
            'judul' => [
                'rules'  => 'required',
                'errors' => []
            ],
            'status' => [
                'rules'  => 'required',
                'errors' => []
            ],
            'source' => [
                'rules'  => 'required',
                'errors' => []
            ],
        ];

        if ($this->request->getJSON()) {
            $json = $this->request->getJSON();
            $data = [
                'judul' => $json->judul,
                'source' => $json->source,
                'video_url' => $json->video_url,
                'kode_youtube' => $json->kode_youtube,
                'upload_time' => date('Y-m-d H:i:s'),
                'status' => $json->status,
            ];
        } else {
            $data = [
                'judul' => $this->request->getPost('judul'),
                'source' => $this->request->getPost('source'),
                'video_url' => $this->request->getPost('video_url'),
                'kode_youtube' => $this->request->getPost('kode_youtube'),
                'upload_time' => date('Y-m-d H:i:s'),
                'status' => $this->request->getPost('status'),
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
            'judul' => [
                'rules'  => 'required',
                'errors' => []
            ],
        ];

        if ($this->request->getJSON()) {
            $json = $this->request->getJSON();
            $data = [
                'judul' => $json->judul,
            ];
        } else {
            $input = $this->request->getRawInput();
            $data = [
                'judul' => $input->judul,
            ];
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
        $source = $hapus['source'];
        $video = $hapus['video_url'];
        if ($hapus) {
            if ($source == '1' && $video != '') :
                unlink($video);
            endif;

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
        $video = $this->request->getFile('video');
        $fileName = $video->getRandomName();
        if ($video !== "") {
            $path = "videos/";
            $moved = $video->move($path, $fileName);
            if ($moved) {
                return $this->respond(["status" => true, "message" => lang('App.videoSuccess'), "data" => ["url" => $path . $fileName]], 200);
            } else {
                return $this->respond(["status" => false, "message" => lang('App.videoFailed'), "data" => []], 200);
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
