<?php

if(!isset($routes))
{ 
    $routes = \Config\Services::routes(true);
}

// ====================================================================
// KELOMPOK 1: WEB ROUTE (HALAMAN ADMIN)
// Diubah filter dari 'auth' menjadi 'auth_session'
// ====================================================================
$routes->group('setting', ['filter' => 'auth_session', 'namespace' => 'App\Modules\Setting\Controllers'], function($routes){
	$routes->add('general', 'Setting::general');
	$routes->add('app', 'Setting::app');
});

// ====================================================================
// KELOMPOK 2: ADMIN API ROUTE (CRUD & Update Config)
// Diubah filter dari 'jwtauth' menjadi 'auth_session'
// ====================================================================
$routes->group('api', ['filter' => 'auth_session', 'namespace' => 'App\Modules\Setting\Controllers\Api'], function($routes){
    $routes->get('setting/general', 'Setting::general');
	$routes->get('setting/app', 'Setting::app');
	
    // Update dan Upload
    $routes->put('setting/update/(:segment)', 'Setting::update/$1');
	$routes->post('setting/upload', 'Setting::upload');

	$routes->put('setting/change/(:segment)', 'Setting::setChange/$1');

    // Data Helper
	$routes->get('setting/kota', 'Setting::kota');
	$routes->get('setting/layout', 'Setting::layout');
});