<?php

namespace  App\Modules\Setting\Controllers;
/*
IT Shop Purwokerto (Tokopedia, Shopee & Bukalapak)
Dibuat oleh: Hari Wicaksono, S.Kom
11-2021
*/

use App\Controllers\BaseController;
use App\Libraries\Settings;

class Setting extends BaseController
{
	protected $setting;

	public function __construct()
	{
		//memanggil Model
		$this->setting = new Settings();
	}

	public function general()
	{
		return view('App\Modules\Setting\Views/setting_general', [
			'title' => 'Pengaturan Umum'
		]);
	}

	public function app()
	{
		return view('App\Modules\Setting\Views/setting_app', [
			'title' => 'Pengaturan Aplikasi'
		]);
	}

}



