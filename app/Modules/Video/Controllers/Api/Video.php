<?php

namespace App\Modules\Video\Controllers\Api;

// *** PENTING: UBAH INI ***
use App\Controllers\BaseControllerApi; 
// use App\Controllers\BaseController; // Hapus yang ini
// *** PENTING: UBAH INI ***

use App\Modules\Video\Models\VideoModel;
use App\Libraries\Settings;
use CodeIgniter\HTTP\ResponseInterface; // Tambahkan ini untuk 403 Forbidden

class Video extends BaseControllerApi // UBAH ke BaseControllerApi
{
    protected $model;
    protected $setting;

    public function __construct()
    {
        // 🔒 Otorisasi Level Admin
        // Hanya Admin (user_type 1) yang boleh mengakses semua fungsi CRUD di Controller ini.
        if (session()->get('user_type') == 2) {
            $response = service('response');
            $response->setStatusCode(ResponseInterface::HTTP_FORBIDDEN)
                     ->setJSON(['status' => false, 'message' => 'Akses Ditolak. Anda tidak memiliki hak untuk mengelola video.']);
            $response->send();
            exit(); 
        }

        $this->model = new VideoModel();
        $this->setting = new Settings();
    }

    // --- GET ALL DATA ---
    public function index()
    {
        // 🔒 Logic Session Dihapus, sudah di-handle oleh Filter 'auth_session' di Routes.
        return $this->respond([
            'status' => true,
            'message' => lang('App.getSuccess'),
            'data' => $this->model->findAll()
        ], 200);
    }

    // --- GET SINGLE DATA ---
    public function show($id = null)
    {
        // 🔒 Logic Session Dihapus
        return $this->respond([
            'status' => true,
            'message' => lang('App.getSuccess'),
            'data' => $this->model->find($id)
        ], 200);
    }

    // --- PUBLIC DISPLAY (AMAN: TIDAK ADA GEMBOK DI KONSTRUKTOR) ---
    public function display()
    {
        if (($this->setting->info['video_youtube'] ?? 'no') == 'no') {
            $data = $this->model->where(['source' => 1, 'status' => 1])->orderBy('upload_time', 'DESC')->findAll();
            $result = array();
            foreach ($data as $video) {
                $alamat = base_url() . '/' . $video['video_url'];
                array_push($result, array($alamat));
            }
        } else {
            $result = [];
        }

        return $this->respond([
            'status' => true,
            'message' => lang('App.getSuccess'),
            'data' => $result
        ], 200);
    }

    // --- CREATE DATA ---
    public function create()
    {
        // 🔒 Logic Session Dihapus
        $rules = [
            'judul' => 'required',
            'status' => 'required',
            'source' => 'required',
        ];

        // Menggunakan $this->validate() dari BaseControllerApi
        if (!$this->validate($rules)) {
            return $this->getResponse(
                [
                    'status' => false,
                    'message' => lang('App.isRequired'),
                    'data' => $this->validator->getErrors(),
                ],
                ResponseInterface::HTTP_OK
            );
        }

        $input = $this->getRequestInput(); // Dari BaseControllerApi

        $data = [
            'judul' => $input['judul'],
            'source' => $input['source'],
            'video_url' => $input['video_url'] ?? '',
            'kode_youtube' => $input['kode_youtube'] ?? '',
            'upload_time' => date('Y-m-d H:i:s'),
            'status' => $input['status'],
        ];

        $this->model->save($data);
        return $this->respond(['status' => true, 'message' => lang('App.saveSuccess')], 200);
    }

    // --- UPDATE DATA ---
    public function update($id = NULL)
    {
        // 🔒 Logic Session Dihapus
        $input = $this->getRequestInput(); // Dari BaseControllerApi
        $data = ['judul' => $input['judul']];
        
        $this->model->update($id, $data);
        return $this->respond(['status' => true, 'message' => lang('App.updSuccess')], 200);
    }

    // --- DELETE DATA ---
    public function delete($id = null)
    {
        // 🔒 Logic Session Dihapus
        $hapus = $this->model->find($id);
        if ($hapus) {
            if ($hapus['source'] == '1' && $hapus['video_url'] != '' && file_exists($hapus['video_url'])) {
                unlink($hapus['video_url']);
            }
            $this->model->delete($id);
            return $this->respond(['status' => true, 'message' => lang('App.delSuccess')], 200);
        }
        return $this->respond(['status' => false, 'message' => lang('App.delFailed')], 200);
    }

    // --- UPLOAD ---
    public function upload()
    {
        // 🔒 Logic Session Dihapus
        $video = $this->request->getFile('video');
        if ($video && $video->isValid() && !$video->hasMoved()) {
            $fileName = $video->getRandomName();
            $path = "videos/"; 
            if ($video->move($path, $fileName)) {
                return $this->respond(["status" => true, "message" => lang('App.videoSuccess'), "data" => ["url" => $path . $fileName]], 200);
            }
        }
        return $this->respond(["status" => false, "message" => lang('App.uploadFailed')], 200);
    }

    // --- SET AKTIF ---
    public function setAktif($id = NULL)
    {
        // 🔒 Logic Session Dihapus
        $input = $this->getRequestInput(); // Dari BaseControllerApi
        $this->model->update($id, ['status' => $input['status']]);
        
        return $this->respond(['status' => true, 'message' => lang('App.updSuccess')], 200);
    }
    
    // Method failUnauthorized() dihapus karena tidak lagi diperlukan.
}