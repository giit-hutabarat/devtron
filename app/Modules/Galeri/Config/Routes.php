<?php

if(!isset($routes))
{ 
    $routes = \Config\Services::routes(true);
}

// ====================================================================
// KELOMPOK 1: WEB ROUTE (HALAMAN ADMIN)
// Diubah filter dari 'auth' menjadi 'auth_session'
// ====================================================================
$routes->group('galeri', ['filter' => 'auth_session', 'namespace' => 'App\Modules\Galeri\Controllers'], function($routes){
	// URL: localhost/tron/galeri
	$routes->get('/', 'Galeri::index');
});


// ====================================================================
// KELOMPOK 2: PUBLIC API ROUTE (DISPLAY)
// TIDAK PERLU FILTER. Digunakan oleh layar TV publik.
// ====================================================================
$routes->group('api', ['namespace' => 'App\Modules\Galeri\Controllers\Api'], function($routes){
    $routes->get('display/galeri', 'Galeri::display');
});


// ====================================================================
// KELOMPOK 3: ADMIN API ROUTE (CRUD & Upload)
// Diubah filter dari 'jwtauth' menjadi 'auth_session'
// Semua operasi di sini sensitif
// ====================================================================
$routes->group('api', ['filter' => 'auth_session', 'namespace' => 'App\Modules\Galeri\Controllers\Api'], function($routes){
    $routes->get('galeri', 'Galeri::index');
    $routes->post('galeri/save', 'Galeri::create');
    $routes->put('galeri/update/(:segment)', 'Galeri::update/$1');
	$routes->delete('galeri/delete/(:segment)', 'Galeri::delete/$1');
    $routes->post('galeri/upload', 'Galeri::upload');
    $routes->put('galeri/setaktif/(:segment)', 'Galeri::setAktif/$1');
});