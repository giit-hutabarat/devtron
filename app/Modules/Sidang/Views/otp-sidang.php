<?php $this->extend("layouts/backend"); ?>

<?= $this->section('content'); ?>
<template>
    <v-app>
        <v-card class="mb-5 rounded-xl elevation-4">
            <v-card-title class="text-h6 white--text primary pa-4">
                <v-icon left dark>mdi-shield-key</v-icon> Setup Akses & Keamanan Sidang
            </v-card-title>
            
            <v-card-text class="py-5">
                <div class="d-flex align-center mb-4">
                    <v-icon color="primary" class="mr-2">mdi-account-plus</v-icon>
                    <h3 class="text-h6 font-weight-medium grey--text text--darken-3 mb-0">Tambah Pegawai</h3>
                </div>
                
                <v-form ref="form" v-model="valid" lazy-validation @submit.prevent="saveNip">
                    <v-row dense>
                        <v-col cols="12" md="4">
                            <v-text-field
                                v-model="form.nip"
                                label="NIP Pegawai (18 Digit)"
                                :rules="[rules.required, rules.number, rules.nipLength]"
                                outlined dense
                                placeholder="Cth: 1980xxxx..."
                                type="number"
                                counter="18"
                            ></v-text-field>
                        </v-col>
                        <v-col cols="12" md="5">
                            <v-text-field
                                v-model="form.namaPegawai"
                                label="Nama Lengkap"
                                :rules="[rules.required]"
                                outlined dense
                                placeholder="Nama pegawai beserta gelar"
                            ></v-text-field>
                        </v-col>
                        <v-col cols="12" md="3">
                            <v-btn
                                color="success" dark block large
                                :loading="loading === 'add'"
                                type="submit"
                                class="rounded-pill elevation-2"
                            >
                                <v-icon left>mdi-plus-circle</v-icon> Beri Akses
                            </v-btn>
                        </v-col>
                    </v-row>
                </v-form>
            </v-card-text>
            
            <v-divider></v-divider>

            <v-card-text class="pt-5">
                <div class="d-flex align-center mb-2">
                    <v-icon color="primary" class="mr-2">mdi-account-group</v-icon>
                    <h3 class="text-h6 font-weight-medium grey--text text--darken-3 mb-0">Daftar Pegawai Berakses</h3>
                </div>

                <v-data-table
                    :headers="headers"
                    :items="indexedListAdmins" 
                    :items-per-page="10"
                    class="elevation-0"
                    :loading="loading === 'table'"
                    loading-text="Memuat data keamanan..."
                    no-data-text="Belum ada data pegawai."
                >
                    <template v-slot:item.is_active="{ item }">
                        <v-chip 
                            :color="item.is_active == '1' ? 'green lighten-5' : 'red lighten-5'" 
                            :text-color="item.is_active == '1' ? 'green darken-2' : 'red darken-2'"
                            small class="font-weight-bold"
                        >
                            <v-icon left small>{{ item.is_active == '1' ? 'mdi-check-circle' : 'mdi-close-circle' }}</v-icon>
                            {{ item.is_active == '1' ? 'AKTIF' : 'NONAKTIF' }}
                        </v-chip>
                    </template>

                    <template v-slot:item.actions="{ item }">
                        <div class="d-flex justify-center">
                            
                            <v-tooltip bottom>
                                <template v-slot:activator="{ on, attrs }">
                                    <v-btn icon small 
                                        :color="item.qr_regenerate_count > 0 ? 'orange darken-2' : 'primary'"
                                        v-bind="attrs" v-on="on"
                                        @click="confirmGenerateQr(item)" 
                                        :loading="loading === `qr-${item.id}`"
                                        :disabled="item.is_active == '1'" 
                                    >
                                        <v-icon small>mdi-qrcode-scan</v-icon>
                                    </v-btn>
                                </template>
                                <span>{{ item.qr_regenerate_count > 0 ? 'Reset Ulang QR Code' : 'Setup QR Code Baru' }}</span>
                            </v-tooltip>
                            
                            <v-tooltip bottom>
                                <template v-slot:activator="{ on, attrs }">
                                    <v-btn icon small 
                                        :color="item.is_active == '1' ? 'green' : 'grey'" 
                                        v-bind="attrs" v-on="on"
                                        :loading="loading === `toggle-${item.id}`"
                                        @click="set2fa(item)" 
                                        :disabled="item.qr_regenerate_count == 0" 
                                    >
                                        <v-icon small>{{ item.is_active == '1' ? 'mdi-toggle-switch' : 'mdi-toggle-switch-off' }}</v-icon>
                                    </v-btn>
                                </template>
                                <span>{{ item.is_active == '1' ? 'Matikan Akses' : 'Aktifkan Akses' }}</span>
                            </v-tooltip>

                            <v-tooltip bottom>
                                <template v-slot:activator="{ on, attrs }">
                                    <v-btn icon small color="red lighten-1" 
                                        v-bind="attrs" v-on="on"
                                        @click="confirmDelete(item)" 
                                        :disabled="item.is_active == '1'" 
                                    >
                                        <v-icon small>mdi-delete-outline</v-icon>
                                    </v-btn>
                                </template>
                                <span>Hapus Data</span>
                            </v-tooltip>
                        </div>
                    </template>
                </v-data-table>
            </v-card-text>
        </v-card>

        <v-dialog v-model="dialogConfirmQr" max-width="450px">
            <v-card class="rounded-xl">
                <v-card-title class="text-h6 orange white--text">
                    <v-icon left dark>mdi-alert</v-icon> Peringatan Keamanan
                </v-card-title>
                <v-card-text class="py-5">
                    Pegawai <strong>{{ itemToRegenerate.nama_pegawai }}</strong> sudah memiliki QR Code.<br><br>
                    Membuat QR Code baru akan <strong>MEMBATALKAN</strong> kode lama. Pegawai harus scan ulang di aplikasi Authenticator mereka.
                </v-card-text>
                <v-card-actions class="pb-4 justify-end pr-4">
                    <v-btn color="grey darken-1" text @click="dialogConfirmQr = false">Batal</v-btn>
                    <v-btn color="orange darken-2" dark elevation="2" @click="processGenerateQr">Buat Baru & Batalkan Lama</v-btn>
                </v-card-actions>
            </v-card>
        </v-dialog>

        <v-dialog v-model="dialogDelete" max-width="400px">
            <v-card class="rounded-xl">
                <v-card-title class="text-h6 error white--text">
                    <v-icon left dark>mdi-delete-alert</v-icon> Hapus Akses
                </v-card-title>
                <v-card-text class="py-5 text-center">
                    Yakin ingin menghapus akses NIP:<br>
                    <strong class="text-h6">{{ itemToDelete.nip }}</strong><br>
                    ({{ itemToDelete.nama_pegawai }})?
                </v-card-text>
                <v-card-actions class="pb-4 justify-center">
                    <v-btn color="grey" text @click="dialogDelete = false">Batal</v-btn>
                    <v-btn color="error" elevation="2" :loading="loading === 'delete'" @click="deleteNip">Hapus Permanen</v-btn>
                </v-card-actions>
            </v-card>
        </v-dialog>

        <v-dialog v-model="dialogQr" max-width="500px" scrollable @keydown.esc="closeQrDialog">
            <v-card class="rounded-xl">
                <v-card-title class="text-h6 white--text primary pa-4">
                    <v-icon left dark>mdi-cellphone-key</v-icon> Setup 2FA Authenticator
                    <v-spacer></v-spacer>
                    <v-btn icon dark @click="closeQrDialog"><v-icon>mdi-close</v-icon></v-btn>
                </v-card-title>
                
                <v-card-text class="py-5 text-center">
                    <div class="subtitle-1 mb-1">NIP: <strong>{{ qrData.nip }}</strong></div>
                    <div class="subtitle-2 grey--text">{{ qrData.nama_pegawai }}</div>
                    
                    <div class="d-flex justify-center my-4">
                        <div class="qr-container pa-3 white elevation-2 rounded-lg">
                            <img :src="qrData.qrCodeImage" alt="QR Code" style="width: 220px; height: 220px; display:block;">
                        </div>
                    </div>
                    
                    <v-alert type="info" dense text border="left" class="text-left caption mb-4">
                        1. Buka aplikasi <strong>Google Authenticator</strong> di HP.<br>
                        2. Pilih menu <strong>Scan QR Code</strong> dan arahkan ke gambar di atas.
                    </v-alert>

                    <v-divider class="mb-3"></v-divider>
                    
                    <div class="d-flex align-center justify-center">
                        <span class="caption grey--text mr-2">Kode Rahasia Manual:</span>
                        <v-btn icon small @click="showSecret = !showSecret">
                            <v-icon small>{{ showSecret ? 'mdi-eye-off' : 'mdi-eye' }}</v-icon>
                        </v-btn>
                    </div>
                    
                    <div v-if="showSecret" class="d-block pa-2 red lighten-5 red--text text--darken-3 font-weight-bold text-h6 rounded mt-1 letter-spacing-2">
                        {{ qrData.secretKey }}
                    </div>
                    <div v-else class="d-block pa-2 grey lighten-4 grey--text font-weight-bold text-h6 rounded mt-1">
                        •••• •••• •••• ••••
                    </div>

                </v-card-text>
                <v-card-actions class="justify-center pb-5">
                    <v-btn color="primary" block large @click="closeQrDialog">Selesai & Tutup</v-btn>
                </v-card-actions>
            </v-card>
        </v-dialog>

        <v-snackbar v-model="snackbar" :color="snackbarType" :timeout="timeout" top right>
            <v-icon left>mdi-bell-ring</v-icon> {{ snackbarMessage }}
            <template v-slot:action="{ attrs }">
                <v-btn text v-bind="attrs" @click="snackbar = false">Tutup</v-btn>
            </template>
        </v-snackbar>
    </v-app>
</template>
<?= $this->endSection(); ?>

<?= $this->section('js'); ?>

<script>
    const appConfig = Object.freeze({
        csrfTokenName: '<?= csrf_token() ?>',
        csrfHash: '<?= csrf_hash() ?>',
        apiUrls: {
            list: '<?= base_url('setting/admin-sidang/api/admins') ?>',
            save: '<?= base_url('setting/admin-sidang/api/admins/save') ?>',
            generateQr: '<?= base_url('setting/admin-sidang/generate') ?>', 
            toggle2fa: '<?= base_url('setting/admin-sidang/api/admins/toggle') ?>', 
            delete: '<?= base_url('setting/admin-sidang/api/admins/delete') ?>', 
        },
        flashSuccess: '<?= esc(session()->getFlashdata('success') ?? '', 'js') ?>',
        flashError: '<?= esc(session()->getFlashdata('error') ?? '', 'js') ?>'
    });
</script>

<script src="<?= base_url('assets/js/otp-sidang.js') ?>"></script>

<?= $this->endSection(); ?>                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                       