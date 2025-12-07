<?php

if(!isset($routes))
{ 
    $routes = \Config\Services::routes(true);
}

// Definisikan Controller yang dibutuhkan
// Controller WEB untuk tampilan utama/login
$sidangController = '\App\Modules\Sidang\Controllers\SidangController';
// Controller WEB untuk setup OTP
$adminSetupController = '\App\Modules\Sidang\Controllers\AdminSetupController';


// ====================================================================
// 1. ⬇️ RUTE AKSES PEGAWAI (DILINDUNGI OTP) ⬇️
// Digunakan oleh pegawai untuk login dan sync. Filter sidang_auth
// ====================================================================
$routes->group('sidang', function($routes) use ($sidangController) {
    
    // Rute Akses Login OTP (Pintu masuk menu utama)
    $routes->get('access', "{$sidangController}::accessForm"); 
    $routes->post('verify', "{$sidangController}::verifyOtp"); 

    // Rute Terlindungi (Memerlukan Filter 'sidang_auth')
    $routes->get('/', "{$sidangController}::index", ['filter' => 'sidang_auth']);
    $routes->get('sync', "{$sidangController}::sync", ['filter' => 'sidang_auth']);
    $routes->post('proses', "{$sidangController}::proses", ['filter' => 'sidang_auth']);
    
    // ✅ RUTE API DATA LAMA (Contoh)
    // URL: /sidang/api/data
    $routes->get('api/data', "{$sidangController}::apiData", ['filter' => 'sidang_auth']);
});


// ====================================================================
// 2. ⬇️ RUTE ADMINISTRASI OTP (Diperlukan oleh Setting OTP) ⬇️
// 💥 FILTER: Menggunakan filter 'sidang_auth' sesuai permintaan lo
// Controller: App\Modules\Sidang\Controllers\Api\SidangAdmin
// ====================================================================
$routes->group('api/sidang', ['filter' => 'sidang_auth', 'namespace' => 'App\\Modules\\Sidang\\Controllers\\Api'], function($routes){
    
    // GET Data Admin untuk Tabel (LOAD) - Dipanggil oleh setting_otp.php
    // URL: /api/sidang/admins
    $routes->get('admins', 'SidangAdmin::index'); 
    
    // POST/SAVE Admin Baru (NIP & Nama) - Dipanggil oleh setting_otp.php
    // URL: /api/sidang/admins/save
    $routes->post('admins/save', 'SidangAdmin::save');
    
    // PUT/TOGGLE Status Aktif (id) - Dipanggil oleh setting_otp.php
    // URL: /api/sidang/admins/toggle/123
    $routes->put('admins/toggle/(:segment)', 'SidangAdmin::toggle/$1');
    
    // DELETE Admin (Jika ada)
    $routes->delete('admins/delete/(:segment)', 'SidangAdmin::delete/$1');
});


// ====================================================================
// 3. ⬇️ RUTE GENERATE QR CODE (URL REDIRECT) ⬇️
// Ini adalah rute web yang dipanggil oleh tombol QR di setting_otp.php
// ====================================================================
$routes->group('setting/otp-sidang', ['filter' => 'auth_session', 'namespace' => 'App\\Modules\\Sidang\\Controllers'], function($routes) use ($adminSetupController){
    
    // RUTE GENERATE QR CODE
    // URL: /setting/otp-sidang/generate/(:num)
    $routes->get('generate/(:num)', "{$adminSetupController}::generateQr/$1");
    
});