<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cetak Laporan Sidang | TRON</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;800&display=swap" rel="stylesheet">

    <style>
        body { background-color: #121212; color: #e0e0e0; font-family: 'Inter', sans-serif; height: 100vh; display: flex; flex-direction: column; overflow: hidden; }
        .navbar { background-color: #1e1e1e; border-bottom: 1px solid #333; padding: 10px 0; }
        .navbar-brand { font-weight: 800; color: #fff !important; font-size: 1.2rem; }
        
        .main-container { flex: 1; display: flex; justify-content: center; align-items: flex-start; padding: 20px; overflow-y: auto; }
        
        .card-custom { background-color: #1e1e1e; border: 1px solid #333; border-radius: 12px; width: 100%; max-width: 1100px; box-shadow: 0 10px 30px rgba(0,0,0,0.5); }
        
        .card-header-custom { 
            background: linear-gradient(to right, #b71c1c, #d32f2f); 
            padding: 15px 25px; 
            display: flex; justify-content: space-between; align-items: center; 
            border-radius: 12px 12px 0 0; 
        }

        .filter-box { background: #252525; border-bottom: 1px solid #333; padding: 20px; }
        .form-label { font-size: 0.85rem; color: #aaa; margin-bottom: 5px; }
        .form-select { background-color: #2c2c2c; border: 1px solid #444; color: #fff; font-size: 0.9rem; }
        .form-select:focus { background-color: #333; color: white; box-shadow: none; border-color: #ffc107; }
        
        .custom-check-group { display: flex; gap: 15px; align-items: center; height: 100%; background: #2c2c2c; border: 1px solid #444; padding: 6px 15px; border-radius: 6px; }
        .form-check-input { cursor: pointer; border-color: #666; background-color: #444; }
        .form-check-input:checked { background-color: #ffc107; border-color: #ffc107; }
        
        .table-responsive { padding: 0 20px 20px 20px; }
        table.dataTable { margin-top: 10px !important; border-collapse: collapse !important; width: 100% !important; }
        .table-dark thead th { background-color: #333; color: #ffc107; font-size: 0.9rem; text-transform: uppercase; padding: 12px; border-bottom: 2px solid #444; }
        .table-dark tbody td { background-color: #1e1e1e; border-bottom: 1px solid #333; color: #ddd; padding: 10px 12px; font-size: 0.9rem; vertical-align: middle; }
        .table-dark tbody tr:hover td { background-color: #303030; color: white; }
        
        .btn-cetak { background: linear-gradient(45deg, #ffca28, #ff6f00); border: none; color: #000; font-weight: 700; padding: 10px 30px; font-size: 1rem; border-radius: 50px; transition: all 0.3s; }
        .btn-cetak:hover { transform: scale(1.05); box-shadow: 0 0 15px rgba(255, 202, 40, 0.5); color: #000; }
        
        .btn-success-custom { background: linear-gradient(45deg, #2e7d32, #1b5e20); border: none; color: #fff; font-weight: 700; padding: 10px 20px; border-radius: 50px; font-size: 0.9rem; }
        .btn-success-custom:hover { transform: scale(1.05); box-shadow: 0 0 15px rgba(46, 125, 50, 0.5); }

        /* Tombol Sync */
        .btn-sync { background: rgba(0,0,0,0.3); border: 1px solid rgba(255,255,255,0.2); color: white; font-weight: 600; font-size: 0.85rem; border-radius: 50px; padding: 8px 15px; transition: 0.3s; text-decoration: none; }
        .btn-sync:hover { background: white; color: #b71c1c; border-color: white; }
        .fa-spin-hover:hover { animation: fa-spin 1s infinite linear; }
    </style>
</head>
<body>

    <nav class="navbar navbar-expand-lg">
        <div class="container">
            <a class="navbar-brand" href="#"><i class="fa-solid fa-gavel me-2"></i> TRON SYSTEM</a>
            <div class="ms-auto">
                <a href="<?= base_url('home'); ?>" class="btn btn-outline-light btn-sm">Kembali ke Dashboard</a>
            </div>
        </div>
    </nav>

    <div class="main-container">
        <div class="card-custom">
            
            <div class="card-header-custom">
                <div>
                    <h5 class="m-0 text-white fw-bold"><i class="fa-solid fa-print me-2"></i> CETAK BERKAS SIDANG</h5>
                    <span class="badge bg-black bg-opacity-25">Database Mode | NIP: <?= esc($nip_user ?? '-') ?></span>
                </div>
                <a href="<?= base_url('sidang/sync') ?>" class="btn-sync" onclick="return confirm('Proses ini akan mengambil data terbaru dari Google Sheet dan menyimpannya ke Database. Lanjutkan?');">
                    <i class="fa-solid fa-arrows-rotate me-1 fa-spin-hover"></i> SINKRONISASI DATA
                </a>
            </div>

            <form action="<?= base_url('sidang/proses') ?>" method="post" id="formCetak">
                
                <input type="hidden" name="mode_cetak" id="inputModeCetak" value="seleksi">
                <input type="hidden" name="tanggal_terpilih" id="inputTanggalHidden" value="">

                <div class="filter-box">
                    <div class="row g-3 align-items-end">
                        <div class="col-md-4">
                            <label class="form-label">1. Pilih Tanggal Sidang</label>
                            <select id="dateFilter" class="form-select">
                                <option value="" selected disabled>-- PILIH TANGGAL UNTUK FILTER --</option>
                                <?php foreach($opt_tanggal as $tgl): ?>
                                    <option value="<?= esc($tgl) ?>"><?= esc($tgl) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">2. Jenis Dokumen</label>
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
                            <button type="button" id="btnFullP38" class="btn btn-success-custom mb-2 w-100" style="display:none;">
                                <i class="fa-solid fa-print me-2"></i> CETAK SEMUA P-38
                            </button>
                            <button type="submit" id="btnProses" class="btn btn-cetak w-100">
                                <i class="fa-solid fa-file-word me-2"></i> PROSES SELEKSI
                            </button>
                        </div>
                    </div>
                </div>

                <div class="table-responsive mt-3">
                    
                    <?php if (session()->getFlashdata('error')) : ?>
                        <div class="alert alert-danger py-2 small"><i class="fa-solid fa-circle-exclamation me-2"></i> <?= esc(session()->getFlashdata('error')); ?></div>
                    <?php endif; ?>

                    <?php if (session()->getFlashdata('success')) : ?>
                        <div class="alert alert-success py-2 small"><i class="fa-solid fa-check-circle me-2"></i> <?= esc(session()->getFlashdata('success')); ?></div>
                    <?php endif; ?>

                    <table id="tableSidang" class="table table-dark table-hover align-middle w-100">
                        <thead>
                            <tr>
                                <th class="text-center" width="5%">
                                    <input class="form-check-input" type="checkbox" id="checkAll">
                                </th>
                                <th width="30%">Nama Terdakwa</th>
                                <th width="20%">Nomor Perkara</th>
                                <th width="25%">Jaksa Penuntut umum</th>
                                <th width="20%">Tanggal</th> </tr>
                        </thead>
                        <tbody>
                            <?php if(!empty($all_data)): ?>
                                <?php foreach($all_data as $row): ?>
                                    <tr>
                                        <td class="text-center">
                                            <input class="form-check-input row-checkbox" type="checkbox" name="pilih_data[]" value="<?= esc($row['nomor_perkara'] ?? '') ?>">
                                        </td>
                                        <td class="fw-bold text-white"><?= esc($row['nama_terdakwa'] ?? '-') ?></td>
                                        <td class="text-secondary small"><?= esc($row['nomor_perkara'] ?? '-') ?></td>
                                        <td class="text-secondary small"><?= esc($row['jpu'] ?? '-') ?></td>
                                        <td><?= esc($row['tanggal_sidang'] ?? '') ?></td> </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

            </form>
        </div>
    </div>

 <script src="https://code.jquery.com/jquery-3.7.0.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

    <script>
        $(document).ready(function() {
            // 1. Inisiasi DataTables
            var table = $('#tableSidang').DataTable({
                "pageLength": 10,
                "lengthChange": false,
                "ordering": false,
                "language": {
                    "search": "Cari:",
                    "info": "Total _TOTAL_ Data",
                    "emptyTable": "Data kosong. Silakan klik 'Sinkronisasi Data' di atas.",
                    "zeroRecords": "Data tidak ditemukan."
                },
                "columnDefs": [
                    { "targets": 4, "visible": false } // Kolom Tanggal Sembunyi
                ]
            });

            // --- Fungsi untuk Update Teks Tombol Proses ---
            function updateBtnState() {
                var totalChecked = table.rows({ 'search': 'applied' }).nodes().to$().find('.row-checkbox:checked').length;
                var isP37 = $('#chkP37').is(':checked');
                var isP38 = $('#chkP38').is(':checked');
                var btn = $('#btnProses');
                
                var text = 'PROSES SELEKSI';
                
                if (totalChecked > 0) {
                    text = 'CETAK (' + totalChecked + ')';
                    if(isP37 && isP38) text += ' P-37 & P-38';
                    else if(isP37) text += ' P-37';
                    else if(isP38) text += ' P-38';
                }

                btn.html('<i class="fa-solid fa-file-word me-2"></i> ' + text);
            }

            // --- 2. CUSTOM FILTER DATA TABLES (Logic Perbandingan) ---
            $.fn.dataTable.ext.search.push(function(settings, data) {
                var selDate = $('#dateFilter').val(); // Nilai dari Dropdown (DD-MM-YYYY)
                var rawDate = data[4]; // Nilai dari Kolom Tabel (YYYY-MM-DD)
                
                if (!selDate || selDate === "") return true; 

                // Format YYYY-MM-DD tabel ke DD-MM-YYYY
                try {
                    var dateObj = new Date(rawDate);
                    // Pastikan objek tanggal valid
                    if(isNaN(dateObj)) return false; 
                    
                    var day = String(dateObj.getDate()).padStart(2, '0');
                    var month = String(dateObj.getMonth() + 1).padStart(2, '0');
                    var year = dateObj.getFullYear();
                    var formattedTableDate = day + '-' + month + '-' + year;
                } catch (e) {
                    return false; 
                }

                // Bandingkan
                return formattedTableDate === selDate; 
            });
                
            // --- 3. EVENT LISTENER (KOREKSI FUNGSI YANG HILANG) ---
            $('#dateFilter').on('change', function() {
                var val = $(this).val(); // Dapatkan nilai dropdown yang baru
                
                // Tindakan Filter dan Reset
                $('#inputTanggalHidden').val(val);
                table.draw(); // ⬅️ MEMICU LOGIC CUSTOM FILTER
                
                // Reset Checkbox
                $('#checkAll').prop('checked', false);
                // Hanya reset checkbox di baris yang difilter
                table.rows({ 'search': 'applied' }).nodes().to$().find('.row-checkbox').prop('checked', false);
                
                // Update Tombol State
                updateBtnState();
                
                // Toggle Tombol Full P38
                if(val) $('#btnFullP38').fadeIn(); else $('#btnFullP38').hide();
            });

            // --- LOGIKA CHECKBOX ---
            // Check All
            $('#checkAll').on('click', function() {
                var isChecked = this.checked;
                var rows = table.rows({ 'search': 'applied' }).nodes(); 
                $('input[type="checkbox"]', rows).prop('checked', isChecked);
                updateBtnState();
            });
            // Check individual row
            $('#tableSidang tbody').on('change', 'input[type="checkbox"]', function(){
                updateBtnState();
            });
            // Cek dokumen jenis
            $('#chkP37, #chkP38').on('change', function() { updateBtnState(); });

            // --- KLIK TOMBOL HIJAU (FULL P38) ---
            $('#btnFullP38').on('click', function() {
                var tgl = $('#inputTanggalHidden').val();
                if(!tgl) { alert("Pilih tanggal sidang dulu!"); return; }
                if(confirm("Anda yakin ingin mencetak SEMUA surat P-38 untuk tanggal "+tgl+"?")) {
                    // Set mode dan submit
                    $('#inputModeCetak').val('full_p38');
                    $('#formCetak').off('submit').submit();
                }
            });

            // --- SUBMIT FORM BIASA (PROSES SELEKSI) ---
            $('#formCetak').on('submit', function(e){
                if ($('#inputModeCetak').val() === 'full_p38') return true; 

                var form = this;
                var countChecked = table.rows({ 'search': 'applied' }).nodes().to$().find('.row-checkbox:checked').length;

                // Validasi Minimum
                if(countChecked === 0){ alert("Pilih minimal satu data untuk dicetak!"); e.preventDefault(); return false; }
                if(!$('#chkP37').is(':checked') && !$('#chkP38').is(':checked')) { alert("Pilih minimal satu jenis dokumen (P-37 atau P-38)!"); e.preventDefault(); return false; }
                
                // Trik Kirim Data
                table.$('.row-checkbox:checked').each(function(){
                    if(!$.contains(document, this)){
                        $(form).append($('<input>').attr('type','hidden').attr('name',this.name).val(this.value));
                    }
                });
                
                return true;
            });
            
            // Inisiasi awal state tombol
            updateBtnState();
        });
    </script>
</body>
</html>