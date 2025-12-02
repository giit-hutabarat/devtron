<?php

if (!isset($routes)) {
    $routes = \Config\Services::routes(true);
}

// ====================================================================
// KELOMPOK 1: WEB ROUTE (HALAMAN ADMIN)
// Filter 'auth_session' dipasang untuk melindungi halaman input/manajemen.
// ====================================================================
$routes->group('video', ['filter' => 'auth_session', 'namespace' => 'App\Modules\Video\Controllers'], function($routes) {
    // URL: localhost/tron/video
    $routes->get('/', 'Video::index'); 
});


// ====================================================================
// KELOMPOK 2: PUBLIC API ROUTE (DISPLAY)
// TIDAK PERLU FILTER. Digunakan oleh layar TV publik.
// ====================================================================
$routes->group('api/video', ['namespace' => 'App\Modules\Video\Controllers\Api'], function($routes) {
    $routes->get('display', 'Video::display');
});


// ====================================================================
// KELOMPOK 3: ADMIN API ROUTE (CRUD & Upload)
// Filter 'auth_session' dipasang untuk melindungi CRUD.
// ====================================================================
$routes->group('api/video', ['filter' => 'auth_session', 'namespace' => 'App\Modules\Video\Controllers\Api'], function($routes) {
    // CRUD Operations (Membutuhkan Login Admin)
    $routes->get('/', 'Video::index');
    $routes->post('save', 'Video::create');
    $routes->put('update/(:num)', 'Video::update/$1');
    $routes->delete('delete/(:num)', 'Video::delete/$1');
    $routes->post('upload', 'Video::upload');
    $routes->put('setaktif/(:num)', 'Video::setAktif/$1');
});