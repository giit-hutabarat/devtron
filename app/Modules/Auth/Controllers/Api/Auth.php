<?php

namespace App\Modules\Auth\Controllers\Api;

use App\Controllers\BaseControllerApi;
use App\Modules\Auth\Models\LoginModel;
use CodeIgniter\HTTP\Response;
use CodeIgniter\HTTP\ResponseInterface;
use Exception;
use ReflectionException;

class Auth extends BaseControllerApi
{
    // Pastikan BaseControllerApi Anda sudah meng-extend CI4 ResourceController atau sejenisnya
    protected $format       = 'json';
    protected $modelName    = LoginModel::class;
    
    // Perhatikan: Session service harus di-load (biasanya di BaseControllerApi atau construct)
    // Jika belum, Anda bisa tambahkan: protected $session;
    // public function __construct() { $this->session = \Config\Services::session(); }

    /**
     * Register a new user
     * (Logika Register tidak diubah, hanya memastikan tidak ada JWT)
     * @return Response
     * @throws ReflectionException
     */
    public function register()
    {
        // ... (Logika Register tidak diubah)
        $rules = [
            'username' => 'required',
            'email' => 'required|min_length[6]|max_length[50]|valid_email|is_unique[user.email]',
            'password' => 'required|min_length[8]|max_length[255]'
        ];

        $input = $this->getRequestInput();

        if (!$this->validate($rules)) {
            return $this->getResponse(
                [
                    'status' => false,
                    'message' => $this->validator->getErrors()
                ],
                ResponseInterface::HTTP_OK
            );
        }

        $token = base64_encode(mt_rand(100000, 999999));
        $data = [
            'email' => $input['email'],
            'username' => $input['username'],
            'password' => $input['password'],
            'token' => $token
        ];

        if ($this->model->save($data)) {
            helper('email');
            sendEmail("Verifikasi Akun", $input['email'], view('App\Modules\Auth\Views\email/verify', $data));
            return $this->getResponse(
                [
                    'status' => true,
                    'message' => lang('App.regSuccess'),
                    'data' => ['url' => base_url("")]
                ],
                ResponseInterface::HTTP_OK
            );
        } else {
            return $this->getResponse(
                [
                    'status' => false,
                    'message' => lang('App.regFailed'),
                    'data' => []
                ],
                ResponseInterface::HTTP_OK
            );
        }
    }

    /**
     * Authenticate Existing User (login_admin)
     * Kita asumsikan route 'login_admin' memanggil method ini.
     * @return Response
     */
    public function login_admin()
    {
        // Ganti 'login' menjadi 'login_admin' sesuai dengan route yang Anda miliki
        return $this->login();
    }

    /**
     * Authenticate Existing User
     * Method utama untuk memverifikasi kredensial
     * @return Response
     */
    public function login()
    {
        $rules = [
            'email' => 'required|min_length[6]|max_length[50]|valid_email|validateUser[email,password]',
            'password' => 'required|min_length[8]|max_length[255]|validateUser[email, password]'
        ];

        $errors = [
            'email' => ['validateUser' => lang('App.errorLogin')],
            'password' => ['validateUser' => lang('App.errorPassword')]
        ];

        $input = $this->getRequestInput();

        if (!$this->validate($rules, $errors)) {
            return $this->getResponse(
                [
                    'status' => false,
                    'message' => lang('App.invalid'),
                    'data' => $this->validator->getErrors()
                ],
                ResponseInterface::HTTP_OK
            );
        }

        // *** PERUBAHAN KRUSIAL DI SINI ***
        // Panggil method baru untuk membuat Session, bukan JWT.
        return $this->createSessionForUser($input['email']);
    }

