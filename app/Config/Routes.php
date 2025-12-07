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
$routes->setDefaultNamespace('App\\Controllers');
$routes->setDefaultController('Home');
$routes->setDefaultMethod('index');
$routes->get('/datasidang', 'DataSheet::index');
$routes->setTranslateURIDashes(false);
$routes->set404Override();
// The Auto Routing (Legacy) is very dangerous. It is easy to create vulnerable apps
// where controller filters or CSRF protection are bypassed.
// If you don't want to define all routes, please use the Auto Routing (Improved).
// Set `$autoRoutesImproved` to true in `app/Config/Feature.php` and set the following to true.
$routes->setAutoRoute(false);

// We get a performance increase by specifying the default
// route since we don't have to scan directories.
$routes->get('/', 'Home::index');

// ⬇️⬇️ TAMBAHKAN INI BRO ⬇️⬇️
// Arahkan /dashboard langsung ke Controller di dalam Module
$routes->get('dashboard', '\App\Modules\Dashboard\Controllers\Dashboard::index', ['filter' => 'auth_session']);

/*
 * --------------------------------------------------------------------
 * ROUTES SETUP NIP (UTILITY ADMIN)
 * --------------------------------------------------------------------
 * Akses ini dilindungi oleh Admin TRON utama (admin_auth)
 */
$routes->group('admin/sidang', ['filter' => 'auth_session'], function($routes) {
    
    // Controller Admin Setup berada di dalam namespace modul Sidang
    $adminController = '\App\Modules\Sidang\Controllers\AdminSetupController'; 

    // 1. Setup Index (GET) - Menampilkan daftar NIP dan form tambah
    //$routes->get('setup', "{$adminController}::setupIndex"); 
    
    // 2. Save NIP (POST) - Menerima data NIP baru/update
    $routes->post('save-nip', "{$adminController}::saveNip"); 
    
    // 3. Generate QR (GET) - Membuat Secret Key baru dan menampilkan QR Code
    $routes->get('generate/(:num)', "{$adminController}::generateQr/$1"); 
});

// Tambahkan rute ini untuk menampung panggilan yang salah dari frontend
// dan mengarahkannya ke fungsi 'display()' yang benar.
#$routes->get('api/display/video', 'App\Modules\Video\Controllers\Api\Video::display');
/**
 * --------------------------------------------------------------------
 * HMVC Routing - AUTO DISCOVERY (KODE INI SUDAH BENAR!)
 * --------------------------------------------------------------------
 * Kode ini memastikan semua file Routes.php di setiap modul dimuat.
 * Ini yang memuat file app/Modules/Sidang/Config/Routes.php Anda!
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