<?php

namespace  App\Modules\Display\Controllers;
/*
IT Shop Purwokerto (Tokopedia, Shopee & Bukalapak)
Dibuat oleh: Hari Wicaksono, S.Kom
11-2021
*/

use App\Controllers\BaseController;
use App\Modules\Video\Models\VideoModel;
use App\Libraries\Settings;

class Display extends BaseController
{
	protected $setting;
	protected $video; // ✅ PERBAIKAN: Deklarasi properti $video

	public function __construct()
	{
		//memanggil Model
		$this->setting = new Settings();
		$this->video = new VideoModel();
	}

	public function index()
	{
		switch ($this->setting->info['layout']) {
			case 'layout_2':
				$background = $this->setting->info['background'];
				$content = 'App\Modules\Display\Views\display/layout_2';
				break;
			case 'layout_3':
				$background = $this->setting->info['background'];
				$content = 'App\Modules\Display\Views\display/layout_3';
				break;
			case 'layout_4':
				$background = $this->setting->info['background'];
				$content = 'App\Modules\Display\Views\display/layout_4';
				break;
			case 'layout_5':
				$background = $this->setting->info['background'];
				$content = 'App\Modules\Display\Views\display/layout_5';
				break;
			case 'layout_6':
				$background = $this->setting->info['background'];
				$content = 'App\Modules\Display\Views\display/layout_6';
				break;
			case 'layout_7':
				$background = $this->setting->info['background'];
				$content = 'App\Modules\Display\Views\display/layout_7';
				break;
			case 'layout_8':
				$background = $this->setting->info['background'];
				$content = 'App\Modules\Display\Views\display/layout_8';
				break;
			case 'layout_9':
				$background = $this->setting->info['background'];
				$content = 'App\Modules\Display\Views\display/layout_9';
				break;
			case 'layout_10':
				$background = $this->setting->info['background'];
				$content = 'App\Modules\Display\Views\display/layout_10';
				break;
			case 'layout_11':
				$background = $this->setting->info['background_masjid'];
				$content = 'App\Modules\Display\Views\display/layout_11';
				break;
			case 'layout_12':
				$background = $this->setting->info['background_masjid'];
				$content = 'App\Modules\Display\Views\display/layout_12';
				break;
			case 'layout_13':
				$background = $this->setting->info['background_masjid'];
				$content = 'App\Modules\Display\Views\display/layout_13';
				break;
			default:
				$background = $this->setting->info['background'];
				$content = 'App\Modules\Display\Views\display/layout_1';
		}

		//Video MP4 Muted
		$video_muted = $this->setting->info['video_muted'];
		if ($video_muted == 'yes') {
			$muted = 'muted';
		} else {
			$muted = '';
		}

		//Video Youtube
		$video = $this->video->where(['source' => 2, 'status' => 1])->orderBy('upload_time', 'DESC')->first();
		if ($video) {
			$videoYT = $video['kode_youtube'];
		}

		return view('App\Modules\Display\Views/view', [
			'title' => $this->setting->info['nama_aplikasi'],
			'background' => $background,
			'logo' => $this->setting->info['logo'],
			'nama_instansi' => $this->setting->info['nama_instansi'],
			'alamat' => $this->setting->info['alamat'],
			'news_refresh' => $this->setting->info['news_refresh'],
			'agenda_refresh' => $this->setting->info['agenda_refresh'],
			'slide_refresh' => $this->setting->info['slide_refresh'],
			'jadwal_sholat' => $this->setting->info['jadwal_sholat'],
			'video_youtube' => $this->setting->info['video_youtube'],
			'videoId' => $videoYT ?? "0",
			'video_muted' => $muted,
			'content' => $content,
		]);
	}
}