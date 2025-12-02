<?php

if(!isset($routes))
{ 
    $routes = \Config\Services::routes(true);
}

// ====================================================================
// KELOMPOK 1: MODUL DASHBOARD (DILINDUNGI SESSION)
// ====================================================================

// URL: localhost/tron/dashboard
// FILTER DIUBAH dari 'auth' menjadi 'auth_session'
$routes->group('dashboard', ['filter' => 'auth_session', 'namespace' => 'App\Modules\Dashboard\Controllers'], function($routes){
	// Endpoint utama Dashboard
    $routes->add('/', 'Dashboard::index'); 
});


// ====================================================================
// KELOMPOK 2 & 3: MODUL NEWS (API)
// ====================================================================

// Route API News publik (tanpa filter otentikasi)
$routes->group('api', ['namespace' => 'App\Modules\News\Controllers\Api'], function($routes){
	$routes->add('news/news', 'News::news');
    $routes->add('news/info', 'News::info');
});

// Route API News yang DILINDUNGI (FILTER JWT DIUBAH ke SESSION)
$routes->group('api', ['filter' => 'auth_session', 'namespace' => 'App\Modules\News\Controllers\Api'], function($routes){
    // Endpoint ini sekarang memerlukan Session login yang valid
    $routes->add('news', 'News::index'); 
});