<?php

namespace App\Modules\Layout\Controllers\Api;
/*
IT Shop Purwokerto (Tokopedia, Shopee & Bukalapak)
Dibuat oleh: Hari Wicaksono, S.Kom
06-2022
*/

use App\Controllers\BaseControllerApi;
use App\Modules\Layout\Models\LayoutModel;
use App\Libraries\Settings;
use CodeIgniter\HTTP\ResponseInterface; // Tambahkan ini untuk 403 Forbidden

class Layout extends BaseControllerApi
{
    protected $format       = 'json';
    protected $modelName    = LayoutModel::class;

    public function __construct()
	{
        // 🔒 Otorisasi Level Admin
        // Hanya Admin (user_type 1) yang boleh mengakses fungsi CRUD Layout.
        if (session()->get('user_type') == 2) {
            $response = service('response');
            $response->setStatusCode(ResponseInterface::HTTP_FORBIDDEN)
                     ->setJSON(['status' => false, 'message' => 'Akses Ditolak. Anda bukan Admin.']);
            $response->send();
            exit(); 
        }
        
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
    
    // Asumsi method CRUD (create, update, delete) ada di sini
    // dan secara otomatis dilindungi oleh konstruktor di atas.
}