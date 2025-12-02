<?php $this->extend("layouts/backend"); ?>
<?php $this->section("content"); ?>
<template>
    <v-card>
        <v-card-title>
            <h1 class="font-weight-medium"><?= $title; ?></h1>
        </v-card-title>
        <v-toolbar flat>
            <!-- Button Tambah -->
            <v-btn color="success" dark large @click="modalAddOpen" elevation="1">
                <v-icon>mdi-file-excel</v-icon> Import
            </v-btn>
            <v-spacer></v-spacer>
            <v-select v-model="idBulan" label="Bulan" :items="dataBulan" item-text="text" item-value="value" single-line hide-details style="width: 50px;" @change="getJadwalsholat" multiple></v-select>

            <v-text-field v-model="pencarian" append-icon="mdi-magnify" label="<?= lang('App.search') ?>" single-line hide-details>
            </v-text-field>
        </v-toolbar>
        <v-data-table :headers="dataHeader" :items="dataJadwalsholat" :items-per-page="10" :loading="loading" :search="pencarian" class="elevation-0" loading-text="<?= lang('App.loadingWait'); ?>">
        </v-data-table>
    </v-card>
</template>

<!-- Modal -->
<!-- Modal Save (Import Excel) -->
<template>
    <v-row justify="center">
        <v-dialog v-model="modalAdd" max-width="700px" persistent scrollable>
            <v-card>
                <v-card-title>
                    <?= lang('App.add') ?> <?= $title; ?>
                    <v-spacer></v-spacer>
                    <v-btn icon @click="modalAddClose">
                        <v-icon>mdi-close</v-icon>
                    </v-btn>
                </v-card-title>
                <v-divider></v-divider>
                <v-card-text class="pt-5">
                    <v-form ref="form" v-model="valid">
                        <p class="mb-0 text-subtitle-1">Bulan</p>
                        <v-select v-model="idBulan" label="Pilih Bulan" :items="dataBulan" item-text="text" item-value="value" class="mb-3" single-line hide-details multiple outlined></v-select>

                        <p class="mb-0 text-subtitle-1">File</p>
                        <v-file-input v-model="file" show-size label="File Upload" id="file" class="mb-2" accept=".xls, .xlsx" prepend-icon="mdi-file-excel" @change="onFileChange" :loading="loading2" filled :disabled="idBulan == ''"></v-file-input>

                        <v-alert type="info" text>
                            Download Excel:
                        <a href="<?= base_url('files/excel/Jadwal_sholat.xlsx'); ?>">Format Jadwal Sholat</a> dan
                        <a href="<?= base_url('files/excel/Contoh_jadwal_sholat.xlsx'); ?>">Contoh Jadwal Sholat</a>
                        </v-alert>
                    </v-form>
                </v-card-text>
                <v-divider></v-divider>
                <v-card-actions>
                    <v-spacer></v-spacer>
                    <v-btn large color="primary" text @click="modalAdd = false" :loading="loading" elevation="0">
                        <?= lang('App.close') ?>
                    </v-btn>
                </v-card-actions>
            </v-card>
        </v-dialog>
    </v-row>
</template>
<!-- End Modal Save -->

<v-dialog v-model="modalShow" persistent width="500">
    <v-card>
        <v-card-title class="text-h5 grey lighten-2 mb-5">
            Pilih Bulan
        </v-card-title>

        <v-card-text>
            <v-select v-model="idBulan" label="Pilih Bulan" :items="dataBulan" item-text="text" item-value="value" class="mb-1" single-line hide-details @change="getJadwalsholat" multiple outlined></v-select>
        </v-card-text>

        <v-divider></v-divider>

        <v-card-actions>
            <v-spacer></v-spacer>
            <v-btn color="primary" text @click="modalShow = false" :disabled="idBulan == ''">
                Simpan
            </v-btn>
        </v-card-actions>
    </v-card>
</v-dialog>

<?php $this->endSection("content") ?>

