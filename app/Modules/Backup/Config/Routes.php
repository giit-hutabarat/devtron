<?php

if(!isset($routes))
{ 
    $routes = \Config\Services::routes(true);
}

// ====================================================================
// KELOMPOK 1: WEB ROUTE (HALAMAN ADMIN BACKUP)
// Diubah filter dari 'auth' menjadi 'auth_session'
// ====================================================================
$routes->group('backup', ['filter' => 'auth_session', 'namespace' => 'App\Modules\Backup\Controllers'], function($routes){
	// URL: localhost/tron/backup
	$routes->add('/', 'Backup::index');
});


// ====================================================================
// KELOMPOK 2: ADMIN API ROUTE (CRUD & Download)
// Diubah filter dari 'jwtauth' menjadi 'auth_session'
// Semua operasi di sini sensitif (CRUD, Download)
// ====================================================================
$routes->group('api', ['filter' => 'auth_session', 'namespace' => 'App\Modules\Backup\Controllers\Api'], function($routes){
    $routes->get('backup', 'Backup::index');
	$routes->post('backup/save', 'Backup::create');
	$routes->delete('backup/delete/(:segment)', 'Backup::delete/$1');
	$routes->post('backup/download', 'Backup::download'); // Download file sensitif
});