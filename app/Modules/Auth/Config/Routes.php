<?php

if (!isset($routes)) {
    $routes = \Config\Services::routes(true);
}

// ====================================================================
// KELOMPOK 1: Web / AJAX Login (Controller: Auth.php di root Controllers)
// Digunakan untuk menangani form login yang menghasilkan JSON {redirect: '...'}
// ====================================================================

$routes->group('auth', ['namespace' => 'App\Modules\Auth\Controllers'], function($routes) {
    
    // Endpoint Login (POST) - Akses publik. Memanggil Controller WEB.
    // Memanggil Controller: App\Modules\Auth\Controllers\Auth::login_admin
    // URL: localhost/tron/auth/login_admin
    $routes->post('login_admin', 'Auth::login_admin'); 
    $routes->get('loading', 'Redirect::loading');
});


// ====================================================================
// KELOMPOK 2: API Logout (Controller: Api/Auth.php)
// Digunakan untuk menangani proses logout yang menghasilkan JSON {success: true}
// ====================================================================

$routes->group('api/auth', ['namespace' => 'App\Modules\Auth\Controllers\Api'], function($routes) {
    
    // Endpoint Logout (GET) - HARUS dilindungi. Memanggil Controller API.
    // Filter 'auth_session' digunakan.
    // URL: localhost/tron/api/auth/logout (Disarankan memakai prefix /api)
    $routes->get('logout', 'Auth::logout', ['filter' => 'auth_session']);
    
    // Endpoint Registrasi (Tambahan, jika ada) - Biasanya hanya tersedia di API.
    $routes->post('register', 'Auth::register');

});