<?php

if(!isset($routes))
{ 
    $routes = \Config\Services::routes(true);
}

// Definisikan Controller yang dibutuhkan
$sidangController = '\App\Modules\Sidang\Controllers\SidangController';
$adminSetupController = '\App\Modules\Sidang\Controllers\AdminSetupController';


// ====================================================================
// 1. ⬇️ RUTE AKSES PEGAWAI (DILINDUNGI OTP) ⬇️ (TIDAK BERUBAH)
// ====================================================================
// ... baris 19 ...
$routes->group('sidang', function($routes) use ($sidangController) {
    
    // Rute Akses Login OTP
    $routes->get('access', "{$sidangController}::accessForm"); 
    $routes->post('verify', "{$sidangController}::verifyOtp"); 

    // Rute Terlindungi
    $routes->get('/', "{$sidangController}::index", ['filter' => 'sidang_auth']);
    $routes->get('sync', "{$sidangController}::sync", ['filter' => 'sidang_auth']);
    
    // --- [TEMPELKAN DISINI] ---
    //$routes->get('diagnosa', "{$sidangController}::diagnosa", ['filter' => 'sidang_auth']); 
    // --------------------------

    $routes->post('proses', "{$sidangController}::proses", ['filter' => 'sidang_auth']);
    
    $routes->get('api/data', "{$sidangController}::apiData", ['filter' => 'sidang_auth']);
    $routes->get('logout', "{$sidangController}::logout");
});

// ====================================================================
// 2. ⬇️ RUTE ADMINISTRASI ADMIN SIDANG (WEB VIEW + API) ⬇️
// URL Baru: setting/admin-sidang/*
// Dilindungi penuh oleh 'auth_session'
// ====================================================================
$routes->group('setting/admin-sidang', ['filter' => 'auth_session'], function($routes) use ($adminSetupController){
    
    // 2.1. Rute WEB View
    // URL: /setting/admin-sidang/
    $routes->get('/', "{$adminSetupController}::index"); 
    
    // RUTE GENERATE QR CODE (Redirect ke view setup_qr.php)
    // URL: /setting/admin-sidang/generate/(:num)
    $routes->get('generate/(:num)', "{$adminSetupController}::generateQr/$1");
    
    
    // 2.2. Rute API (Diambil oleh Vue.js di otp-sidang.php)
    // Namespace API langsung digunakan di rute ini agar tidak konflik dengan AdminSetupController
    $routes->group('api', ['namespace' => 'App\\Modules\\Sidang\\Controllers\\Api'], function($routes){
        
        // GET Data Admin untuk Tabel (LOAD)
        // URL: /setting/admin-sidang/api/admins
        $routes->get('admins', 'SidangAdmin::index'); 
        
        // POST/SAVE Admin Baru
        // URL: /setting/admin-sidang/api/admins/save
        $routes->post('admins/save', 'SidangAdmin::save');
        
        // PUT/TOGGLE Status Aktif
        // URL: /setting/admin-sidang/api/admins/toggle/123
        $routes->put('admins/toggle/(:segment)', 'SidangAdmin::toggle/$1');
        
        // DELETE Admin
        $routes->delete('admins/delete/(:segment)', 'SidangAdmin::delete/$1');
    });

});

// 💥 Hapus rute API lama (Bagian 2 yang lama)

// 💥 Hapus rute WEB lama (Bagian 3 yang lama)