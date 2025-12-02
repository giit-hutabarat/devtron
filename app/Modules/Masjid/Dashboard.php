<?php

namespace App\Modules\Dashboard\Controllers;

use App\Controllers\BaseController;
use App\Libraries\Settings; 

class Dashboard extends BaseController
{
    protected $setting;

    public function __construct()
    {
        // 1. PENGAMANAN LEVEL USER (TIDAK DITANGANI OLEH Filter)
        // Pengecekan Login Status (logged_in) SUDAH DITANGANI oleh 'auth_session' Filter di Routes.php.
        // Kita hanya perlu memastikan pengguna ini memiliki hak akses (user_type).

        // CATATAN: Kunci session 'user_type' yang diset di Auth.php adalah kunci yang digunakan.
        if (session()->get('user_type') == 2) {
            // Jika user_type BUKAN admin (misal type 2), arahkan kembali ke halaman login.
            // Gunakan fungsi redirect() CI4, bukan header() dan exit().
            $response = service('response');
            
            // Redirect ke halaman root (biasanya halaman login)
            $response->redirect(base_url())->send();
            exit(); // Penting untuk menghentikan eksekusi
        }
        
        // Inisialisasi properti lain
        $this->setting = new Settings();
    }

    public function index()
    {
        $data = [
            'title'         => 'Dashboard Admin',
            'nama_instansi' => $this->setting->info['nama_instansi'] ?? 'TRON',
            'alamat'        => $this->setting->info['alamat'] ?? '',
            'logo'          => $this->setting->info['logo'] ?? 'logo.png',
            'jmlUser'       => 100,
            'user_nama'    => session()->get('fullname')
        ];

        // Memuat View Dashboard
        return view('App\Modules\Dashboard\Views\dashboard', $data);
    }
}