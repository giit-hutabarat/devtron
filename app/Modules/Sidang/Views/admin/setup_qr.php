<?php
/**
 * SECURITY HEADERS (Server-Side)
 * Mencegah Clickjacking dan memastikan QR Code tidak tersimpan di Cache browser
 */
if (!headers_sent()) {
    // 1. Anti-Clickjacking: Melarang halaman ini dibuka di dalam iframe
    header("X-Frame-Options: DENY");

    // 2. Cache Control: QR Code bersifat rahasia, jangan sampai tersimpan di history/cache
    header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
    header("Cache-Control: post-check=0, pre-check=0", false);
    header("Pragma: no-cache");
    header("Expires: Mon, 26 Jul 1997 05:00:00 GMT"); // Tanggal lampau agar lgsg expired

    // 3. Tambahan XSS Protection
    header("X-Content-Type-Options: nosniff");
    header("X-XSS-Protection: 1; mode=block");
}
$baseUrl = site_url();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <meta http-equiv="X-Content-Type-Options" content="nosniff">
    
    <title><?= $title ?? 'Setup 2FA OTP' ?></title>
    
    <link rel="stylesheet" href="<?= base_url('assets/css/setup-qr.css') ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&family=Courier+Prime:wght@700&display=swap" rel="stylesheet">

    <style id="antiClickjack">body{display:none !important;}</style>
    <script>
        if (self === top) {
            var antiClickjack = document.getElementById("antiClickjack");
            antiClickjack.parentNode.removeChild(antiClickjack);
        } else {
            top.location = self.location;
        }
    </script>
</head>
<body>

    <div class="container">
        
        <div class="header-box">
            <h1><i class="fa-solid fa-shield-halved me-2"></i> Kunci Keamanan Baru</h1>
            <p>Setup Google Authenticator untuk Pegawai</p>
        </div>

        <div class="content-box">
            
            <div class="user-info mb-4">
                <div style="font-size: 1.1rem; font-weight: 700; color: #2c3e50;">
                    <?= esc($user['nama_pegawai']) ?>
                </div>
                <div style="color: #6c757d; font-size: 0.9rem;">
                    NIP: <?= esc($user['nip']) ?>
                </div>
            </div>

            <?php if (isset($errorMessage) && !empty($errorMessage)): ?>
                <div class="alert-error">
                    <strong><i class="fa-solid fa-circle-exclamation"></i> Gagal Memuat QR:</strong> 
                    <?= esc($errorMessage) ?><br>
                    Silakan gunakan metode <strong>Input Manual</strong> di bawah.
                </div>
            <?php endif; ?>

            <?php 
                // Cek Validitas Gambar QR (Basic Check)
                $showQr = (isset($qrCodeImage) && strpos($qrCodeImage, 'data:image/') !== false);
            ?>

            <?php if ($showQr): ?>
                <div class="qr-section">
                    <div class="qr-frame">
                        <img src="<?= $qrCodeImage ?>" alt="QR Code 2FA" class="qr-image">
                    </div>
                    <p class="small text-muted mb-4">
                        <i class="fa-solid fa-mobile-screen-button"></i> 
                        Buka aplikasi <strong>Authenticator</strong> > Scan QR Code di atas.
                    </p>
                </div>
            <?php endif; ?>

            <hr style="border: 0; border-top: 1px solid #eee; margin: 20px 0;">

            <div class="manual-section">
                <p class="mb-2" style="font-size: 0.9rem; font-weight: 600;">Atau masukkan kode secara manual:</p>
                
                <div class="secret-container">
                    <span class="secret-label">SECRET KEY</span>
                    <div id="keyDisplay" class="secret-display blur-text" data-key="<?= esc($secretKey) ?>">
                        •••• •••• •••• ••••
                    </div>
                    
                    <div class="action-buttons">
                        <button type="button" class="btn btn-outline" id="btnToggleKey">
                            <i class="fa-solid fa-eye" id="iconEye"></i> Lihat Kunci
                        </button>
                        <button type="button" class="btn btn-outline" id="btnCopyKey" title="Salin ke Clipboard">
                            <i class="fa-regular fa-copy"></i> Salin
                        </button>
                    </div>
                </div>
                
                <div style="margin-top: 15px; font-size: 0.8rem; color: #888;">
                    <i class="fa-solid fa-triangle-exclamation text-warning"></i>
                    Kode ini bersifat <strong>RAHASIA</strong>. Jangan bagikan kepada siapapun.
                </div>
            </div>

            <div class="footer-nav">
                <p class="small text-muted mb-3">Setelah di-scan, silakan kembali untuk tes login.</p>
                <a href="<?= site_url('setting/admin-sidang') ?>" class="btn-back">
                    <i class="fa-solid fa-arrow-left"></i> Kembali ke Daftar Pegawai
                </a>
            </div>

        </div>
    </div>

    <script src="<?= base_url('assets/js/setup-qr.js') ?>"></script>

</body>
</html>