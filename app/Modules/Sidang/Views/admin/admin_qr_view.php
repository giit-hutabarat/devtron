<!DOCTYPE html>
<html lang="id">
<head>
    <title><?= $title ?></title>
    <style>
        .container { max-width: 600px; margin: 20px auto; padding: 20px; border: 1px solid #ddd; border-radius: 8px; text-align: center; }
        .qr-box { border: 2px solid #dc3545; /* Ganti warna border menjadi merah/warning */ padding: 20px; margin-top: 20px; display: inline-block; width: 100%; box-sizing: border-box; }
        img { display: block; margin: 10px auto; max-width: 300px; height: auto; border: 1px solid #ccc; /* Border untuk placeholder */ }
        .alert-warning { background-color: #fff3cd; color: #856404; border: 1px solid #ffeeba; padding: 10px; margin-bottom: 20px; border-radius: 5px; }
        .secret-key-display { font-size: 20px; font-weight: bold; letter-spacing: 2px; color: #dc3545; /* Warna merah untuk penekanan */ border: 2px dashed #dc3545; padding: 15px; margin-top: 10px; display: inline-block; background-color: #f8d7da; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Setup Selesai: Kunci OTP</h1>
        <p>NIP: <strong><?= $user['nip'] ?></strong></p>
        <p>Nama: <strong><?= $user['nama_pegawai'] ?></strong></p>

        <?php if (isset($errorMessage)): ?>
            <div class="alert-warning">
                **GENERATING QR GAGAL:** <?= esc($errorMessage) ?>
                <br>Harap lakukan setup KUNCI MANUAL di bawah.
            </div>
        <?php endif; ?>

        <div class="qr-box">
            <h2>Langkah 1: Masukkan Kunci Manual (WAJIB)</h2>
            <p>Kode QR tidak dapat ditampilkan. Masukkan kunci rahasia ini ke aplikasi Authenticator Anda (misalnya Google Authenticator) secara **manual**:</p>
            
            <div class="secret-key-display">
                <?= chunk_split(esc($secretKey), 4, ' ') ?>
            </div>

            <?php 
                // Kita asumsikan $qrCodeImage adalah Base64 atau URI.
                // Jika isinya adalah placeholder 1x1 dari controller, itu akan tetap tampil.
                // Jika Anda ingin menyembunyikan gambar yang gagal:
                if (isset($qrCodeImage) && strpos($qrCodeImage, 'data:image/') !== false && strlen($qrCodeImage) > 100) : 
            ?>
                <p style="color: green; margin-top: 15px;">**QR Code Berhasil Dibuat (Opsional):**</p>
                <img src="<?= $qrCodeImage ?>" alt="QR Code TOTP">
            <?php else: ?>
                <p style="color: red; margin-top: 15px;">**Gambar QR Code GAGAL ditampilkan.**</p>
            <?php endif; ?>

            <p style="color: #666; font-size: 0.9em; margin-top: 10px;">Kode OTP baru akan dihasilkan setiap 30 detik. Pastikan proses setup berhasil.</p>
        </div>

        <p style="margin-top: 20px;">Langkah 2: Setelah sukses di aplikasi, NIP **<?= $user['nip'] ?>** bisa menggunakan Kode OTP untuk masuk.</p>

        <a href="<?= site_url('admin/sidang/setup') ?>" style="display: block; margin-top: 20px; color: #007bff;">
            &larr; Kembali ke Daftar NIP
        </a>
    </div>
</body>
</html>