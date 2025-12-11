<?php $this->extend("layouts/backend"); ?>
<?php $this->section("content"); ?>
<template>
    <!-- Card Kontainer Utama -->
    <v-card class="rounded-xl elevation-4">
        
        <!-- Header Halaman -->
        <v-card-title class="pa-4 grey lighten-5">
            <v-icon left color="indigo">mdi-database</v-icon>
            <h1 class="font-weight-bold text-h5 grey--text text--darken-3"><?= $title ?></h1>
        </v-card-title>
        
        <!-- Toolbar Responsif -->
        <v-card-text class="pa-4">
            <v-row class="mb-2">
                <!-- Kolom Button Backup Now -->
                <v-col cols="12" md="4" class="py-0">
                    <v-btn 
                        large 
                        color="primary" 
                        dark 
                        @click="saveBackup" 
                        elevation="2"
                        class="rounded-pill"
                        block
                        :loading="loading"
                    >
                        <v-icon left>mdi-database-plus</v-icon> Backup Now
                    </v-btn>
                </v-col>
                
                <!-- Kolom Search Field -->
                <v-col cols="12" md="8" class="py-0">
                    <v-text-field 
                        v-model="search" 
                        append-icon="mdi-magnify" 
                        label="Cari File atau Tanggal..." 
                        single-line 
                        hide-details 
                        outlined 
                        dense 
                        class="mt-2"
                        clearable
                    >
                    </v-text-field>
                </v-col>
            </v-row>
        </v-card-text>
        
        <!-- Data Table (Responsive & Rapi) -->
        <v-data-table 
            :headers="dataTable" 
            :items="dataBackup" 
            :items-per-page="10" 
            :loading="loading" 
            :search="search" 
            class="elevation-0 px-4 pb-4 my-4 pt-0" 
            loading-text="Sedang memuat... Harap tunggu"
        >
            <template v-slot:item.id="{ item, index }">
                {{ index + 1 }} 
            </template>
            <template v-slot:item.created_at="{ item }">
                <v-chip small color="blue-grey lighten-5" class="font-weight-medium">{{ item.created_at }}</v-chip>
            </template>
            
            <!-- Custom Slot untuk Kolom 'Aksi' (value: actions) -->
            <template v-slot:item.actions="{ item }">
                <v-btn color="success" @click="downloadItem(item)" small icon title="Download File">
                    <v-icon small>mdi-download</v-icon>
                </v-btn>
                <v-btn color="error" @click="deleteItem(item)" small icon title="Hapus File">
                    <v-icon small>mdi-delete-empty-outline</v-icon>
                </v-btn>
            </template>
            
        </v-data-table>
    </v-card>
    
    <!-- Modal Delete -->
    <template>
        <v-row justify="center">
            <v-dialog v-model="modalDelete" persistent max-width="450px">
                <v-card class="rounded-xl pa-2">
                    <v-card-title>
                        <v-icon color="error" class="mr-2" x-large>mdi-alert-octagon</v-icon> Konfirmasi Hapus
                    </v-card-title>
                    <v-card-text>
                        <div class="mt-2">
                            <h3 class="font-weight-regular">Yakin hapus file backup ini? Tindakan ini tidak bisa dibatalkan.</h3>
                        </div>
                    </v-card-text>
                    <v-card-actions>
                        <v-spacer></v-spacer>
                        <v-btn large text @click="modalDelete = false">Batal</v-btn>
                        <v-btn large color="red darken-2" dark @click="deleteData" :loading="loading">Ya, Hapus</v-btn>
                    </v-card-actions>
                </v-card>
            </v-dialog>
        </v-row>
    </template>
    <!-- End Modal Delete -->

    <v-dialog v-model="loading2" hide-overlay persistent width="300">
        <v-card class="rounded-lg">
            <v-card-text class="pt-3">
                Memuat, silahkan tunggu...
                <v-progress-linear indeterminate color="primary" class="mb-0"></v-progress-linear>
            </v-card-text>
        </v-card>
    </v-dialog>
</template>
<?php $this->endSection("content") ?>

