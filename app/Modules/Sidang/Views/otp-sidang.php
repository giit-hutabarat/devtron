<?php $this->extend("layouts/backend"); ?>

<?= $this->section('content'); ?>
<template>
    <v-app>
        <v-card class="mb-5 rounded-xl elevation-4">
            <v-card-title class="text-h6 white--text primary pa-4">
                <v-icon left dark>mdi-key-chain</v-icon> Setup Akses Sidang
            </v-card-title>
            
            <v-card-text class="py-5">
                <h3 class="text-h6 font-weight-medium mb-4 grey--text text--darken-3">Tambah/Setup NIP Baru</h3>
                <v-form ref="form" v-model="valid" lazy-validation>
                    <input type="hidden" name="<?= csrf_token() ?>" value="<?= csrf_hash() ?>" />
                    
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
                        <v-chip :color="item.is_active == 1 ? 'green' : 'red'" dark small class="font-weight-medium">
                            {{ item.is_active == 1 ? 'Aktif' : 'Nonaktif' }}
                        </v-chip>
                    </template>
                    <!-- Aksi Tombol -->
                     <template v-slot:item.actions="{ item }">
                        <v-col cols="auto" class="py-0">
                            <v-btn 
                                icon 
                                small 
                                :color="item.qr_regenerate_count >= 5 ? 'red' : 'primary'"
                                @click="showQrCode(item)" 
                                :loading="loading === item.id"
                                title="Generate/Tampilkan QR Code TOTP"
                                
                                :disabled="item.is_active == 1 || item.qr_regenerate_count >= 5" 
                            >
                                <v-icon small>mdi-qrcode-scan</v-icon>
                            </v-btn>
                        </v-col>
                        
                        <v-col cols="auto" class="py-0">
                            <v-btn 
                                icon 
                                small 
                                :color="item.is_active == 1 ? 'amber darken-2' : 'grey'" 
                                :loading="loading === `toggle-${item.id}`"
                                @click="set2fa(item)" 
                                title="Aktifkan/Nonaktifkan Akses 2FA"
                                
                                :disabled="item.is_active != 1" 
                            >
                                <v-icon small>mdi-toggle-switch</v-icon>
                            </v-btn>
                        </v-col>

                        <v-col cols="auto" class="py-0">
                            <v-btn 
                                icon 
                                small 
                                color="red darken-1" 
                                @click="confirmDelete(item)" 
                                title="Hapus Data NIP Permanen"
                                
                                :disabled="item.is_active == 1" 
                            >
                                <v-icon small>mdi-delete</v-icon>
                            </v-btn>
                        </v-col>
                    </template>
                  
                </v-data-table>
            </v-card-text>
        </v-card>
        <!-- Dialog Hapus -->
        <v-dialog v-model="dialogDelete" max-width="400px">
            <v-card class="rounded-xl">
                <v-card-title class="text-h6 error white--text">
                    <v-icon left dark>mdi-alert-circle</v-icon> Konfirmasi Hapus Data
                </v-card-title>
                <v-card-text class="py-5">
                    Anda yakin ingin menghapus NIP <strong>{{ itemToDelete.nip }}</strong> ({{ itemToDelete.nama_pegawai }})?
                    <br>Aksi ini bersifat permanen!
                </v-card-text>
                <v-card-actions>
                    <v-spacer></v-spacer>
                    <v-btn color="grey" text @click="dialogDelete = false">Batal</v-btn>
                    <v-btn color="error" dark :loading="loading === 'delete'" @click="deleteNip">Hapus Permanen</v-btn>
                </v-card-actions>
            </v-card>
        </v-dialog>
        <!-- StartDialog QR Code -->
                <v-dialog v-model="dialogQr" max-width="500px" 
                scrollable
                :persistent="false" 
                @keydown.esc="closeQrDialog"
                @click:outside="closeQrDialog"
            >
                <v-card class="rounded-xl">
                    
                    <v-card-title class="text-h6 white--text primary pa-4">
                        <v-icon left dark>mdi-qrcode-scan</v-icon> Setup Kunci OTP Pegawai
                        
                        <v-spacer></v-spacer>
                        <v-btn icon dark @click="closeQrDialog" title="Tutup">
                            <v-icon>mdi-close</v-icon> </v-btn>
                    </v-card-title>
                    
                    <v-card-text class="py-5 text-center">
                        <p>NIP: <strong>{{ qrData.nip }}</strong></p>
                        <p>Nama: <strong>{{ qrData.nama_pegawai }}</strong></p>
                        
                        <div class="qr-box" style="border: 2px dashed #007bff; padding: 20px; margin: 20px auto; width: fit-content; border-radius: 8px;">
                            <img :src="qrData.qrCodeImage" alt="QR Code TOTP" style="max-width: 250px; display: block; margin: 10px auto;">
                            <p style="color: green; font-weight: bold; margin-top: 10px;">Scan QR Code di atas!</p>
                        </div>

                        <p style="margin-top: 25px;">Atau gunakan kunci rahasia ini secara **manual**:</p>
                        <div style="font-size: 16px; font-weight: bold; color: #dc3545; border: 1px solid #dc3545; padding: 10px; border-radius: 5px; word-break: break-all; background-color: #f8d7da;">
                            {{ qrData.secretKey }}
                        </div>
                    </v-card-text>
                    
                    </v-card>
                 </v-dialog>
        <!-- Dialog QR Code -->
        
        </v-app>
