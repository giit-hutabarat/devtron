<?php

namespace  App\Modules\Masjid\Controllers;
/*
IT Shop Purwokerto (Tokopedia, Shopee & Bukalapak)
Dibuat oleh: Hari Wicaksono, S.Kom
11-2021
*/

use App\Controllers\BaseController;
use App\Libraries\Settings;
use CodeIgniter\HTTP\ResponseInterface; // Tambahkan ini

class Masjid extends BaseController
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


	public function jadwalSholat()
	{
		return view('App\Modules\Masjid\Views/jadwalsholat', [
			'title' => 'Jadwal Sholat'
		]);
	}

	public function agamaQuotes()
	{
		return view('App\Modules\Masjid\Views/agamaquotes', [
			'title' => 'Quotes Agama'
		]);
	}

	public function keuanganMasjid()
	{
		return view('App\Modules\Masjid\Views/keuanganmasjid', [
			'title' => 'Keuangan Masjid',
		]);
	}
}