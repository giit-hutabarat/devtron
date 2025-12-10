<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;
use CodeIgniter\Filters\CSRF;
use CodeIgniter\Filters\DebugToolbar;
use CodeIgniter\Filters\Honeypot;
use App\Filters\AuthSessionFilter; 
use App\Modules\Sidang\Filters\SidangAdminFilter;
use App\Filters\SidangAuth; // ✅ Diperlukan karena classnya ada di app/Filters

class Filters extends BaseConfig
{
    /**
     * Configures aliases for Filter classes to
     * make reading things nicer and simpler.
     *
     * @var array
     */
    public $aliases = [
        'csrf'          => CSRF::class,
        'toolbar'       => DebugToolbar::class,
        'honeypot'      => Honeypot::class,
        'auth_session'  => AuthSessionFilter::class, // Filter Session BARU
        
        // --- ALIAS SIDANG & ADMIN ---
        'sidang_auth'   => SidangAuth::class, // ✅ Menggunakan Class yang di-import di atas (App\Filters\SidangAuth)

    ];

    /**
     * List of filter aliases that are always
     * applied before and after every request.
     *
     * @var array
     */
    public $globals = [
        'before' => [
            // Filter utama yang mengamankan sistem (Admin TRON)
            'auth_session' => ['except' => [
                
                // --- RUTE PUBLIK & DISPLAY (Mengatasi error 401 Unauthorized di Frontend) ---
                '/', 
                'home', 
                'display', 
                'video', 
                'video/*',
                'api/display/*',
                'api/video/display/',
                'api/news/*',
                'api/cuaca',
                'api/jadwalsholat',
                'api/agamaquotes',
                'api/keuanganmasjid',
                'api/setting/general',
                'datasidang',
                
                // --- RUTE SIDANG (DIKECUALIKAN DARI FILTER GLOBAL) ---
                'sidang', 
                'sidang/*',


                // ✅ TAMBAHKAN RUTE LOGIN ADMIN
                'auth/login_admin', 
                // Tambahkan juga rute yang mungkin dipakai untuk form login:
                'auth/login',
                
                // --- RUTE SETUP ADMIN (Dikecualikan agar filter admin_auth yang terpisah bisa menangani) ---
                'admin/sidang/setup', 
                'admin/sidang/generate/*',
                'admin/sidang/save-nip',
            ]],
        ],
        'after' => [
            // Toolbar (harus dikecualikan dari rute API/redirect)
            'toolbar' => ['except' => [
                'sidang', 
                'sidang/*',
                'admin/sidang/setup',
                'admin/sidang/generate/*',
            ]],
        ],
    ];

    /**
     * List of filter aliases that should run on any
     * before or after URI patterns.
     *
     * @var array
     */
    public $filters = [];
}