</template>
<?= $this->endSection(); ?>

<?= $this->section('js'); ?>
<script>
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
        
        // Data untuk fitur Hapus
        dialogDelete: false, // Status dialog konfirmasi
        itemToDelete: {},    // Objek data yang akan dihapus
        
        
        // Data Table
        headers: [
            { text: 'No.', value: 'index', width: '5%', sortable: false },
            { text: 'NIP', value: 'nip', width: '20%' },
            { text: 'NAMA PEGAWAI', value: 'nama_pegawai' },
            { text: 'STATUS', value: 'is_active', align: 'center', width: '15%' },
            { text: 'AKSI', value: 'actions', sortable: false, align: 'center', width: '15%' },
        ],
        
        // Dialog QR Code
        dialogQr: false,
        qrData:{
            nip: '',
            nama_pegawai: '',
            secretKey: '',
            qrCodeImage: '',
        },
        rawListAdmins: [],

    });
    
    // 2. Computed Properties (Untuk Penomoran)
    Object.assign(window.computedVue, {
        indexedListAdmins() { 
            const list = this.rawListAdmins || [];
            return list.map(
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
            axios.get('<?= base_url('setting/admin-sidang/api/admins') ?>')
                .then(res => {
                    if (res.data.status === true) {
                        this.rawListAdmins = res.data.data.map(item => ({
                            ...item,
                            is_active: String(item.is_active)
                        }));
                    } else {
                        this.showSnackbar('Gagal memuat data admins.', 'error');
                        this.rawListAdmins = [];
                    }
                })
                .catch(error => {
                    this.showSnackbar('Error saat memuat data admins.', 'error');
                    console.error("Error loading admins:", error);
                    this.rawListAdmins = [];
                })
                .finally(() => {
                    this.loading = false;
                });
        },

        // Save NIP (Add New Admin)
        saveNip: async function() {
            if (!this.$refs.form.validate()) return;
            this.loading = 'add';

            const tokenName = '<?= csrf_token() ?>';
            const tokenInput = this.$refs.form.$el.querySelector(`input[name="' + tokenName + '"]`);
            if (!tokenInput) {
                this.showSnackbar('Error CSRF : Input token tidak ditemukan di DOM.', 'error');
                this.loading = false;
                return;
            }

            const tokenValue = tokenInput.value;

            try {
                const response = await axios.post('<?= base_url('setting/admin-sidang/api/admins/save') ?>', {
                    [tokenName]: tokenValue,

                     // Data Form
                    nip: this.nip,
                    nama_pegawai: this.namaPegawai,
                });
                
                if (response.data.status === false) {
                     this.showSnackbar(response.data.message, 'error');
                } else {
                     this.showSnackbar(response.data.message, 'success');

                     // Reset Form
                     tokenInput.value = response.data.csrf_hash;
                     
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
                const url = `<?= base_url('setting/admin-sidang/generate') ?>/${item.id}`;
                const response = await axios.get(url);

                const isSuccess = response.data.status === 'true';
                if (isSuccess) {
                    this.showSnackbar(response.data.message, 'success');

                    this.qrData = response.data.data;
                    this.dialogQr = true;
                } else {
                    this.showSnackbar(response.data.message, 'error');
                } 
                
            }catch (error) {
                    let msg = 'Gagal memuat QR Code. ';
                    if (error.response && error.response.data && error.response.data.message) { 
                        msg += error.response.data.message;
                    } 
                    this.showSnackbar(msg, 'error');
                    console.error("Error loading QR Code:", error);
                } finally {
                    this.loading = false;
            } 
        },
        
                closeQrDialog: function() {
                this.dialogQr = false;
                this.loadAdmins(); // Muat ulang data setelah menutup dialog
            },
        // Toggle 2FA Status
        set2fa: async function(item) {

            // 💥 KOREKSI LOGIKA: Jika Nonaktif, jangan lanjutkan (hanya boleh dinonaktifkan jika sudah aktif)
            // Jika status 0 (Nonaktif), kita tidak izinkan toggle dari sini.

            if(item.is_active !== '1') {
                this.showSnackbar('Harap selesaikan proses "Generate QR Code" terlebih dahulu.', 'warning');
                return;
            }

            // Lanjutkan toggle jika statusnya aktif (1)
            this.loading = `toggle-${item.id}`;
            const newStatus = item.is_active === '1' ? '0' : '1';
            
            try {
                const response = await axios.put(`<?= base_url('setting/admin-sidang/api/admins/toggle') ?>/${item.id}`, {
                    is_active: newStatus
            });
            if (response.data.status === true) {
                    this.showSnackbar(response.data.message, 'success');
                    this.loadAdmins(); // Muat ulang data
                } else {
                    this.showSnackbar(response.data.message, 'error');
                }
            }
            catch(error) {
                this.showSnackbar('Gagal mengubah status 2FA.', 'error');
                console.error("Error toggling 2FA:", error);
            } finally {
                this.loading = false;
            }
        },
        
        // --- Metode Baru untuk Hapus ---
        confirmDelete: function(item) {
            this.itemToDelete = item;
            this.dialogDelete = true;
        },

        deleteNip: async function() {
            this.loading = 'delete';
            
            try {
                // URL: /setting/admin-sidang/api/admins/delete/123
                const url = `<?= base_url('setting/admin-sidang/api/admins/delete') ?>/${this.itemToDelete.id}`;
                
                const response = await axios.delete(url);

                if (response.data.status === true) {
                    this.showSnackbar(response.data.message, 'success');
                    this.loadAdmins(); // Muat ulang data
                } else {
                    this.showSnackbar(response.data.message, 'error');
                }
                
            } catch (error) {
                this.showSnackbar('Gagal menghapus data. Cek koneksi server.', 'error');
                console.error("Error deleting NIP:", error);
            } finally {
                this.loading = false;
                this.dialogDelete = false; // Tutup dialog
                this.itemToDelete = {};    // Reset item
            }
        }
    });

 // 4. Created Hook
window.defaultCreatedVue = function() {
    
    axios.defaults.headers.common['X-requested-with'] = 'XMLHttpRequest';
    
    this.loadAdmins();
    
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