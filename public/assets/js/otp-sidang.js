/**
 * OTP SIDANG LOGIC
 * Clean Code, Separation of Concerns, Enhanced Security UI
 */

// --- 1. AXIOS CSRF INTERCEPTOR GLOBAL ---
axios.interceptors.response.use(
    (response) => {
        if (response.data && response.data.csrf_hash) {
            const newHash = response.data.csrf_hash;
            if (typeof appConfig !== 'undefined') appConfig.csrfHash = newHash;
            
            // Update global data store
            if (window.dataVue) window.dataVue.currentCsrfHash = newHash;
            
            // KRUSIAL: Jika instance Vue lo namanya 'app', kita paksa update di sana
            if (typeof app !== 'undefined' && app.currentCsrfHash !== undefined) {
                app.currentCsrfHash = newHash;
            }
        }
        return response;
    },
    (error) => {
        if (error.response && error.response.data && error.response.data.csrf_hash) {
            const newHash = error.response.data.csrf_hash;
            if (typeof appConfig !== 'undefined') appConfig.csrfHash = newHash;
            if (window.dataVue) window.dataVue.currentCsrfHash = newHash;
            
            if (typeof app !== 'undefined' && app.currentCsrfHash !== undefined) {
                app.currentCsrfHash = newHash;
            }
        }
        return Promise.reject(error);
    }
);

