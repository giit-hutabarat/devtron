<?php

namespace  App\Modules\Cuaca\Controllers;
/*
IT Shop Purwokerto (Tokopedia, Shopee & Bukalapak)
Dibuat oleh: Hari Wicaksono, S.Kom
06-2022
*/

use App\Controllers\BaseController;
use App\Libraries\Settings;
use CodeIgniter\HTTP\ResponseInterface; // Tambahkan ini

class Cuaca extends BaseController
{
	protected $setting;

	public function __construct()
	{
        // PENTING: Pengecekan Login Status (isLoggedIn) SUDAH DITANGANI
        // oleh 'auth_session' Filter di Routes.php.

        // 🔒 GEMBOK Otorisasi: Cek Admin Level (user_type)
        // Jika User Biasa (Tipe 2) dilarang masuk sini.
        if (session()->get('user_type') == 2) {
            // Gunakan redirect() CI4 yang lebih bersih
            $response = service('response');
            $response->redirect(base_url('dashboard'))->send(); // Tendang ke Dashboard jika bukan Admin
            exit(); 
        }
        
		//memanggil Model
		$this->setting = new Settings();
	}


	public function index()
	{
		return view('App\Modules\Cuaca\Views/cuaca', [
			'title' => 'Prakiraan Cuaca',
		]);
	}

}