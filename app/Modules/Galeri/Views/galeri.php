<?php $this->extend("layouts/backend"); ?>
<?php $this->section("content"); ?>
<template>
    <!-- Card Kontainer Utama -->
    <v-card class="rounded-xl elevation-4">
        
        <!-- Header Halaman -->
        <v-card-title class="pa-4 grey lighten-5">
            <v-icon left color="indigo">mdi-image-multiple</v-icon>
            <h1 class="font-weight-bold text-h5 grey--text text--darken-3"><?= $title; ?></h1>
        </v-card-title>
        
        <!-- Toolbar Responsif -->
        <v-card-text class="pa-4">
            <v-row class="mb-2">
                <!-- Kolom Button Tambah -->
                <v-col cols="12" md="4" class="py-0">
                    <v-btn 
                        color="indigo" 
                        dark 
                        large 
                        @click="modalAddOpen" 
                        elevation="2" 
                        class="rounded-pill"
                        block
                    >
                        <v-icon left>mdi-plus-box-multiple-outline</v-icon> <?= lang('App.add') ?>
                    </v-btn>
                </v-col>
                
                <!-- Kolom Search Field -->
                <v-col cols="12" md="8" class="py-0">
                    <v-text-field 
                        v-model="pencarian" 
                        append-icon="mdi-magnify" 
                        label="Cari Label/Deskripsi..." 
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
            :items="dataGaleri" 
            :items-per-page="10" 
            :loading="loading" 
            :search="pencarian" 
            class="elevation-0 px-4 pb-4 my-4 pt-0" 
            loading-text="<?= lang('App.loadingWait'); ?>"
        >
            <template v-slot:item.image_url="{ item }">
                <!-- Avatar Rapi -->
                <v-avatar rounded size="60">
                    <img :src="'<?= base_url() ?>' + '/' + item.image_url" v-if="item.image_url != null" :alt="item.label">
                </v-avatar>
            </template>

            <template v-slot:item.status="{ item }">
                <!-- Switch Aktif -->
                <v-switch 
                    v-model="item.status" 
                    :true-value="'1'" 
                    :false-value="'0'" 
                    color="success" 
                    @change="setAktif(item)"
                    :loading="item.loadingStatus"
                    hide-details
                    class="mt-0"
                ></v-switch>
            </template>

            <template v-slot:item.actions="{ item }">
                <v-btn color="primary" class="mr-1" small @click="editItem(item)">
                    <v-icon small>mdi-pencil-outline</v-icon>
                </v-btn>
                <v-btn color="error" small @click="deleteItem(item)">
                    <v-icon small>mdi-delete-empty-outline</v-icon>
                </v-btn>
            </template>
        </v-data-table>
    </v-card>
</template>

