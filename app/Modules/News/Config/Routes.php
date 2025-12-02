<?php

if(!isset($routes))
{ 
    $routes = \Config\Services::routes(true);
}

// ====================================================================
// KELOMPOK 1: WEB ROUTE (HALAMAN ADMIN)
// Diubah filter dari 'auth' menjadi 'auth_session'
// ====================================================================
$routes->group('news', ['filter' => 'auth_session', 'namespace' => 'App\Modules\News\Controllers'], function($routes){
	// URL: localhost/tron/news
	$routes->get('/', 'News::index');
});

// ====================================================================
// KELOMPOK 2: PUBLIC API ROUTE (DISPLAY)
// TIDAK PERLU FILTER. Digunakan oleh layar TV publik.
// ====================================================================
$routes->group('api', ['namespace' => 'App\Modules\News\Controllers\Api'], function($routes){
	$routes->get('news/news', 'News::news');
    $routes->get('news/info', 'News::info');
    $routes->get('news/masjid', 'News::masjid');
});

// ====================================================================
// KELOMPOK 3: ADMIN API ROUTE (CRUD)
// Diubah filter dari 'jwtauth' menjadi 'auth_session'
// Semua operasi di sini sensitif
// ====================================================================
$routes->group('api', ['filter' => 'auth_session', 'namespace' => 'App\Modules\News\Controllers\Api'], function($routes){
    $routes->get('news', 'News::index');
    $routes->post('news/save', 'News::create');
    $routes->put('news/update/(:segment)', 'News::update/$1');
	$routes->delete('news/delete/(:segment)', 'News::delete/$1');
});