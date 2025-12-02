<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class KotaSeeder extends Seeder
{
  public function run()
  {
    $curl = curl_init();

    curl_setopt_array($curl, array(
      CURLOPT_URL => "https://api.myquran.com/v1/sholat/kota/semua",
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_ENCODING => "",
      CURLOPT_MAXREDIRS => 10,
      CURLOPT_TIMEOUT => 30,
      CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
      CURLOPT_CUSTOMREQUEST => "GET",
      //CURLOPT_HTTPHEADER => array(
        //"key: "
      //),
    ));

    $response = curl_exec($curl);
    $kota = json_decode($response, true);

   
    foreach ($kota as $value) {
      $data = [
        'id' => $value['id'],
        'lokasi' => $value['lokasi']
      ];
      $this->db->table('kotas')->insert($data);
    }
  }
}
