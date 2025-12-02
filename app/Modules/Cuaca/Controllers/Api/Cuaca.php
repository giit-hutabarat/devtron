<?php

namespace App\Modules\Cuaca\Controllers\Api;
/*
IT Shop Purwokerto (Tokopedia, Shopee & Bukalapak)
Dibuat oleh: Hari Wicaksono, S.Kom
11-2021
*/

use App\Controllers\BaseControllerApi;
use App\Modules\Setting\Models\KotaModel;
use App\Libraries\Settings;
use PhpOffice\PhpSpreadsheet\Reader\Xls;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx;

class Cuaca extends BaseControllerApi
{
    protected $format       = 'json';
    protected $modelName    = KotaModel::class;
    protected $setting;

    public function __construct()
    {
        //memanggil Model
        $this->setting = new \App\Libraries\Settings(); // Menginisiasi $setting
    }

    function _isCurl()
    {
        return function_exists('curl_version');
    }

    function fOpenRequest($url)
    {
        $file = fopen($url, 'r');
        $data = stream_get_contents($file);
        fclose($file);
        return $data;
    }

    function curlRequest($url)
    {
        $c = curl_init();
        curl_setopt($c, CURLOPT_URL, $url);
        curl_setopt($c, CURLOPT_RETURNTRANSFER, true);
        $data = curl_exec($c);
        curl_close($c);
        return $data;
    }


    public function index()
    {
        //get City
        $city = $this->setting->info['kota'];

        $cari = $this->model->find($city);
		$data = $cari['lokasi'];
		$pecah = explode(" ", $data);
		$kota = ucfirst($pecah[1]);

        if (@fsockopen('www.google.com', 80)) {
            if ($this->_isCurl()) {
				$json = $this->curlRequest("http://api.openweathermap.org/data/2.5/weather?appid=770a17f9520e41124656aa601bc34b3c&units=metric&q=$kota", false);
			} else {
				$json = $this->fOpenRequest("http://api.openweathermap.org/data/2.5/weather?appid=770a17f9520e41124656aa601bc34b3c&units=metric&q=$kota", false);
			}
        } else {
            $json = "";
        }

        //decode JSON to array
        $result = json_decode($json, true);
        //return data array()
        return $this->respond(["status" => true, "message" => lang('App.getSuccess'), "data" => $result], 200);

    }

    public function display()
    {
        //get City
        $city = $this->setting->info['kota'];

        $cari = $this->model->find($city);
		$data = $cari['lokasi'];
		$pecah = explode(" ", $data);
		$kota = ucfirst($pecah[1]);

        if (@fsockopen('www.google.com', 80)) {
            if ($this->_isCurl()) {
				$json = $this->curlRequest("http://api.openweathermap.org/data/2.5/weather?appid=770a17f9520e41124656aa601bc34b3c&units=metric&q=$kota", false);
			} else {
				$json = $this->fOpenRequest("http://api.openweathermap.org/data/2.5/weather?appid=770a17f9520e41124656aa601bc34b3c&units=metric&q=$kota", false);
			}
        } else {
            $json = "";
        }

        //decode JSON to array
        $result = json_decode($json, true);
        //return data array()
        return $this->respond(["status" => true, "message" => lang('App.getSuccess'), "data" => $result], 200);
    }
}
