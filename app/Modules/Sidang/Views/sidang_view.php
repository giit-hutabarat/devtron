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
    <?= $this->include('art/preloader') ?>
<nav class="navbar navbar-expand-lg">
    <div class="container">
        <a class="navbar-brand" href="#">
            <img src="<?= esc($path_logo) ?>" 
                 onerror="this.onerror=null;this.src='<?= base_url('/images/default_logo.png') ?>';" 
                 alt="Logo Instansi" class="navbar-logo">
            <span><?= esc($nama_instansi_app) ?></span>
        </a>
        
        <div class="ms-auto d-flex align-items-center">
            <div class="glass-nav-group">
                <a href="<?= base_url('/') ?>" class="glass-btn" title="Dashboard">
                    <i class="fa-solid fa-house"></i>
                    <span class="d-none d-md-inline ms-2">Dashboard</span>
                </a>
                <div class="divider-vertical"></div>
                <a href="<?= base_url('sidang/logout') ?>" class="glass-btn text-danger-glow" onclick="return confirm('Keluar?');" title="Logout">
                    <i class="fa-solid fa-power-off"></i>
                </a>
            </div>
        </div>
    </div>
</nav>

    <div class="main-container">
        <div class="card-custom">
            
            <div class="card-header-custom">
                <div class="d-flex flex-column flex-md-row justify-content-between align-items-center w-100 gap-3">
                    
                    <div class="d-flex align-items-center w-100">
                        <i class="fa-solid fa-building-columns me-3 d-none d-sm-block" style="font-size: 1.5rem; color:#fff;"></i>
                        <div class="text-center text-md-start w-100">
                            <h5 class="m-0 fw-bold text-white text-uppercase" style="letter-spacing:1px;">CETAK BERKAS SIDANG</h5>
                            <small class="text-white opacity-75 d-block mt-1">
                                <i class="fa-solid fa-user me-1"></i> <?= esc($nama_pegawai) ?> 
                                <span class="d-none d-sm-inline">| NIP: <?= esc($nip_user) ?></span>
                            </small>
                        </div>
                    </div>

                    <div class="w-100 w-md-auto text-center text-md-end">
                        <a href="javascript:void(0)" id="btnSyncData"
                           class="btn btn-light btn-sm fw-bold text-primary shadow-sm w-100 w-md-auto py-2">
                            <i class="fa-solid fa-cloud-arrow-down me-2"></i> SINKRONISASI
                        </a>
                    </div>
                </div>
            </div>

            <form action="<?= base_url('/sidang/proses') ?>" method="post" id="formCetak">
                <?= csrf_field() ?>
                <input type="hidden" name="mode_cetak" id="inputModeCetak" value="seleksi">
                <input type="hidden" name="tanggal_terpilih" id="inputTanggalHidden" value="">

                <div class="filter-box">
                    <div class="row g-3"> 
                        <div class="col-12 col-md-4">
                            <label class="form-label text-white fw-bold mb-2">
                                <i class="fa-solid fa-calendar-day me-2"></i> 1. Pilih Tanggal Sidang
                            </label>
                            
                            <div class="d-flex gap-2 mb-2">
                                <button type="button" class="btn btn-sm btn-outline-light flex-fill btn-quick-date" data-target="today">
                                    <i class="fa-solid fa-calendar-check me-1"></i> Hari Ini
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-light flex-fill btn-quick-date" data-target="tomorrow">
                                    <i class="fa-solid fa-calendar-plus me-1"></i> Besok
                                </button>
                            </div>

                            <div class="input-group shadow-sm">
                                <span class="input-group-text bg-white border-0 text-primary">
                                    <i class="fa-solid fa-calendar"></i>
                                </span>
                                <input type="date" id="dateFilter" class="form-control fw-bold text-center border-0" 
                                    value="<?= date('Y-m-d') ?>" 
                                    style="font-size: 1.05rem; letter-spacing: 0.5px; height: 45px;">
                            </div>
                        </div>

                        <div class="col-12 col-md-4">
                            <label class="form-label"><i class="fa-solid fa-file-contract me-2"></i> 2. Jenis Dokumen</label>
                            <div class="custom-check-group d-flex justify-content-around align-items-center">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="jenis_dokumen[]" value="p37" id="chkP37">
                                    <label class="form-check-label" for="chkP37">Form P-37</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="jenis_dokumen[]" value="p38" id="chkP38">
                                    <label class="form-check-label" for="chkP38">Form P-38</label>
                                </div>
                            </div>
                        </div>

                        <div class="col-12 col-md-4 d-flex align-items-end">
                            <button type="submit" id="btnProses" class="btn btn-cetak w-100 shadow-sm">
                                <i class="fa-solid fa-file-word me-2"></i> PROSES SELEKSI
                            </button>
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
                    
                    <div id="alertModal" class="alert alert-danger py-2 small d-none">
                        <i class="fa-solid fa-circle-exclamation me-2"></i> Harap lengkapi semua kolom wajib!
                    </div>

                    <div class="modal-section-label">
                        <i class="fa-solid fa-building-flag"></i> 1. Kop & Lokasi
                    </div>
                    
                    <div class="mb-3" id="blockNomorSurat" style="display:none;">
                        <label class="form-label-modern required-field">Nomor Surat</label>
                        <div class="input-group">
                            <span class="input-group-text input-group-text-modern"><i class="fa-solid fa-envelope-open-text"></i></span>
                            <input type="text" class="form-control form-control-modern validate-input" id="cfgNomorSurat" placeholder="Contoh: B-123/M.3.29/Es.2/12/2025">
                        </div>
                        <div class="invalid-feedback-custom">Nomor surat wajib diisi untuk P-38.</div>
                    </div>

                    <div class="row g-3 mb-4">
                        <div class="col-md-7">
                            <label class="form-label-modern required-field">Nama Instansi</label>
                            <div class="input-group">
                                <span class="input-group-text input-group-text-modern"><i class="fa-solid fa-landmark"></i></span>
                                <input type="text" class="form-control form-control-modern validate-input" id="cfgInstansi" placeholder="KEJAKSAAN NEGERI...">
                            </div>
                        </div>
                        <div class="col-md-5">
                            <label class="form-label-modern required-field">Kota (Tempat TTD)</label>
                            <div class="input-group">
                                <span class="input-group-text input-group-text-modern"><i class="fa-solid fa-map-location-dot"></i></span>
                                <input type="text" class="form-control form-control-modern validate-input" id="cfgKota" placeholder="Contoh: Boyolali">
                            </div>
                        </div>
                    </div>

                    <div class="modal-section-label">
                        <i class="fa-solid fa-signature"></i> 2. Penandatangan
                    </div>

                    <div class="info-box-modern mb-3">
                        <i class="fa-solid fa-circle-info mt-1"></i>
                        <div>
                            <strong>PENTING:</strong> Data Penandatangan wajib diisi lengkap untuk keperluan arsip digital.
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label-modern required-field">Jabatan Struktural</label>
                            <div class="input-group">
                                <span class="input-group-text input-group-text-modern"><i class="fa-solid fa-briefcase"></i></span>
                                <input type="text" class="form-control form-control-modern validate-input" id="cfgJabatan" placeholder="KEPALA SEKSI...">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label-modern required-field">Pangkat / Golongan</label>
                            <div class="input-group">
                                <span class="input-group-text input-group-text-modern"><i class="fa-solid fa-star"></i></span>
                                <input type="text" class="form-control form-control-modern validate-input" id="cfgPangkat" placeholder="Jaksa Madya (IV/a)">
                            </div>
                        </div>
                    </div>

                    <div class="row g-3 mb-4">
                        <div class="col-md-7">
                            <label class="form-label-modern required-field">Nama Lengkap Pejabat</label>
                            <div class="input-group">
                                <span class="input-group-text input-group-text-modern"><i class="fa-solid fa-user-tie"></i></span>
                                <input type="text" class="form-control form-control-modern validate-input" id="cfgNamaPejabat" placeholder="Nama lengkap...">
                            </div>
                        </div>
                        <div class="col-md-5">
                            <label class="form-label-modern required-field">NIP / NRP</label>
                            <div class="input-group">
                                <span class="input-group-text input-group-text-modern"><i class="fa-solid fa-id-card"></i></span>
                                <input type="text" class="form-control form-control-modern" id="cfgNipPejabat" placeholder="199...">
                            </div>
                        </div>
                    </div>

                    <div class="modal-section-label">
                        <i class="fa-solid fa-file-export"></i> 3. Format Output
                    </div>

                    <div class="d-flex gap-3">
                        <div class="form-check custom-radio-box w-50">
                            <input class="form-check-input" type="radio" name="formatOutput" id="fmtWord" value="word" checked>
                            <label class="form-check-label w-100" for="fmtWord">
                                <i class="fa-solid fa-file-word text-primary me-2"></i> Microsoft Word (.docx)
                                <div class="small text-muted">Bisa diedit kembali</div>
                            </label>
                        </div>
                        <div class="form-check custom-radio-box w-50">
                            <input class="form-check-input" type="radio" name="formatOutput" id="fmtPdf" value="pdf">
                            <label class="form-check-label w-100" for="fmtPdf">
                                <i class="fa-solid fa-file-pdf text-danger me-2"></i> PDF Document (.pdf)
                                <div class="small text-muted">Siap cetak / arsip</div>
                            </label>
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