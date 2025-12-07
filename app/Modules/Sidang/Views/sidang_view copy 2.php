<?php
// Ambil URL Base dari CodeIgniter (penting untuk AJAX)
$base_url = base_url();
?>
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
        
        /* --- Style Global (Desktop/Tablet) --- */
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
        
        .table-responsive { padding: 0 20px 20px 20px; } /* Padding default 20px untuk desktop */
        table.dataTable { margin-top: 10px !important; border-collapse: collapse !important; width: 100% !important; }
        .table-dark thead th { background-color: #333; color: #ffc107; font-size: 0.9rem; text-transform: uppercase; padding: 12px; border-bottom: 2px solid #444; }
        .table-dark tbody td { background-color: #1e1e1e; border-bottom: 1px solid #333; color: #ddd; padding: 10px 12px; font-size: 0.9rem; vertical-align: middle; }
        .table-dark tbody tr:hover td { background-color: #303030; color: white; }
        
        /* Style Tombol */
        .btn-cetak { background: linear-gradient(45deg, #ffca28, #ff6f00); border: none; color: #000; font-weight: 700; padding: 10px 30px; font-size: 1rem; border-radius: 50px; transition: all 0.3s; }
        .btn-cetak:hover { transform: scale(1.05); box-shadow: 0 0 15px rgba(255, 202, 40, 0.5); color: #000; }
        .btn-success-custom { background: linear-gradient(45deg, #2e7d32, #1b5e20); border: none; color: #fff; font-weight: 700; padding: 10px 20px; border-radius: 50px; font-size: 0.9rem; }
        .btn-success-custom:hover { transform: scale(1.05); box-shadow: 0 0 15px rgba(46, 125, 50, 0.5); }
        .btn-sync { background: rgba(0,0,0,0.3); border: 1px solid rgba(255,255,255,0.2); color: white; font-weight: 600; font-size: 0.85rem; border-radius: 50px; padding: 8px 15px; transition: 0.3s; text-decoration: none; }
        .btn-sync:hover { background: white; color: #b71c1c; border-color: white; }
        .fa-spin-hover:hover { animation: fa-spin 1s infinite linear; }

        /* KOREKSI FINAL: Targetkan elemen DataTables agar tertata rapi */
        div.dataTables_wrapper div.row {
            margin-left: 0;
            margin-right: 0;
            margin-top: 15px; 
        }
        /* Pastikan Search dan Paging berada di kolom yang benar */
        div.dataTables_wrapper div.col-md-6 {
            padding-left: 0;
            padding-right: 0;
        }
        /* Pastikan kotak pencarian tidak terlalu kecil */
        div.dataTables_wrapper div.dataTables_filter input {
            width: 100%; 
        }
        
        /* --- Media Query untuk penyesuaian di layar kecil (Mobile) --- */
        @media (max-width: 767.98px) {
            /* Mengurangi padding dan margin di mobile */
            .main-container { padding: 5px; } 
            .filter-box { padding: 15px; } /* Padding lebih kecil */
            .table-responsive { padding: 0 5px 15px 5px; } /* Padding tabel lebih kecil di mobile */
            
            /* Card Header untuk tumpuk */
            .card-header-custom { 
                flex-direction: column; 
                align-items: flex-start; 
                padding: 10px 15px; 
            }
            .card-header-custom > div:first-child { margin-bottom: 10px; }
            .btn-sync { 
                width: 100%; 
                text-align: center; 
                padding: 5px 10px;
            }
            
            /* Filter: Memaksa elemen filter ke baris baru */
            .filter-box .row > div { margin-bottom: 10px; }
            .filter-box .row > div:last-child { margin-bottom: 0; } 
            
            /* Custom Checkbox Group: Memungkinkan wrap */
            .custom-check-group { 
                flex-wrap: wrap; 
            }
            
            /* Tombol Cetak/Proses: Paksa stack (tumpuk) */
            .col-md-4.text-end { 
                text-align: left !important; 
                display: flex; 
                flex-direction: column;
                gap: 10px;
            }
            .btn-success-custom, .btn-cetak {
                width: 100% !important; /* Paksa lebar penuh */
            }
            
            /* DataTables mobile override */
            div.dataTables_wrapper div.dataTables_filter {
                text-align: left !important; /* Paksa search ke kiri di mobile */
            }
        }
    </style>
</head>
<body>

    <nav class="navbar navbar-expand-lg">
        <div class="container">
            <a class="navbar-brand" href="#"><i class="fa-solid fa-gavel me-2"></i> TRON SYSTEM</a>
            <div class="ms-auto">
                <a href="<?= $base_url . 'home'; ?>" class="btn btn-outline-light btn-sm">Kembali ke Dashboard</a>
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
                <a href="<?= $base_url . '/sidang/sync' ?>" class="btn-sync mt-2 mt-md-0" onclick="return confirm('Proses ini akan mengambil data terbaru dari Google Sheet dan menyimpannya ke Database. Lanjutkan?');">
                    <i class="fa-solid fa-arrows-rotate me-1 fa-spin-hover"></i> SINKRONISASI DATA
                </a>
            </div>

            <form action="<?= $base_url . '/sidang/proses' ?>" method="post" id="formCetak">
                
                <input type="hidden" name="mode_cetak" id="inputModeCetak" value="seleksi">
                <input type="hidden" name="tanggal_terpilih" id="inputTanggalHidden" value="">

                <div class="filter-box">
                    <div class="row g-3 align-items-end">
                        <div class="col-md-4">
                            <label class="form-label">1. Pilih Tanggal Sidang</label>

                            <select id="dateFilter" class="form-select">

                            <?php 
                            // Ambil tanggal terpilih yang dikirim dari Controller (format d-m-Y)
                            $selectedDateFromController = $selected_date ?? null; 
                            
                            // Cek apakah ada tanggal yang tersedia di database
                            $hasDates = !empty($opt_tanggal);
                            ?>

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
                        <div class="alert alert-danger py-2 small"><i class="fa-solid fa-circle-exclamation me-2"></i> <?= esc(session()->getFlashdata('error')); ?></div>
                    <?php endif; ?>

                    <?php if (session()->getFlashdata('success')) : ?>
                        <div class="alert alert-success py-2 small"><i class="fa-solid fa-check-circle me-2"></i> <?= esc(session()->getFlashdata('success')); ?></div>
                    <?php endif; ?>

                    <table id="tableSidang" class="table table-dark table-hover align-middle w-100">
<thead>
    <tr>
        <th> <input type="checkbox" id="checkAll"> </th> 
        <th>Nama Terdakwa</th>
        <th>Nomor Perkara</th>
        <th>Jaksa Penuntut umum</th>
        <th class="text-center">Aksi</th>
        <th class="d-none">Tanggal</th>
    </tr>
</thead>        
                        <tbody id="dataTableBody">
                             <tr><td colspan="5" class="text-center">Silakan pilih tanggal sidang untuk memuat data.</td></tr>
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
    
    if (totalChecked > 0) {
        text = 'CETAK (' + totalChecked + ')';
        if(isP37 && isP38) text += ' P-37 & P-38';
        else if(isP37) text += ' P-37';
        else if(isP38) text += ' P-38';
    }

    // Tombol hanya disable jika tidak ada centang DAN tidak ada jenis dokumen dipilih
    var isDisabled = (totalChecked === 0 && !isP37 && !isP38);
    // Jika ada data terpilih, tapi dokumen belum dipilih, tombol tetap aktif, tapi label menyesuaikan
    if (totalChecked > 0 && !isP37 && !isP38) {
         text = 'Pilih Jenis Dokumen (' + totalChecked + ' Data)';
         isDisabled = false;
    }


    btn.prop('disabled', isDisabled);
    btn.html('<i class="fa-solid fa-file-word me-2"></i> ' + text);
}


        // --- Fungsi untuk memuat data dari API ---
        function loadData(selectedDate) {
            
            // Konversi tanggal DD-MM-YYYY ke YYYY-MM-DD untuk URL API (database)
            var dbFormatDate = '';
            if (selectedDate) {
                var parts = selectedDate.split('-');
                if (parts.length === 3) {
                dbFormatDate = selectedDate;                
                }
            }
            
// 2. Bangun URL dengan aman (menghilangkan double slash)
    var cleanBaseUrl = baseUrlApp.replace(/\/+$/, '');
    var apiUrl = cleanBaseUrl + '/' + apiUrlPath + (dbFormatDate ? '?tanggal=' + dbFormatDate : '');
    
    console.log('API Request URL:', apiUrl);

    
            // Tampilkan loading state
            $('#dataTableBody').html('<tr><td colspan="5" class="text-center"><i class="fa fa-spinner fa-spin me-2"></i> Memuat data...</td></tr>');
            
            // Hancurkan DataTables lama (jika ada) sebelum memuat data baru
            if (isDataTableInitialized && $.fn.DataTable.isDataTable('#tableSidang')) {
                table.destroy();
                isDataTableInitialized = false;
            }

            // Panggil API
            fetch(apiUrl)
                .then(response => {
                    if (!response.ok) {
                        // Jika 404, pesan error yang lebih baik
                        if (response.status === 404) {
                            throw new Error("Rute API tidak ditemukan. Cek routes.php. URL: " + apiUrl);
                        }
                        // Jika 403 (Forbidden/Filter redirect), baca respons sebagai teks
                        if (response.status === 403) {
                             throw new Error("Sesi login sidang Anda berakhir. Silakan refresh dan login.");
                        }
                        // Jika respons bukan JSON, akan ditangkap oleh catch
                        throw new Error(`Gagal memuat data: ${response.status}`);
                    }
                    return response.json();
                })
                .then(result => {
                    let dataArray = [];

                    if (result.status === 200 && result.data.length > 0) {
                        result.data.forEach(row => {
                            // Format data untuk DataTables (sesuai urutan kolom)
                            dataArray.push([

                                // Kolom 0: Checkbox
                                '<input class="form-check-input row-checkbox" type="checkbox" name="pilih_data[]" value="' + row.nomor_perkara + '">',
                                // Kolom 1: Nama Terdakwa
                                '<span class="fw-bold text-white">' + row.nama_bersih + '</span>',                                
                                // Kolom 2: Nomor Perkara
                                 row.nomor_perkara,
                                // Kolom 3: JPU
                                row.jpu,
                                // Kolom 4: Aksi (Tombol Detail, jika ada)
                                '<button type="button" class="btn btn-sm btn-info view-details" data-nomor="'+row.nomor_perkara+'" title="Lihat Detail"><i class="fa-solid fa-eye"></i></button>',
                                // Kolom 5: Tanggal (Tersembunyi)
                                row.tanggal_sidang // Tanggal format DB (YYYY-MM-DD)
                            ]);
                        });
                    }

                    // Inisiasi DataTables baru dengan data yang diambil
                    table = $('#tableSidang').DataTable({
                        "data": dataArray, // Memuat data dari AJAX
                        "pageLength": 10,
                        "lengthChange": false,
                        "ordering": false,
                        "destroy": true, // Pastikan bisa dihancurkan ulang
                        "searching": true,
                        "responsive": true, 
                        // KOREKSI FINAL: Set DOM untuk layout Bootstrap
                        "dom": '<"row"<"col-md-6"l><"col-md-6 text-end"f>>' + 
                               'rt' + 
                               '<"row"<"col-md-6"i><"col-md-6"p>>',
                        "language": {
                            "search": "Cari:",
                            "info": "Total _TOTAL_ Data",
                            "emptyTable": "Tidak ada data sidang untuk tanggal ini.",
                            "zeroRecords": "Data tidak ditemukan."
                        },
                        "columnDefs": [
                            { "targets": 0, "className": "text-center" }, // Checkbox
                            { "targets": 4, "className": "text-center" }, // Aksi
                            { "targets": 5, "visible": false } // Kolom Tanggal Sembunyi
                        ]
                    });
                    
                    isDataTableInitialized = true;
                    updateBtnState();
                    
                    // Update hidden input tanggal
                    $('#inputTanggalHidden').val(selectedDate);
                    // Toggle tombol Full P38
                    if(selectedDate) $('#btnFullP38').fadeIn(); else $('#btnFullP38').hide();

                })
                .catch(error => {
                    console.error('Error fetching data:', error);
                    let errorMessage = error.message;

                    // Inisiasi DataTables kosong
                    table = $('#tableSidang').DataTable({
                        "data": [], "pageLength": 10, "lengthChange": false, "ordering": false, "destroy": true, "searching": true,
                        "responsive": true,
                        // KOREKSI FINAL: Set DOM untuk layout Bootstrap
                        "dom": '<"row"<"col-md-6"l><"col-md-6 text-end"f>>' + 
                               'rt' + 
                               '<"row"<"col-md-6"i><"col-md-6"p>>',
                        "language": {
                            "search": "Cari:", "info": "Total _TOTAL_ Data", "emptyTable": errorMessage, "zeroRecords": "Data tidak ditemukan."
                        },
                        "columnDefs": [
                            { "targets": 0, "className": "text-center" }, { "targets": 4, "className": "text-center" }, { "targets": 5, "visible": false }
                        ]
                    });
                    isDataTableInitialized = true;
                    updateBtnState();
                });
        }


        // --- Fungsi Utama pada Load Halaman ---
        $(document).ready(function() {
            var dateOnLoad = dateFilter.val(); 
            
            // 1. Inisiasi DataTables Kosong (Langsung di-load jika tidak ada tanggal terpilih)
            if (!dateOnLoad || dateOnLoad === '') {
                 table = $('#tableSidang').DataTable({
                    "data": [],
                    "pageLength": 10,
                    "lengthChange": false,
                    "ordering": false,
                    "destroy": true,
                    "searching": true,
                    "responsive": true,
                    // KOREKSI FINAL: Set DOM untuk layout Bootstrap
                    "dom": '<"row"<"col-md-6"l><"col-md-6 text-end"f>>' + 
                           'rt' + 
                           '<"row"<"col-md-6"i><"col-md-6"p>>',
                    "language": {
                        "search": "Cari:",
                        "info": "Total _TOTAL_ Data",
                        "emptyTable": "Silakan pilih tanggal sidang untuk memuat data.",
                        "zeroRecords": "Data tidak ditemukan."
                    },
                    "columnDefs": [
                        { "targets": 0, "className": "text-center" }, 
                        { "targets": 4, "className": "text-center" }, 
                        { "targets": 5, "visible": false }
                    ]
                });
                isDataTableInitialized = true;
            } else {
                // 2. Jika ada tanggal terpilih (dari redirect), muat data AJAX
                loadData(dateOnLoad);
            }
            
            updateBtnState(); 
            

            // --- EVENT LISTENER (Filter Dropdown) ---
            $('#dateFilter').on('change', function() {
                var val = $(this).val(); 
                
                // Panggil fungsi loadData dengan tanggal yang baru dipilih
                loadData(val);

                // Reset Checkbox
                $('#checkAll').prop('checked', false);
                
                // Update Tombol State
                updateBtnState();
            });

            // --- LOGIKA CHECKBOX (Disesuaikan untuk AJAX) ---
            // Check All
            $('#checkAll').on('click', function() {
                var isChecked = this.checked;
                // Gunakan DataTables API untuk iterasi row
                table.rows().nodes().to$().find('.row-checkbox').prop('checked', isChecked);
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
                var countChecked = table ? table.rows().nodes().to$().find('.row-checkbox:checked').length : 0;

                // Validasi Minimum
                if(countChecked === 0){ alert("Pilih minimal satu data untuk dicetak!"); e.preventDefault(); return false; }
                if(!$('#chkP37').is(':checked') && !$('#chkP38').is(':checked')) { alert("Pilih minimal satu jenis dokumen (P-37 atau P-38)!"); e.preventDefault(); return false; }
                
                // Trik Kirim Data (Harus mengambil nilai dari DataTables DOM saat ini)
                var selectedValues = [];
                // Hanya ambil data dari checkbox yang tercentang di DOM
                $('#tableSidang tbody').find('.row-checkbox:checked').each(function(){
                    selectedValues.push($(this).val());
                });

                // Hapus hidden input lama dan tambahkan yang baru
                $('input[name="pilih_data[]"]').remove();
                selectedValues.forEach(function(val) {
                    $(form).append($('<input>').attr('type', 'hidden').attr('name', 'pilih_data[]').val(val));
                });
                
                return true;
            });
            
            // Inisiasi awal state tombol
            updateBtnState();
        });
    </script>
</body>
</html>