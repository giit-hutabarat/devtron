<!DOCTYPE html>
<html>
<head>
    <style>
        @page {
            margin-top: 1cm;
            margin-left: 2cm;
            margin-right: 1.5cm;
            margin-bottom: 1.5cm;
        }
        body { 
            font-family: 'Bookman Old Style', serif; 
            font-size: 12pt; 
            line-height: 1.3;
            color: #000; 
        }
        .kop-table { width: 100%; border-bottom: 3px double black; margin-bottom: 10px; }
        .kop-text { text-align: center; }
        .kop-instansi { font-size: 14pt; font-weight: bold; text-transform: uppercase; }
        .kode-pojok { text-align: right; font-weight: bold; margin-bottom: 5px; }
        
        .data-table { 
            width: 100%; 
            border-collapse: collapse; 
            margin: 15px 0;
            table-layout: fixed;
        }
        .data-table th, .data-table td { 
            border: 1px solid black; 
            padding: 8px 5px; 
            vertical-align: top;
            font-size: 11pt;
            word-wrap: break-word;
            white-space: normal;
        }
        .text-center { text-align: center; }
        .text-bold { font-weight: bold; }
        .underline { text-decoration: underline; }
        .indent { text-indent: 40px; text-align: justify; }
        .ttd-wrapper { width: 100%; margin-top: 20px; page-break-inside: avoid; }
    </style>
</head>
<body>
    <table class="kop-table">
        <tr>
            <td width="15%"><img src="<?= $logo_path ?>" width="70"></td>
            <td class="kop-text">
                <div class="kop-instansi">KEJAKSAAN REPUBLIK INDONESIA</div>
                <div class="kop-instansi">KEJAKSAAN TINGGI JAWA TENGAH</div>
                <div class="kop-instansi"><?= strtoupper($nama_instansi) ?></div>
                <div style="font-size: 10pt; font-style: italic;"><?= $alamat_instansi ?></div>
            </td>
        </tr>
    </table>

    <div class="kode-pojok">P-38</div>

    <table width="100%">
        <tr>
            <td width="12%">Nomor</td><td width="2%">:</td>
            <td width="46%"><?= $nomor_surat ?></td>
            <td width="40%" style="text-align: right;"><?= $kota_surat ?>, <?= $tanggal_surat ?></td>
        </tr>
        <tr><td>Sifat</td><td>:</td><td>Biasa</td><td></td></tr>
        <tr><td>Lampiran</td><td>:</td><td>-</td><td></td></tr>
        <tr><td>Hal</td><td>:</td><td colspan="2"><strong>Bantuan Pemanggilan Terdakwa untuk menjalani Persidangan.</strong></td></tr>
    </table>

    <p style="margin-top: 20px;">
        Kepada Yth :<br>
        <strong>Kepala Rumah Tahanan Negara <?= strtoupper($kota_surat) ?></strong><br>
        di -<br>
        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span class="underline text-bold"><?= strtoupper($kota_surat) ?></span>
    </p>

    <p class="indent">
        Sehubungan dengan perkara atas nama <strong><?= $nama_terdakwa_pertama ?> dkk</strong>, pada hari <strong><?= $hari_sidang ?></strong> tanggal <strong><?= $tanggal_sidang ?></strong>, Untuk keperluan pelaksanaan Sidang dengan ini diminta bantuannya kepada orang yang namanya tersebut di bawah ini disampaikan surat panggilan terlampir.
    </p>

    <table class="data-table">
        <thead>
            <tr>
                <th width="5%">No</th>
                <th width="30%">Nama Lengkap</th>
                <th width="35%">Jaksa P.U.</th>
                <th width="30%">Agenda</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach($data_tabel as $row): ?>
            <tr>
                <td class="text-center"><?= $row['no'] ?></td>
                <td class="text-bold"><?= strtoupper($row['nama_terdakwa']) ?></td>
                <td><?= $row['jpu'] ?></td>
                <td><?= $row['agenda'] ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <p class="indent">
        Setelah Surat Panggilan ditandatangani oleh yang bersangkutan, agar tanda terimanya dikembalikan kepada kami. Demikian atas perhatian dan kerjasamanya diucapkan terima kasih.
    </p>

<div class="ttd-wrapper">
    <table width="100%">
        <tr>
            <td width="55%">
                </td>
            <td width="45%" style="text-align: center;">
                <div style="margin-bottom: 5px;">
                    An. Kepala <?= $nama_instansi ?><br>
                    <?= $ttd_jabatan ?>,
                </div>
                
                <div style="margin: 5px 0;">
                    <img src="<?= FCPATH . 'images/logoEsign.png' ?>" width="200">
                </div>

                <strong><u><?= $ttd_nama ?></u></strong><br>
                <?= !empty($ttd_pangkat) ? $ttd_pangkat : '' ?>
                <?php if(!empty($ttd_nip)): ?>
                    NIP. <?= $ttd_nip ?>
                <?php endif; ?>
            </td>
        </tr>
    </table>
</div>
</body>
</html>