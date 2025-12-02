<?php

namespace App\Modules\Setting\Controllers\Api;

use App\Controllers\BaseControllerApi;
use App\Modules\Setting\Models\SettingModel;
use App\Modules\Setting\Models\KotaModel;
use App\Modules\Layout\Models\LayoutModel;

class Setting extends BaseControllerApi
{
    protected $format       = 'json';
    protected $modelName    = SettingModel::class;
    protected $setting;
    protected $kota;
    protected $layout;
    

    public function __construct()
	{
		//memanggil Model
        $this->setting = new \App\Libraries\Settings();
		$this->kota = new KotaModel();
        $this->layout = new LayoutModel();
        
	}

    public function general()
    {
        return $this->respond(["status" => true, "message" => "Success", "data" => $this->model->where('group_setting', 'general')->findAll()], 200);
    }

    public function app()
    {
        return $this->respond(["status" => true, "message" => "Success", "data" => $this->model->where('group_setting', 'app')->findAll()], 200);
    }

    public function update($id = NULL)
    {
        $rules = [
            'value_setting' => [
                'rules'  => 'required',
                'errors' => []
            ],
        ];

        if ($this->request->getJSON()) {
            $json = $this->request->getJSON();
            $data = [
                'value_setting' => $json->value_setting,
            ];
        } else {
            $data = $this->request->getRawInput();
        }

        if (!$this->validate($rules)) {
            $response = [
                'status' => false,
                'message' => 'Terjadi kesalahan',
                'data' => $this->validator->getErrors(),
            ];
            return $this->respond($response, 200);
        } else {
            $this->model->update($id, $data);
            $response = [
                'status' => true,
                'message' => 'Success',
                'data' => [],
            ];
            return $this->respond($response, 200);
        }
    }

    public function upload()
    {
        $id = $this->request->getVar('id');
        $image = $this->request->getFile('image');
        $fileName = $image->getRandomName();
        if ($image !== "") {
            $path = "images/";
            $moved = $image->move($path, $fileName);
            if ($moved) {
                $simpan = $this->model->update($id, [
                    'value_setting' => $path . $fileName
                ]);
                if ($simpan) {
                    return $this->respond(["status" => true, "message" => lang('App.imgSuccess'), "data" => [$path . $fileName]], 200);
                } else {
                    return $this->respond(["status" => false, "message" => lang('App.imgFailed'), "data" => []], 200);
                }
            }
        } else {
            $response = [
                'status' => false,
                'message' => lang('App.uploadFailed'),
                'data' => []
            ];
            return $this->respond($response, 200);
        }
    }

    public function kota()
    {
        return $this->respond(["status" => true, "message" => "Success", "data" => $this->kota->findAll()], 200);
    }

    public function layout()
    {
        return $this->respond(["status" => true, "message" => "Success", "data" => $this->layout->findAll()], 200);
    }

    public function setChange($id = NULL)
    {
        if ($this->request->getJSON()) {
            $json = $this->request->getJSON();
            $data = [
                'value_setting' => $json->value_setting,
            ];
        } else {
            $input = $this->request->getRawInput();
            $data = [
                'value_setting' => $input['value_setting']
            ];
        }

        if ($data > 0) {
            $this->model->update($id, $data);
            $response = [
                'status' => true,
                'message' => lang('App.updSuccess'),
                'data' => []
            ];
            return $this->respond($response, 200);
        } else {
            $response = [
                'status' => false,
                'message' => lang('App.updFailed'),
                'data' => []
            ];
            return $this->respond($response, 200);
        }
    }
}
