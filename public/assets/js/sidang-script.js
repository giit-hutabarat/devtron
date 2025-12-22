/**
 * SIDANG SCRIPT - SECURE VERSION
 * Pastikan variabel 'baseUrlApp' sudah didefinisikan di file VIEW utama (sidang_view.php)
 */

// --- HELPER SECURITY: Escape HTML untuk mencegah XSS ---
function escapeHtml(text) {
    if (text === null || text === undefined) return '';
    var map = {
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;'
    };
    return String(text).replace(/[&<>"']/g, function(m) { return map[m]; });
}

// --- CONFIG & STATE ---
var table; 
// var baseUrlApp diambil dari Global Variable di View (JANGAN didefinisikan ulang disini pakai PHP tag)
var apiUrlPath = 'sidang/api/data'; 
var isDataTableInitialized = false;

// 1. UPDATE STATUS TOMBOL (LOGIKA HYBRID)
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
        // SKENARIO P-38
        if (isP38) {
            isDisabled = false; 
            if (totalChecked > 0) {
                text = 'CETAK (' + totalChecked + ') DATA P-38';
            } else {
                text = 'CETAK SEMUA DATA P-38';
                btnClass = 'btn-success-custom'; 
            }
        } 
        // SKENARIO P-37
        else if (isP37) {
            if (totalChecked > 0) {
                isDisabled = false;
                text = 'CETAK (' + totalChecked + ') DATA P-37';
            } else {
                isDisabled = true;
                text = 'Pilih Minimal 1 Data Terdakwa';
            }
        }
    }

    btn.prop('disabled', isDisabled);
    btn.html('<i class="fa-solid fa-file-word me-2"></i> ' + text);
    btn.removeClass('btn-cetak btn-success-custom').addClass(btnClass);
}

// --- FUNGSI LOAD DATA (SECURE) ---
function loadData(selectedDate) {
    var dbFormatDate = selectedDate || '';
    
    // --- SECURITY UPDATE: URL Builder ---
    // Gunakan baseUrlApp global dari View sebagai base
    // Hapus trailing slash dari base dan leading slash dari path agar rapi
    var base = (typeof baseUrlApp !== 'undefined') ? baseUrlApp : window.location.origin;
    var cleanBase = base.replace(/\/+$/, '');
    var cleanPath = apiUrlPath.replace(/^\/+/, '');
    
    var urlObj = new URL(cleanBase + '/' + cleanPath);
    
    if (dbFormatDate) {
        urlObj.searchParams.append('tanggal', dbFormatDate);
    }
    
    $('#dataTableBody').html('<tr><td colspan="5" class="text-center"><i class="fa fa-spinner fa-spin me-2"></i> Memuat data...</td></tr>');
    
    if ($.fn.DataTable.isDataTable('#tableSidang')) {
        $('#tableSidang').DataTable().destroy();
    }

    fetch(urlObj)
        .then(response => {
            if (!response.ok) throw new Error("Gagal memuat data.");
            return response.json();
        })
        .then(result => {
            let dataArray = [];
            if (result.status === 200 && result.data.length > 0) {
                result.data.forEach(row => {
                    // --- SECURITY: GUNAKAN escapeHtml() ---
                    // Mencegah injeksi script jika ada data nama yang aneh
                    dataArray.push([
                        '<input class="form-check-input row-checkbox" type="checkbox" name="pilih_data[]" value="' + escapeHtml(row.nomor_perkara) + '">',
                        '<span class="fw-bold text-white">' + escapeHtml(row.nama_bersih) + '</span>',                                
                        escapeHtml(row.nomor_perkara),
                        escapeHtml(row.jpu),
                        '<button type="button" class="btn btn-sm btn-outline-info view-details"><i class="fa-solid fa-magnifying-glass-chart"></i></button>',
                        row.tanggal_sidang 
                    ]);
                });
            }
            initDataTable(dataArray);
            $('#inputTanggalHidden').val(selectedDate);
            updateBtnState(); 
        })
        .catch(error => {
            initDataTable([]); 
            updateBtnState();
        });
}

function initDataTable(dataSet) {
    table = $('#tableSidang').DataTable({
        "data": dataSet, 
        "pageLength": 10, 
        "lengthChange": false, 
        "ordering": false, 
        "destroy": true, 
        "searching": true,
        "responsive": true, 
        "dom": '<"row mb-2"<"col-12 d-flex justify-content-end"f>>rt<"row"<"col-md-6"i><"col-md-6"p>>',
        "language": {
            "search": "", 
            "searchPlaceholder": "Cari Data Terdakwa...",
            "emptyTable": "Tidak ada data.", 
            "zeroRecords": "Data tidak ditemukan."
        },
        "columnDefs": [
            { "targets": [0, 4], "className": "text-center", "orderable": false }, 
            { "targets": 5, "visible": false }
        ]
    });
}