(function() {
    
    // Pastikan Vue instance global tersedia
    window.dataVue = window.dataVue || {}; 
    window.methodsVue = window.methodsVue || {}; 
    window.computedVue = window.computedVue || {}; 

    // --- STATE MANAGEMENT ---
    Object.assign(window.dataVue, {
        // UI & Loading State
        snackbar: false,
        timeout: 4000, 
        snackbarType: 'success',
        snackbarMessage: '',
        valid: true,
        loading: false, 
        
        // Input Form
        form: {
            nip: '',
            namaPegawai: ''
        },
        
        // Dialog States
        dialogDelete: false, 
        dialogQr: false,
        dialogConfirmQr: false, // Security: Dialog konfirmasi reset QR
        showSecret: false,      // Security: Toggle intip kode rahasia
        
        // Temp Data Holders
        itemToDelete: {},    
        itemToRegenerate: {},

        // Data Table
        search: '',
        headers: [
            { text: 'No.', value: 'index', width: '5%', sortable: false, align: 'center' },
            { text: 'NIP', value: 'nip', width: '20%' },
            { text: 'NAMA PEGAWAI', value: 'nama_pegawai' },
            { text: 'STATUS', value: 'is_active', align: 'center', width: '15%' },
            { text: 'AKSI', value: 'actions', sortable: false, align: 'center', width: '15%' },
        ],
        rawListAdmins: [],
        
        // QR Data
        qrData: {
            nip: '',
            nama_pegawai: '',
            secretKey: '',
            qrCodeImage: '',
        },
        
        // Rules Validasi
        rules: {
            required: v => !!v || 'Harus diisi',
            number: v => !isNaN(v) || 'Harus angka',
            nipLength: v => (v && v.length === 18) || 'NIP harus 18 digit',
        },

        // CSRF Store
        currentCsrfHash: (typeof appConfig !== 'undefined') ? appConfig.csrfHash : ''
    });
    
    // --- COMPUTED PROPERTIES ---
    Object.assign(window.computedVue, {
        indexedListAdmins() { 
            return (this.rawListAdmins || []).map((item, index) => ({
                ...item,
                index: index + 1
            }));
        },
    });

    // --- METHODS ---
    Object.assign(window.methodsVue, {
        
        showSnackbar(message, type = 'success') {
            this.snackbarMessage = message;
            this.snackbarType = type;
            this.snackbar = true;
        },

        // Security Helper: Sync CSRF Token agar request berikutnya tidak 403 Forbidden
        updateCsrf(newHash) {
            if (newHash) {
                this.currentCsrfHash = newHash;
            }
        },

        // 1. LOAD DATA
        loadAdmins() {
            this.loading = 'table';
            axios.get(appConfig.apiUrls.list)
                .then(res => {
                    if (res.data.status === true) {
                        this.rawListAdmins = res.data.data.map(item => ({
                            ...item,
                            is_active: String(item.is_active),
                            // Pastikan count di-cast ke number
                            qr_regenerate_count: Number(item.qr_regenerate_count || 0) 
                        }));
                    } else {
                        this.showSnackbar('Gagal memuat data admins.', 'error');
                        this.rawListAdmins = [];
                    }
                })
                .catch(err => {
                    this.showSnackbar('Gagal koneksi server.', 'error');
                    console.error(err);
                })
                .finally(() => this.loading = false);
        },

        // 2. SAVE (Create) - FIXED & RESTORED
        async saveNip() {
            if (!this.$refs.form.validate() || this.loading) return; 
            this.loading = 'add'; // Set loading segera

            // Gunakan URLSearchParams agar format data stabil di CI4
            const payload = new URLSearchParams();
            payload.append('nip', this.form.nip);
            payload.append('nama_pegawai', this.form.namaPegawai);
            // Pakai currentCsrfHash sesuai variabel di script lo
            payload.append(appConfig.csrfTokenName, this.currentCsrfHash); 

            try {
                const response = await axios.post(appConfig.apiUrls.save, payload);
                
                // Token sudah diupdate otomatis oleh Interceptor, lo fokus ke UI
                    if (response.data.status === true) {
                        this.showSnackbar(response.data.message, 'success');
                        this.form.nip = '';
                        this.form.namaPegawai = '';
                        this.$refs.form.resetValidation();
                        this.loadAdmins(); // Refresh data tabel
                    } else {
                        this.showSnackbar(response.data.message, 'error');
                    }
            } catch (error) {
                // Jika error 400 (Duplikat) tapi data sudah masuk, beri pesan lebih bersahabat
                let msg = error.response?.data?.message || 'Terjadi kesalahan sistem.';
                this.showSnackbar(msg, 'error');
                
                // Refresh tabel tetap dilakukan karena ada kemungkinan request pertama sukses tapi lo dapet error dari request kedua
                this.loadAdmins(); 
                
                console.error("Error Log:", error);
        } finally {
            // Kembalikan status loading ke false agar tombol bisa diklik lagi
                this.loading = false;
                
            }
        },

        // 3. GENERATE QR (Dengan Logika Keamanan Konfirmasi)
        confirmGenerateQr(item) {
            // Jika belum pernah generate, langsung proses
            if (item.qr_regenerate_count === 0) {
                this.itemToRegenerate = item;
                this.processGenerateQr();
            } else {
                // Jika sudah pernah, minta konfirmasi dulu (Destructive Action)
                this.itemToRegenerate = item;
                this.dialogConfirmQr = true;
            }
        },

        async processGenerateQr() {
            const item = this.itemToRegenerate;
            this.dialogConfirmQr = false; // Tutup dialog konfirmasi jika ada
            this.loading = `qr-${item.id}`;
            
            try {
                const url = `${appConfig.apiUrls.generateQr}/${item.id}`;
                const response = await axios.get(url);

                // Cek status string 'true' dari backend
                if (String(response.data.status) === 'true') { 
                    this.showSnackbar(response.data.message, 'success');
                    this.qrData = response.data.data;
                    this.showSecret = false; // Reset intip password agar tertutup lagi
                    this.dialogQr = true;
                } else {
                    this.showSnackbar(response.data.message, 'error');
                }
            } catch (error) {
                this.showSnackbar('Gagal generate QR Code.', 'error');
            } finally {
                this.loading = false;
            } 
        },
        
        closeQrDialog() {
            this.dialogQr = false;
            this.loadAdmins(); // Refresh agar counter qr_regenerate_count terupdate
        },

        // 4. TOGGLE ACTIVE (Update)
        async set2fa(item) {
            // Validasi Frontend: Jangan boleh aktifkan jika belum punya QR
            if (item.qr_regenerate_count === 0) {
                this.showSnackbar('Setup QR Code terlebih dahulu!', 'warning');
                return;
            }

            this.loading = `toggle-${item.id}`;
            const newStatus = String(item.is_active) === '1' ? '0' : '1';
            
            try {
                const url = `${appConfig.apiUrls.toggle2fa}/${item.id}`;
                const payload = { 
                    is_active: newStatus,
                    [appConfig.csrfTokenName]: this.currentCsrfHash 
                };

                const response = await axios.put(url, payload);
                if(response.data.csrf_hash) this.updateCsrf(response.data.csrf_hash);

                if (response.data.status === true) {
                    const statusText = newStatus === '1' ? 'Aktif' : 'Nonaktif';
                    this.showSnackbar(`Akses berhasil diubah menjadi: ${statusText}`, 'success');
                    this.loadAdmins();
                } else {
                    this.showSnackbar(response.data.message, 'error');
                }
            } catch(error) {
                this.showSnackbar('Gagal mengubah status akses.', 'error');
            } finally {
                this.loading = false;
            }
        },
        
        // 5. DELETE
        confirmDelete(item) {
            this.itemToDelete = item;
            this.dialogDelete = true;
        },

        async deleteNip() {
            this.loading = 'delete';
            try {
                const url = `${appConfig.apiUrls.delete}/${this.itemToDelete.id}`;
                // Kirim CSRF lewat body 'data'
                const response = await axios.delete(url, {
                    data: {
                        [appConfig.csrfTokenName]: this.currentCsrfHash
                    }
                });
                
                if(response.data.csrf_hash) this.updateCsrf(response.data.csrf_hash);

                if (response.data.status === true) {
                    this.showSnackbar('Akses pegawai berhasil dihapus.', 'success');
                    this.loadAdmins();
                } else {
                    this.showSnackbar(response.data.message, 'error');
                }
            } catch (error) {
                this.showSnackbar('Gagal menghapus data.', 'error');
            } finally {
                this.loading = false;
                this.dialogDelete = false;
                this.itemToDelete = {};
            }
        }
    });

    // --- INITIALIZATION ---
    window.defaultCreatedVue = function() {
        axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
        
        this.loadAdmins();
        
        if (appConfig.flashSuccess) this.showSnackbar(appConfig.flashSuccess, 'success');
        if (appConfig.flashError)   this.showSnackbar(appConfig.flashError, 'error');
    };

})();