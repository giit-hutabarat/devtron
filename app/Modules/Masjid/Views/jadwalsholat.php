<?php $this->extend("layouts/backend"); ?>
<?php $this->section("content"); ?>
<template>
    <!-- Card Kontainer Utama -->
    <v-card class="rounded-xl elevation-4">
        
        <!-- Header Halaman -->
        <v-card-title class="pa-4 grey lighten-5">
            <v-icon left color="indigo">mdi-clock-check-outline</v-icon>
            <h1 class="font-weight-bold text-h5 grey--text text--darken-3"><?= $title; ?></h1>
        </v-card-title>
        
        <!-- Toolbar Responsif (Filter Bulan dan Import) -->
        <v-card-text class="pa-4">
            <v-row class="mb-2">
                <!-- Kolom Button Import -->
                <v-col cols="12" md="4" class="py-0">
                    <v-btn 
                        color="success" 
                        dark 
                        large 
                        @click="modalAddOpen" 
                        elevation="2" 
                        class="rounded-pill"
                        block
                    >
                        <v-icon left>mdi-file-excel</v-icon> Import Jadwal
                    </v-btn>
                </v-col>
                
                <!-- Kolom Filter Bulan -->
                <v-col cols="12" md="3" class="py-0">
                    <v-select 
                        v-model="idBulan" 
                        label="Filter Bulan" 
                        :items="dataBulan" 
                        item-text="text" 
                        item-value="value" 
                        outlined 
                        dense 
                        hide-details 
                        class="mt-2"
                        @change="getJadwalsholat"
                        single-line
                    ></v-select>
                </v-col>
                
                <!-- Kolom Search Field -->
                <v-col cols="12" md="5" class="py-0">
                    <v-text-field 
                        v-model="pencarian" 
                        append-icon="mdi-magnify" 
                        label="Cari Tanggal..." 
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
            :headers="dataHeader" 
            :items="dataJadwalsholat" 
            :items-per-page="10" 
            :loading="loading" 
            :search="pencarian" 
            class="elevation-0 px-4 pb-4 my-4 pt-0" 
            loading-text="<?= lang('App.loadingWait'); ?>"
        >
            <!-- 💥 KOREKSI: Slot item.id untuk penomoran urut -->
            <template v-slot:item.id="{ item, index }">
                {{ index + 1 }}
            </template>
            <!-- 💥 KOREKSI: Slot item.date (agar format tanggal lebih baik jika diperlukan) -->
            <template v-slot:item.date="{ item }">
                <v-chip small color="blue-grey lighten-5" class="font-weight-medium">{{ item.date }}</v-chip>
            </template>
        </v-data-table>
    </v-card>
</template>