<?php $this->section("js") ?>
<script>
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

    // --- LOGIKA JWT LAMA DIHAPUS ---
    // const token = JSON.parse(localStorage.getItem('access_token'));
    // const options = { headers: { "Authorization": `Bearer ${token}`, "Content-Type": "application/json" } };
    // --- LOGIKA JWT LAMA DIHAPUS ---

    window.dataVue = {
        ...window.dataVue,
        pencarian: "",
        modalAdd: false,
        modalEdit: false,
        modalShow: true,
        modalDelete: false,
        modalTgl: false,
        modalJam: false,
        dataHeader: [{
            text: "#",
            value: "id"
        }, {
            text: "Tanggal",
            value: "date"
        }, {
            text: "Imsak",
            value: "imsak"
        }, {
            text: "Subuh",
            value: "subuh"
        }, {
            text: "Terbit",
            value: "terbit"
        }, {
            text: "Dhuha",
            value: "dhuha"
        }, {
            text: "Dzuhur",
            value: "dzuhur"
        }, {
            text: "Ashar",
            value: "ashar"
        }, {
            text: "Maghrib",
            value: "maghrib"
        }, {
            text: "Isya",
            value: "isya"
        }],
        dataJadwalsholat: [],
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
        idJadwalsholat: "",
        idBulan: "",
        tanggal: "",
        imsak: "",
        subuh: "",
        terbit: "",
        duha: "",
        dzuhur: "",
        ashar: "",
        magrib: "",
        isya: "",
        file: null,
        filePreview: null,
    }

    window.createdVue = function() {
        this.getJadwalsholat();
    }

    var watchVue = {
        idBulan: function() {
            if (this.idBulan.length > 1) {
                this.idBulan.pop();
                this.snackbar = true;
                this.snackbarMessage = "Anda hanya dapat memilih 1";
            }
        },
    }

    window.methodsVue = {
        ...window.methodsVue,
        modalAddOpen: function() {
            this.modalAdd = true;
            this.notifType = '';
            //this.$refs.form.resetValidation();
            //this.$refs.form.reset();
        },

        modalAddClose: function() {
            this.modalAdd = false;
            //this.$refs.form.resetValidation();
        },

        // Get Data
        getJadwalsholat: function() {
            this.loading = true;
            // AXIOS POLOS
            axios.get(`<?= base_url(); ?>/api/jadwalsholat?idbulan=${this.idBulan}`)
                .then(res => {
                    this.loading = false;
                    var data = res.data;
                    if (data.status == true) {
                        this.dataJadwalsholat = data.data;
                    } else {
                        this.snackbar = true;
                        this.snackbarMessage = data.message;
                    }
                })
                .catch(err => {
                    // Cukup log error. Filter 401 akan menangani redirect global.
                    console.error("Error fetching Jadwal Sholat:", err.response);
                    this.loading = false;
                })
        },

        //Upload
        onFileChange() {
            const reader = new FileReader()
            reader.readAsDataURL(this.file)
            reader.onload = e => {
                this.filePreview = e.target.result;
                this.uploadFile(this.filePreview);
            }
        },
        onFileClear() {
            this.file = null;
            this.filePreview = null;
            this.snackbar = true;
            this.snackbarMessage = 'File dihapus';
        },
        uploadFile: function(file) {
            var formData = new FormData() 
            var block = file.split(";"); 
            var contentType = block[0].split(":")[1]; 
            var realData = block[1].split(",")[1]; 

            var blob = b64toBlob(realData, contentType);
            formData.append('fileexcel', blob);
            formData.append('idbulan', this.idBulan);
            this.loading2 = true;
            // AXIOS POLOS (Untuk Import)
            axios.post(`<?= base_url() ?>/api/jadwalsholat/import`, formData)
                .then(res => {
                    this.loading2 = false
                    var data = res.data;
                    if (data.status == true) {
                        this.snackbar = true;
                        this.snackbarMessage = data.message;
                        this.file = null;
                        this.filePreview = null;
                        this.getJadwalsholat();
                        this.modalAdd = false;
                    } else {
                        this.snackbar = true;
                        this.snackbarMessage = data.message;
                    }
                })
                .catch(err => {
                    console.error("Error importing file:", err.response);
                    this.loading2 = false;
                })
        },

        // --- METHOD LAMA YANG TIDAK RELEVAN DENGAN JADWALSHOLAT DIHAPUS ---
        // saveAgenda, editItem, updateAgenda, deleteItem, deleteAgenda
        
        // CATATAN: Karena Anda tidak memberikan method update/delete untuk Jadwalsholat di sini, 
        // Saya asumsikan Anda hanya menggunakan Import/Display.
        // Jika Anda ingin menambahkan CRUD untuk Jadwal Sholat, Anda harus mengimplementasikannya di sini.

    }
</script>
<?php $this->endSection("js") ?>