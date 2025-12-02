<?php

namespace App\Controllers;

use App\Libraries\Settings;

class Home extends BaseController
{
	protected $setting;

	public function __construct()
	{
		// Load Library Settings
		$this->setting = new Settings();
	}

	public function index()
	{
        // KITA KIRIM DATA LENGKAP KE VIEW BIAR GAK ERROR
		$data = [
			'title'         => $this->setting->info['nama_aplikasi'] ?? 'TRON System',
            'nama_instansi' => $this->setting->info['nama_instansi'] ?? 'Instansi',
            'alamat'        => $this->setting->info['alamat'] ?? '',
            'logo'          => $this->setting->info['logo'] ?? 'logo.png',
			'background'    => $this->setting->info['background'] ?? ''
		];

		return view('home', $data);
	}

	public function setLanguage()
	{
		$lang = $this->request->uri->getSegments()[1];
		$this->session->set("lang", $lang);
		return redirect()->to(base_url());
	}
}