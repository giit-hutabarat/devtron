<?php $this->extend("\\App\\Views\\layouts\\backend"); ?>

<?= $this->section('content'); ?>
<template>
    <v-app>
        <!-- Card Utama Setup NIP -->
        <v-card class="mb-5 rounded-xl elevation-4">
            <v-card-title class="text-h6 white--text primary pa-4">
                <v-icon left dark>mdi-key-chain</v-icon> Setup NIP Pegawai (Akses Sidang)
            </v-card-title>
            
            <!-- Form Tambah NIP -->
            <v-card-text class="py-5">
                <h3 class="text-h6 font-weight-medium mb-4 grey--text text--darken-3">Tambah/Setup NIP Baru</h3>
                <v-form ref="form" v-model="valid" lazy-validation>
                    <!-- CSRF Token (Tetap dipertahankan) -->
                    <input type="hidden" name="<?= csrf_token() ?>" value="<?= csrf_hash() ?>" />
                    
                    <!-- 💥 Responsiveness Form -->
                    <v-row dense>
                        <v-col cols="12" md="4">
                            <v-text-field
                                v-model="nip"
                                label="NIP Pegawai"
                                :rules="[rules.required, rules.number]"
                                outlined
                                dense
                            ></v-text-field>
                        </v-col>
                        <v-col cols="12" md="5">
                            <v-text-field
                                v-model="namaPegawai"
                                label="Nama Pegawai"
                                :rules="[rules.required]"
                                outlined
                                dense
                            ></v-text-field>
                        </v-col>
                        <v-col cols="12" md="3">
                            <v-btn
                                color="success"
                                dark
                                block
                                large
                                :loading="loading === 'add'"
                                @click="saveNip"
                                class="rounded-pill"
                            >
                                <v-icon left>mdi-plus</v-icon> Tambah NIP
                            </v-btn>
                        </v-col>
                    </v-row>
                </v-form>
            </v-card-text>
            
            <!-- Tabel Data NIP -->
            <v-card-text class="pt-0">
                <v-data-table
                    :headers="headers"
                    :items="indexedListAdmins" 
                    :items-per-page="10"
                    class="elevation-0"
                    :loading="loading === 'table'"
                    loading-text="Memuat data..."
                >
                    <template v-slot:item.is_active="{ item }">
                        <!-- Chip Status Aktif -->
                        <v-chip :color="item.is_active == 1 ? 'green' : 'red'" dark small class="font-weight-medium">
                            {{ item.is_active == 1 ? 'Aktif' : 'Nonaktif' }}
                        </v-chip>
                    </template>
                    
                    <template v-slot:item.actions="{ item }">
                        <!-- Tombol Show QR Code -->
                        <v-btn 
                            icon 
                            small 
                            class="mr-2" 
                            color="blue" 
                            @click="showQrCode(item)" 
                            :loading="loading === item.id"
                            title="Tampilkan QR Code"
                        >
                            <v-icon small>mdi-qrcode</v-icon>
                        </v-btn>
                        
                        <!-- Tombol Toggle 2FA -->
                        <v-btn 
                            icon 
                            small 
                            color="orange" 
                            :loading="loading === `toggle-${item.id}`"
                            @click="set2fa(item)" 
                            title="Toggle Status 2FA"
                        >
                            <v-icon small>mdi-toggle-switch</v-icon>
                        </v-btn>
                    </template>
                </v-data-table>
            </v-card-text>
        </v-card>
    </v-app>
</template>
<?= $this->endSection(); ?>