<!-- Modal Save (Import Excel) -->
<template>
    <v-row justify="center">
        <v-dialog v-model="modalAdd" max-width="700px" persistent scrollable>
            <v-card class="rounded-xl">
                <v-card-title class="text-h6 white--text primary">
                    <v-icon left dark>mdi-file-excel</v-icon> Import <?= $title; ?>
                    <v-spacer></v-spacer>
                    <v-btn icon dark @click="modalAddClose">
                        <v-icon>mdi-close</v-icon>
                    </v-btn>
                </v-card-title>
                <v-card-text class="pt-5">
                    <v-form ref="form" v-model="valid">
                        <p class="mb-2 text-subtitle-1 font-weight-medium grey--text text--darken-2">Bulan Tujuan Import</p>
                        <v-select 
                            v-model="importBulan" 
                            label="Pilih Bulan Tujuan" 
                            :items="dataBulan" 
                            item-text="text" 
                            item-value="value" 
                            class="mb-3" 
                            single-line 
                            hide-details 
                            outlined
                            dense
                        ></v-select>

                        <p class="mb-2 text-subtitle-1 font-weight-medium grey--text text--darken-2">File Excel</p>
                        <v-file-input 
                            v-model="file" 
                            show-size 
                            label="File Upload" 
                            id="file" 
                            class="mb-2" 
                            accept=".xls, .xlsx" 
                            prepend-icon="mdi-file-excel" 
                            @change="onFileChange" 
                            :loading="loading2" 
                            outlined
                            dense
                            :disabled="!importBulan"
                        ></v-file-input>

                        <v-alert type="info" text class="rounded-lg">
                            Download format Excel yang wajib digunakan:
                        <a href="<?= base_url('files/excel/Jadwal_sholat.xlsx'); ?>" target="_blank">Format Jadwal Sholat</a> | 
                        <a href="<?= base_url('files/excel/Contoh_jadwal_sholat.xlsx'); ?>" target="_blank">Contoh Data</a>
                        </v-alert>
                    </v-form>
                </v-card-text>
                <v-divider></v-divider>
                <v-card-actions class="py-3">
                    <v-spacer></v-spacer>
                    <v-btn large color="primary" @click="uploadFile" :loading="loading" elevation="2" :disabled="!file || !importBulan">
                        <v-icon left>mdi-upload</v-icon> Upload & Import
                    </v-btn>
                </v-card-actions>
            </v-card>
        </v-dialog>
    </v-row>
</template>

<!-- Modal Pilih Bulan (Dihapus karena sudah ada Select di Toolbar) -->
<!-- <v-dialog v-model="modalShow" ...> : Dihapus -->

<?php $this->endSection("content") ?>

