<?php

if(!isset($routes))
{ 
    $routes = \Config\Services::routes(true);
}

// ====================================================================
// KELOMPOK 1: WEB ROUTE (HALAMAN ADMIN)
// Diubah filter dari 'auth' menjadi 'auth_session'
// ====================================================================
$routes->group('', ['filter' => 'auth_session', 'namespace' => 'App\Modules\Masjid\Controllers'], function($routes){
	// URL: localhost/tron/jadwalsholat
	$routes->get('jadwalsholat', 'Masjid::jadwalSholat');
    $routes->get('agamaquotes', 'Masjid::agamaQuotes');
    $routes->get('keuanganmasjid', 'Masjid::keuanganMasjid');
});

// ====================================================================
// KELOMPOK 2: PUBLIC API ROUTE (DISPLAY)
// TIDAK PERLU FILTER. Digunakan oleh layar TV publik.
// ====================================================================
$routes->group('api', ['namespace' => 'App\Modules\Masjid\Controllers\Api'], function($routes){
    $routes->get('display/jadwalsholat', 'JadwalSholat::display');
    $routes->get('display/cekwaktusolat', 'JadwalSholat::cek_waktu_solat');
	$routes->get('display/agamaquotes', 'AgamaQuotes::display');
    $routes->get('display/keuanganmasjid', 'KeuanganMasjid::display');
    $routes->get('display/kasmasjid', 'KeuanganMasjid::saldo');
});

// ====================================================================
// KELOMPOK 3: ADMIN API ROUTE (CRUD)
// Diubah filter dari 'jwtauth' menjadi 'auth_session'
// Semua operasi di sini sensitif (CRUD, Import)
// ====================================================================
$routes->group('api', ['filter' => 'auth_session', 'namespace' => 'App\Modules\Masjid\Controllers\Api'], function($routes){
    
    // Jadwal Sholat CRUD
    $routes->get('jadwalsholat', 'JadwalSholat::index');
    $routes->post('jadwalsholat/save', 'JadwalSholat::create');
    $routes->put('jadwalsholat/update/(:segment)', 'JadwalSholat::update/$1');
	$routes->delete('jadwalsholat/delete/(:segment)', 'JadwalSholat::delete/$1');
    $routes->post('jadwalsholat/import', 'JadwalSholat::import');

    // Agama Quotes CRUD
    $routes->get('agamaquotes', 'AgamaQuotes::index');
    $routes->post('agamaquotes/save', 'AgamaQuotes::create');
    $routes->put('agamaquotes/update/(:segment)', 'AgamaQuotes::update/$1');
	$routes->delete('agamaquotes/delete/(:segment)', 'AgamaQuotes::delete/$1');

    // Keuangan Masjid CRUD
    $routes->get('keuanganmasjid', 'KeuanganMasjid::index');
    $routes->post('keuanganmasjid/save', 'KeuanganMasjid::create');
    $routes->put('keuanganmasjid/update/(:segment)', 'KeuanganMasjid::update/$1');
	$routes->delete('keuanganmasjid/delete/(:segment)', 'KeuanganMasjid::delete/$1');
    $routes->get('keuanganmasjid/saldo', 'KeuanganMasjid::saldo');
});