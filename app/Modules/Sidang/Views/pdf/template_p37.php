<!DOCTYPE html>
<html>
<head>
    <style>
        @page {
            margin: 2cm;
        }
        body { 
            font-family: 'Bookman Old Style', serif; 
            font-size: 12pt; 
            line-height: 1.5; 
            color: #000;
        }
        .header-top { width: 100%; margin-bottom: 10px; }
        .title { 
            text-align: center; 
            font-weight: bold; 
            text-decoration: underline; 
            text-transform: uppercase;
            font-size: 12pt; 
            margin-bottom: 20px; 
        }
        .table-data { width: 100%; border-collapse: collapse; margin-bottom: 15px; }
        .table-data td { vertical-align: top; padding: 3px; }
        .label { width: 35%; }
        .separator { width: 3%; }
        .main-content { text-align: justify; margin-bottom: 15px; text-indent: 40px; }
        .bold { font-weight: bold; }
        .center-heading { 
            text-align: center; 
            font-weight: bold; 
            text-decoration: underline; 
            margin: 15px 0;
            text-transform: uppercase;
        }
        .ttd-table { width: 100%; margin-top: 30px; page-break-inside: avoid; }
    </style>
</head>
<body>
    <table class="header-top">
        <tr>
            <td style="text-align: left; font-weight: bold; text-decoration: underline; text-transform: uppercase;"><?= $nama_instansi ?></td>
            <td style="text-align: right; font-weight: bold;">P-37</td>
        </tr>
    </table>

    <div class="title">SURAT PANGGILAN TERDAKWA</div>

    <div class="main-content">
        Untuk melaksanakan surat Penetapan Hakim Pengadilan Negeri <?= strtoupper($kota_surat) ?>, sehubungan dengan perkara tindak Pidana <span class="bold"><?= $jenis_perkara ?></span>, dengan acara pemeriksaan biasa atas nama terdakwa <span class="bold"><?= $nama_lengkap_raw ?></span>. Untuk keperluan persidangan diminta agar Saudara memberikan keterangan.
    </div>

    <table class="table-data" style="margin-left: 40px; width: 90%;">
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

    <div class="center-heading">MENGHADAP KEPADA:</div>

    <table class="table-data" style="margin-left: 40px; width: 90%;">
        <tr><td class="label">Nama, Jabatan</td><td class="separator">:</td><td class="bold"><?= $jpu ?></td></tr>
        <tr><td class="label">Di kantor / Alamat</td><td class="separator">:</td><td>Pengadilan Negeri <?= $kota_surat ?></td></tr>
        <tr><td class="label">Pada hari, Tanggal</td><td class="separator">:</td><td><?= $nama_hari ?>, <?= $hari_sidang ?></td></tr>
        <tr><td class="label">Jam</td><td class="separator">:</td><td>10.00 WIB – Selesai</td></tr>
        <tr><td class="label">Untuk Keperluan</td><td class="separator">:</td><td>Persidangan Dengan Agenda <?= $agenda ?></td></tr>
    </table>

    <div style="margin-top: 15px;">Demikian untuk diindahkan sebagaimana mestinya.</div>

    <table class="ttd-table" style="width: 100%; margin-top: 20px;">
    <tr>
        <td style="width: 50%; text-align: center; vertical-align: top;">
            <br><br><br> Terdakwa<br><br><br><br>
            ( <strong><?= strtoupper($nama_terdakwa) ?></strong> )
        </td>

        <td style="width: 50%; text-align: center; vertical-align: top;">
            <?= $kota_surat ?>, <?= $tanggal_surat ?><br> An. Kepala <?= $nama_instansi ?><br> <?= $ttd_jabatan ?>,<br> Up. Penuntut Umum <br><br><br><br><strong><u><?= $jpu ?></u></strong><br>
            <?= $ttd_pangkat ?> 
            <?php if(!empty($ttd_nip)): ?>
                NIP. <?= $ttd_nip ?>
            <?php endif; ?>
        </td>
    </tr>
</table>

</body>
</html>