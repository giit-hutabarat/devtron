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
        /* --- CUSTOM DARK THEME --- */
        body {
            background-color: #121212; color: #e0e0e0; font-family: 'Inter', sans-serif;
            height: 100vh; display: flex; flex-direction: column; overflow: hidden;
        }
        
        /* Navbar Compact */
        .navbar { background-color: #1e1e1e; border-bottom: 1px solid #333; padding: 10px 0; }
        .navbar-brand { font-weight: 800; color: #fff !important; font-size: 1.2rem; }

        /* Main Layout */
        .main-container {
            flex: 1; display: flex; justify-content: center; align-items: flex-start;
            padding: 20px; overflow-y: auto;
        }

        .card-custom {
            background-color: #1e1e1e; border: 1px solid #333; border-radius: 12px;
            width: 100%; max-width: 1100px; /* Lebih lebar biar tabel lega */
            box-shadow: 0 10px 30px rgba(0,0,0,0.5);
        }

        /* Header Merah Compact */
        .card-header-custom {
            background: linear-gradient(to right, #b71c1c, #d32f2f);
            padding: 15px 25px; display: flex; justify-content: space-between; align-items: center;
            border-radius: 12px 12px 0 0;
        }

        /* Filter Section Compact */
        .filter-box {
            background: #252525; border-bottom: 1px solid #333; padding: 20px;
        }
        .form-label { font-size: 0.85rem; color: #aaa; margin-bottom: 5px; }
        .form-select, .form-control {
            background-color: #2c2c2c; border: 1px solid #444; color: #fff; font-size: 0.9rem;
        }
        .form-select:focus { background-color: #333; color: white; box-shadow: none; border-color: #ffc107; }

        /* Checkbox Custom */
        .custom-check-group {
            display: flex; gap: 15px; align-items: center; height: 100%;
            background: #2c2c2c; border: 1px solid #444; padding: 6px 15px; border-radius: 6px;
        }
        .form-check-input { cursor: pointer; border-color: #666; background-color: #444; }
        .form-check-input:checked { background-color: #ffc107; border-color: #ffc107; }
        .form-check-label { cursor: pointer; font-size: 0.9rem; margin-top: 2px; }

        /* Table Styling */
        .table-responsive { padding: 0 20px 20px 20px; }
        table.dataTable { margin-top: 10px !important; border-collapse: collapse !important; width: 100% !important; }
        
        /* Header Tabel */
        .table-dark thead th {
            background-color: #333; color: #ffc107; font-size: 0.9rem; text-transform: uppercase;
            padding: 12px; border-bottom: 2px solid #444;
        }
        
        /* Body Tabel */
        .table-dark tbody td {
            background-color: #1e1e1e; border-bottom: 1px solid #333; color: #ddd;
            padding: 10px 12px; font-size: 0.9rem; vertical-align: middle;
        }
        /* Zebra Striping tapi Dark */
        .table-dark tbody tr:nth-of-type(odd) td { background-color: #252525; }
        .table-dark tbody tr:hover td { background-color: #303030; color: white; }

        /* DataTables Pagination Customization */
        .dataTables_info, .dataTables_length { color: #888 !important; font-size: 0.85rem; margin-top: 10px; }
        .page-link { background-color: #2c2c2c; border-color: #444; color: #ccc; }
        .page-item.active .page-link { background-color: #ffc107; border-color: #ffc107; color: black; }
        .page-item.disabled .page-link { background-color: #1a1a1a; border-color: #333; }

        /* Search Box */
        .dataTables_filter input {
            background-color: #2c2c2c; border: 1px solid #444; color: white; border-radius: 5px; margin-left: 10px;
        }

        /* Tombol Proses */
        .btn-cetak {
            background: linear-gradient(45deg, #ffca28, #ff6f00); border: none; color: #000;
            font-weight: 700; padding: 10px 30px; font-size: 1rem; border-radius: 50px;
            transition: all 0.3s;
        }
        .btn-cetak:hover { transform: scale(1.05); box-shadow: 0 0 15px rgba(255, 202, 40, 0.5); color: #000; }
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
                <h5 class="m-0 text-white fw-bold"><i class="fa-solid fa-print me-2"></i> CETAK BERKAS SIDANG</h5>
                <span class="badge bg-black bg-opacity-25">P-37 & P-38</span>
            </div>

            <form action="<?= base_url('sidang/proses') ?>" method="post" id="formCetak">
                
                <div class="filter-box">
                    <div class="row g-3 align-items-end">
                        <div class="col-md-4">
                            <label class="form-label">1. Pilih Tanggal Sidang</label>
                            <select id="dateFilter" class="form-select">
                                <option value="">-- Pilih Tanggal --</option>
                                <?php foreach($opt_tanggal as $tgl): ?>
                                    <option value="<?= $tgl ?>"><?= $tgl ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-md-5">
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

                        <div class="col-md-3 text-end">
                            <button type="submit" class="btn btn-cetak w-100">
                                <i class="fa-solid fa-download me-2"></i> PROSES
                            </button>
                        </div>
                    </div>
                </div>

                <div class="table-responsive mt-3">
                    <?php if (session()->getFlashdata('error')) : ?>
                        <div class="alert alert-danger py-2 small"><i class="fa-solid fa-circle-exclamation me-2"></i> <?= session()->getFlashdata('error'); ?></div>
                    <?php endif; ?>

                    <table id="tableSidang" class="table table-dark table-hover align-middle w-100">
                        <thead>
                            <tr>
                                <th class="text-center" width="5%">
                                    <input class="form-check-input" type="checkbox" id="checkAll">
                                </th>
                                <th width="30%">Nama Terdakwa</th>
                                <th width="20%">Nomor Perkara</th>
                                <th width="25%">JPU</th>
                                <th width="20%">Tanggal</th> </tr>
                        </thead>
                        <tbody>
                            <?php if(!empty($all_data)): ?>
                                <?php foreach($all_data as $index => $row): ?>
                                    <tr>
                                        <td class="text-center">
                                            
                                        </td>
                                        <td class="fw-bold text-white"><?= $row[2] ?? '-' ?></td>
                                        <td class="text-secondary small"><?= $row[0] ?? '-' ?></td>
                                        <td class="text-secondary small"><?= $row[3] ?? '-' ?></td>
                                        <td><?= $row[6] ?? '' ?></td> </tr>
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
            // 1. Inisialisasi DataTables
            var table = $('#tableSidang').DataTable({
                "pageLength": 10, // Batasi 10 data per halaman
                "lengthChange": false, // Hilangkan opsi 'Show 10/25/50' biar bersih
                "ordering": false, // Matikan sorting biar checkbox gak lari
                "language": {
                    "search": "Cari Nama:",
                    "info": "Menampilkan _START_ sampai _END_ dari _TOTAL_ data",
                    "emptyTable": "Silakan pilih tanggal sidang terlebih dahulu.",
                    "zeroRecords": "Tidak ada data yang cocok."
                },
                "columnDefs": [
                    { "targets": 4, "visible": false } // Sembunyikan Kolom Tanggal (Kolom ke-5)
                ]
            });

            // 2. Filter Tanggal Custom
            // Kita bikin fungsi pencarian khusus buat DataTables
            $.fn.dataTable.ext.search.push(
                function(settings, data, dataIndex) {
                    var selectedDate = $('#dateFilter').val();
                    var rowDate = data[4]; // Ambil data dari kolom ke-5 (Tanggal)

                    // Kalau belum pilih tanggal, jangan tampilin apa-apa
                    if (selectedDate === "") {
                        return false;
                    }
                    // Kalau tanggal cocok, tampilin
                    if (rowDate === selectedDate) {
                        return true;
                    }
                    return false;
                }
            );

            // Event Listener: Saat Dropdown Tanggal Berubah
            $('#dateFilter').on('change', function() {
                table.draw(); // Redraw tabel sesuai filter
                $('#checkAll').prop('checked', false); // Reset check all
                $('.row-checkbox').prop('checked', false);
            });

            // Trigger draw pertama kali biar tabel kosong di awal
            table.draw();

            // 3. Fitur Check All (Hanya centang yang terlihat di halaman ini)
            $('#checkAll').on('click', function() {
                var isChecked = this.checked;
                // Cari checkbox yang ada di dalam baris tabel yang terlihat
                table.rows({ filter: 'applied' }).nodes().to$().find('.row-checkbox').prop('checked', isChecked);
            });
        });
    </script>

</body>
</html>