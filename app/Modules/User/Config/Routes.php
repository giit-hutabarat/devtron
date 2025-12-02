<?php

if(!isset($routes))
{ 
    $routes = \Config\Services::routes(true);
}

// ====================================================================
// KELOMPOK 1: WEB ROUTE (HALAMAN ADMIN)
// Diubah filter dari 'auth' menjadi 'auth_session'
// ====================================================================
$routes->group('user', ['filter' => 'auth_session', 'namespace' => 'App\Modules\User\Controllers'], function($routes){
	// URL: localhost/tron/user
	$routes->add('/', 'User::index');
});


// ====================================================================
// KELOMPOK 2: ADMIN API ROUTE (CRUD User)
// Diubah filter dari 'jwtauth' menjadi 'auth_session'
// Semua operasi di sini sensitif (hanya Admin)
// ====================================================================
$routes->group('api', ['filter' => 'auth_session', 'namespace' => 'App\Modules\User\Controllers\Api'], function($routes){
	$routes->get('user', 'User::index'); // GET data user
	$routes->post('user/save', 'User::create');
	$routes->put('user/update/(:segment)', 'User::update/$1');
	$routes->delete('user/delete/(:segment)', 'User::delete/$1');
	
	// Operasi Otorisasi/Pengaturan Level
	$routes->put('user/setactive/(:segment)', 'User::setActive/$1');
	$routes->put('user/setrole/(:segment)', 'User::setRole/$1');
	$routes->post('user/changepassword', 'User::changePassword');
});