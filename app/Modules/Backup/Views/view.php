<?php $this->extend("layouts/backend"); ?>
<?php $this->section("content"); ?>
<template>
    <h1 class="mb-3 font-weight-medium"><?= $title ?></h1>
    <v-card>
        <v-card-title>
            <v-btn large color="primary" dark @click="saveBackup" elevation="1">
                <v-icon>mdi-database-plus</v-icon> Backup Now
            </v-btn>
            <v-spacer></v-spacer>
            <v-text-field v-model="search" append-icon="mdi-magnify" label="Search" single-line hide-details>
            </v-text-field>
        </v-card-title>
                <v-data-table 
            :headers="dataTable" 
            :items="dataBackup" 
            :items-per-page="10" 
            :loading="loading" 
            :search="search" 
            loading-text="Sedang memuat... Harap tunggu">

            <!-- Vuetify akan merender kolom ID, File Name, File Path, dan Tanggal secara OTOMATIS -->
            
            <!-- Custom Slot untuk Kolom 'Aksi' (value: actions) -->
            <template v-slot:item.actions="{ item }">
                <v-btn color="primary" @click="downloadItem(item)" icon>
                    <v-icon>mdi-download</v-icon>
                </v-btn>
                <v-btn color="error" @click="deleteItem(item)" icon>
                    <v-icon>mdi-delete</v-icon>
                </v-btn>
            </template>
            
        </v-data-table>
    </v-card>
</template>

<!-- Modal Delete -->
<template>
    <v-row justify="center">
        <v-dialog v-model="modalDelete" persistent max-width="600px">
            <v-card class="pa-2">
                <v-card-title><v-icon color="error" class="mr-2" x-large>mdi-alert-octagon</v-icon> Konfirmasi Hapus</v-card-title>
                <v-divider></v-divider>
                <v-card-text>
                    <div class="mt-5 py-4">
                        <h3 class="mb-3">Apakah anda yakin ingin menghapus?</h3>
                    </div>
                </v-card-text>
                <v-divider></v-divider>
                <v-card-actions>
                    <v-spacer></v-spacer>
                    <v-btn @click="modalDelete = false" elevation="0" large>Tutup</v-btn>
                    <v-btn color="red" dark @click="deleteData" :loading="loading" elevation="0" large>Hapus</v-btn>
                    <v-spacer></v-spacer>
                </v-card-actions>
            </v-card>
        </v-dialog>
    </v-row>
</template>
<!-- End Modal Delete -->

<v-dialog v-model="loading2" hide-overlay persistent width="300">
    <v-card>
        <v-card-text class="pt-3">
            Memuat, silahkan tunggu...
            <v-progress-linear indeterminate color="primary" class="mb-0"></v-progress-linear>
        </v-card-text>
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
        modalDelete: false,
        dataBackup: [],
        dataTable: [{
                text: 'ID',
                value: 'id'
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
                value: 'created_at'
            },
            {
                text: 'Aksi',
                value: 'actions',
                sortable: false
            },
        ],
        idBackup: "",
    }

    var errorKeys = []

    window.createdVue = function() {
        if (typeof window.defaultCreatedVue !== 'undefined') {
            window.defaultCreatedVue.call(this); // Mengatur konteks 'this'
        }
        this.getBackup();
    }

    window.methodsVue = {
        ...window.methodsVue,
        // Get
        getBackup: function() {
            this.loading = true;
            // AXIOS POLOS
            axios.get('<?= base_url() ?>/api/backup')
                .then(res => {
                    // handle success
                    this.loading = false;
                    var data = res.data;
                    if (data.status == true) {
                        //this.snackbar = true;
                        //this.snackbarMessage = data.message;
                        this.dataBackup = data.data;
                    } else {
                        this.snackbar = true;
                        this.snackbarMessage = data.message;
                    }
                })
                .catch(err => {
                    // Cukup log error. Filter 401/403 akan menangani redirect global.
                    console.error("Error fetching backup list:", err.response);
                    this.loading = false;
                })
        },

        // Save Data
        saveBackup: function() {
            this.loading = true;
            // AXIOS POLOS
            axios.post(`<?= base_url() ?>/api/backup/save`, {})
                .then(res => {
                    // handle success
                    this.loading = false
                    var data = res.data;
                    if (data.status == true) {
                        this.snackbar = true;
                        this.snackbarMessage = data.message;
                        this.getBackup();
                    } else {
                        this.snackbar = true;
                        this.snackbarMessage = data.message;
                    }
                })
                .catch(err => {
                    // Cukup log error. Filter 401/403 akan menangani redirect global.
                    console.error("Error creating backup:", err.response);
                    this.loading = false;
                })
        },

        // Download
        downloadItem: function(item) {
            this.loading2 = true;
            this.idBackup = item.id;
            // AXIOS POLOS
            axios.post(`<?= base_url()?>/api/backup/download`, {
                    id: this.idBackup
                })
                .then(res => {
                    // handle success
                    this.loading2 = false
                    var data = res.data;
                    if (data.status == true) {
                        this.snackbar = true;
                        this.snackbarMessage = data.message;
                        // LANGSUNG GUNAKAN WINDOW LOCATION UNTUK DOWNLOAD
                        window.location.href = data.data.url;
                        
                        // NOTE: Logic download file di bawah ini tidak efektif untuk file besar 
                        // dan tidak perlu karena server mengembalikan URL file.
                        /*
                        var fileURL = window.URL.createObjectURL(new Blob([data.data.url]));
                        var fileLink = document.createElement('a');
                        fileLink.href = fileURL;
                        fileLink.setAttribute('download', data.data.filename);
                        document.body.appendChild(fileLink);
                        fileLink.click();
                        */

                    } else {
                        this.snackbar = true;
                        this.snackbarMessage = data.message;
                    }
                })
                .catch(err => {
                    // Cukup log error. Filter 401/403 akan menangani redirect global.
                    console.error("Error downloading file:", err.response);
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
            // AXIOS POLOS
            axios.delete(`<?= base_url() ?>/api/backup/delete/${this.idBackup}`)
                .then(res => {
                    // handle success
                    this.loading = false;
                    var data = res.data;
                    if (data.status == true) {
                        this.snackbar = true;
                        this.snackbarMessage = data.message;
                        this.getBackup();
                        this.modalDelete = false;
                    } else {
                        this.snackbar = true;
                        this.snackbarMessage = data.message;
                        this.modalDelete = true;
                    }
                })
                .catch(err => {
                    // Cukup log error. Filter 401/403 akan menangani redirect global.
                    console.error("Error deleting backup:", err.response);
                    this.loading = false;
                })
        },
    }
</script>
<?php $this->endSection("js") ?>