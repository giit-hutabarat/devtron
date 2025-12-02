<?php

if(!isset($routes))
{ 
    $routes = \Config\Services::routes(true);
}

$routes->group('display', ['namespace' => 'App\Modules\Display\Controllers'], function($routes){
	$routes->add('/', 'Display::index');

});