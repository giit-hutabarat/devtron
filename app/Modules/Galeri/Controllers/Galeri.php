<?php

namespace  App\Modules\Galeri\Controllers;
/*
IT Shop Purwokerto (Tokopedia, Shopee & Bukalapak)
Dibuat oleh: Hari Wicaksono, S.Kom
11-2021
*/

use App\Controllers\BaseController;
use App\Libraries\Settings;

class Galeri extends BaseController
{
	protected $setting;

	public function __construct()
	{
        // PENTING: Pengecekan Login Status (isLoggedIn) SUDAH DITANGANI
        // oleh 'auth_session' Filter di Routes.php.

        // 🔒 GEMBOK Otorisasi: Cek Admin Level (user_type)
        // Jika User Biasa (Tipe 2) dilarang masuk sini, kita pertahankan pengecekan ini.
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
		return view('App\Modules\Galeri\Views/galeri', [
			'title' => 'Galeri'
		]);
	}

}