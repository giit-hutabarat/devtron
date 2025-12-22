<?php

namespace App\Modules\Display\Controllers;

use App\Controllers\BaseController;
use App\Modules\Video\Models\VideoModel;
use App\Libraries\Settings;

class Display extends BaseController
{
    protected $setting;
    protected $video;

    public function __construct()
    {
        $this->setting = new Settings();
        $this->video = new VideoModel();
    }

    public function index()
    {
        // 1. Tentukan View Layout Spesifik
        $layoutName = $this->setting->info['layout'] ?? 'layout_9';
        // Pastikan path ini sesuai dengan lokasi file layout Anda yang sebenarnya
        $viewPath = 'App\Modules\Display\Views\display\\' . $layoutName; 

        // 2. Logic Video (Tetap)
        $video_muted = ($this->setting->info['video_muted'] == 'yes') ? 'muted' : '';
        $video = $this->video->where(['source' => 2, 'status' => 1])->orderBy('upload_time', 'DESC')->first();
        $videoYT = $video ? $video['kode_youtube'] : "0";

        // 3. Kirim Data langsung ke View Layout Spesifik
        return view($viewPath, [
            'title' => $this->setting->info['nama_aplikasi'],
            'background' => $this->setting->info['background'], // Background dinamis
            'logo' => $this->setting->info['logo'],
            'nama_instansi' => $this->setting->info['nama_instansi'],
            'alamat' => $this->setting->info['alamat'],
            'news_refresh' => $this->setting->info['news_refresh'],
            'agenda_refresh' => $this->setting->info['agenda_refresh'],
            'slide_refresh' => $this->setting->info['slide_refresh'],
            'jadwal_sholat' => $this->setting->info['jadwal_sholat'],
            'video_youtube' => $this->setting->info['video_youtube'],
            'videoId' => $videoYT,
            'video_muted' => $video_muted,
        ]);
    }
}