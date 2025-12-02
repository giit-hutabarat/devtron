<?php

namespace  App\Modules\Layout\Controllers;
/*
IT Shop Purwokerto (Tokopedia, Shopee & Bukalapak)
Dibuat oleh: Hari Wicaksono, S.Kom
06-2022
*/

use App\Controllers\BaseController;
use App\Modules\Layout\Models\LayoutModel;
use App\Libraries\Settings;
use CodeIgniter\HTTP\ResponseInterface; // Tambahkan ini

class Layout extends BaseController
{
	protected $layout;
	protected $setting;

	public function __construct()
	{
        // PENTING: Pengecekan Login Status (isLoggedIn) SUDAH DITANGANI
        // oleh 'auth_session' Filter (jika Routes diaktifkan).

        // 🔒 GEMBOK Otorisasi: Cek Admin Level (user_type)
        // Jika User Biasa (Tipe 2) dilarang masuk sini.
        if (session()->get('user_type') == 2) {
            // Gunakan redirect() CI4 yang lebih bersih
            $response = service('response');
            $response->redirect(base_url('dashboard'))->send(); // Tendang ke Dashboard jika bukan Admin
            exit(); 
        }

		//memanggil Model
		$this->layout = new LayoutModel();
		$this->setting = new Settings();
	}


	public function index()
	{
		$nama_layout = $this->setting->info['layout'];
		$layout = $this->layout->where('value', $nama_layout)->first();
		$id_layout = $layout['id']; 
		return view('App\Modules\Layout\Views/layout', [
			'title' => 'Layout DISFO',
			'active' => $id_layout-1,
		]);
	}

}