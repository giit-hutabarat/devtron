<?php
// Ambil URL Base dari CodeIgniter (penting untuk AJAX)
$base_url = base_url();

// --- PENTING: ASUMSI VARIABEL DARI CONTROLLER ---
// Jika variabel tidak disetel dari Controller, gunakan nilai default
$nama_instansi_app = $nama_instansi_app ?? 'INSTANSI ERROR'; 
$path_logo_instansi_db = $path_logo_instansi ?? 'images/default_logo.png'; 
$nip_user = $nip_user ?? '-';
$nama_pegawai = $nama_pegawai ?? '-'; // Tambahkan default
$opt_tanggal = $opt_tanggal ?? [];

// KOREKSI PATH FINAL LOGO
// Menghapus trailing slash dari base_url dan leading slash dari path logo, lalu menggabungkannya.
$base_url_clean = rtrim($base_url, '/'); 
$path_logo = $base_url_clean . '/' . ltrim(esc($path_logo_instansi_db), '/'); // Path logo juga di-esc
// --- END ASUMSI ---
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cetak Laporan Sidang | <?= esc($nama_instansi_app) ?></title> 
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;800&display=swap" rel="stylesheet">

    <style>
        /* ==================================================================== */
        /* --- STYLE GLOBAL & BASE (CORPORATE/ENTERPRISE DARK) --- */
        /* ==================================================================== */
        :root {
            --bg-color: #212121; /* Dark Base */
            --card-color: #2D2D2D; /* Card Darker */
            --primary-accent: #1976D2; /* Biru Cobalt */
            --secondary-accent: #FFC107; /* Gold/Kuning (Aksi Utama) */
            --text-color: #e0e0e0;
            --border-color: #444;
            --filter-color: #383838; /* Filter Box (Slightly lighter than card) */
            --header-color-start: #1976D2; /* Biru Cobalt Start */
            --header-color-end: #42A5F5; /* Biru Lebih Terang End */
        }

        body { 
            background-color: var(--bg-color); 
            color: var(--text-color); 
            font-family: 'Inter', sans-serif; 
            height: 100vh; 
            display: flex; 
            flex-direction: column; 
            overflow-y: auto; 
            padding-bottom: 20px; 
        }
        .navbar { 
            background-color: var(--card-color); 
            border-bottom: 3px solid var(--primary-accent); 
            padding: 10px 0; 
        }
        .navbar-brand { 
            font-weight: 800; 
            color: var(--primary-accent) !important; 
            font-size: 1.3rem; 
            display: flex; 
            align-items: center;
        }
        .navbar-logo {
            height: 32px; 
            margin-right: 10px;
        }
        
        /* Card Utama: Border radius kecil */
        .main-container { flex: 1; display: flex; justify-content: center; align-items: flex-start; padding: 20px; }
        .card-custom { 
            background-color: var(--card-color); 
            border: 1px solid var(--border-color); 
            border-radius: 8px; 
            width: 100%; 
            max-width: 1100px; 
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.4); 
        }
        .card-header-custom { 
            /* HEADER KINI BIRU COBALT GLOSSY */
            background: linear-gradient(to right, var(--header-color-start), var(--header-color-end)); 
            padding: 18px 25px; 
            display: flex; justify-content: space-between; align-items: center; 
            border-radius: 8px 8px 0 0; 
            position: relative;
            overflow: hidden;
        }
        /* Efek Glossy pada Header */
        .card-header-custom::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 50%;
            background: linear-gradient(to bottom, rgba(255, 255, 255, 0.2), transparent);
            z-index: 1;
        }
        .card-header-custom h5, .card-header-custom .badge { color: #fff; position: relative; z-index: 2; }
        .badge.bg-black { background-color: rgba(0,0,0,0.3) !important; color: #fff !important; }

        /* Filter Box */
        .filter-box { 
            background: #252525; 
            border: 1px solid var(--border-color);
            padding: 20px; 
            margin: 15px;
            border-radius: 6px; 
            box-shadow: inset 0 0 5px rgba(0, 0, 0, 0.3);
        }

        /* --- KOREKSI ALIGNMENT FILTER INPUT START --- */
        .filter-box .row {
            /* KUNCI: Gunakan flex-end untuk menyamakan dasar input/checkbox/tombol */
            align-items: flex-end !important; 
        }
        .filter-box .col-md-4 {
            /* Menggunakan flex-column untuk menyusun Label dan Input/Group */
            display: flex;
            flex-direction: column;
            gap: 5px; 
            padding-top: 5px;
            padding-bottom: 5px;
        }
        .filter-box .form-select {
            margin-bottom: 0 !important;
        }
        .filter-box .custom-check-group { 
            /* Pastikan group mengisi ruang dan kontennya rata tengah */
            flex-grow: 1;
            display: flex;
            align-items: center;
        }
        /* --- KOREKSI ALIGNMENT FILTER INPUT END --- */

        /* Form & Checkbox */
        .form-label { font-size: 0.85rem; color: #ccc; margin-bottom: 0px; /* Hapus margin bawah */ font-weight: 500; }
        .form-select { 
            /* Z-INDEX FIX */
            position: relative;
            z-index: 1000;
            background-color: #333; 
            border: 1px solid #444; 
            color: #fff; 
            font-size: 0.9rem; 
            border-radius: 4px; 
        }
        .form-select:focus { 
            background-color: #444; 
            box-shadow: 0 0 0 3px rgba(25, 118, 210, 0.4); 
            border-color: var(--primary-accent); 
        }
        .custom-check-group { 
            background: #333; 
            border: 1px solid #444; 
            padding: 8px 15px; 
            border-radius: 4px; 
            height: auto; 
        }
        .form-check-input:checked { 
            background-color: var(--primary-accent); 
            border-color: var(--primary-accent);
        }
        .form-check-label { color: #ccc; }


        /* Style Tombol Aksi */
        .btn-cetak { 
            /* Aksi Utama: Gold + Glossy */
            background: linear-gradient(180deg, #FFC107, #FF9800); 
            border: 2px solid var(--secondary-accent); 
            color: #000; 
            font-weight: 700; 
            padding: 10px 30px; 
            font-size: 1rem; 
            border-radius: 4px; 
            transition: all 0.3s;
            position: relative;
            overflow: hidden;
            z-index: 0;
        }
        .btn-cetak::before {
            content: '';
            position: absolute;
            top: 0; left: 0; width: 100%; height: 100%;
            background: linear-gradient(to bottom, rgba(255, 255, 255, 0.4), transparent); 
            opacity: 0.8; transition: opacity 0.3s; pointer-events: none; z-index: 1;
        }
        .btn-cetak:hover { 
            transform: translateY(-2px); 
            box-shadow: 0 6px 15px rgba(255, 152, 0, 0.5); 
            color: #000; 
        }
        .btn-cetak:hover::before { opacity: 0.5; }

        .btn-success-custom { 
            /* Aksi Sekunder: Biru + Glossy (Sesuai Primary Accent) */
            background: linear-gradient(180deg, #2196F3, var(--primary-accent)); 
            border: 2px solid var(--primary-accent); 
            color: #fff; 
            font-weight: 700; 
            padding: 10px 20px; 
            border-radius: 4px;
            font-size: 0.9rem; 
            position: relative;
            overflow: hidden;
            z-index: 0;
        }
        .btn-success-custom::before {
            content: '';
            position: absolute;
            top: 0; left: 0; width: 100%; height: 100%;
            background: linear-gradient(to bottom, rgba(255, 255, 255, 0.3), transparent); 
            opacity: 0.8; transition: opacity 0.3s; pointer-events: none; z-index: 1;
        }
        .btn-success-custom:hover { 
            transform: translateY(-2px); 
            box-shadow: 0 6px 15px rgba(25, 118, 210, 0.5); 
            color: #fff; 
        }
        .btn-success-custom:hover::before { opacity: 0.5; }

        .btn-sync { 
            background: rgba(255,255,255,0.1); 
            border: 1px solid rgba(255,255,255,0.2); 
            color: var(--text-color);
            padding: 8px 15px;
            border-radius: 4px;
            transition: all 0.3s;
        }
        .btn-sync:hover { background: rgba(255,255,255,0.2); color: var(--text-color); }
        .fa-spin-hover:hover { animation: fa-spin 2s linear infinite; }
        
        /* DataTables Styling */
        .table-responsive { padding: 0 20px 20px 20px; } 
        table.dataTable { margin-top: 10px !important; }
        .table-dark thead th { 
            background-color: #383838; 
            color: var(--primary-accent); 
            font-size: 0.9rem; 
            border-bottom: 2px solid var(--primary-accent); 
        }
        .table-dark tbody td { background-color: var(--card-color); }
        .table-dark tbody tr:hover td { background-color: #333; color: var(--text-color); }
        
        /* FIX PENCARIAN/SEARCH BAR ***************************************/
        div.dataTables_wrapper div.dataTables_filter label {
            display: flex; 
            align-items: center;
            gap: 10px; 
            color: var(--text-color);
            white-space: nowrap; 
        }
        div.dataTables_wrapper div.dataTables_filter input {
            background-color: #444; 
            border: 1px solid #555;
            color: var(--text-color);
            border-radius: 4px;
            padding: 5px 10px;
            width: 200px; 
        }
        /* END FIX PENCARIAN/SEARCH BAR ***********************************/

        /* Fix DataTables Layout */
        div.dataTables_wrapper div.row { margin-left: 0; margin-right: 0; margin-top: 15px; }
        div.dataTables_wrapper div.col-md-6 { padding-left: 0; padding-right: 0; }

        /* ==================================================================== */
        /* --- MEDIA QUERY (RESPONSIVE OPTIMIZATION & TIMELINE FIX) --- */
        /* ==================================================================== */
        @media (max-width: 767.98px) {
            .main-container { padding: 0; } 
            .card-custom { border-radius: 0; border-left: none; border-right: none; }
            .card-header-custom {
                border-radius: 0; padding: 15px 15px; flex-direction: column; align-items: flex-start;
            }
            .card-header-custom::before { content: none; }
            
            .btn-sync { width: 100%; text-align: center; padding: 10px; }
            
            .filter-box { margin: 10px 0px; padding: 15px; border-radius: 0; }
            
            .filter-box .row > div { margin-bottom: 15px; }
            .col-md-4.text-end { text-align: left !important; } 
            .custom-check-group { flex-wrap: wrap; height: auto; }
            
            .btn-cetak { width: 100%; }
            .btn-cetak::before { content: none; } 
            .btn-success-custom { width: 100%; }
            .btn-success-custom::before { content: none; } 
            
            .table-responsive { padding: 0 5px 15px 5px; }
            
            /* DataTables Mobile Layout fix */
            div.dataTables_wrapper div.dataTables_filter input { 
                width: 100%; 
                margin-top: 5px; 
            }
            div.dataTables_wrapper div.dataTables_filter label {
                 flex-direction: column;
                 align-items: flex-start;
                 gap: 5px;
            }
            
            /* --- TIMELINE/CARD VIEW STYLING --- */
            
            /* Sembunyikan header tabel */
            #tableSidang thead { display: none; }
            
            /* Paksa baris tabel menjadi blok */
            #tableSidang tbody tr {
                display: block;
                margin-bottom: 15px;
                border: 1px solid var(--border-color);
                border-radius: 6px;
                background-color: var(--card-color);
            }

            /* Paksa sel tabel menjadi blok dan berikan padding */
            #tableSidang tbody td {
                display: block;
                text-align: right; /* Align value to the right */
                padding: 8px 15px;
                border-bottom: 1px solid #282828;
                position: relative;
            }

            /* Label Timeline (Nomor Perkara, JPU, dll.) */
            #tableSidang tbody td::before {
                content: attr(data-label); /* Menggunakan data-label untuk nama kolom */
                float: left;
                font-weight: bold;
                color: var(--primary-accent); /* Aksen Biru Cobalt */
            }

            /* Hapus border bawah pada sel terakhir */
            #tableSidang tbody tr:last-child td { border-bottom: none; }
            
            /* Styling Aksi (Tombol) di Timeline */
            #tableSidang tbody td:nth-child(5) { 
                text-align: center; /* Tombol aksi di tengah */
                padding: 10px;
            }
            
            /* Terdakwa/Kolom Utama selalu di atas */
            #tableSidang tbody td:nth-child(2) { 
                font-size: 1.1em; 
                font-weight: bold; 
                border-top: 3px solid var(--primary-accent); 
            }
        }

        /* --- DROPDOWN OFFSET FIX --- */
.form-select { 
    position: relative;
    z-index: 1000; 
}
.select2-container, .form-select-dropdown, .dataTables_wrapper select {
    z-index: 10000 !important;
}
    </style>
</head>
<body>
<nav class="navbar navbar-expand-lg">
    <div class="container">
        <a class="navbar-brand" href="#">
            <img src="<?= esc($path_logo) ?>" 
                 onerror="this.onerror=null;this.src='<?= $base_url_clean . '/images/default_logo.png' ?>';" 
                 alt="Logo Instansi" class="navbar-logo">
            <span><?= esc($nama_instansi_app) ?></span>
        </a>
        
        <div class="ms-auto d-flex align-items-center"> 
            
            <a href="<?= $base_url . '/'; ?>" class="btn btn-outline-light btn-sm me-2">
                 <i class="fa-solid fa-home me-1"></i> Kembali ke Dashboard
            </a>
            
            <a href="<?= site_url('sidang/logout'); ?>" class="btn btn-danger btn-sm" 
               onclick="return confirm('Anda yakin ingin keluar dan mengakhiri ?');">
                 <i class="fa-solid fa-right-from-bracket me-1"></i> Logout
            </a>
        </div>
    </div>
</nav>

    <div class="main-container">
    <div class="main-container">
        <div class="card-custom">
            
            <div class="card-header-custom">
                <div class="d-flex align-items-center">
                    <i class="fa-solid fa-building-columns me-3" style="font-size: 1.5rem; color:#fff;"></i>
                    <div>
                        <h5 class="m-0 fw-bold text-white">CETAK BERKAS SIDANG</h5>
                        <small class="text-white" style="font-size: 0.85rem;">Nama Pegawai: <?= esc($nama_pegawai ?? '-') ?> | User NIP: <?= esc($nip_user ?? '-') ?></small>
                    </div>
                </div>
                <a href="<?= $base_url . '/sidang/sync' ?>" class="btn-sync" onclick="return confirm('Proses ini akan mengambil data terbaru dari Google Sheet dan menyimpannya ke Database. Lanjutkan?');">
                    <i class="fa-solid fa-arrows-rotate me-1 fa-spin-hover"></i> SINKRONISASI DATA
                </a>
            </div>

            <form action="<?= $base_url . '/sidang/proses' ?>" method="post" id="formCetak">
                
                <input type="hidden" name="mode_cetak" id="inputModeCetak" value="seleksi">
                <input type="hidden" name="tanggal_terpilih" id="inputTanggalHidden" value="">

                <div class="filter-box">
                    <div class="row g-3 align-items-end"> 
                        <div class="col-md-4">
                            <label class="form-label"><i class="fa-solid fa-calendar-day me-2"></i> 1. Pilih Tanggal Sidang</label>
                            <select id="dateFilter" class="form-select">
                                <?php $hasDates = !empty($opt_tanggal); ?>
                                <option value="" selected disabled>
                                    <?= (!$hasDates) ? '-- TIDAK ADA DATA SIDANG --' : '-- PILIH TANGGAL --' ?>
                                </option>
                                <?php if ($hasDates): ?>
                                    <?php foreach($opt_tanggal as $tgl): ?>
                                        <option value="<?= esc($tgl) ?>">
                                            <?= esc($tgl) ?>
                                        </option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label"><i class="fa-solid fa-file-contract me-2"></i> 2. Jenis Dokumen</label>
                            <div class="custom-check-group">
                                <div class="form-check me-3">
                                    <input class="form-check-input" type="checkbox" name="jenis_dokumen[]" value="p37" id="chkP37">
                                    <label class="form-check-label" for="chkP37">Form P-37</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="jenis_dokumen[]" value="p38" id="chkP38">
                                    <label class="form-check-label" for="chkP38">Form P-38</label>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-4 text-end">
                            <div class="d-flex flex-column gap-2"> 
                                <button type="button" id="btnFullP38" class="btn btn-success-custom w-100" style="display:none;">
                                    <i class="fa-solid fa-print me-2"></i> CETAK SEMUA P-38
                                </button>
                                <button type="submit" id="btnProses" class="btn btn-cetak w-100">
                                    <i class="fa-solid fa-file-word me-2"></i> PROSES SELEKSI
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="table-responsive mt-3">
                    
                    <?php if (session()->getFlashdata('error')) : ?>
                        <div class="alert alert-danger py-2 small mx-3 mt-3"><i class="fa-solid fa-circle-exclamation me-2"></i> <?= esc(session()->getFlashdata('error')); ?></div>
                    <?php endif; ?>

                    <?php if (session()->getFlashdata('success')) : ?>
                        <div class="alert alert-success py-2 small mx-3 mt-3"><i class="fa-solid fa-check-circle me-2"></i> <?= esc(session()->getFlashdata('success')); ?></div>
                    <?php endif; ?>

                    <table id="tableSidang" class="table table-dark table-hover align-middle w-100">
                        <thead>
                            <tr>
                                <th data-label="Pilih"> <input type="checkbox" id="checkAll"> </th> 
                                <th data-label="Terdakwa">Nama Terdakwa</th>
                                <th data-label="No. Perkara">Nomor Perkara</th>
                                <th data-label="JPU">Jaksa Penuntut umum</th>
                                <th data-label="Aksi" class="text-center">Aksi</th>
                                <th class="d-none">Tanggal</th>
                            </tr>
                        </thead>        
                        <tbody id="dataTableBody">
                            <tr><td colspan="5" class="text-center">Silakan pilih tanggal sidang di atas untuk memuat data.</td></tr>
                        </tbody>
                    </table>
                </div>
                </form>
        </div>
    </div>
<div class="modal fade" id="modalConfigCetak" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="background-color: #2D2D2D; color: #fff; border: 1px solid #444;">
            <div class="modal-header" style="border-bottom: 1px solid #444;">
                <h5 class="modal-title"><i class="fa-solid fa-gear me-2 text-warning"></i>Konfigurasi Data Cetak</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p class="small text-muted mb-3">Data ini akan tersimpan otomatis di browser (LocalStorage) agar tidak perlu input ulang.</p>
                
                <h6 class="text-primary border-bottom border-secondary pb-1 mb-2">1. Info Surat</h6>
                <div class="row g-2 mb-3">
                    <div class="col-md-6">
                        <label class="form-label text-warning" style="font-size:0.8rem;">Nama Instansi (Kop)</label>
                        <input type="text" class="form-control form-control-sm bg-dark text-white border-secondary" id="cfgInstansi" placeholder="KEJAKSAAN NEGERI...">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label text-warning" style="font-size:0.8rem;">Kota (Tempat TTD)</label>
                        <input type="text" class="form-control form-control-sm bg-dark text-white border-secondary" id="cfgKota" placeholder="Contoh: Boyolali">
                    </div>
                </div>

                <h6 class="text-success border-bottom border-secondary pb-1 mb-2">2. Pejabat Penandatangan</h6>
                <div class="alert alert-dark border-secondary p-2 mb-2" style="font-size: 0.75rem;">
                    <i class="fa-solid fa-circle-info me-1"></i> Jika dikosongkan, sistem akan menggunakan data JPU dari database & NIP Login Anda.
                </div>
                
                <div class="mb-2">
                    <label class="form-label" style="font-size:0.8rem;">Jabatan (Struktural)</label>
                    <input type="text" class="form-control form-control-sm bg-dark text-white border-secondary" id="cfgJabatan" placeholder="Contoh: KEPALA SEKSI TINDAK PIDANA UMUM">
                </div>

                <div class="row g-2">
                    <div class="col-md-6">
                        <label class="form-label" style="font-size:0.8rem;">Nama Lengkap Pejabat</label>
                        <input type="text" class="form-control form-control-sm bg-dark text-white border-secondary" id="cfgNamaPejabat" placeholder="Nama Penandatangan...">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" style="font-size:0.8rem;">NIP / NRP</label>
                        <input type="text" class="form-control form-control-sm bg-dark text-white border-secondary" id="cfgNipPejabat" placeholder="NIP Penandatangan...">
                    </div>
                </div>
            </div>
            <div class="modal-footer" style="border-top: 1px solid #444;">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-primary" id="btnFinalCetak">
                    <i class="fa-solid fa-print me-2"></i> LANJUTKAN CETAK
                </button>
            </div>
        </div>
    </div>
</div>
    <script src="https://code.jquery.com/jquery-3.7.0.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

    <script>
// --- VARIABEL GLOBAL & HELPER FUNCTIONS ---
var table;
var baseUrlApp = '<?= $base_url ?>'; 
var apiUrlPath = 'sidang/api/data'; 
var isDataTableInitialized = false;
var dateFilter = $('#dateFilter');

// ✅ FUNGSI GLOBAL 1: Mengatur status tombol 'Proses Cetak'
function updateBtnState() {
    var totalChecked = table ? table.rows().nodes().to$().find('.row-checkbox:checked').length : 0;
    
    var isP37 = $('#chkP37').is(':checked');
    var isP38 = $('#chkP38').is(':checked');
    var btn = $('#btnProses');
    
    var text = 'PROSES SELEKSI';
    var isDisabled = true;
    
    // LOGIC BARU:
    // 1. Jika P38 dipilih -> Tombol SELALU AKTIF (karena cetak semua)
    if (isP38) {
        // Kalau P38 dipilih, tombol selalu AKTIF, tidak peduli checklist tabel
        isDisabled = false;
        text = 'PROSES CETAK SEMUA (P-38)';
        if(isP37) text += ' & P-37';
    } 
    
    // 2. Jika P37 dipilih -> Harus ada checklist
    else if (totalChecked > 0) {
        if(isP37) {
            isDisabled = false;
            text = 'CETAK (' + totalChecked + ') P-37';
        } else {
            text = 'Pilih Jenis Dokumen';
            isDisabled = true;
        }
    } else {
        text = 'Pilih Data / Dokumen';
        isDisabled = true;
    }

    btn.prop('disabled', isDisabled);
    btn.html('<i class="fa-solid fa-file-word me-2"></i> ' + text);
}


// --- Fungsi untuk memuat data dari API ---
function loadData(selectedDate) {
    
    var dbFormatDate = '';
    if (selectedDate) {
        var parts = selectedDate.split('-');
        if (parts.length === 3) {
        dbFormatDate = selectedDate;                
        }
    }
    
    var cleanBaseUrl = baseUrlApp.replace(/\/+$/, '');
    var apiUrl = cleanBaseUrl + '/' + apiUrlPath + (dbFormatDate ? '?tanggal=' + dbFormatDate : '');
    
    $('#dataTableBody').html('<tr><td colspan="5" class="text-center"><i class="fa fa-spinner fa-spin me-2"></i> Memuat data...</td></tr>');
    
    if (isDataTableInitialized && $.fn.DataTable.isDataTable('#tableSidang')) {
        table.destroy();
        isDataTableInitialized = false;
    }

    fetch(apiUrl)
        .then(response => {
            if (!response.ok) {
                if (response.status === 404) { throw new Error("Rute API tidak ditemukan."); }
                if (response.status === 403) { throw new Error("Sesi login sidang Anda berakhir."); }
                throw new Error(`Gagal memuat data: ${response.status}`);
            }
            return response.json();
        })
        .then(result => {
            let dataArray = [];

            if (result.status === 200 && result.data.length > 0) {
                result.data.forEach(row => {
                    dataArray.push([
                        '<input class="form-check-input row-checkbox" type="checkbox" name="pilih_data[]" value="' + row.nomor_perkara + '">',
                        '<span class="fw-bold text-white">' + row.nama_bersih + '</span>',                                
                         row.nomor_perkara,
                        row.jpu,
                        // Ikon aksi
                        '<button type="button" class="btn btn-sm btn-outline-info view-details" data-nomor="'+row.nomor_perkara+'" title="Lihat Detail"><i class="fa-solid fa-magnifying-glass-chart"></i></button>',
                        row.tanggal_sidang 
                    ]);
                });
            }

            table = $('#tableSidang').DataTable({
                "data": dataArray, 
                "pageLength": 10, "lengthChange": false, "ordering": false, "destroy": true, "searching": true,
                "responsive": true, 
                "dom": '<"row"<"col-md-6"l><"col-md-6 text-end"f>>' + 'rt' + '<"row"<"col-md-6"i><"col-md-6"p>>',
                "language": {
                    "search": "Cari:", "info": "Total _TOTAL_ Data", "emptyTable": "Tidak ada data sidang untuk tanggal ini.", "zeroRecords": "Data tidak ditemukan."
                },
                "columnDefs": [
                    { "targets": 0, "className": "text-center" }, 
                    { "targets": 4, "className": "text-center" }, 
                    { "targets": 5, "visible": false }
                ]
            });
            
            isDataTableInitialized = true;
            updateBtnState();
            
            $('#inputTanggalHidden').val(selectedDate);
            if(selectedDate) $('#btnFullP38').fadeIn(); else $('#btnFullP38').hide();

            // Pasang listener di tabel baru untuk update state
            $('#tableSidang tbody').on('change', '.row-checkbox', updateBtnState);


        })
        .catch(error => {
            let errorMessage = error.message;
            
            table = $('#tableSidang').DataTable({
                "data": [], "pageLength": 10, "lengthChange": false, "ordering": false, "destroy": true, "searching": true,
                "responsive": true,
                "dom": '<"row"<"col-md-6"l><"col-md-6 text-end"f>>' + 'rt' + '<"row"<"col-md-6"i><"col-md-6"p>>',
                "language": {
                    "search": "Cari:", "info": "Total _TOTAL_ Data", "emptyTable": errorMessage, "zeroRecords": "Data tidak ditemukan."
                },
                "columnDefs": [
                    { "targets": 0, "className": "text-center" }, 
                    { "targets": 4, "className": "text-center" }, 
                    { "targets": 5, "visible": false }
                ]
            });
            isDataTableInitialized = true;
            updateBtnState();
        });
}


// --- Fungsi Utama pada Load Halaman ---
$(document).ready(function() {
    
    // Inisiasi data kosong awal
    table = $('#tableSidang').DataTable({
        "data": [], "pageLength": 10, "lengthChange": false, "ordering": false, "destroy": true, "searching": true,
        "responsive": true,
        "dom": '<"row"<"col-md-6"l><"col-md-6 text-end"f>>' + 'rt' + '<"row"<"col-md-6"i><"col-md-6"p>>',
        "language": {
            "search": "Cari:", "info": "Total _TOTAL_ Data", "emptyTable": "Silakan pilih tanggal sidang di atas untuk memuat data.", "zeroRecords": "Data tidak ditemukan."
        },
        "columnDefs": [
            { "targets": 0, "className": "text-center" }, 
            { "targets": 4, "className": "text-center" }, 
            { "targets": 5, "visible": false }
        ]
    });
    isDataTableInitialized = true;

    // 2. SETUP MODAL (INISIALISASI DI AWAL BIAR GAK ERROR SAAT CLOSE)
    // Kita simpan instance modal di variabel global biar bisa dipanggil dimanapun
    var elModal = document.getElementById('modalConfigCetak');
    var myAppModal = new bootstrap.Modal(elModal, {
        backdrop: 'static',
        keyboard: false
    });

    var formToSubmit = null; // Variabel simpan form

    // --- EVENT LISTENER Filter & Checkbox ---
    
    // Tanggal berubah
    $('#dateFilter').on('change', function() {
        var val = $(this).val(); 
        loadData(val);
        $('#checkAll').prop('checked', false);
        updateBtnState();
    });

    // Dokumen berubah
    $('#chkP37, #chkP38').on('change', updateBtnState);

    // Tabel checklist berubah -> Cek tombol
    $('#tableSidang tbody').on('change', '.row-checkbox', updateBtnState);
    $('#checkAll').on('click', function() { /* ... logic check all ... */ updateBtnState(); });

    // Check All
    $('#checkAll').on('click', function() {
        var isChecked = this.checked;
        table.rows().nodes().to$().find('.row-checkbox').prop('checked', isChecked);
        updateBtnState();
    });
    
    // 1. KLIK TOMBOL CETAK FULL (HIJAU)
    $('#btnFullP38').on('click', function() {
        var tgl = $('#inputTanggalHidden').val();
        if(!tgl) { alert("Pilih tanggal sidang dulu!"); return; }

            $('#inputModeCetak').val('full_p38');
            $('#formCetak').submit();
    });

    // 2. INTERCEPT SUBMIT FORM (Untuk Validasi & Tampil Modal)
    $('#formCetak').on('submit', function(e){
        e.preventDefault();
        if ($('#inputModeCetak').val() === 'full_p38') return true; 

        var mode = $('#inputModeCetak').val();
        var countChecked = table.rows().nodes().to$().find('.row-checkbox:checked').length;
        var isP38 = $('#chkP38').is(':checked');

        // Validasi Manual (Jika bukan Full P38 dan bukan P38 Checklist)
        
        if (mode !== 'full_p38') {
             if (!isP38 && countChecked === 0){ 
                alert("Pilih minimal satu data untuk dicetak!"); 
                return false; 
            }
            if(!$('#chkP37').is(':checked') && !isP38) { 
                alert("Pilih minimal satu jenis dokumen (P-37 atau P-38)!"); 
                return false; 
            }
        }

        // Simpan Form ke variabel global
        formToSubmit = this;

// 🔥 KOREKSI UTAMA DISINI: 
        // Panggil .show() pada variabel global 'myAppModal' yg sudah dibuat di atas.
        // JANGAN buat 'new bootstrap.Modal' lagi disini!
        myAppModal.show();
        });

        // 3. TOMBOL "LANJUTKAN CETAK" DI DALAM MODAL
    $('#btnFinalCetak').on('click', function() {
        // A. Ambil Data dari Input Modal
        var vInstansi = $('#cfgInstansi').val();
        var vKota = $('#cfgKota').val();
        var vJabatan = $('#cfgJabatan').val();
        var vNama = $('#cfgNamaPejabat').val();
        var vNip = $('#cfgNipPejabat').val();

        // B. Masukkan Input Modal ke Hidden Input Form
        $(formToSubmit).find('.extra-data').remove(); // Bersihkan sisa lama
        
        var inputs = [
            { name: 'custom_instansi', val: vInstansi },
            { name: 'custom_kota', val: vKota },
            { name: 'ttd_jabatan', val: vJabatan },
            { name: 'ttd_nama', val: vNama },
            { name: 'ttd_nip', val: vNip }
        ];

        inputs.forEach(item => {
            $('<input>').attr({type: 'hidden', name: item.name, value: item.val, class: 'extra-data'}).appendTo(formToSubmit);
        });

        // C. Handle Data Checklist (Pilih Data)
        // Ambil ID dari checklist walaupun di pagination berbeda
        var selectedValues = [];
        if (table) {
             table.rows().nodes().to$().find('.row-checkbox:checked').each(function(){
                selectedValues.push($(this).val());
            });
        }
        // 🔥 PERBAIKAN DISINI: CUMA HAPUS INPUT HIDDEN, JANGAN HAPUS CHECKBOX!
        // Kode Lama: $('input[name="pilih_data[]"]').remove();  <-- INI SALAH (Checkbox ikut kehapus)
        // Kode Baru:
        $(formToSubmit).find('input[type="hidden"][name="pilih_data[]"]').remove();// Hapus input lama
        if (selectedValues.length > 0) {
            selectedValues.forEach(function(val) {
                $(formToSubmit).append($('<input>').attr('type', 'hidden').attr('name', 'pilih_data[]').val(val));
            });
        }

        myAppModal.hide();
                // Kosongkan form modal agar bersih saat dibuka lagi
        $('#cfgInstansi').val('');
        $('#cfgKota').val('');
        $('#cfgJabatan').val('');
        $('#cfgNamaPejabat').val('');
        $('#cfgNipPejabat').val('');
        setTimeout(() => {
            formToSubmit.submit();
        }, 300);
        

    });
    
    updateBtnState();
});
    </script>
</body>
</html>