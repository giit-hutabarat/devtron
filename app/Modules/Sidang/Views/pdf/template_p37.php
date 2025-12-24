<!DOCTYPE html>
<html>
<head>
    <style>
        body { font-family: 'Arial', sans-serif; font-size: 11pt; line-height: 1.3; }
        .top-header { width: 100%; border-bottom: 1px solid #000; padding-bottom: 5px; margin-bottom: 20px; }
        .header-title { text-align: center; font-weight: bold; text-decoration: underline; margin-bottom: 25px; font-size: 12pt; }
        .main-text { text-align: justify; margin-bottom: 20px; }
        .table-data { width: 100%; border-collapse: collapse; margin-left: 10px; }
        .table-data td { vertical-align: top; padding: 2px; }
        .label { width: 30%; }
        .separator { width: 2%; }
        .signature-section { width: 100%; margin-top: 40px; }
        .bold { font-weight: bold; }
    </style>
</head>
<body>
    <table style="width: 100%;">
        <tr>
            <td style="text-align: left; text-decoration: underline; font-weight: bold;"><?= $nama_instansi ?></td>
            <td style="text-align: right; font-weight: bold;">P37</td>
        </tr>
    </table>

    <div class="header-title">SURAT PANGGILAN TERDAKWA</div>

    <div class="main-text">
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

    <div style="margin-top: 25px; font-weight: bold; text-align: center; text-decoration: underline;">
        MENGHADAP KEPADA :
    </div><br>
    <table class="table-data" style="margin-top: 5px;">
        <tr><td class="label">Nama, jabatan</td><td class="separator">:</td><td><?= $jpu ?></td></tr>
        <tr><td class="label">Di kantor/alamat</td><td class="separator">:</td><td>Pengadilan Negeri Boyolali</td></tr>
        <tr>
            <td class="label">Pada hari, tanggal</td><td class="separator">:</td>
            <td><?= $nama_hari ?>, <?= $hari_sidang ?></td>
        </tr>
        <tr><td class="label">Jam</td><td class="separator">:</td><td>10.00 WIB – Selesai</td></tr>
        <tr><td class="label">Untuk Keperluan</td><td class="separator">:</td><td>Persidangan Dengan Agenda <?= $agenda ?></td></tr>
    </table>

    <div style="margin-top: 20px;">Demikian untuk diindahkan sebagaimana mestinya.</div>

    <table style="width: 100%; margin-top: 30px;">
        <tr>
            <td style="width: 50%; text-align: center; vertical-align: top;">
                <div style="height: 45px;"></div> Terdakwa<br><br><br><br><br>
                ( <?= $nama_terdakwa ?> )
            </td>
            
            <td style="width: 50%; text-align: center; vertical-align: top;">
                <?= $kota_surat ?>, <?= $tanggal_surat ?><br>
                A.N KEPALA <?= $nama_instansi ?><br>
                <?= $ttd_jabatan ?>,<br>
                Ub. Penuntut Umum<br><br><br><br>
                <span style="font-weight: bold; text-decoration: underline;"><?= $ttd_nama ?></span><br>
                <?= $ttd_pangkat ?> NIP. <?= $ttd_nip ?>
            </td>
        </tr>
    </table>
</body>
</html>