<?php $this->section("js") ?>
<script>
    // Helper function upload (dipertahankan)
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
        var blob = new Blob(byteArrays, { type: contentType });
        return blob;
    }

    // 💥 KOREKSI WAJIB: Amankan objek global sebelum merging
    window.dataVue = window.dataVue || {};
    window.methodsVue = window.methodsVue || {};
    window.computedVue = window.computedVue || {};

    // 1. Gabungkan Data Spesifik Jadwal Sholat ke window.dataVue
    Object.assign(window.dataVue, {
        // --- Properti Default Layout yang WAJIB ada (Anti-ReferenceError) ---
        snackbar: false,
        timeout: 4000, 
        snackbarType: '',
        snackbarMessage: '',
        valid: true, // Untuk Form Validasi
        
        // --- Properti Spesifik View ---
        pencarian: "",
        modalAdd: false,
        loading: false, 
        loading2: false, // Untuk loading upload
        
        // Data Headers
        dataHeader: [
            { text: "No.", value: "id", sortable: false, width: '5%' },
            { text: "Tanggal", value: "date" }, 
            { text: "Imsak", value: "imsak" },
            { text: "Subuh", value: "subuh" },
            { text: "Terbit", value: "terbit" },
            { text: "Dhuha", value: "duha" }, // Perhatikan perbedaan spelling Dhuha/Duha
            { text: "Dzuhur", value: "dzuhur" },
            { text: "Ashar", value: "ashar" },
            { text: "Maghrib", value: "maghrib" },
            { text: "Isya", value: "isya" }
        ],
        dataJadwalsholat: [],
        
        // Data Bulan (Didefinisikan ulang di sini)
        dataBulan: [{
            text: "Januari",
            value: "1"
        }, {
            text: "Februari",
            value: "2"
        }, {
            text: "Maret",
            value: "3"
        }, {
            text: "April",
            value: "4"
        }, {
            text: "Mei",
            value: "5"
        }, {
            text: "Juni",
            value: "6"
        }, {
            text: "Juli",
            value: "7"
        }, {
            text: "Agustus",
            value: "8"
        }, {
            text: "September",
            value: "9"
        }, {
            text: "Oktober",
            value: "10"
        }, {
            text: "November",
            value: "11"
        }, {
            text: "Desember",
            value: "12"
        }],

        // Data Form dan Filter
        idBulan: String(new Date().getMonth() + 1), // Default ke bulan saat ini (string)
        importBulan: null, // Bulan tujuan import
        file: null, // File input
        filePreview: null,
    });

    // 2. Gabungkan Methods Spesifik Jadwal Sholat ke window.methodsVue
    Object.assign(window.methodsVue, {
        // Method helper untuk menampilkan Snackbar
        showSnackbar: function(message, type = 'success') {
            this.snackbarMessage = message;
            this.snackbarType = type;
            this.snackbar = true;
        },

        // Modal
        modalAddOpen: function() {
            this.modalAdd = true;
            this.importBulan = this.idBulan; // Set default import ke bulan yang sedang dilihat
            this.file = null;
        },
        modalAddClose: function() {
            this.modalAdd = false;
        },
        
        // Get Data
        getJadwalsholat: function() {
            this.loading = true;
            
            // Pastikan idBulan terisi
            if (!this.idBulan) {
                this.dataJadwalsholat = [];
                this.loading = false;
                this.showSnackbar('Pilih bulan untuk melihat jadwal.', 'warning');
                return;
            }

            axios.get(`<?= base_url(); ?>/api/jadwalsholat?idbulan=${this.idBulan}`)
                .then(res => {
                    this.loading = false;
                    var data = res.data;
                    if (data.status == true) {
                        this.dataJadwalsholat = data.data;
                    } else {
                        this.showSnackbar(data.message, 'error');
                        this.dataJadwalsholat = [];
                    }
                })
                .catch(err => {
                    console.error("Error fetching Jadwal Sholat:", err.response);
                    this.loading = false;
                    this.showSnackbar('Gagal memuat jadwal. Cek server.', 'error');
                })
        },

        // Upload Handler
        onFileChange() {
            if (!this.file) return;
            const reader = new FileReader()
            reader.readAsDataURL(this.file);
            reader.onload = e => {
                this.filePreview = e.target.result;
            }
        },

        // Upload dan Import File
        uploadFile: function() {
            if (!this.file || !this.importBulan) {
                this.showSnackbar('Pilih bulan tujuan dan file Excel terlebih dahulu.', 'error');
                return;
            }

            this.loading = true;
            this.loading2 = true;
            
            var formData = new FormData(); 
            var block = this.filePreview.split(";"); 
            var contentType = block[0].split(":")[1]; 
            var realData = block[1].split(",")[1]; 

            var blob = b64toBlob(realData, contentType);
            formData.append('fileexcel', blob, this.file.name);
            formData.append('idbulan', this.importBulan);
            
            axios.post(`<?= base_url() ?>/api/jadwalsholat/import`, formData)
                .then(res => {
                    this.loading2 = false;
                    var data = res.data;
                    if (data.status == true) {
                        this.showSnackbar(data.message, 'success');
                        this.file = null;
                        this.filePreview = null;
                        // Refresh data di bulan yang di-import
                        this.getJadwalsholat(); 
                        this.modalAddClose();
                    } else {
                        this.showSnackbar(data.message, 'error');
                    }
                })
                .catch(err => {
                    console.error("Error importing file:", err.response);
                    this.loading2 = false;
                    this.showSnackbar('Gagal import file. Cek format Excel.', 'error');
                })
                .finally(() => {
                    this.loading = false;
                })
        },

        // METHOD YANG TIDAK RELEVAN (Hanya untuk jaga-jaga)
        // ... (Hapus modalEditClose, deleteAgenda, dll. yang tidak ada di template)
    });

    // 3. Created Hook
    window.createdVue = function() {
        // Panggil created hook default dari layout jika ada
        if (typeof window.defaultCreatedVue === 'function') {
            window.defaultCreatedVue.call(this);
        }
        // Panggil getJadwalsholat setelah idBulan terisi (default bulan sekarang)
        this.getJadwalsholat();
        console.log("Jadwal Sholat View: Data Loaded");
    };
</script>
<?php $this->endSection("js") ?>