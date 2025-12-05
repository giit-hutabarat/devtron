<?php

/**
 * --------------------------------------------------------------------
 * Route File untuk Modul Sidang (Hanya Akses Pegawai)
 * --------------------------------------------------------------------
 * File ini menangani akses pegawai (NIP + OTP) dan tidak mengandung logika login terpisah.
 */

// Definisikan Controller yang dibutuhkan (Hanya Setup dan Sidang utama)
$sidangController = '\App\Modules\Sidang\Controllers\SidangController';


// 1. ⬇️ RUTE AKSES PEGAWAI (DILINDUNGI OTP) ⬇️
// Ini adalah satu-satunya pintu masuk bagi pegawai ke modul sidang.
$routes->group('sidang', function($routes) use ($sidangController) {
    
    // Rute Akses Login OTP (Pintu masuk menu utama)
    $routes->get('access', "{$sidangController}::accessForm"); 
    $routes->post('verify', "{$sidangController}::verifyOtp"); 

    // Rute Terlindungi (Memerlukan Filter 'sidang_auth')
    $routes->get('/', "{$sidangController}::index", ['filter' => 'sidang_auth']);
    $routes->get('sync', "{$sidangController}::sync", ['filter' => 'sidang_auth']);
    $routes->post('proses', "{$sidangController}::proses", ['filter' => 'sidang_auth']);
    
    
    
    
    
    // ✅ RUTE API DATA BARU (Dilindungi sidang_auth)
    // URL: /sidang/api/data
    $routes->get('api/data', "{$sidangController}::apiData", ['filter' => 'sidang_auth']);

});

