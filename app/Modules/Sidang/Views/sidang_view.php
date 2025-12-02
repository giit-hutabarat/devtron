<?= $this->extend('App\Views\layouts\backend') ?>

<?= $this->section('content') ?>

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
                <input type="hidden" name="mode_cetak" v-model="inputModeCetak">
                <input type="hidden" name="tanggal_terpilih" v-model="selectedDate">
                <?= csrf_field() ?>
                
                <div class="filter-box">
                    <div class="row g-3 align-items-end">
                        <div class="col-md-4">
                            <label class="form-label">1. Pilih Tanggal Sidang</label>
                            <select id="dateFilter" class="form-select" v-model="selectedDate" @change="applyFilter">
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
                                    <input class="form-check-input" type="checkbox" name="jenis_dokumen[]" value="p37" id="chkP37" v-model="chkP37">
                                    <label class="form-check-label" for="chkP37">Form P-37</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="jenis_dokumen[]" value="p38" id="chkP38" v-model="chkP38">
                                    <label class="form-check-label" for="chkP38">Form P-38</label>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-4 text-end">
                            <button type="button" id="btnFullP38" class="btn btn-success-custom mb-2 w-100" 
                                    v-show="selectedDate" 
                                    @click="submitFullP38">
                                <i class="fa-solid fa-print me-2"></i> CETAK SEMUA P-38
                            </button>
                            <button type="submit" id="btnProses" class="btn btn-cetak w-100" :disabled="!canSubmit" @click="setSeleksiMode">
                                <i class="fa-solid fa-file-word me-2"></i> {{ btnProsesText }}
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
                                    <input class="form-check-input" type="checkbox" id="checkAll" @click="toggleSelectAll">
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
                                            <input class="form-check-input row-checkbox" type="checkbox" 
                                                   name="pilih_data[]" 
                                                   value="<?= esc($row['nomor_perkara'] ?? '') ?>">
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
    
<?= $this->endSection() ?>

<?= $this->section('js') ?> 
   <script>
        // Inisiasi DataTables (diasumsikan variabel 'table' sudah diinisiasi di initDataTable)
        var table; 
        
        // --- 1. DATA/STATE UNTUK VUE ---
        window.dataVue = {
            ...window.dataVue,
            selectedDate: '',
            inputModeCetak: 'seleksi',
            chkP37: false,
            chkP38: false,
            checkboxCounter: 0, // State untuk memicu re-render
        };
        
        // --- 2. COMPUTED PROPERTIES (Logic Deklaratif) ---
        window.computedVue = {
            ...window.computedVue,
            
            // Mengambil jumlah baris yang dicek
            totalCheckedRows() {
                this.checkboxCounter; 
                if (!table) return 0;
                return table.rows({ 'search': 'applied' }).nodes().to$().find('.row-checkbox:checked').length;
            },
            
            // Teks tombol berubah otomatis
            btnProsesText() {
                let count = this.totalCheckedRows; 
                let text = 'PROSES SELEKSI';
                
                if (count > 0) {
                    text = `CETAK (${count})`;
                    if (this.chkP37 && this.chkP38) text += ' P-37 & P-38';
                    else if (this.chkP37) text += ' P-37';
                    else if (this.chkP38) text += ' P-38';
                }
                return text;
            },

            // Properti untuk mengontrol atribut 'disabled' tombol
            canSubmit() {
                 return this.totalCheckedRows > 0 && (this.chkP37 || this.chkP38);
            }
        };
        
        // --- 3. METHODS (Aksi Imperatif untuk DataTables) ---
        window.methodsVue = {
            ...window.methodsVue,
            
            // Inisiasi DataTables dan Custom Filter
            initDataTable() {
                // Pastikan DataTables diinisiasi di sini
                table = $('#tableSidang').DataTable({
                    "pageLength": 10,
                    "lengthChange": false,
                    "ordering": false,
                    "language": { "search": "Cari:", "info": "Total _TOTAL_ Data", "emptyTable": "Data kosong.", "zeroRecords": "Data tidak ditemukan." },
                    "columnDefs": [
                        { "targets": 4, "visible": false } // Kolom Tanggal Sembunyi (YYYY-MM-DD)
                    ]
                });
                
                // Event listener untuk checkbox individu (Memperbarui state Vue)
                $('#tableSidang tbody').on('change', 'input[type="checkbox"]', () => {
                    this.checkboxCounter++; // Memicu re-kalkulasi totalCheckedRows
                });
                
                // Mendaftarkan Custom Filter
                $.fn.dataTable.ext.search.push(this.customDateFilter);
            },

            // Logic Filter Tanggal (Konversi YYYY-MM-DD menjadi DD-MM-YYYY untuk perbandingan)
            customDateFilter(settings, data) {
                var selDate = window.app ? window.app.selectedDate : ''; // Ambil State DD-MM-YYYY
                var rawDate = data[4]; // Data mentah tabel YYYY-MM-DD
                
                if (!selDate || selDate === "") return true; 

                // Format YYYY-MM-DD tabel ke DD-MM-YYYY untuk perbandingan
                try {
                    var dateObj = new Date(rawDate);
                    if(isNaN(dateObj) || rawDate === '0000-00-00') return false; 
                    
                    var day = String(dateObj.getDate()).padStart(2, '0');
                    var month = String(dateObj.getMonth() + 1).padStart(2, '0');
                    var year = dateObj.getFullYear();
                    var formattedTableDate = day + '-' + month + '-' + year;
                } catch (e) {
                    return false; 
                }

                return formattedTableDate === selDate; // Perbandingan final
            },

            // Event handler saat dropdown tanggal berubah
            applyFilter() {
                this.inputModeCetak = 'seleksi';
                if (table) table.draw(); // Memicu filter
                
                // Reset UI
                $('#checkAll').prop('checked', false);
                if (table) table.rows({ 'search': 'applied' }).nodes().to$().find('.row-checkbox').prop('checked', false);
                this.checkboxCounter++; // Force update
            },
            
            // Atur mode cetak ke 'seleksi' (dipanggil saat tombol kuning diklik)
            setSeleksiMode() {
                this.inputModeCetak = 'seleksi';
            },

            // Toggle Select All Checkbox
            toggleSelectAll(event) {
                var isChecked = event.target.checked;
                if (table) {
                    var rows = table.rows({ 'search': 'applied' }).nodes(); 
                    $('input[type="checkbox"]', rows).prop('checked', isChecked);
                }
                this.checkboxCounter++; // Force update
            },
            
            // Submit Mode Full P38
            submitFullP38() {
                const tgl = this.selectedDate;
                if(!tgl) { alert("Pilih tanggal sidang dulu!"); return; }
                if(confirm(`Anda yakin ingin mencetak SEMUA surat P-38 untuk tanggal ${tgl}?`)) {
                    this.inputModeCetak = 'full_p38';
                    $('#formCetak').submit();
                }
            },
        };

        // --- 4. HOOKS (Lifecycle) ---
        // Hook created yang dieksekusi oleh backend.php
        window.createdVue = function() {
            this.initDataTable();
            window.defaultCreatedVue.call(this);
        };
        
        // Listener pada checkbox jenis dokumen (di luar scope Vue)
        $(function() {
            $('#chkP37, #chkP38').on('change', function() {
                if (window.app) window.app.checkboxCounter++;
            });
        });
    </script>
<?= $this->endSection() ?>