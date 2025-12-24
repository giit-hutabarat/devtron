<!DOCTYPE html>
<html>
<head>
    <style>
        body { font-family: 'Arial', sans-serif; font-size: 11pt; line-height: 1.3; }
        .header-top { width: 100%; margin-bottom: 20px; }
        .title { text-align: center; font-weight: bold; text-decoration: underline; font-size: 12pt; margin-bottom: 20px; }
        .table-data { width: 100%; border-collapse: collapse; margin-left: 5px; }
        .table-data td { vertical-align: top; padding: 2px; }
        .label { width: 30%; }
        .separator { width: 2%; }
        .main-content { text-align: justify; margin-bottom: 15px; }
        .bold { font-weight: bold; }
        /* CSS Baru untuk meratakan teks ke tengah */
        .center-heading { 
            text-align: center; 
            font-weight: bold; 
            text-decoration: underline; 
            margin-top: 25px; 
            margin-bottom: 10px; 
        }
    </style>
</head>
<body>
    <table class="header-top">
        <tr>
            <td style="text-align: left; font-weight: bold; text-decoration: underline;"><?= $nama_instansi ?></td>
            <td style="text-align: right; font-weight: bold;">P37</td>
        </tr>
    </table>

    <div class="title">SURAT PANGGILAN TERDAKWA</div>

    <div class="main-content">
        Untuk melaksanakan surat Penetapan Hakim Pengadilan Negeri BOYOLALI, sehubungan dengan perkara tindak Pidana <span class="bold"><?= $jenis_perkara ?></span>, dengan acara pemeriksaan biasa atas nama terdakwa <span class="bold"><?= $nama_terdakwa ?> Bin <?= $nama_ortu ?></span> Untuk keperluan persidangan diminta agar Saudara memberikan keterangan.
    </div>

    <table class="table-data">
        <tr><td class="label">Nama lengkap</td><td class="separator">:</td><td class="bold"><?= $nama_terdakwa ?></td></tr>
        <tr><td class="label">Tempat lahir</td><td class="separator">:</td><td><?= $tempat_lahir ?></td></tr>
        <tr><td class="label">Umur / Tgl Lahir</td><td class="separator">:</td><td><?= $umur ?> Tahun / <?= $tgl_lahir ?></td></tr>
        <tr><td class="label">Jenis kelamin</td><td class="separator">:</td><td><?= $jenis_kelamin ?></td></tr>
        <tr><td class="label">Kewarganegaraan</td><td class="separator">:</td><td><?= $kewarganegaraan ?></td></tr>
        <tr><td class="label">Tempat tinggal</td><td class="separator">:</td><td><?= $alamat ?></td></tr>
        <tr><td class="label">Agama</td><td class="separator">:</td><td><?= $agama ?></td></tr>
        <tr><td class="label">Pekerjaan</td><td class="separator">:</td><td><?= $pekerjaan ?></td></tr>
        <tr><td class="label">Pendidikan</td><td class="separator">:</td><td><?= $pendidikan ?></td></tr>
    </table>

    <div class="center-heading">MENGHADAP KEPADA :</div><br>

    <table class="table-data">
        <tr><td class="label">Nama, jabatan</td><td class="separator">:</td><td class="bold"><?= $jpu ?></td></tr>
        <tr><td class="label">Di kantor/alamat</td><td class="separator">:</td><td>Pengadilan Negeri Boyolali</td></tr>
        <tr><td class="label">Pada hari, tanggal</td><td class="separator">:</td><td><?= $nama_hari ?>, <?= $hari_sidang ?></td></tr>
        <tr><td class="label">Jam</td><td class="separator">:</td><td>10.00 WIB – Selesai</td></tr>
        <tr><td class="label">Untuk Keperluan</td><td class="separator">:</td><td>Persidangan Dengan Agenda <?= $agenda ?></td></tr>
    </table>

    <div style="margin-top: 15px;">Demikian untuk diindahkan sebagaimana mestinya.</div>

    <table style="width: 100%; margin-top: 30px;">
        <tr>
            <td style="width: 50%; text-align: center; vertical-align: top;">
                <div style="color: white;">Spacer</div>
                <div style="color: white;">Spacer</div>
                <div style="color: white;">Spacer</div>
                Terdakwa
            </td>
            <td style="width: 50%; text-align: center; vertical-align: top;">
                <?= $kota_surat ?>, <?= $tanggal_surat ?><br>
                An. Kepala <?= $nama_instansi ?><br>
                <?= $ttd_jabatan ?>,<br>
                Ub. Penuntut Umum
            </td>
        </tr>
        <tr>
            <td style="text-align: center; padding-top: 50px;">
                ( <?= $nama_terdakwa ?> )
            </td>
            <td style="text-align: center; padding-top: 50px;">
                <strong><u><?= $ttd_nama ?></u></strong><br>
                <?= $ttd_pangkat ?>
            </td>
        </tr>
    </table>
</body>
</html>