<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <meta http-equiv="X-Content-Type-Options" content="nosniff">
    <meta http-equiv="Referrer-Policy" content="strict-origin-when-cross-origin">
    
    <title><?= $title ?? 'Akses Menu Sidang' ?></title>
    
    <link rel="stylesheet" href="<?= base_url('assets/css/access-style.css') ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">

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

    <noscript>
        <div style="position:fixed; top:0; left:0; width:100%; height:100%; background:white; z-index:9999; display:flex; align-items:center; justify-content:center; text-align:center;">
            <h3 style="color:red; font-family:sans-serif;">JavaScript Wajib Diaktifkan Untuk Mengakses Halaman Ini Demi Keamanan.</h3>
        </div>
    </noscript>

    <div class="login-container">
        
        <?php if (!empty($logo_instansi)): ?>
            <img src="<?= esc($logo_instansi) ?>" 
                 onerror="this.onerror=null;this.src='<?= base_url('images/default_logo.png') ?>';" 
                 alt="Logo Instansi" 
                 class="logo-login">
        <?php endif; ?>

        <h1 class="instansi-name">
            <?= esc($nama_instansi ?? 'Aplikasi Sidang') ?>
        </h1>

        <?php if (session()->getFlashdata('error')): ?>
            <div class="alert-error">
                <i class="fa-solid fa-circle-exclamation"></i>
                <span><?= session()->getFlashdata('error') ?></span>
            </div>
        <?php endif; ?>
        <?php if (session()->getFlashdata('success')): ?>
            <div class="alert-error" style="background-color: #f0fdf4; color: #166534; border-color: #bbf7d0;">
                <i class="fa-solid fa-check-circle"></i>
                <span><?= session()->getFlashdata('success') ?></span>
            </div>
        <?php endif; ?>

        <form action="<?= site_url('sidang/verify') ?>" method="post">
            
            <?= csrf_field() ?>

            <div class="form-group">
                <label for="nip" class="form-label">NIP Pegawai</label>
                <div class="input-wrapper">
                    <input type="text" 
                           id="nip" 
                           name="nip" 
                           class="form-input"
                           required 
                           maxlength="18"
                           pattern="\d*" 
                           inputmode="numeric" 
                           placeholder="Masukkan 18 digit NIP"
                           autocomplete="off"
                           autofocus>
                    <i class="fa-solid fa-id-badge toggle-password" style="cursor: default;"></i>
                </div>
            </div>

            <div class="form-group">
                <label for="otp_code" class="form-label">Kode OTP Authenticator</label>
                <div class="input-wrapper">
                    <input type="password" 
                           id="otp_code" 
                           name="otp_code" 
                           class="form-input"
                           required 
                           maxlength="6"
                           pattern="\d*" 
                           inputmode="numeric" 
                           placeholder="6 Digit Kode"
                           autocomplete="one-time-code">
                    
                    <span id="toggleOtp" class="toggle-password" title="Lihat Kode">
                        <i class="fa-solid fa-eye" id="iconEye"></i>
                    </span>
                </div>
            </div>
            
            <button type="submit" class="btn-submit">
                <i class="fa-solid fa-unlock-keyhole"></i> Verifikasi Akses
            </button>
        </form>

        <div class="footer-note">
            <i class="fa-solid fa-shield-halved me-1"></i> Area Terbatas. <br>
            Hubungi admin jika terkendala akses 2FA.
        </div>
    </div>

    <script src="<?= base_url('assets/js/access-script.js') ?>"></script>

</body>
</html>