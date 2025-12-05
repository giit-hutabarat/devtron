<?php 
// Pastikan tidak ada extend layout Vuetify di sini, karena QR Code biasanya full page.

// Ambil Base URL dengan site_url() karena ini adalah file view PHP murni.
$baseUrl = site_url();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?></title>
    <style>
        body { font-family: sans-serif; background-color: #f4f6f9; color: #333; }
        .container { 
            max-width: 700px; 
            margin: 50px auto; 
            padding: 30px; 
            border: 1px solid #ddd; 
            border-radius: 12px; 
            text-align: center; 
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            background-color: #fff;
        }
        h1 { color: #007bff; font-size: 24px; margin-bottom: 5px; }
        .qr-box { 
            border: 2px dashed #007bff; 
            padding: 25px; 
            margin-top: 25px; 
            display: inline-block; 
            width: 90%; 
            box-sizing: border-box;
            background-color: #e9f2ff;
            border-radius: 8px;
        }
        img { 
            display: block; 
            margin: 15px auto; 
            max-width: 250px; 
            height: auto; 
            border: 5px solid #fff; 
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .alert-warning { 
            background-color: #fff3cd; 
            color: #856404; 
            border: 1px solid #ffeeba; 
            padding: 15px; 
            margin-bottom: 20px; 
            border-radius: 5px; 
            font-weight: bold;
        }
        .secret-key-display { 
            font-size: 20px; 
            font-weight: bold; 
            letter-spacing: 2px; 
            color: #dc3545;
            border: 1px solid #dc3545; 
            padding: 15px; 
            margin-top: 15px; 
            display: inline-block; 
            background-color: #f8d7da; 
            border-radius: 5px;
            word-break: break-all;
        }
        .back-link {
            display: inline-block; 
            margin-top: 30px; 
            padding: 10px 20px;
            background-color: #6c757d;
            color: #fff;
            text-decoration: none;
            border-radius: 6px;
            font-weight: bold;
        }
        .back-link:hover {
            background-color: #5a6268;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Setup Selesai: Kunci OTP Pegawai</h1>
        <p style="margin-top: 15px;">NIP: <strong><?= $user['nip'] ?></strong></p>
        <p>Nama: <strong><?= $user['nama_pegawai'] ?></strong></p>

        <?php if (isset($errorMessage) && !empty($errorMessage)): ?>
            <div class="alert-warning">
                **GENERATING QR GAGAL:** <?= esc($errorMessage) ?>
                <br>Harap masukkan KUNCI MANUAL di bawah ini.
            </div>
        <?php endif; ?>

        <div class="qr-box">
            <h2>Langkah 1: Masukkan Kunci Manual (Wajib Jika QR Gagal)</h2>
            <p style="margin-top: 10px;">Masukkan kunci rahasia ini ke aplikasi Authenticator Anda (misalnya Google Authenticator) secara **manual**:</p>
            
            <div class="secret-key-display">
                <?= chunk_split(esc($secretKey), 4, ' ') ?>
            </div>

            <?php 
                // Asumsi $qrCodeImage adalah Base64 URI. Cek apakah bukan placeholder transparan
                if (isset($qrCodeImage) && strpos($qrCodeImage, 'data:image/') !== false && strpos($qrCodeImage, 'R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7') === false) : 
            ?>
                <p style="color: green; margin-top: 25px; font-weight: bold;">Atau Scan QR Code di bawah:</p>
                <img src="<?= $qrCodeImage ?>" alt="QR Code TOTP">
            <?php else: ?>
                <p style="color: red; margin-top: 15px; font-weight: bold;">Gambar QR Code GAGAL ditampilkan. Gunakan KUNCI MANUAL di atas.</p>
            <?php endif; ?>

            <p style="color: #666; font-size: 0.9em; margin-top: 15px;">Kode OTP baru akan dihasilkan setiap 30 detik. Pastikan proses setup berhasil.</p>
        </div>

        <p style="margin-top: 25px;">Langkah 2: Setelah sukses di aplikasi, NIP **<?= $user['nip'] ?>** bisa menggunakan Kode OTP untuk masuk.</p>

        <a href="<?= site_url('setting/otp-sidang') ?>" class="back-link">
            &larr; Kembali ke Daftar NIP
        </a>
    </div>
</body>
</html>