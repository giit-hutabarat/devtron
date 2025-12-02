<?php

namespace App\Modules\Loading\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\Services;

/**
 * Controller ini bertindak sebagai helper untuk memuat Loading View.
 */
class Loading extends BaseController
{
    /**
     * Memuat View Loading, lalu mengarahkan ke halaman utama.
     * @return string
     */
    public function show()
    {
        // 1. Tampilkan View Loading (loading_template.php)
        $view = view('App\Modules\Loading\Views\loading_template');
        
        // 2. Tambahkan script redirect setelah beberapa detik (misalnya 3 detik)
        $redirectUrl = base_url('/'); // Arahkan kembali ke halaman utama/home
        
        // Cek jika sesi sudah terhapus, redirect segera
        if (session()->get('isLoggedIn') === false || session()->get('isLoggedIn') === null) {
             // Jika sudah logout (setelah proses /api/auth/logout), redirect segera
             $script = "<script>window.location.href = '{$redirectUrl}';</script>";
             return $view . $script;
        }


        // Jika masih ada bug di sisi server, lakukan redirect dengan jeda
        $script = "
        <script>
            setTimeout(function() {
                window.location.href = '{$redirectUrl}';
            }, 10000); // Jeda 3 detik
        </script>";

        return $view . $script;
    }
}