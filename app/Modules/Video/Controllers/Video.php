<?php

namespace App\Modules\Video\Controllers;

use App\Controllers\BaseController;
use App\Libraries\Settings;
use CodeIgniter\HTTP\ResponseInterface; // Tambahkan ini untuk menggunakan response service

class Video extends BaseController
{
    protected $setting;

    public function __construct()
    {
        // PENTING: Pengecekan Login Status (isLoggedIn) SUDAH DITANGANI
        // oleh 'auth_session' Filter di Routes.php. Kita hapus GEMBOK 1.

        // 🔒 GEMBOK 2: Cek Admin Level (Otorisasi)
        // User biasa (Tipe 2) dilarang masuk
        if (session()->get('user_type') == 2) {
            // Ganti header() dan exit() dengan metode redirect CI4 yang benar
            $response = service('response');
            $response->redirect(base_url('dashboard'))->send(); // Tendang ke Dashboard
            exit();
        }

        $this->setting = new Settings();
    }

    public function index()
    {
        // Panggil View dengan Namespace Lengkap
        return view('App\Modules\Video\Views\video', [
            'title' => 'Video Playlist',
            // Kirim info setting youtube (yes/no) biar view tau mau nampilin apa
            'videoYoutube' => $this->setting->info['video_youtube'] ?? 'no',
        ]);
    }
}