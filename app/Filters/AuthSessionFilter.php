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
    if (!session()->get('isLoggedIn')) {
        
        // --- KUSTOMISASI DI SINI ---
        
        // Cek apakah request mengharapkan JSON (misal dari Axios/Fetch)
        if (strpos($request->getHeaderLine('Accept'), 'application/json') !== false || $request->isAJAX()) {
            $response = service('response');
            
            return $response->setJSON([
                'status'   => false,
                'error'    => 'AUTH_REQUIRED',
                'message'  => 'Waduh! Sesi login lo udah abis bro. Silakan login ulang ke Dashboard Tron.',
                // Opsional: kirim hash baru biar axios nggak crash
                'csrf_hash' => csrf_hash() 
            ])->setStatusCode(401);
        }

        // Jika akses lewat browser biasa, lempar ke halaman login
        return redirect()->to(site_url('login'))->with('error', 'Silakan login terlebih dahulu.');
    }
}

    /**
     * Jalankan setelah Controller di eksekusi.
     */
    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // Tidak ada yang perlu dilakukan setelah eksekusi
    }
}