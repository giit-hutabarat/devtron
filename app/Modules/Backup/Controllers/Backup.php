<?php

namespace  App\Modules\Backup\Controllers;
/*
IT Shop Purwokerto (Tokopedia, Shopee & Bukalapak)
Dibuat oleh: Hari Wicaksono, S.Kom
11-2021
*/

use App\Controllers\BaseController;
use App\Libraries\Settings;
use CodeIgniter\HTTP\ResponseInterface; // Tambahkan ini

class Backup extends BaseController
{
	protected $setting;

	public function __construct()
	{
        // PENTING: Pengecekan Login Status (isLoggedIn) SUDAH DITANGANI
        // oleh 'auth_session' Filter di Routes.php.

        // 🔒 GEMBOK Otorisasi: Cek Level Super Admin
        // Hanya Super Admin (Tipe 1) yang bisa mengakses halaman backup.
        if (session()->get('user_type') != 1) {
            // Jika bukan Super Admin, tendang ke Dashboard
            $response = service('response');
            $response->redirect(base_url('dashboard'))->send(); 
            exit(); 
        }
        
		//memanggil Model
		$this->setting = new Settings();
	}


	public function index()
	{
		return view('App\Modules\Backup\Views/view', [
			'title' => 'Backup Database'
		]);
	}

}