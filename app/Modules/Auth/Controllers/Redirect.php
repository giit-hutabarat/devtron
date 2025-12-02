<?php

namespace App\Modules\Auth\Controllers;

use App\Controllers\BaseController;

class Redirect extends BaseController
{
    /**
     * Menampilkan loading screen dan langsung mengalihkan ke URL target.
     * Menggantikan method preload.
     * @param string $target - URL yang akan dituju setelah loading selesai.
     * @return string
     */
    public function loading($target = '/')
    {
        // Target URL harus di-sanitize untuk keamanan
        $target = filter_var($target, FILTER_SANITIZE_URL);
        $targetUrl = base_url($target);
        
        // 1. Muat view loading dari Modul Loading
        // PASTIKAN VIEW PATH MENGGUNAKAN NAMA MODULE 'Loading'
        $loadingHtml = view('App\Modules\Loading\Views\loading_template');

        // 2. Tambahkan script redirect
        $script = "
        <script>
            // Lakukan redirect setelah 1 detik (agar loading screen sempat terlihat)
            setTimeout(function() {
                window.location.href = '{$targetUrl}';
            }, 1000); 
        </script>";

        // Tampilkan Loading diikuti script redirect
        return $loadingHtml . $script;
    }
}