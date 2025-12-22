<?php

namespace Config;

// Create a new instance of our RouteCollection class.
$routes = Services::routes(true);

// Load the system's routing file first, so that the app and ENVIRONMENT
// can override as needed.
if (file_exists(SYSTEMPATH . 'Config/Routes.php')) {
    require SYSTEMPATH . 'Config/Routes.php';
}

/*
 * --------------------------------------------------------------------
 * Router Setup
 * --------------------------------------------------------------------
 */
$routes->setDefaultNamespace('App\Controllers');
$routes->setDefaultController('Home');
$routes->setDefaultMethod('index');
$routes->setTranslateURIDashes(false);

// HAPUS BARIS INI (REDUNDAN):
// $routes->set404Override(); 

$routes->setAutoRoute(false);

/*
 * --------------------------------------------------------------------
 * Route Definitions
 * --------------------------------------------------------------------
 */

// We get a performance increase by specifying the default
// route since we don't have to scan directories.
$routes->get('/', 'Home::index');
$routes->get('/datasidang', 'DataSheet::index');

// Arahkan /dashboard langsung ke Controller di dalam Module
$routes->get('dashboard', '\App\Modules\Dashboard\Controllers\Dashboard::index', ['filter' => 'auth_session']);

/**
 * --------------------------------------------------------------------
 * HMVC Routing - AUTO DISCOVERY
 * --------------------------------------------------------------------
 */
foreach(glob(APPPATH . 'Modules/*', GLOB_ONLYDIR) as $item_dir)
{
	if (file_exists($item_dir . '/Config/Routes.php'))
	{
		require_once($item_dir . '/Config/Routes.php');
	}	
}

$routes->get("/lang/{locale}", "Home::setLanguage");

/*
 * --------------------------------------------------------------------
 * Additional Routing
 * --------------------------------------------------------------------
 */
if (file_exists(APPPATH . 'Config/' . ENVIRONMENT . '/Routes.php')) {
    require APPPATH . 'Config/' . ENVIRONMENT . '/Routes.php';
}

/*
 * --------------------------------------------------------------------
 * GLOBAL 404 OVERRIDE (THE GLITCH PAGE)
 * --------------------------------------------------------------------
 * Ini akan menangkap SEMUA error 404 dari controller manapun (termasuk Module)
 */
$routes->set404Override(function() {
    // Pastikan path view sesuai dengan file yang baru kita buat
    return view('errors/html/error_404_custom');
});