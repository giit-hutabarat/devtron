<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * Filter untuk memeriksa status otentikasi berbasis Session.
 * Digunakan untuk melindungi routes yang hanya bisa diakses oleh user yang sudah login.
 */
class AuthSessionFilter implements FilterInterface
{
    /**
     * Jalankan sebelum Controller di eksekusi.
     */
    public function before(RequestInterface $request, $arguments = null)
    {
        // 1. Cek apakah session service tersedia dan sudah ada sesi login
        if (! service('session')->get('isLoggedIn')) {
            
            // 2. Jika tidak terotentikasi, kembalikan respons 401 Unauthorized.
            // Ini penting untuk API (yang tidak mengalihkan ke halaman login)
            $response = service('response');
            
            // Mengatur status code 401
            $response->setStatusCode(ResponseInterface::HTTP_UNAUTHORIZED); 
            
            // Mengembalikan respons JSON agar mudah dihandle oleh frontend/client
            $response->setJSON([
                'status' => 401,
                'error'  => 'Unauthorized',
                'messages' => 'Akses ditolak. Anda harus login untuk mengakses sumber daya ini.'
            ]);
            
            return $response;
        }
        
        // 3. Jika session ditemukan (isLoggedIn = TRUE), lanjutkan request
        return $request;
    }

    /**
     * Jalankan setelah Controller di eksekusi.
     */
    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // Tidak ada yang perlu dilakukan setelah eksekusi
    }
}