<?php

if(!isset($routes))
{ 
    $routes = \Config\Services::routes(true);
}

// ====================================================================
// KELOMPOK 1 & 2: WEB ROUTE & ADMIN API ROUTE
// SEMUA ROUTE DI MODUL LAYOUT DIHAPUS.
// Modul Layout berfungsi sebagai helper template dan tidak boleh mendaftarkan endpoint.
// ====================================================================

// Route ini sekarang kosong