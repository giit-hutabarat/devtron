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
    <link rel="stylesheet" href="<?= base_url('/assets/css/sidang-style.css') ?>">

</head>
<body>
<nav class="navbar navbar-expand-lg">
    <div class="container">
        <a class="navbar-brand" href="#">
            <img src="<?= esc($path_logo) ?>" 
                 onerror="this.onerror=null;this.src='<?= base_url('/images/default_logo.png') ?>';" 
                 alt="Logo Instansi" class="navbar-logo">
            <span><?= esc($nama_instansi_app) ?></span>
        </a>
        
        <div class="ms-auto d-flex align-items-center"> 
            
            <a href="<?= base_url('/') ?>" class="btn btn-outline-light btn-sm me-2">
                 <i class="fa-solid fa-home me-1"></i> Kembali ke Dashboard
            </a>
            
            <a href="<?= base_url('sidang/logout') ?>" class="btn btn-danger btn-sm" 
               onclick="return confirm('Anda yakin ingin keluar dan mengakhiri ?');">
                 <i class="fa-solid fa-right-from-bracket me-1"></i> Logout
            </a>
        </div>
    </div>
</nav>

    <div class="main-container">
        <div class="card-custom">
            
            <div class="card-header-custom">
                <div class="d-flex align-items-center">
                    <i class="fa-solid fa-building-columns me-3" style="font-size: 1.5rem; color:#fff;"></i>
                    <div>
                        <h5 class="m-0 fw-bold text-white">CETAK BERKAS SIDANG</h5>
                        <small class="text-white" style="font-size: 0.85rem;">Nama Pegawai: <?= esc($nama_pegawai) ?> | User NIP: <?= esc($nip_user) ?></small>
                    </div>
                </div>
                <a href="<?= base_url('/sidang/sync') ?>" id="btnSyncData" 
                class="btn btn-outline-light btn-sm fw-bold px-3 py-2">
                <i class="fa-solid fa-cloud-arrow-down me-2"></i> SINKRONISASI DATA
                </a>
            </div>

            <form action="<?= base_url('/sidang/proses') ?>" method="post" id="formCetak">
                <?= csrf_field() ?>
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

    <div class="modal fade modal-modern" id="modalConfigCetak" tabindex="-1" data-bs-backdrop="static">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fa-solid fa-sliders me-2"></i> Konfigurasi Cetak
                    </h5>
                    <button type="button" class="btn-close btn-close-white opacity-50" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                
                <div class="modal-body p-4">
                    
                    <div class="modal-section-label">
                        <i class="fa-solid fa-building-flag"></i> 1. Kop & Lokasi
                    </div>
                    
                    <div class="mb-3" id="blockNomorSurat" style="display:none;">
                        <label class="form-label-modern">Nomor Surat</label>
                        <div class="input-group">
                            <span class="input-group-text input-group-text-modern"><i class="fa-solid fa-envelope-open-text"></i></span>
                            <input type="text" class="form-control form-control-modern" id="cfgNomorSurat" placeholder="Contoh: B-123/M.3.29/Es.2/12/2025">
                        </div>
                    </div>

                    <div class="row g-3 mb-4">
                        <div class="col-md-7">
                            <label class="form-label-modern">Nama Instansi</label>
                            <div class="input-group">
                                <span class="input-group-text input-group-text-modern"><i class="fa-solid fa-landmark"></i></span>
                                <input type="text" class="form-control form-control-modern" id="cfgInstansi" placeholder="KEJAKSAAN NEGERI...">
                            </div>
                        </div>
                        <div class="col-md-5">
                            <label class="form-label-modern">Kota (Tempat TTD)</label>
                            <div class="input-group">
                                <span class="input-group-text input-group-text-modern"><i class="fa-solid fa-map-location-dot"></i></span>
                                <input type="text" class="form-control form-control-modern" id="cfgKota" placeholder="Contoh: Boyolali">
                            </div>
                        </div>
                    </div>

                    <div class="modal-section-label">
                        <i class="fa-solid fa-signature"></i> 2. Penandatangan
                    </div>

                    <div class="info-box-modern">
                        <i class="fa-solid fa-circle-info mt-1"></i>
                        <div>
                            <strong>Opsional:</strong> Jika bagian ini dikosongkan, sistem otomatis menggunakan data Jaksa (JPU) dari database & NIP Anda saat ini.
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label-modern">Jabatan Struktural</label>
                        <div class="input-group">
                            <span class="input-group-text input-group-text-modern"><i class="fa-solid fa-briefcase"></i></span>
                            <input type="text" class="form-control form-control-modern" id="cfgJabatan" placeholder="Contoh: KEPALA SEKSI TINDAK PIDANA UMUM">
                        </div>
                    </div>

                    <div class="row g-3">
                        <div class="col-md-7">
                            <label class="form-label-modern">Nama Lengkap Pejabat</label>
                            <div class="input-group">
                                <span class="input-group-text input-group-text-modern"><i class="fa-solid fa-user-tie"></i></span>
                                <input type="text" class="form-control form-control-modern" id="cfgNamaPejabat" placeholder="Nama lengkap...">
                            </div>
                        </div>
                        <div class="col-md-5">
                            <label class="form-label-modern">NIP / NRP</label>
                            <div class="input-group">
                                <span class="input-group-text input-group-text-modern"><i class="fa-solid fa-id-card"></i></span>
                                <input type="text" class="form-control form-control-modern" id="cfgNipPejabat" placeholder="199...">
                            </div>
                        </div>
                    </div>

                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-cancel-modern" data-bs-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-action-modern" id="btnFinalCetak">
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
        // --- VARIABEL GLOBAL DARI PHP ---
        var table;
        // Gunakan helper PHP langsung
        var baseUrlApp = '<?= base_url() ?>'; 
        var apiUrlPath = 'sidang/api/data';
    </script>

    <script src="<?= base_url('/assets/js/sidang-script.js') ?>"></script>
</body>
</html>