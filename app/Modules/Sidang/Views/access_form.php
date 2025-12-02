<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'Akses Menu Sidang' ?></title>
    <style>
        body { font-family: Arial, sans-serif; background-color: #f4f4f4; display: flex; justify-content: center; align-items: center; height: 100vh; margin: 0; }
        .login-container { background: #fff; padding: 30px; border-radius: 8px; box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1); width: 350px; text-align: center; }
        h2 { color: #333; margin-bottom: 20px; }
        .form-group { margin-bottom: 15px; text-align: left; }
        label { display: block; margin-bottom: 5px; font-weight: bold; font-size: 0.9em; }
        input[type="text"] { width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; font-size: 1em; }
        button { background-color: #007bff; color: white; padding: 10px 15px; border: none; border-radius: 4px; cursor: pointer; width: 100%; font-size: 1em; margin-top: 10px; }
        button:hover { background-color: #0056b3; }
        .alert-error { background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; padding: 10px; border-radius: 4px; margin-bottom: 15px; font-size: 0.9em; }
        .instansi { color: #666; font-size: 0.8em; margin-top: 15px; }
    </style>
</head>
<body>

<div class="login-container">
    <h2>Akses Sidang (2FA)</h2>
    <p class="instansi"><?= $nama_instansi ?? 'Instansi Anda' ?></p>

    <?php if (session()->getFlashdata('error')): ?>
        <div class="alert-error">
            <?= session()->getFlashdata('error') ?>
        </div>
    <?php endif; ?>

    <form action="<?= site_url('sidang/verify') ?>" method="post">
        
        <div class="form-group">
            <label for="nip">NIP Pegawai</label>
            <input type="text" id="nip" name="nip" required placeholder="Masukkan NIP Anda">
        </div>

        <div class="form-group">
            <label for="otp_code">Kode OTP 6 Digit</label>
            <input type="text" id="otp_code" name="otp_code" required maxlength="6" pattern="\d{6}" placeholder="Kode dari Authenticator">
        </div>
        
        <button type="submit">Verifikasi & Akses</button>
    </form>

    <p class="instansi" style="margin-top: 25px;">Pastikan Anda sudah mengkonfigurasi 2FA di aplikasi Authenticator.</p>
</div>

</body>
</html>