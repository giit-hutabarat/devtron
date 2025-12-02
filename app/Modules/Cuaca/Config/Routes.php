<?php

if(!isset($routes))
{ 
    $routes = \Config\Services::routes(true);
}

// ====================================================================
// KELOMPOK 1: WEB ROUTE (HALAMAN ADMIN)
// Diubah filter dari 'auth' menjadi 'auth_session'
// ====================================================================
$routes->group('cuaca', ['filter' => 'auth_session', 'namespace' => 'App\Modules\Cuaca\Controllers'], function($routes){
	// URL: localhost/tron/cuaca
	$routes->get('/', 'Cuaca::index');
});

// ====================================================================
// KELOMPOK 2: PUBLIC API ROUTE (DISPLAY)
// TIDAK PERLU FILTER. Digunakan oleh layar TV publik.
// ====================================================================
$routes->group('api', ['namespace' => 'App\Modules\Cuaca\Controllers\Api'], function($routes){
    $routes->get('display/cuaca', 'Cuaca::display');
});

// ====================================================================
// KELOMPOK 3: ADMIN API ROUTE (CRUD/Input)
// Diubah filter dari 'jwtauth' menjadi 'auth_session'
// ====================================================================
$routes->group('api', ['filter' => 'auth_session', 'namespace' => 'App\Modules\Cuaca\Controllers\Api'], function($routes){
    $routes->get('cuaca', 'Cuaca::index');
    // Tambahkan route CRUD di sini jika ada (save, update, delete)
});