<!-- Modal Save/Edit (Digabung menjadi satu template) -->
<template>
    <v-row justify="center">
        <v-dialog v-model="modalAdd" max-width="700px" persistent scrollable>
            <v-card class="rounded-xl">
                
                <!-- Header Modal (Menggunakan isEdit) -->
                <v-card-title class="text-h6 white--text" :class="isEdit ? 'teal darken-1' : 'primary'">
                    <v-icon left dark>{{ isEdit ? 'mdi-pencil-box-multiple' : 'mdi-plus-box-multiple' }}</v-icon>
                    {{ isEdit ? '<?= lang('App.edit') ?>' : '<?= lang('App.add') ?>' }} <?= $title; ?>
                    <v-spacer></v-spacer>
                    <v-btn icon dark @click="modalAddClose">
                        <v-icon>mdi-close</v-icon>
                    </v-btn>
                </v-card-title>
                
                <v-card-text class="pt-5">
                    <v-form ref="form" v-model="valid">
                        <p class="mb-2 text-subtitle-1 font-weight-medium grey--text text--darken-2">Label/Caption Gambar</p>
                        <v-text-field v-model="label" label="Label/Caption Gambar" :error-messages="labelError" outlined dense></v-text-field>

                        <p class="mb-2 text-subtitle-1 font-weight-medium grey--text text--darken-2">Deskripsi</p>
                        <v-text-field v-model="deskripsi" label="Deskripsi Gambar" :error-messages="deskripsiError" outlined dense></v-text-field>
                        
                        <p class="mb-2 text-subtitle-1 font-weight-medium grey--text text--darken-2">Gambar</p>
                        
                        <!-- Preview Gambar Saat Ini (untuk mode Edit) -->
                        <div v-if="isEdit && imageUrlEdit && !imagePreview">
                            <img v-bind:src="'<?= base_url() ?>' + '/' + imageUrlEdit" width="150" class="mb-3 rounded-lg" alt="Current Image" />
                            <p class="text-caption grey--text">Kosongkan kolom di bawah jika tidak ingin mengganti gambar.</p>
                        </div>
                        
                        <!-- Input File Upload -->
                        <v-file-input 
                            v-model="image" 
                            show-size 
                            label="Image Upload" 
                            id="file" 
                            class="mb-2" 
                            accept=".jpg, .jpeg, .png" 
                            prepend-icon="mdi-camera" 
                            @change="onFileChange" 
                            :loading="loading2" 
                            :error-messages="image_urlError"
                            outlined
                            dense
                            clearable
                            @click:clear="onFileClear"
                        ></v-file-input>
                        
                        <!-- Image Preview Setelah Upload (menggunakan v-img) -->
                        <div v-show="imagePreview">
                            <v-img :src="imagePreview" max-width="200" class="rounded-lg">
                                <v-overlay v-model="overlay" absolute :opacity="0.1">
                                    <v-btn small class="ma-2" color="success" dark>
                                        OK
                                        <v-icon dark right>mdi-checkbox-marked-circle</v-icon>
                                    </v-btn>
                                </v-overlay>
                            </v-img>
                        </div>
                    </v-form>
                </v-card-text>
                <v-divider></v-divider>
                
                <!-- Tombol Aksi -->
                <v-card-actions class="py-3">
                    <v-spacer></v-spacer>
                    <v-btn large text @click="modalAddClose" class="mr-2">Tutup</v-btn>
                    
                    <v-btn large :color="isEdit ? 'teal darken-1' : 'primary'" @click="isEdit ? updateGaleri() : saveGaleri()" :loading="loading" elevation="2">
                        <v-icon left>mdi-content-save</v-icon> {{ isEdit ? '<?= lang('App.update') ?>' : '<?= lang('App.save') ?>' }}
                    </v-btn>
                </v-card-actions>
            </v-card>
        </v-dialog>
    </v-row>
</template>

<!-- Modal Delete -->
<template>
    <v-row justify="center">
        <v-dialog v-model="modalDelete" persistent max-width="450px">
            <v-card class="rounded-xl pa-2">
                <v-card-title>
                    <v-icon color="error" class="mr-2" x-large>mdi-alert-octagon</v-icon> <?= lang('App.delConfirm') ?>
                </v-card-title>
                <v-card-text>
                    <div class="mt-2">
                        <h3 class="font-weight-regular">Yakin hapus data ini? Tindakan ini tidak bisa dibatalkan.</h3>
                    </div>
                </v-card-text>
                <v-card-actions>
                    <v-spacer></v-spacer>
                    <v-btn large text @click="modalDeleteClose">Batal</v-btn>
                    <v-btn large color="red darken-2" dark @click="deleteGaleri" :loading="loading">Ya, Hapus</v-btn>
                </v-card-actions>
            </v-card>
        </v-dialog>
    </v-row>
</template>
<?php $this->endSection("content") ?>

