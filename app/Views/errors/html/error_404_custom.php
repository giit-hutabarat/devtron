<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>404 - Access Denied</title>
    <link href="https://fonts.googleapis.com/css2?family=Special+Elite&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        body {
            background-color: #111;
            background-image: radial-gradient(#222 15%, transparent 16%),
            radial-gradient(#222 15%, transparent 16%);
            background-size: 60px 60px;
            background-position: 0 0, 30px 30px;
            font-family: 'Special Elite', cursive; /* Font Mesin Tik */
            height: 100vh;
            margin: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            color: #333;
        }

        /* Container Dokumen */
        .file-folder {
            position: relative;
            width: 90%;
            max-width: 600px;
            background: #fdfbf7; /* Warna Kertas Tua */
            padding: 40px;
            box-shadow: 0 0 50px rgba(0,0,0,0.8);
            transform: rotate(-2deg);
            animation: floatDoc 6s ease-in-out infinite;
            border: 1px solid #ccc;
        }

        /* Klip Kertas di atas */
        .file-folder::before {
            content: "";
            position: absolute;
            top: -15px; left: 20px;
            width: 100px; height: 30px;
            background: rgba(0,0,0,0.2);
            transform: rotate(-3deg);
            z-index: -1;
        }

        /* Header Dokumen */
        .doc-header {
            border-bottom: 2px solid #000;
            padding-bottom: 10px;
            margin-bottom: 20px;
            display: flex;
            justify-content: space-between;
            align-items: end;
        }

        .logo-text {
            font-size: 1.2rem;
            font-weight: bold;
            text-transform: uppercase;
        }

        .case-number {
            font-size: 0.9rem;
            color: #cc0000;
        }

        /* Isian Dokumen */
        .content {
            font-size: 1.1rem;
            line-height: 1.8;
            position: relative;
        }

        /* EFEK SENSOR / REDACTED (Coretan Hitam) */
        .sensor {
            background-color: #000;
            color: #000; /* Sembunyikan teks */
            padding: 0 5px;
            border-radius: 2px;
            cursor: help;
            transition: 0.3s;
            user-select: none; /* Biar ga bisa diblok/dicopy */
        }
        
        .sensor:hover {
            background-color: #111; /* Sedikit berubah warna saat hover */
        }

        /* Stempel Merah Besar */
        .stamp-box {
            border: 5px solid #cc0000;
            color: #cc0000;
            font-size: 3rem;
            font-weight: bold;
            text-transform: uppercase;
            padding: 10px 20px;
            position: absolute;
            top: 40%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-15deg);
            opacity: 0.8;
            mask-image: url('https://s3-us-west-2.amazonaws.com/s.cdpn.io/8399/grunge.png'); /* Efek Grunge/Kasar */
            -webkit-mask-image: url('https://s3-us-west-2.amazonaws.com/s.cdpn.io/8399/grunge.png');
            pointer-events: none;
            z-index: 10;
            white-space: nowrap;
        }

        /* Pesan Error Khusus */
        .alert-message {
            margin-top: 30px;
            background: #000;
            color: #fff;
            padding: 15px;
            text-align: center;
            font-family: 'Courier New', monospace;
            border: 2px solid #cc0000;
            box-shadow: 5px 5px 0 #cc0000;
        }

        .btn-back {
            display: block;
            width: 100%;
            text-align: center;
            margin-top: 20px;
            text-decoration: none;
            color: #333;
            font-weight: bold;
            border: 1px dashed #333;
            padding: 10px;
            transition: all 0.3s;
        }

        .btn-back:hover {
            background: #333;
            color: #fff;
        }

        /* Animasi Melayang */
        @keyframes floatDoc {
            0% { transform: rotate(-2deg) translateY(0px); }
            50% { transform: rotate(-1deg) translateY(-15px); }
            100% { transform: rotate(-2deg) translateY(0px); }
        }

        /* Responsif HP */
        @media (max-width: 600px) {
            .file-folder { padding: 20px; width: 85%; }
            .stamp-box { font-size: 2rem; border-width: 3px; }
            .content { font-size: 0.9rem; }
        }
    </style>
</head>
<body>

    <div class="file-folder">
        
        <div class="stamp-box">404 NOT FOUND</div>

        <div class="doc-header">
            <div class="logo-text">
                <i class="fa-solid fa-scale-balanced"></i> KEJAKSAAN REPUBLIK INDONESIA
            </div>
            <div class="case-number">CASE ID: #404-UNKNOWN</div>
        </div>

        <div class="content">
            <p>
                <strong>TANGGAL:</strong> <?= date('d M Y') ?><br>
                <strong>SUBJEK:</strong> PENELUSURAN DATA HILANG<br>
                <strong>STATUS:</strong> <span style="color:#cc0000; font-weight:bold;">SANGAT RAHASIA</span>
            </p>
            
            <p>
                Berdasarkan laporan sistem, pengguna dengan IP <span class="sensor">192.168.1.X</span> mencoba mengakses berkas perkara <span class="sensor">/halaman-ini-tidak-ada</span>.
            </p>
            <p>
                Hasil investigasi intelijen siber menunjukkan bahwa <span class="sensor">data tersebut telah dihapus permanen</span> atau <span class="sensor">dipindahkan ke lokasi aman</span> oleh administrator.
            </p>
            <p>
                Tindakan lanjut: <span class="sensor">Segera amankan pengguna</span> dan arahkan kembali ke zona aman.
            </p>
        </div>

        <div class="alert-message">
            <i class="fa-solid fa-hand-paper"></i> AKSES DITOLAK.<br> 
            Informasi ini diklasifikasikan SANGAT RAHASIA atau mungkin... tidak pernah ada.
        </div>

        <a href="<?= base_url('/') ?>" class="btn-back">
            <i class="fa-solid fa-arrow-left"></i> KEMBALI KE BERANDA
        </a>

    </div>

</body>
</html>