// --- DOCUMENT READY ---
$(document).ready(function() {

    // 1. LOGIKA SINKRONISASI (BLOCK WEEKEND)
    $('#btnSyncData').on('click', function(e) {
        e.preventDefault(); 

        var now = new Date();
        var day = now.getDay(); // 0 = Minggu, 6 = Sabtu

        if (day === 0 || day === 6) {
            alert("MAAF, SINKRONISASI TIDAK BISA DILAKUKAN.\n\nAlasan: Hari ini bukan hari kerja (Sabtu/Minggu).");
            return false; 
        }

        var confirmAction = confirm('Proses ini akan mengambil data terbaru dari Google Sheet.\nLanjutkan Sinkronisasi?');
        if (confirmAction) {

            var $btn = $(this);
            var redirectUrl = $btn.attr('href');

            $btn.addClass('disabled').css('pointer-events','none');
            $btn.html('<i class="fa-solid fa-circle-notch fa-spin me-2"></i> MOHON TUNGGU...');

            setTimeout(function(){
            window.location.href = redirectUrl;

            }, 300);
        }
    });
    
    // 2. AUTO HIDE ALERT
    window.setTimeout(function() {
        $(".alert").fadeTo(500, 0).slideUp(500, function(){ $(this).remove(); });
    }, 10000); 

    // 3. Init
    initDataTable([]);
    var elModal = document.getElementById('modalConfigCetak');
    var myAppModal = new bootstrap.Modal(elModal, { backdrop: 'static', keyboard: false });
    var formToSubmit = null; 

    // 4. EVENT LISTENER
    $('#dateFilter').on('change', function() {
        loadData($(this).val());
        $('#checkAll').prop('checked', false);
    });

    $('#chkP38').on('change', function() {
        if ($(this).is(':checked')) {
            $('#chkP37').prop('checked', false).prop('disabled', true);
        } else {
            $('#chkP37').prop('disabled', false);
        }
        updateBtnState();
    });

    $('#chkP37').on('change', function() {
        if ($(this).is(':checked')) {
            $('#chkP38').prop('checked', false).prop('disabled', true);
        } else {
            $('#chkP38').prop('disabled', false);
        }
        updateBtnState();
    });

    $('#tableSidang tbody').on('change', '.row-checkbox', updateBtnState);

    // UX KLIK ROW
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

    // 5. SUBMIT HANDLER
    $('#formCetak').on('submit', function(e){
        e.preventDefault();

        var isP38 = $('#chkP38').is(':checked');
        var isP37 = $('#chkP37').is(':checked');
        var countChecked = table ? table.rows().nodes().to$().find('.row-checkbox:checked').length : 0;

        if (!isP38 && !isP37) { alert("Pilih jenis dokumen!"); return false; }
        if (isP37 && countChecked === 0) { 
            alert("Untuk P-37, Anda wajib memilih minimal satu data terdakwa!"); 
            return false; 
        }

        if (isP38) {
            $('#blockNomorSurat').show();
            if (countChecked > 0) {
                $('#inputModeCetak').val('seleksi'); 
            } else {
                $('#inputModeCetak').val('full_p38'); 
            }
        } else {
            $('#blockNomorSurat').hide();
            $('#cfgNomorSurat').val('');
            $('#inputModeCetak').val('seleksi'); 
        }

        formToSubmit = this;
        myAppModal.show();
    });

    // 6. FINAL PROSES DI MODAL
    $('#btnFinalCetak').on('click', function() {
        $(formToSubmit).find('.extra-data').remove(); 
        var inputs = [
            { name: 'custom_nomor_surat', val: $('#cfgNomorSurat').val() },
            { name: 'custom_instansi', val: $('#cfgInstansi').val() },
            { name: 'custom_kota', val: $('#cfgKota').val() },
            { name: 'ttd_jabatan', val: $('#cfgJabatan').val() },
            { name: 'ttd_nama', val: $('#cfgNamaPejabat').val() },
            { name: 'ttd_nip', val: $('#cfgNipPejabat').val() }
        ];
        inputs.forEach(item => {
            $('<input>').attr({type: 'hidden', name: item.name, value: item.val, class: 'extra-data'}).appendTo(formToSubmit);
        });

        $(formToSubmit).find('input[type="hidden"][name="pilih_data[]"]').remove();
        if (table) {
             table.rows().nodes().to$().find('.row-checkbox:checked').each(function(){
                $(formToSubmit).append($('<input>').attr('type', 'hidden').attr('name', 'pilih_data[]').val($(this).val()));
            });
        }

        $(this).blur();
        myAppModal.hide();
        $('#modalConfigCetak input').val('');

        setTimeout(() => {
            formToSubmit.submit();
        }, 300);
    });
});