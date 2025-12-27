/**
 * SIDANG SCRIPT - SECURE VERSION (FULL COMPLETED)
 * - Fitur: Sinkronisasi, Date Picker, Cetak P37/P38, Validasi Modal
 * - Pastikan variabel 'baseUrlApp' sudah didefinisikan di file VIEW utama
 */

// ============================================================
// 1. HELPER FUNCTIONS (GLOBAL SCOPE)
// ============================================================

// A. Security: Escape HTML (Mencegah XSS)
function escapeHtml(text) {
    if (text === null || text === undefined) return '';
    var map = { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' };
    return String(text).replace(/[&<>"']/g, function(m) { return map[m]; });
}

// B. Format Tanggal: YYYY-MM-DD (Input) -> DD-MM-YYYY (Server)
function formatDateToIndo(ymd) {
    if (!ymd) return '';
    // Input: 2025-12-23 -> Split jadi ['2025', '12', '23']
    var parts = ymd.split('-'); 
    if (parts.length !== 3) return ymd; // Safety check
    // Output: 23-12-2025
    return parts[2] + '-' + parts[1] + '-' + parts[0]; 
}

// ============================================================
// 2. CONFIG & STATE
// ============================================================
var table; 
var apiUrlPath = 'sidang/api/data'; 
var isDataTableInitialized = false;

// ============================================================
// 3. CORE FUNCTIONS (LOGIC UTAMA)
// ============================================================

// UPDATE STATUS TOMBOL CETAK (Logika P37 vs P38)
function updateBtnState() {
    var dateVal = $('#dateFilter').val();
    var isP37 = $('#chkP37').is(':checked');
    var isP38 = $('#chkP38').is(':checked');
    var totalChecked = table ? table.rows().nodes().to$().find('.row-checkbox:checked').length : 0;
    
    var btn = $('#btnProses');
    var text = 'PROSES SELEKSI';
    var isDisabled = true; 
    var btnClass = 'btn-cetak'; 

    // Syarat 1: Tanggal Harus Dipilih
    if (!dateVal) {
        text = 'Pilih Tanggal Sidang Dulu';
        isDisabled = true;
    } 
    // Syarat 2: Salah satu dokumen harus dipilih
    else if (!isP37 && !isP38) {
        text = 'Pilih Jenis Dokumen (P-37 / P-38)';
        isDisabled = true;
    }
    else {
        // SKENARIO P-38 (Surat Panggilan Saksi/Terdakwa)
        if (isP38) {
            isDisabled = false; 
            if (totalChecked > 0) {
                text = 'CETAK (' + totalChecked + ') BERKAS P-38';
            } else {
                text = 'CETAK SEMUA BERKAS P-38';
                btnClass = 'btn-success-custom'; 
            }
        } 
        // SKENARIO P-37 (Surat Panggilan Terdakwa - WAJIB PILIH)
        else if (isP37) {
            if (totalChecked > 0) {
                isDisabled = false;
                text = 'CETAK (' + totalChecked + ') BERKAS P-37';
            } else {
                isDisabled = true;
                text = 'Pilih Minimal 1 BERKAS Terdakwa';
            }
        }
    }

    btn.prop('disabled', isDisabled);
    btn.html('<i class="fa-solid fa-file-word me-2"></i> ' + text);
    // Reset class dulu baru tambah yang sesuai
    btn.removeClass('btn-cetak btn-success-custom').addClass(btnClass);
}

// FUNGSI LOAD DATA (AJAX)
function loadData(selectedDateYMD) {
    // FIX: Gunakan helper formatDateToIndo yang sudah didefinisikan di atas
    var dbFormatDate = formatDateToIndo(selectedDateYMD);
    
    // Setup URL
    var base = (typeof baseUrlApp !== 'undefined') ? baseUrlApp : window.location.origin;
    var cleanBase = base.replace(/\/+$/, '');
    var cleanPath = apiUrlPath.replace(/^\/+/, '');
    
    var urlObj = new URL(cleanBase + '/' + cleanPath);
    
    if (dbFormatDate) {
        urlObj.searchParams.append('tanggal', dbFormatDate);
    }
    
    // Loading State
    $('#dataTableBody').html('<tr><td colspan="5" class="text-center py-5"><i class="fa fa-spinner fa-spin fa-2x text-primary mb-2"></i><br>Memuat data...</td></tr>');
    
    // Destroy tabel lama jika ada
    if ($.fn.DataTable.isDataTable('#tableSidang')) {
        $('#tableSidang').DataTable().destroy();
    }

    // Fetch Data
    fetch(urlObj)
        .then(response => {
            if (!response.ok) throw new Error("Gagal memuat data.");
            return response.json();
        })
        .then(result => {
            let dataArray = [];
            if (result.status === 200 && result.data.length > 0) {
                result.data.forEach(row => {
                    dataArray.push([
                        // Kolom 0: Checkbox
                        '<input class="form-check-input row-checkbox" type="checkbox" name="pilih_data[]" value="' + escapeHtml(row.nomor_perkara) + '">',
                        // Kolom 1: Nama
                        '<span class="fw-bold text-white">' + escapeHtml(row.nama_bersih) + '</span>',                                
                        // Kolom 2: No Perkara
                        escapeHtml(row.nomor_perkara),
                        // Kolom 3: JPU
                        escapeHtml(row.jpu),
                        // Kolom 4: Aksi
                        '<button type="button" class="btn btn-sm btn-outline-info view-details"><i class="fa-solid fa-magnifying-glass-chart"></i></button>',
                        // Kolom 5: Hidden Tanggal
                        row.tanggal_sidang 
                    ]);
                });
            }
            initDataTable(dataArray);
            
            // Simpan tanggal format Indo ke hidden input (untuk form submit)
            $('#inputTanggalHidden').val(dbFormatDate);
            
            updateBtnState(); 
        })
        .catch(error => {
            console.error(error);
            initDataTable([]); 
            updateBtnState();
        });
}

// INISIALISASI DATATABLE (UI/UX PREMIUM)
function initDataTable(dataSet) {
    table = $('#tableSidang').DataTable({
        "data": dataSet, 
        "destroy": true, 
        "searching": true,
        "responsive": true,
        
        // Aktifkan pilihan jumlah data
        "lengthChange": true, 
        "lengthMenu": [ [10, 25, 50, 100, -1], [10, 25, 50, 100, "Semua"] ], 
        "pageLength": 10, 

        // --- LAYOUTING DOM (RAHASIA TAMPILAN RAPI) ---
        // Penjelasan:
        // <"row..."  -> Baris pembungkus
        // <"col-md-6..." l> -> Kolom Kiri: Length (Dropdown)
        // <"col-md-6..." f> -> Kolom Kanan: Filter (Search) - Kita kasih class 'text-md-end' biar rata kanan
"dom": '<"d-flex justify-content-between align-items-center mb-3"lf>rt<"row mt-3"<"col-md-6"i><"col-md-6"p>>',        
        "language": {
            "lengthMenu": "_MENU_", // Kita singkat jadi cuma Dropdown saja, label "Tampilkan" kita taruh di CSS biar rapi
            "search": "", 
            "searchPlaceholder": "Ketik Nama / No Perkara...",
            "info": "Menampilkan _START_ s/d _END_ dari _TOTAL_ data",
            "infoEmpty": "0 data",
            "infoFiltered": "(total _MAX_)",
            "emptyTable": "Tidak ada data sidang.",
            "zeroRecords": "Data tidak ditemukan.",
            "paginate": {
                "first": '<i class="fa-solid fa-angles-left"></i>',
                "last": '<i class="fa-solid fa-angles-right"></i>',
                "next": '<i class="fa-solid fa-angle-right"></i>',
                "previous": '<i class="fa-solid fa-angle-left"></i>'
            }
        },
        "columnDefs": [
            { "targets": [0, 4], "className": "text-center", "orderable": false }, 
            { "targets": 5, "visible": false }
        ],
        "createdRow": function (row, data, dataIndex) {
            var columns = ['Pilih', 'Nama Terdakwa', 'Nomor Perkara', 'Jaksa Penuntut Umum', 'Aksi'];
            $('td', row).each(function (i) {
                if (columns[i]) $(this).attr('data-label', columns[i]);
            });
        }
    });
}

// ============================================================
// 4. DOCUMENT READY (EVENT LISTENERS)
// ============================================================
$(document).ready(function() {

    // A. LOGIKA SINKRONISASI (FIXED DATE PICKER)
    $('#btnSyncData').on('click', function(e) {
        e.preventDefault(); 
        
        // 1. Ambil value dari Input Date (Format: YYYY-MM-DD)
        var rawDate = $('#dateFilter').val();

        if (!rawDate) {
            alert("Harap pilih tanggal di kalender terlebih dahulu!");
            return false;
        }

        // 2. Ubah ke format DD-MM-YYYY
        var indoDate = formatDateToIndo(rawDate);

        // 3. Konfirmasi
        var confirmAction = confirm('SINKRONISASI GOOGLE SHEET\n\nTanggal Sasaran: ' + indoDate + '\n\nPastikan data di Excel Master/Harian sudah siap. Lanjutkan?');
        
        if (confirmAction) {
            var $btn = $(this);
            var base = (typeof baseUrlApp !== 'undefined') ? baseUrlApp : window.location.origin;
            var cleanBase = base.replace(/\/+$/, '');
            var targetUrl = cleanBase + '/sidang/sync?tanggal=' + indoDate;

            $btn.addClass('disabled').css('pointer-events','none');
            $btn.html('<i class="fa-solid fa-circle-notch fa-spin me-2"></i> MOHON TUNGGU...');

            setTimeout(function(){
                window.location.href = targetUrl;
            }, 500);
        }
    });

    // B. EVENT GANTI TANGGAL (Load Data Otomatis)
    $('#dateFilter').on('change', function() {
        var val = $(this).val();
        if(val) {
            loadData(val);
        } else {
            initDataTable([]);
        }
        $('#checkAll').prop('checked', false);
    });

    // C. CHECKBOX P-38 vs P-37 LOGIC
    function toggleModalFields() {
        const isP38Checked = $('#chkP38').is(':checked');
        const $colJabatan = $('#col-jabatan-struktural');
        const $colNamaPejabat = $('#col-nama-pejabat');
        const $parentPangkat = $('#parent-pangkat');
        const $parentNip = $('#parent-nip');

        if (isP38Checked) {
            $colJabatan.show();
            $colNamaPejabat.show();
            // Kembalikan ke layout bagi dua
            $parentPangkat.removeClass('col-md-12').addClass('col-md-6');
            $parentNip.removeClass('col-md-12').addClass('col-md-5');
        } else {
            $colJabatan.hide();
            $colNamaPejabat.hide();
            // Buat Full Width untuk P37
            $parentPangkat.removeClass('col-md-6').addClass('col-md-12');
            $parentNip.removeClass('col-md-5').addClass('col-md-12');
        }
    }

    $('#chkP38').on('change', function() {
        if ($(this).is(':checked')) {
            $('#chkP37').prop('checked', false).prop('disabled', true);
        } else {
            $('#chkP37').prop('disabled', false);
        }
        toggleModalFields();
        updateBtnState();
    });

    $('#chkP37').on('change', function() {
        if ($(this).is(':checked')) {
            $('#chkP38').prop('checked', false).prop('disabled', true);
        } else {
            $('#chkP38').prop('disabled', false);
        }
        toggleModalFields();
        updateBtnState();
    });

    // D. CHECKBOX TABEL LOGIC
    $('#tableSidang tbody').on('change', '.row-checkbox', updateBtnState);

    // Fitur klik baris tabel (UX)
    $('#tableSidang tbody').on('click', 'tr', function(e) {
        if ($(e.target).closest('button, a, .view-details').length) return; 

        var chk = $(this).find('.row-checkbox');
        if ($(e.target).is('input[type="checkbox"]')) {
            chk.is(':checked') ? $(this).addClass('selected-row') : $(this).removeClass('selected-row');
            return;
        }
        if (chk.prop('disabled')) return;

        var currentState = chk.prop('checked');
        chk.prop('checked', !currentState);
        !currentState ? $(this).addClass('selected-row') : $(this).removeClass('selected-row');
        chk.trigger('change');
    });

    $('#checkAll').on('click', function() {
        if(table) {
            var isChecked = this.checked;
            table.rows().nodes().to$().find('.row-checkbox').prop('checked', isChecked);
            isChecked ? table.rows().nodes().to$().addClass('selected-row') : table.rows().nodes().to$().removeClass('selected-row');
            updateBtnState();
        }
    });

    // E. MODAL & SUBMIT FORM CETAK
    var elModal = document.getElementById('modalConfigCetak');
    // Init Bootstrap Modal
    var myAppModal = new bootstrap.Modal(elModal, { backdrop: 'static', keyboard: false });
    
    // Fix Error Aria-Hidden Bootstrap
    elModal.addEventListener('show.bs.modal', function () { 
        document.body.classList.add('modal-open');
    
    });
    
    elModal.addEventListener('hidden.bs.modal', function () {
    document.body.classList.remove('modal-open');
    });
    var formToSubmit = null; 

    // Tombol "Proses Seleksi" diklik
    $('#formCetak').on('submit', function(e){
        e.preventDefault();

        var isP38 = $('#chkP38').is(':checked');
        var isP37 = $('#chkP37').is(':checked');
        var countChecked = table ? table.rows().nodes().to$().find('.row-checkbox:checked').length : 0;

        if (!isP38 && !isP37) { alert("Pilih jenis dokumen!"); return false; }
        
        // Validasi P-37 wajib pilih minimal 1
        if (isP37 && countChecked === 0) { 
            alert("Untuk P-37, Anda wajib memilih minimal satu data terdakwa!"); 
            return false; 
        }

        toggleModalFields();
        
        // Tampilkan/Sembunyikan Input Nomor Surat
        if (isP38) {
            $('#blockNomorSurat').show();
            // Jika pilih checkbox -> mode seleksi, jika tidak -> mode full
            if (countChecked > 0) {
                $('#inputModeCetak').val('seleksi'); 
            } else {
                $('#inputModeCetak').val('full_p38'); 
            }
        } else {
            // P-37 biasanya otomatis nomor suratnya atau per-perkara
            $('#blockNomorSurat').hide();
            $('#cfgNomorSurat').val('');
            $('#inputModeCetak').val('seleksi'); 
        }

        formToSubmit = this;
        myAppModal.show(); // Tampilkan Modal Konfigurasi
    });

    // Tombol "LANJUTKAN CETAK" di Modal
    $('#btnFinalCetak').on('click', function() {
        
        // 1. Reset State
            const $alert = $('#alertModal');
            $alert.addClass('d-none').html(''); // Kosongkan pesan lama
            $('.form-control-modern').removeClass('is-invalid');
            
            let isValid = true;
            let errorList = []; // Gunakan array untuk menampung banyak error


        // 2. Cek Input Wajib di Modal
        $('.validate-input').each(function() {
            // Skip Nomor Surat jika sedang di-hidden
            if ($(this).closest('#col-jabatan-struktural').is(':hidden') || 
                $(this).closest('#col-nama-pejabat').is(':hidden') ||
                ($(this).attr('id') === 'cfgNomorSurat' && $('#blockNomorSurat').is(':hidden'))) {
                return; // Jangan validasi jika kolomnya sedang di-hidden
        }

        const value = $.trim($(this).val());
        const type = $(this).data('type');
        const label = $(this).closest('.col-md-6, .col-md-7, .col-md-5, .col-md-4, .mb-3').find('label').text().replace('*', '').trim();; 
        
        // A. Validasi Kosong
        if (value === '') {
            $(this).addClass('is-invalid');
            isValid = false;
            errorList.push(`<b>${label}</b> tidak boleh kosong.`);
            return;
        }
        // B. Validasi Tipe Data (Regex)
        if (type === 'text') {
            const textRegex = /^[a-zA-Z\s.,]*$/;
            if (!textRegex.test(value)) {
                $(this).addClass('is-invalid');
                isValid = false;
                errorList.push(`<b>${label}</b> hanya boleh berisi huruf.`);
            }
        } else if (type === 'number') {
            const numRegex = /^[0-9]*$/;
            if (!numRegex.test(value)) {
                $(this).addClass('is-invalid');
                isValid = false;
                errorList.push(`<b>${label}</b> hanya boleh berisi angka.`);
            }
        }
    });
if (!isValid) {
        // Buat struktur HTML pesan error yang rapi
        let htmlContent = `<div class="d-flex align-items-start">
            <i class="fa-solid fa-circle-exclamation me-2 mt-1"></i>
            <div>
                <div class="fw-bold mb-1">Terjadi Kesalahan:</div>
                <ul class="ps-3 mb-0">`;
        
        errorList.forEach(err => {
            htmlContent += `<li>${err}</li>`;
        });

        htmlContent += `</ul></div></div>`;

        $alert.html(htmlContent).removeClass('d-none');
        
        // Efek Shake (Getar) pada modal agar user sadar ada error
        $('.modal-content').addClass('shake-animation');
        setTimeout(() => $('.modal-content').removeClass('shake-animation'), 500);
        
        return false;
    }

        // 4. Proses Lanjut Jika Valid (Logic Submit Tetap Sama)
        // Hapus data lama yg mungkin nempel
        $(formToSubmit).find('.extra-data').remove(); 
        $(formToSubmit).find('input[type="hidden"][name="pilih_data[]"]').remove();

        var formatSelected = $('input[name="formatOutput"]:checked').val(); // Word/PDF

        // Kumpulkan data dari Modal
        var inputs = [
            { name: 'custom_nomor_surat', val: $('#cfgNomorSurat').val() },
            { name: 'custom_instansi', val: $('#cfgInstansi').val() },
            { name: 'custom_kota', val: $('#cfgKota').val() },
            { name: 'ttd_jabatan', val: $('#cfgJabatan').val() },
            { name: 'ttd_pangkat', val: $('#cfgPangkat').val() }, 
            { name: 'ttd_nama', val: $('#cfgNamaPejabat').val() },
            { name: 'ttd_nip', val: $('#cfgNipPejabat').val() },
            { name: 'output_format', val: formatSelected }         
        ];

        // Inject ke Form Utama
        inputs.forEach(item => {
            $('<input>').attr({type: 'hidden', name: item.name, value: item.val, class: 'extra-data'}).appendTo(formToSubmit);
        });

        // Inject Data Checkbox dari DataTable
        if (table) {
             table.rows().nodes().to$().find('.row-checkbox:checked').each(function(){
                $(formToSubmit).append($('<input>').attr('type', 'hidden').attr('name', 'pilih_data[]').val($(this).val()));
            });
        }

        // 4. Eksekusi Submit
        $(this).prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Memproses...');
        
        setTimeout(() => {
            formToSubmit.submit();
            myAppModal.hide();
            $(this).prop('disabled', false).html('<i class="fa-solid fa-print me-2"></i> LANJUTKAN CETAK');
        }, 500);
    });

    // --- FITUR BARU: TOMBOL TANGGAL CEPAT (UI/UX) ---
    $('.btn-quick-date').on('click', function(e) {
        e.preventDefault();
        
        var target = $(this).data('target'); // 'today' atau 'tomorrow'
        var dateObj = new Date();

        if (target === 'tomorrow') {
            dateObj.setDate(dateObj.getDate() + 1);
        }

        // Format ke YYYY-MM-DD untuk input HTML5
        var yyyy = dateObj.getFullYear();
        var mm = String(dateObj.getMonth() + 1).padStart(2, '0');
        var dd = String(dateObj.getDate()).padStart(2, '0');
        var formattedDate = yyyy + '-' + mm + '-' + dd;

        // Set value ke input & Trigger load data
        $('#dateFilter').val(formattedDate).trigger('change');

        // Efek visual tombol aktif
        $('.btn-quick-date').removeClass('active btn-primary').addClass('btn-outline-light');
        $(this).removeClass('btn-outline-light').addClass('active btn-primary');
    });

    // Reset tombol quick date kalau user ganti tanggal manual lewat kalender
    $('#dateFilter').on('input', function() {
        $('.btn-quick-date').removeClass('active btn-primary').addClass('btn-outline-light');
    });

    // F. UTILS LAINNYA
    // Auto Hide Alert
    window.setTimeout(function() {
        $(".alert").fadeTo(500, 0).slideUp(500, function(){ $(this).remove(); });
    }, 10000); 

    // Initial Load (Jika date input sudah ada value/hari ini)
    initDataTable([]);
    var initialDate = $('#dateFilter').val();
    if(initialDate) {
        loadData(initialDate);
    }
});