<?php $this->section("js") ?>
<script>
    // Helper function to convert Base64 to Blob (diperlukan untuk File Upload di Axios)
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

    // 1. Gabungkan Data Spesifik Galeri ke window.dataVue
    Object.assign(window.dataVue, {
        // --- Properti Default Layout yang WAJIB ada (Anti-ReferenceError) ---
        snackbar: false,
        timeout: 4000, 
        snackbarType: '',
        snackbarMessage: '',
        valid: true, // Untuk Form Validasi
        
        // --- Properti Spesifik View Galeri ---
        pencarian: "",
        modalAdd: false, 
        modalDelete: false,
        loading: false, // Loading utama
        loading2: false, // Loading upload gambar
        isEdit: false, // State untuk menentukan mode Edit atau Add
        overlay: false, // Untuk overlay preview gambar
        image: null, // File input
        imagePreview: null, // URL preview base64
        
        // Data Headers
        dataHeader: [
            { text: "No.", value: "id", sortable: false, width: '5%' },
            { text: "Label", value: "label", width: '20%' },
            { text: "Deskripsi", value: "deskripsi" },
            { text: "Gambar", value: "image_url", sortable: false, width: '15%' },
            { text: "Aktif", value: "status", width: '10%' },
            { text: "<?= lang('App.action') ?>", value: "actions", sortable: false, width: '15%' },
        ],
        dataGaleri: <?= json_encode($dataGaleri ?? []) ?> || [], 
        
        // Data Form (untuk Add/Edit)
        idGaleri: "",
        label: "",
        labelError: "",
        deskripsi: "",
        deskripsiError: "",
        imageUrl: "", // URL final gambar yang disimpan di DB
        image_urlError: "",
        imageUrlEdit: null, // URL gambar lama (khusus edit)
        status: "0", // Default status nonaktif
    });

    // 2. Gabungkan Methods Spesifik Galeri ke window.methodsVue
    Object.assign(window.methodsVue, {
        // Method helper untuk menampilkan Snackbar
        showSnackbar: function(message, type = 'success') {
            this.snackbarMessage = message;
            this.snackbarType = type;
            this.snackbar = true;
        },

        // Method untuk membuka modal Add/Edit (Disatukan)
        modalAddOpen: function() {
            this.isEdit = false; // Mode Add
            this.modalAdd = true;
            this.resetForm();
            this.status = "0"; // Reset status default
        },
        // Method untuk menutup modal Add/Edit (Disatukan)
        modalAddClose: function() {
            this.modalAdd = false;
            this.isEdit = false; 
            this.resetForm();
        },
        
        // Method Reset Form
        resetForm: function() {
            if (this.$refs.form) {
                this.$refs.form.resetValidation();
                this.$refs.form.reset();
            }
            this.idGaleri = "";
            this.label = "";
            this.deskripsi = "";
            this.imageUrl = "";
            this.imageUrlEdit = null;
            this.image = null;
            this.imagePreview = null;
            this.overlay = false;
        },
        
        // Get Data
        getGaleri: function() {
            this.loading = true;
            axios.get('<?= base_url(); ?>/api/galeri')
                .then(res => {
                    this.loading = false;
                    var data = res.data;
                    if (data.status == true) {
                        // Pastikan status di-set ke string untuk v-switch
                        this.dataGaleri = data.data.map(item => ({
                            ...item,
                            status: String(item.status), 
                            loadingStatus: false
                        }));
                    } else {
                        this.showSnackbar(data.message, 'error');
                    }
                })
                .catch(err => {
                    console.error("Error fetching gallery:", err.response);
                    this.loading = false;
                })
        },

        // --- UPLOAD METHODS ---
        onFileChange() {
            if (!this.image) {
                this.onFileClear();
                return;
            }
            const reader = new FileReader();
            reader.readAsDataURL(this.image);
            reader.onload = e => {
                this.imagePreview = e.target.result;
                this.uploadFile(this.imagePreview);
            }
        },
        onFileClear() {
            this.image = null;
            this.imagePreview = null;
            this.imageUrl = this.isEdit ? this.imageUrlEdit : null; // Pertahankan URL lama jika mode edit
            this.showSnackbar('Image dibatalkan.', 'warning');
        },
        uploadFile: function(file) {
            var formData = new FormData(); 
            var block = file.split(";"); 
            var contentType = block[0].split(":")[1]; 
            var realData = block[1].split(",")[1]; 

            var blob = b64toBlob(realData, contentType);
            formData.append('image', blob, this.image.name); // Tambahkan nama file
            this.loading2 = true; // Loading khusus upload
            
            axios.post(`<?= base_url() ?>/api/galeri/upload`, formData)
                .then(res => {
                    this.loading2 = false;
                    var data = res.data;
                    if (data.status == true) {
                        this.showSnackbar(data.message, 'success');
                        this.imageUrl = data.data[0]; // Ambil URL dari respons API
                        this.overlay = true;
                        setTimeout(() => this.overlay = false, 1500);
                    } else {
                        this.showSnackbar(data.message, 'error');
                        this.image = null; // Reset input file jika gagal
                        this.imagePreview = null;
                    }
                })
                .catch(err => {
                    console.error("Error uploading file:", err.response);
                    this.showSnackbar('Gagal upload file.', 'error');
                    this.loading2 = false;
                    this.image = null; // Reset input file jika gagal
                    this.imagePreview = null;
                })
        },
        // --- END UPLOAD METHODS ---


        // Save Galeri
        saveGaleri: function() {
            if (!this.$refs.form.validate() || !this.imageUrl) {
                if (!this.imageUrl) this.image_urlError = 'Gambar wajib di-upload sebelum disimpan.';
                return;
            }
            this.loading = true;
            axios.post(`<?= base_url(); ?>/api/galeri/save`, {
                    label: this.label,
                    deskripsi: this.deskripsi,
                    image_url: this.imageUrl,
                    status: this.status,
                })
                .then(res => {
                    this.loading = false;
                    var data = res.data;
                    if (data.status == true) {
                        this.showSnackbar(data.message, 'success');
                        this.getGaleri();
                        this.modalAddClose();
                    } else {
                        this.showSnackbar(data.message, 'error');
                        errorKeys = Object.keys(data.data);
                        errorKeys.forEach((el) => { this[`${el}Error`] = data.data[el]; });
                        setTimeout(() => { errorKeys.forEach((el) => { this[`${el}Error`] = ""; }); }, 4000); 
                    }
                })
                .catch(err => {
                    console.error("Error saving gallery:", err.response);
                    this.showSnackbar('Gagal menyimpan data. Cek server.', 'error');
                    this.loading = false;
                })
        },

        // Get Item Edit
        editItem: function(item) {
            this.isEdit = true; // Mode Edit
            this.modalAdd = true;
            // Map data
            this.idGaleri = item.id;
            this.label = item.label;
            this.deskripsi = item.deskripsi;
            this.imageUrlEdit = item.image_url;
            this.imageUrl = item.image_url; // URL untuk update
            this.status = item.status;
            
            if (this.$refs.form) this.$refs.form.resetValidation();
        },

        //Update Galeri
        updateGaleri: function() {
            if (!this.$refs.form.validate() || !this.imageUrl) return;
            this.loading = true;
            axios.put(`<?= base_url(); ?>/api/galeri/update/${this.idGaleri}`, {
                    label: this.label,
                    deskripsi: this.deskripsi,
                    image_url: this.imageUrl,
                })
                .then(res => {
                    this.loading = false;
                    var data = res.data;
                    if (data.status == true) {
                        this.showSnackbar(data.message, 'success');
                        this.getGaleri();
                        this.modalAddClose();
                    } else {
                        this.showSnackbar(data.message, 'error');
                        errorKeys = Object.keys(data.data);
                        errorKeys.forEach((el) => { this[`${el}Error`] = data.data[el]; });
                        setTimeout(() => { errorKeys.forEach((el) => { this[`${el}Error`] = ""; }); }, 4000); 
                    }
                })
                .catch(err => {
                    console.error("Error updating gallery:", err.response);
                    this.showSnackbar('Gagal update data. Cek server.', 'error');
                    this.loading = false;
                })
        },

        // Get Item Delete
        deleteItem: function(item) {
            this.modalDelete = true;
            this.idGaleri = item.id;
        },

        modalDeleteClose: function() {
            this.modalDelete = false;
        },

        // Delete Galeri
        deleteGaleri: function() {
            this.loading = true;
            axios.delete(`<?= base_url(); ?>/api/galeri/delete/${this.idGaleri}`)
                .then(res => {
                    this.loading = false;
                    var data = res.data;
                    if (data.status == true) {
                        this.showSnackbar(data.message, 'success');
                        this.getGaleri();
                        this.modalDeleteClose();
                    } else {
                        this.showSnackbar(data.message, 'error');
                    }
                })
                .catch(err => {
                    console.error("Error deleting gallery:", err.response);
                    this.showSnackbar('Gagal hapus data. Cek server.', 'error');
                    this.loading = false;
                })
        },

        // Set Item Aktif (Toggle Switch)
        setAktif: function(item) {
            // Set loading status di item tersebut
            const index = this.dataGaleri.findIndex(g => g.id === item.id);
            if (index !== -1) {
                this.$set(this.dataGaleri[index], 'loadingStatus', true);
            }
            
            axios.put(`<?= base_url(); ?>/api/galeri/setaktif/${item.id}`, {
                    status: item.status,
                })
                .then(res => {
                    this.showSnackbar(res.data.message, res.data.status ? 'success' : 'error');
                })
                .catch(err => {
                    console.error("Error setting active status:", err.response);
                    this.showSnackbar('Gagal update status.', 'error');
                    // Revert switch status jika gagal
                    item.status = item.status === '1' ? '0' : '1'; 
                })
                .finally(() => {
                    if (index !== -1) {
                        this.$set(this.dataGaleri[index], 'loadingStatus', false);
                    }
                })
        },
    });

    // 3. Created Hook
    window.createdVue = function() {
        this.getGaleri();
        console.log("Gallery View: Data Loaded");
    };
</script>
<?php $this->endSection("js") ?>