     /**
     * Logika Logout
     * Menghancurkan session pengguna dan mengembalikan respons sukses
     * @return Response
     */
    public function logout()
    {
        try {
            // Hancurkan semua session
            session()->destroy(); 

            // Hapus cookie 'access_token' lama jika masih ada (praktik pembersihan)
            if (isset($_COOKIE['access_token'])) {
                setcookie("access_token", "", time() - 3600, "/");
            }
            
            // *** PERUBAHAN DISINI: Ganti base_url("/login") menjadi base_url() (URL Root) ***
            return $this->getResponse(
                [
                    'status' => true,
                    'message' => lang('App.logoutSuccess'),
                    'data' => ['url' => base_url("/")] // URL root frontend
                ],
                ResponseInterface::HTTP_OK
            );
        } catch (Exception $e) {
            // Handle error jika penghancuran session gagal (jarang terjadi)
            return $this->getResponse(
                [
                    'status' => false,
                    'error' => $e->getMessage()
                ],
                ResponseInterface::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }

    /**
     * Request Reset Password for user
     * @return Response
     * @throws ReflectionException
     */
    public function resetPassword()
    {
        // ... (Logika Reset Password tidak diubah)
        $rules = [
            'email' => 'required|min_length[6]|max_length[50]|valid_email|is_not_unique[user.email]',
        ];

        $input = $this->getRequestInput();

        if (!$this->validate($rules)) {
            return $this->getResponse(
                [
                    'status' => false,
                    'message' => $this->validator->getErrors()
                ],
                ResponseInterface::HTTP_OK
            );
        }

        $token = base64_encode(mt_rand(100000, 999999));
        $data = [
            'email' => $input['email'],
            'token' => $token,
        ];

        $user = $this->model->where(['email' => $input['email']])->first();
        $user_id = $user['id_login'];
        $user_data = [
            'token' => $token,
        ];

        if ($this->model->update($user_id, $user_data)) {
            helper('email');
            sendEmail("Permintaan Reset Password", $input['email'], view('App\Modules\Auth\Views\email/reset', $data));
            return $this->getResponse(
                [
                    'status' => true,
                    'message' => lang('App.checkEmail'),
                    'data' => ['url' => base_url("")]
                ],
                ResponseInterface::HTTP_OK
            );
        } else {
            return $this->getResponse(
                [
                    'status' => false,
                    'message' => lang('App.reqFailed'),
                    'data' => []
                ],
                ResponseInterface::HTTP_OK
            );
        }
    }

    /**
     * Request Change password for user
     * @return Response
     * @throws ReflectionException
     */
    public function changePassword()
    {
        // ... (Logika Change Password tidak diubah)
        $rules = [
            'email' => 'required',
            'token' => 'required',
            'password' => 'required|min_length[8]|max_length[255]',
            'verify' => 'required|matches[password]'
        ];

        $input = $this->getRequestInput();

        if (!$this->validate($rules)) {
            return $this->getResponse(
                [
                    'status' => false,
                    'message' => $this->validator->getErrors()
                ],
                ResponseInterface::HTTP_OK
            );
        }

        $forgot_pass = $this->model->where(['email' => $input['email'], 'token' => $input['token']])->first();
        if (!$forgot_pass) {
            return $this->getResponse(["status" => false, "message" => lang('App.tokenInvalid'), "data" => []], ResponseInterface::HTTP_OK);
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
                    'data' => ['url' => base_url("/login")]
                ],
                ResponseInterface::HTTP_OK
            );
        } else {
            return $this->getResponse(
                [
                    'status' => false,
                    'message' => lang('App.regFailed'),
                    'data' => []
                ],
                ResponseInterface::HTTP_OK
            );
        }
    }
    
    /**
     * Fungsi untuk membuat dan mengatur Session untuk pengguna yang berhasil login.
     * Menggantikan getJWTForUser().
     * @param string $emailAddress
     * @param int $responseCode
     * @return Response
     */
    private function createSessionForUser(
        string $emailAddress,
        int $responseCode = ResponseInterface::HTTP_OK
    ) {
        try {
            // 1. Ambil data user dari model
            $user = $this->model->findUserByEmailAddress($emailAddress);
            unset($user['password']); // Hapus password sebelum disimpan ke Session atau dikirim ke client

            // 2. Siapkan data Session
            $setSession = [
                'id' => $user['id'], // Ganti dengan primary key yang benar
                'email' => $user['email'],
                'fullname' => $user['fullname'] ?? '', // Gunakan null coalescing jika field tidak selalu ada
                'username' => $user['username'],
                'user_type' => $user['user_type'],
                'is_active' => $user['is_active'],
                'isLoggedIn' => true // Nama kunci yang kita cek di AuthSessionFilter
            ];
            
            // 3. Set Session
            session()->set($setSession);

            // *** LOGIKA JWT LAMA SUDAH DIHAPUS DI SINI ***
            // Tidak ada setcookie("access_token", ...)
            // Tidak ada helper('jwt')

            // 4. Berikan respons sukses (Tidak perlu mengembalikan token)
            return $this->getResponse(
                [
                    'status' => true,
                    'message' => lang('App.authSuccess'),
                    'data' => [
                        'user' => $user,
                        // Client cukup tahu login sukses. Cookie session dihandle browser.
                    ]
                ]
            );
        } catch (Exception $exception) {
            return $this->getResponse(
                [
                    'status' => false,
                    'error' => $exception->getMessage()
                ],
                $responseCode
            );
        }
    }
}