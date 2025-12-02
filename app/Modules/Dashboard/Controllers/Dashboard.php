<?php

namespace App\Modules\Dashboard\Controllers;

use App\Controllers\BaseController;
use App\Libraries\Settings; 

class Dashboard extends BaseController
{
    protected $setting;

    public function __construct()
    {
        // PENTING: Pengecekan Login Status (isLoggedIn) SUDAH DITANGANI
        // oleh 'auth_session' Filter di Routes.php. Kita hapus pengecekan itu disini.

        // Kita hanya mempertahankan pengecekan LEVEL ADMIN (user_type)
        if (session()->get('user_type') == 2) {
            // Jika user_type BUKAN admin, arahkan kembali ke halaman root
            $response = service('response');
            $response->redirect(base_url())->send();
            exit(); 
        }
        
        $this->setting = new Settings();
    }

    public function index()
    {
        // PENTING: Semua data yang diambil dari session HARUS menggunakan kunci yang sudah disepakati (misal: 'fullname')
        $data = [
            'title'         => 'Dashboard Admin',
            'nama_instansi' => $this->setting->info['nama_instansi'] ?? 'TRON',
            'alamat'        => $this->setting->info['alamat'] ?? '',
            'logo'          => $this->setting->info['logo'] ?? 'logo.png',
            'jmlUser'       => 100,
            'user_nama'    => session()->get('fullname')
        ];

        return view('App\Modules\Dashboard\Views\dashboard', $data);
    }
}