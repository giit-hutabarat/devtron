<?php

namespace App\Modules\User\Controllers\Api;

use App\Controllers\BaseControllerApi;
use App\Modules\User\Models\UserModel;
use CodeIgniter\HTTP\Response;
use CodeIgniter\HTTP\ResponseInterface;
use Exception;
use ReflectionException;

class User extends BaseControllerApi
{
    protected $format       = 'json';
    protected $modelName    = UserModel::class;

    public function __construct()
    {
        // PENTING: Pengecekan Login Status (isLoggedIn) SUDAH DITANGANI oleh Filter 'auth_session' di Routes.
        
        // 🔒 Otorisasi Level Admin (Hanya Super Admin yang bisa manipulasi user)
        if (session()->get('user_type') != 1) {
            // Jika bukan Super Admin (user_type 1), kembalikan 403 Forbidden.
            // Kita menggunakan response helper CI4 untuk API
            $response = service('response');
            $response->setStatusCode(ResponseInterface::HTTP_FORBIDDEN)
                     ->setJSON(['status' => false, 'message' => 'Akses Ditolak. Anda bukan Super Admin.']);
            $response->send();
            exit(); 
        }

        // Pastikan konstruktor BaseControllerApi dipanggil jika ada inisialisasi di sana
        // parent::__construct(); 
    }

    public function index()
    {
        // Menggunakan respond() dari ResourceController/BaseControllerApi
        return $this->respond(["status" => true, "message" => lang('App.getSuccess'), "data" => $this->model->findAll()], 200);
    }

    public function create()
    {
        // ... (Logic Validasi dan Input) ...
        $rules = [
            'email' => [ 'rules'  => 'required', 'errors' => [] ],
            'fullname' => [ 'rules'  => 'required', 'errors' => [] ],
            'username' => [ 'rules'  => 'required', 'errors' => [] ],
            'password' => [ 'rules'  => 'required', 'errors' => [] ],
        ];

        // Mengambil input melalui getRequestInput() dari BaseControllerApi
        $input = $this->getRequestInput();

        if (!$this->validate($rules)) {
            $response = [
                'status' => false,
                'message' => lang('App.isRequired'),
                'data' => $this->validator->getErrors(),
            ];
            return $this->respond($response, 200);
        } else {
            $data = [
                'email' => $input['email'],
                'fullname' => $input['fullname'],
                'username' => $input['username'],
                'password' => $input['password'],
                'user_type' => 2, // Default user baru adalah Tipe 2
                'is_active' => 1
            ];

            $simpan = $this->model->save($data);
            if ($simpan) {
                $response = [
                    'status' => true,
                    'message' => lang('App.productSuccess'),
                    'data' => [],
                ];
                return $this->respond($response, 200);
            }
        }
    }
    
    public function update($id = NULL)
    {
        // ... (Logic Validasi dan Input) ...
        $rules = [
            'email' => [ 'rules'  => 'required', 'errors' => [] ],
            'fullname' => [ 'rules'  => 'required', 'errors' => [] ],
        ];

        $input = $this->getRequestInput();

        if (!$this->validate($rules)) {
            $response = [
                'status' => false,
                'message' => lang('App.updFailed'),
                'data' => $this->validator->getErrors(),
            ];
            return $this->respond($response, 200);
        } else {
            $data = [
                'email' => $input['email'],
                'fullname' => $input['fullname']
            ];

            $simpan = $this->model->update($id, $data);
            if ($simpan) {
                $response = [
                    'status' => true,
                    'message' => lang('App.updSuccess'),
                    'data' => [],
                ];
                return $this->respond($response, 200);
            }
        }
    }

    public function delete($id = null)
    {
        // ... (Logic Delete) ...
        $hapus = $this->model->find($id);
        if ($hapus) {
            $this->model->delete($id);
            $response = [
                'status' => true,
                'message' => lang('App.delSuccess'),
                'data' => [],
            ];
            return $this->respond($response, 200);
        } else {
            $response = [
                'status' => false,
                'message' => lang('App.delFailed'),
                'data' => [],
            ];
            return $this->respond($response, 200);
        }
    }

    public function setActive($id = NULL)
    {
        // ... (Logic setActive) ...
        $input = $this->getRequestInput();
        $data = [ 'is_active' => $input['is_active'] ];

        if ($this->model->update($id, $data)) {
            $response = [
                'status' => true,
                'message' => lang('App.updSuccess'),
                'data' => []
            ];
            return $this->respond($response, 200);
        } else {
            $response = [
                'status' => false,
                'message' => lang('App.delFailed'),
                'data' => []
            ];
            return $this->respond($response, 200);
        }
    }

    public function setRole($id = NULL)
    {
        // ... (Logic setRole) ...
        $input = $this->getRequestInput();
        $data = [ 'user_type' => $input['user_type'] ];

        if ($this->model->update($id, $data)) {
            $response = [
                'status' => true,
                'message' => lang('App.updSuccess'),
                'data' => []
            ];
            return $this->respond($response, 200);
        } else {
            $response = [
                'status' => false,
                'message' => lang('App.delFailed'),
                'data' => []
            ];
            return $this->respond($response, 200);
        }
    }

    public function changePassword()
    {
        // ... (Logic changePassword) ...
        $rules = [
            'email' => 'required',
            'password' => 'required|min_length[8]|max_length[255]',
            'verify' => 'required|matches[password]'
        ];

        $input = $this->getRequestInput();

        if (!$this->validate($rules)) {
            return $this->getResponse(
                [
                    'status' => false,
                    'message' => 'Error',
                    'data' => $this->validator->getErrors()
                ],
                ResponseInterface::HTTP_OK
            );
        }

        $user = $this->model->where(['email' => $input['email']])->first();
        $user_id = $user['id_login']; 
		$user_data = [
			'password' => $input['password'],
		];
        if ($this->model->update($user_id, $user_data)) {
            return $this->getResponse(
                [
                    'status' => true,
                    'message' => lang('App.passChanged'),
                    'data' => []
                ], ResponseInterface::HTTP_OK
            );
        } else {
            return $this->getResponse(
                [
                    'status' => false,
                    'message' => lang('App.regFailed'),
                    'data' => []
                ], ResponseInterface::HTTP_OK
            );
        }
    }
}