<?= $this->section('js'); ?>
<script>
    // 💥 KOREKSI Wajib: Amankan objek global sebelum merging
    window.dataVue = window.dataVue || {}; 
    window.methodsVue = window.methodsVue || {}; 
    window.computedVue = window.computedVue || {}; 
    
    // 1. Menggabungkan Data Spesifik Halaman ke window.dataVue
    Object.assign(window.dataVue, {
        // --- Properti Default Layout yang WAJIB ada ---
        snackbar: false,
        timeout: 4000, 
        snackbarType: 'success',
        snackbarMessage: '',
        valid: true,
        
        // Data Form & Table
        nip: '',
        namaPegawai: '',
        loading: false, // Loading state utama
        search: '',
        
        // Data Table
        headers: [
            { text: 'No.', value: 'index', width: '5%', sortable: false },
            { text: 'NIP', value: 'nip', width: '20%' },
            { text: 'NAMA PEGAWAI', value: 'nama_pegawai' },
            { text: 'STATUS', value: 'is_active', align: 'center', width: '15%' },
            { text: 'AKSI', value: 'actions', sortable: false, align: 'center', width: '15%' },
        ],
        // Data dari PHP
        rawListAdmins: <?= json_encode($list_admins ?? []) ?> || [], // ✅ Data property mentah
    });
    
    // 2. Computed Properties (Untuk Penomoran)
    Object.assign(window.computedVue, {
        // ✅ FIX REKURSI: Gunakan nama yang berbeda dari data property
        indexedListAdmins() { 
            return this.rawListAdmins.map( // Mengolah rawListAdmins (data property)
                (items, index) => ({
                    ...items,
                    index: index + 1
                }))
        },
    });


    // 3. Menggabungkan Methods Spesifik Halaman ke window.methodsVue
    Object.assign(window.methodsVue, {
        showSnackbar: function(message, type = 'success') {
            this.snackbarMessage = message;
            this.snackbarType = type;
            this.snackbar = true;
        },

        // Get Data
        loadAdmins: function() {
            this.loading = 'table';
            axios.get('<?= base_url('api/sidang/admins') ?>')
                .then(res => {
                    if (res.data.status === true) {
                        // Simpan data di properti data mentah
                        this.rawListAdmins = res.data.data.map(item => ({
                            ...item,
                            is_active: String(item.is_active)
                        }));
                    } else {
                        this.showSnackbar('Gagal memuat data admins.', 'error');
                    }
                })
                .catch(error => {
                    this.showSnackbar('Error saat memuat data admins.', 'error');
                    console.error("Error loading admins:", error);
                })
                .finally(() => {
                    this.loading = false;
                });
        },

        // Save NIP (Add New Admin)
        saveNip: async function() {
            if (!this.$refs.form.validate()) return;
            this.loading = 'add';

            try {
                const response = await axios.post('<?= base_url('api/sidang/admins/save') ?>', {
                    nip: this.nip,
                    nama_pegawai: this.namaPegawai,
                });
                
                if (response.data.status === false) {
                     this.showSnackbar(response.data.message, 'error');
                } else {
                     this.showSnackbar(response.data.message, 'success');
                     this.nip = '';
                     this.namaPegawai = '';
                     this.$refs.form.resetValidation();
                     this.$refs.form.reset();
                     this.loadAdmins(); // Refresh data
                }
            } catch (error) {
                this.showSnackbar('Gagal menyimpan data NIP. Cek koneksi server/database.', 'error');
                console.error("Error saving NIP:", error);
            } finally {
                this.loading = false;
            }
        },

        // Show QR Code (Redirect)
        showQrCode: async function(item) {
            this.loading = item.id; 
            
            try {
                // Redirect ke URL Generate
                const url = `<?= base_url('setting/otp-sidang/generate') ?>/${item.id}`;
                window.location.href = url; 

            } catch (error) {
                this.showSnackbar('Gagal memproses QR Code.', 'error');
            } finally {
                this.loading = false;
            }
        },
        
        // Toggle 2FA Status
        set2fa: async function(item) {
            this.loading = `toggle-${item.id}`;
            const newStatus = item.is_active === '1' ? '0' : '1';
            
            try {
                const response = await axios.put(`<?= base_url('api/sidang/admins/toggle') ?>/${item.id}`, {
                    is_active: newStatus
                });
                
                if (response.data.status === true) {
                    this.showSnackbar(response.data.message, 'success');
                    // Update status di frontend tanpa reload
                    item.is_active = newStatus;
                } else {
                    this.showSnackbar(response.data.message, 'error');
                }
                
            } catch(error) {
                this.showSnackbar('Gagal mengubah status 2FA.', 'error');
                console.error("Error toggling 2FA:", error);
            } finally {
                this.loading = false;
            }
        }
    });

    // 4. Created Hook
    window.defaultCreatedVue = function() {
        // 💥 KOREKSI UTAMA: Hapus semua logika rekursif di sini
        
        // Logic utama
        this.loadAdmins();
        axios.defaults.headers.common['X-requested-with'] = 'XMLHttpRequest';
        // Ambil flashdata dari PHP dan tampilkan
        <?php if (session()->getFlashdata('success')): ?>
            this.showSnackbar('<?= esc(session()->getFlashdata('success'), 'js') ?>', 'success');
        <?php endif; ?>
        <?php if (session()->getFlashdata('error')): ?>
            this.showSnackbar('<?= esc(session()->getFlashdata('error'), 'js') ?>', 'error');
        <?php endif; ?>
        console.log("OTP Setup View: Data Loaded");
    };
</script>
<?= $this->endSection(); ?>