<!DOCTYPE html>
<html>
<head>
    <title>Cetak P-38</title>
    <style>
        /* Margin body 0 agar setting mPDF margin_top 5mm berfungsi maksimal */
        body { font-family: 'Times New Roman', serif; font-size: 11pt; line-height: 1.3; color: #000; margin: 0; padding: 0; }
        
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .text-justify { text-align: justify; }
        .text-bold { font-weight: bold; }
        .uppercase { text-transform: uppercase; }
        .underline { text-decoration: underline; }
        
        /* KOP SURAT */
        .kop-table { width: 100%; border-bottom: 3px double black; margin-bottom: 0; padding-bottom: 5px; }
        .kop-logo { width: 15%; text-align: center; vertical-align: top; }
        .kop-text { width: 85%; text-align: center; vertical-align: middle; }
        .kop-instansi { font-size: 14pt; font-weight: bold; margin: 0; line-height: 1.1; }
        .kop-alamat { font-size: 10pt; margin: 0; font-style: italic; line-height: 1.1; margin-top: 5px; }
        
        /* KODE POJOK */
        .kode-pojok { text-align: right; font-weight: bold; font-size: 10pt; margin-top: 2px; margin-bottom: 5px; }

        /* INFO SURAT (Tabel Atas) */
        .info-table { width: 100%; margin-top: 5px; border-collapse: collapse; }
        .info-table td { vertical-align: top; padding: 2px 0; }
        
        /* KEPADA YTH (RATA KIRI - SESUAI WARJI) */
        .kepada-box {
            margin-top: 20px; 
            margin-bottom: 10px;
            text-align: left; /* Rata Kiri */
            width: 100%;      /* Full Width */
        }

        /* TABEL DATA */
        .data-table { width: 100%; border-collapse: collapse; margin: 10px 0; }
        .data-table th, .data-table td { border: 1px solid black; padding: 5px; vertical-align: top; }
        .data-table th { text-align: center; font-weight: bold; background-color: #ffffff; }
        
        /* TTD */
        .ttd-wrapper { width: 100%; margin-top: 20px; page-break-inside: avoid; }
        .ttd-table { width: 100%; }
        .ttd-col-kiri { width: 50%; }
        .ttd-col-kanan { width: 50%; text-align: center; }
        
        .indent { text-indent: 40px; }
        .clearfix { clear: both; }
    </style>
</head>
<body>

    <table class="kop-table">
        <tr>
            <td class="kop-logo"><img src="<?= $logo_path ?>" width="80"></td>
            <td class="kop-text">
                <div class="kop-instansi">KEJAKSAAN REPUBLIK INDONESIA</div>
                <div class="kop-instansi">KEJAKSAAN TINGGI JAWA TENGAH</div>
                <div class="kop-instansi"><?= strtoupper($nama_instansi) ?></div>
                <div class="kop-alamat"><?= $alamat_instansi ?></div>
            </td>
        </tr>
    </table>

    <div class="kode-pojok">P-38</div>

    <table class="info-table">
        <tr>
            <td width="12%">Nomor</td><td width="2%">:</td>
            <td width="46%"><?= $nomor_surat ?></td>
            <td width="40%" class="text-right"><?= $kota_surat ?>, <?= $tanggal_surat ?></td>
        </tr>
        <tr><td>Sifat</td><td>:</td><td>Biasa</td><td></td></tr>
        <tr><td>Lampiran</td><td>:</td><td>-</td><td></td></tr>
        <tr>
            <td>Hal</td><td>:</td>
            <td colspan="2">Bantuan Pemanggilan Terdakwa untuk menjalani Persidangan.</td>
        </tr>
    </table>

    <div class="kepada-box">
        Kepada Yth :<br>
        <strong>Kepala Rumah Tahanan Negara <?= strtoupper($kota_surat) ?></strong><br>
        di -<br>
        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span class="underline text-bold"><?= strtoupper($kota_surat) ?></span>
    </div>

    <div class="content">
        <p class="text-justify indent" style="margin-top: 5px;">
            Sehubungan dengan perkara atas nama <strong><?= $nama_terdakwa_pertama ?> dkk</strong>, 
            pada hari <strong><?= $hari_sidang ?></strong> tanggal <strong><?= $tanggal_sidang ?></strong>, 
            Untuk keperluan pelaksanaan Sidang dengan ini diminta bantuannya kepada orang yang namanya tersebut di bawah ini disampaikan surat panggilan terlampir.
        </p>
    </div>

    <table class="data-table">
        <thead>
            <tr>
                <th width="5%">No</th><th width="35%">Nama Lengkap</th><th width="30%">Jaksa P.U.</th><th width="30%">Agenda</th>
            </tr>
        </thead>
        <tbody>
            <?php if(!empty($data_tabel)): foreach($data_tabel as $row): ?>
            <tr>
                <td class="text-center"><?= $row['no'] ?></td>
                <td class="text-bold uppercase"><?= $row['nama_terdakwa'] ?></td>
                <td class="text-center"><?= $row['jpu'] ?></td>
                <td class="text-center"><?= $row['agenda'] ?></td>
            </tr>
            <?php endforeach; else: ?>
            <tr><td colspan="4" class="text-center">- Tidak ada data -</td></tr>
            <?php endif; ?>
        </tbody>
    </table>

    <div class="content">
        <p class="text-justify indent">
            Setelah Surat Panggilan ditandatangani oleh yang bersangkutan, agar tanda terimanya dikembalikan kepada kami. 
            Demikian atas perhatian dan kerjasamanya diucapkan terima kasih.
        </p>
    </div>

    <div class="ttd-wrapper">
        <table class="ttd-table">
            <tr>
                <td class="ttd-col-kiri"></td>
                <td class="ttd-col-kanan">
                    An. KEPALA <?= strtoupper($nama_instansi) ?><br>
                    <?= strtoupper($ttd_jabatan) ?>,
                    <br><br><br><br>
                    <span class="text-bold underline"><?= strtoupper($ttd_nama) ?></span><br>
                    <span><?= $ttd_pangkat ?></span>
                </td>
            </tr>
        </table>
    </div>

    <pagebreak />

    <div style="font-weight: bold; margin-bottom: 15px;">
        LAMPIRAN:<br>
        SURAT PENGANTAR KEPALA <?= strtoupper($nama_instansi) ?><br>
        NOMOR : <?= $nomor_surat ?><br>
        TANGGAL : <?= strtoupper($tanggal_surat) ?>
    </div>

    <table class="data-table">
        <thead>
            <tr>
                <th width="5%">No</th><th width="20%">No. Perkara</th>
                <th width="25%">Nama Terdakwa</th><th width="25%">Jaksa P.U.</th>
                <th width="25%">Status / Agenda</th>
            </tr>
        </thead>
        <tbody>
            <?php if(!empty($data_tabel)): foreach($data_tabel as $row): ?>
            <tr>
                <td class="text-center"><?= $row['no'] ?></td>
                <td><?= $row['nomor_perkara'] ?></td>
                <td class="text-bold uppercase"><?= $row['nama_terdakwa'] ?></td>
                <td><?= $row['jpu'] ?></td>
                <td>
                    <?= $row['agenda'] ?><br>
                    <?php if(!empty($row['status_sidang']) && $row['status_sidang'] != '-'): ?>
                    <small>Status: <?= $row['status_sidang'] ?></small>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; else: ?>
            <tr><td colspan="5" class="text-center">- Tidak ada data -</td></tr>
            <?php endif; ?>
        </tbody>
    </table>

    <div class="ttd-wrapper">
        <table class="ttd-table">
            <tr>
                <td class="ttd-col-kiri"></td>
                <td class="ttd-col-kanan">
                    An. KEPALA <?= strtoupper($nama_instansi) ?><br>
                    <?= strtoupper($ttd_jabatan) ?>,
                    <br><br><br><br>
                    <span class="text-bold underline"><?= strtoupper($ttd_nama) ?></span><br>
                    <span><?= $ttd_pangkat ?></span>
                </td>
            </tr>
        </table>
    </div>

</body>
</html>