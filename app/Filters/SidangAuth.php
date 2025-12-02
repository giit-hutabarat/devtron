<?php

namespace App\Filters; 

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * Filter untuk memproteksi akses ke modul Sidang.
 * Memastikan sesi 'isLoggedInSidang' sudah aktif (setelah verifikasi NIP + OTP).
 */
class SidangAuth implements FilterInterface
{
    /**
     * Dilakukan sebelum Controller dipanggil.
     */
    public function before(RequestInterface $request, $arguments = null)
    {
        // Cek apakah sesi login sidang sudah aktif
        if (!session()->get('isLoggedInSidang')) {
            
            // Jika belum login, redirect ke halaman verifikasi NIP + OTP
            // site_url('sidang/access') akan mengarah ke App\Modules\Sidang\Controllers\SidangController::accessForm()
            return redirect()->to(site_url('sidang/access'))
                            ->with('error', 'Akses ditolak. Silakan masukkan NIP dan Kode OTP Anda.');
        }

        // Jika sesi ada, biarkan request berlanjut ke Controller
    }

    /**
     * Dilakukan setelah Controller dieksekusi.
     */
    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // Tidak perlu aksi setelah Controller, biarkan response berlanjut
    }
}