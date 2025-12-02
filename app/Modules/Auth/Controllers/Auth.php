<?php

namespace App\Modules\Auth\Controllers;

use App\Controllers\BaseController;
use App\Modules\Auth\Models\LoginModel; 
use CodeIgniter\HTTP\ResponseInterface;

class Auth extends BaseController
{
    /**
     * Menangani proses login admin dari AJAX/Form Submit (Web/SSR)
     * Output: JSON dengan instruksi redirect atau pesan error.
     */
    public function login_admin()
    {
        $session = session();
        $model = new LoginModel(); // Pastikan LoginModel sudah benar namespacenya

        // 1. Ambil Input
        $username = $this->request->getPost('username');
        $password = $this->request->getPost('password');

        if (empty($username) || empty($password)) {
            return $this->response->setJSON(['status' => false, 'message' => 'Input tidak lengkap.']);
        }

        // 2. Cari User
        $user = $model->groupStart()
                      ->where('username', $username)
                      ->orWhere('email', $username)
                      ->groupEnd()
                      ->where('is_active', 1)
                      ->first();

        if ($user) {
            // 3. Cek Password (Hash)
            if (password_verify($password, $user['password'])) {
                
                // 4. Cek Level (Hanya izinkan Admin/Type 1)
                if ($user['user_type'] == 2) {
                    return $this->response->setJSON(['status' => false, 'message' => 'Bukan akun Admin.']);
                }

                // 5. BIKIN SESSION (Tiket Masuk) - KUNCI KITA KOREKSI
                $sessData = [
                    'id'        => $user['id'],
                    'username'  => $user['username'],
                    'fullname'  => $user['fullname'],
                    'user_type' => $user['user_type'],
                    'isLoggedIn' => true, // <-- Kunci disamakan dengan 'auth_session' Filter!
                ];
                $session->set($sessData);

                // 6. Sukses & Kasih Link Redirect
                return $this->response->setJSON([
                    'status' => true,
                    'message' => 'Login Berhasil!',
                    'redirect' => base_url('dashboard') // Arahkan ke Module Dashboard
                ]);
            } else {
                return $this->response->setJSON(['status' => false, 'message' => 'Password Salah!']);
            }
        } else {
            return $this->response->setJSON(['status' => false, 'message' => 'Username tidak ditemukan.']);
        }
    }

    // Method logout dihapus karena sudah ditangani oleh Controllers/Api/Auth.php
    // dan route-nya sudah diarahkan ke sana.
}