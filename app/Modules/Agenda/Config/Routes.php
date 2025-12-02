<?php

if(!isset($routes))
{ 
    $routes = \Config\Services::routes(true);
}

// ====================================================================
// KELOMPOK 1: WEB ROUTE (HALAMAN ADMIN) - DILINDUNGI
// Filter 'auth' (lama) diganti menjadi 'auth_session'
// ====================================================================
$routes->group('agenda', ['filter' => 'auth_session', 'namespace' => 'App\Modules\Agenda\Controllers'], function($routes){
	$routes->get('/', 'Agenda::index');
});

// ====================================================================
// KELOMPOK 2: PUBLIC API ROUTE (DISPLAY) - PUBLIK
// TIDAK PERLU FILTER. Digunakan oleh layar TV publik.
// ====================================================================
$routes->group('api', ['namespace' => 'App\Modules\Agenda\Controllers\Api'], function($routes){
    $routes->get('display/agenda', 'Agenda::display');
});

// ====================================================================
// KELOMPOK 3: ADMIN API ROUTE (CRUD) - DILINDUNGI
// Filter 'jwtauth' (lama) diganti menjadi 'auth_session'
// ====================================================================
$routes->group('api', ['filter' => 'auth_session', 'namespace' => 'App\Modules\Agenda\Controllers\Api'], function($routes){
    $routes->get('agenda', 'Agenda::index');
    $routes->post('agenda/save', 'Agenda::create');
    $routes->put('agenda/update/(:segment)', 'Agenda::update/$1');
	$routes->delete('agenda/delete/(:segment)', 'Agenda::delete/$1');
});