<?php $this->section("js") ?>
<script>
    // Helper function to convert Base64 to Blob (dipertahankan)
    function b64toBlob(b64Data, contentType, sliceSize) {
        contentType = contentType || '';
        sliceSize = sliceSize || 512;

        var byteCharacters = atob(b64Data);
        var byteArrays = [];

        for (var offset = 0; offset < byteCharacters.length; offset += sliceSize) {
            var slice = byteCharacters.slice(offset, offset + sliceSize);

            var byteNumbers = new Array(slice.length);
            for (var i = 0; i < slice.length; i++) {
                byteNumbers[i] = slice.charCodeAt(i);
            }

            var byteArray = new Uint8Array(byteNumbers);

            byteArrays.push(byteArray);
        }

        var blob = new Blob(byteArrays, {
            type: contentType
        });
        return blob;
    }

    // 💥 KOREKSI WAJIB: Amankan objek global sebelum merging
    window.dataVue = window.dataVue || {};
    window.methodsVue = window.methodsVue || {};
    window.computedVue = window.computedVue || {};

    // 1. Gabungkan Data Spesifik Backup ke window.dataVue
    Object.assign(window.dataVue, {
        // --- Properti Default Layout yang WAJIB ada (Anti-ReferenceError) ---
        snackbar: false,
        timeout: 4000, 
        snackbarType: '',
        snackbarMessage: '',
        valid: true, 
        
        // --- Properti Spesifik View Backup ---
        modalDelete: false,
        dataBackup: [],
        idBackup: "",
        search: "",
        loading: false, // Loading utama
        loading2: false, // Loading untuk download/proses
        
        dataTable: [{
                text: 'No.', // Diubah dari 'ID'
                value: 'id',
                width: '5%',
                sortable: false
            }, {
                text: 'File Name',
                value: 'file_name'
            },
            {
                text: 'File Path',
                value: 'file_path'
            },
            {
                text: 'Tanggal',
                value: 'created_at',
                width: '20%'
            },
            {
                text: 'Aksi',
                value: 'actions',
                sortable: false,
                width: '15%'
            },
        ],
    });

    // 2. Gabungkan Methods Spesifik Backup ke window.methodsVue
    Object.assign(window.methodsVue, {
        // Method helper untuk menampilkan Snackbar
        showSnackbar: function(message, type = 'success') {
            this.snackbarMessage = message;
            this.snackbarType = type;
            this.snackbar = true;
        },

        // Get Data
        getBackup: function() {
            this.loading = true;
            axios.get('<?= base_url() ?>/api/backup')
                .then(res => {
                    this.loading = false;
                    var data = res.data;
                    if (data.status == true) {
                        this.dataBackup = data.data || [];
                    } else {
                        this.showSnackbar(data.message, 'error');
                        this.dataBackup = [];
                    }
                })
                .catch(err => {
                    console.error("Error fetching backup list:", err.response);
                    this.showSnackbar('Gagal memuat daftar backup.', 'error');
                    this.loading = false;
                    this.dataBackup = [];
                })
        },

        // Save Data (Backup Now)
        saveBackup: function() {
            this.loading = true;
            axios.post(`<?= base_url() ?>/api/backup/save`, {})
                .then(res => {
                    this.loading = false
                    var data = res.data;
                    if (data.status == true) {
                        this.showSnackbar(data.message, 'success');
                        this.getBackup();
                    } else {
                        this.showSnackbar(data.message, 'error');
                    }
                })
                .catch(err => {
                    console.error("Error creating backup:", err.response);
                    this.showSnackbar('Gagal membuat backup. Cek logs server.', 'error');
                    this.loading = false;
                })
        },

        // Download
        downloadItem: function(item) {
            this.loading2 = true;
            axios.post(`<?= base_url()?>/api/backup/download`, {
                    id: item.id
                })
                .then(res => {
                    this.loading2 = false
                    var data = res.data;
                    if (data.status == true) {
                        this.showSnackbar(data.message, 'success');
                        // LANGSUNG GUNAKAN WINDOW LOCATION UNTUK DOWNLOAD FILE
                        window.location.href = data.data.url;
                    } else {
                        this.showSnackbar(data.message, 'error');
                    }
                })
                .catch(err => {
                    console.error("Error downloading file:", err.response);
                    this.showSnackbar('Gagal download file.', 'error');
                    this.loading2 = false;
                })
        },


        // Get Item Delete
        deleteItem: function(item) {
            this.modalDelete = true;
            this.idBackup = item.id;
        },

        // Delete
        deleteData: function() {
            this.loading = true;
            axios.delete(`<?= base_url() ?>/api/backup/delete/${this.idBackup}`)
                .then(res => {
                    this.loading = false;
                    var data = res.data;
                    if (data.status == true) {
                        this.showSnackbar(data.message, 'success');
                        this.getBackup();
                        this.modalDelete = false;
                    } else {
                        this.showSnackbar(data.message, 'error');
                    }
                })
                .catch(err => {
                    console.error("Error deleting backup:", err.response);
                    this.showSnackbar('Gagal menghapus file backup.', 'error');
                    this.loading = false;
                })
        },
    });

    // 3. Created Hook
    window.createdVue = function() {
        // Panggil created hook default dari layout jika ada
        if (typeof window.defaultCreatedVue === 'function') {
            window.defaultCreatedVue.call(this);
        }
        this.getBackup();
        console.log("Backup View: Data Loaded");
    };
</script>
<?php $this->endSection("js") ?>