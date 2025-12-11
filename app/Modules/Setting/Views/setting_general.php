<?php $this->extend("layouts/backend"); ?>
<?php $this->section("content"); ?>
<template>
    <!-- Card Kontainer Utama -->
    <v-card class="rounded-xl elevation-4">
        
        <!-- Header Halaman -->
        <v-card-title class="pa-4 grey lighten-5">
            <v-icon left color="indigo">mdi-cog-outline</v-icon>
            <h1 class="font-weight-bold text-h5 grey--text text--darken-3"><?= $title; ?></h1>
        </v-card-title>
        
        <!-- Toolbar Responsif -->
        <v-card-text class="pa-4">
            <v-row class="mb-2">
                <v-spacer></v-spacer>
                <!-- Kolom Search Field -->
                <v-col cols="12" md="5" class="py-0">
                    <v-text-field 
                        v-model="search" 
                        append-icon="mdi-magnify" 
                        label="Cari Deskripsi Setting..." 
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
            :items="dataSettingWithIndex" 
            :items-per-page="10" 
            :loading="loading" 
            :search="search" 
            class="elevation-0 px-4 pb-4 my-4 pt-0" 
            loading-text="Sedang memuat... Harap tunggu"
        >
            <!-- Slot untuk Value Setting (Teks panjang/Logo) -->
            <template v-slot:item.value_setting="{ item }">
                <div v-if="item.variable_setting == 'logo'">
                    <v-avatar rounded size="60">
                        <img :src="'<?= base_url() ?>' + '/' + item.value_setting" alt="Logo">
                    </v-avatar>
                </div>
                <div v-else class="text-caption grey--text text--darken-3">
                    {{ item.value_setting.substring(0, 50) + (item.value_setting.length > 50 ? '...' : '') }}
                </div>
            </template>
            
            <!-- Slot untuk Aksi -->
            <template v-slot:item.actions="{ item }">
                <div v-if="item.variable_setting == 'app_version' || item.variable_setting == 'app_release' || item.variable_setting == 'app_developer'">
                    <!-- Tidak ada aksi untuk versi/developer -->
                </div>
                <div v-else>
                    <v-btn color="primary" @click="editItem(item)" small icon title="Edit Nilai">
                        <v-icon small>mdi-pencil-outline</v-icon>
                    </v-btn>
                </div>
            </template>
        </v-data-table>
    </v-card>

</template>

<!-- Modal Edit (Digabungkan untuk semua tipe setting) -->
<template>
    <v-row justify="center">
        <v-dialog v-model="modalEdit" persistent scrollable width="600px">
            <v-card class="rounded-xl">
                <v-card-title class="text-h6 white--text primary">
                    <v-icon left dark>mdi-pencil-box-multiple</v-icon> Edit {{deskripsiEdit}}
                    <v-spacer></v-spacer>
                    <v-btn icon dark @click="modalEditClose">
                        <v-icon>mdi-close</v-icon>
                    </v-btn>
                </v-card-title>
                
                <v-card-text class="py-5">
                    <v-form ref="form" v-model="valid">
                        <p class="mb-2 text-subtitle-1 font-weight-medium grey--text text--darken-2">Deskripsi Setting</p>
                        <v-text-field v-model="deskripsiEdit" :error-messages="deskripsi_settingError" outlined dense disabled></v-text-field>
                        
                        <p class="mb-2 text-subtitle-1 font-weight-medium grey--text text--darken-2">Nilai Setting</p>
                        
                        <!-- Logika Input Berdasarkan Tipe Setting -->
                        <div v-if="variableEdit == 'logo'">
                            <!-- Preview Gambar Saat Ini -->
                            <img v-bind:src="'<?= base_url() ?>' + '/' + valueEdit" width="100" class="mb-3 rounded-lg" alt="Current Logo" />
                            
                            <!-- Upload File -->
                            <v-file-input v-model="image" show-size label="Image Upload" id="file" class="mb-2" accept=".jpg, .jpeg, .png" prepend-icon="mdi-camera" @change="onFileChange" @click:clear="onFileClear" :loading="loading2" outlined dense hint="Ganti logo baru, otomatis tersimpan setelah di-upload." persistent-hint></v-file-input>
                            
                            <!-- Image Preview Overlay -->
                            <div v-show="imagePreview">
                                <v-img :src="imagePreview" max-width="100" class="rounded-lg">
                                    <v-overlay v-model="overlay" absolute :opacity="0.1">
                                        <v-icon color="success">mdi-checkbox-marked-circle</v-icon>
                                    </v-overlay>
                                </v-img>
                            </div>
                        </div>
                        <div v-else-if="groupEdit == 'icon'">
                            <!-- Select untuk Icon (jika ada data icon) -->
                            <v-select v-model="valueEdit" :items="dataIcon" item-text="text" item-value="value" :error-messages="value_settingError" outlined dense>
                                <template v-slot:item="props">
                                    <img v-bind:src="'<?= base_url() ?>' + '/assets/images/' + props.item.value" width="30" class="mr-2" />
                                    {{props.item.text}}
                                </template>
                            </v-select>
                        </div>
                        <div v-else>
                            <!-- Textarea atau Textfield untuk nilai umum -->
                            <v-textarea v-model="valueEdit" :error-messages="value_settingError" rows="3" outlined></v-textarea>
                        </div>
                    </v-form>
                </v-card-text>
                <v-divider></v-divider>
                
                <!-- Aksi Modal -->
                <v-card-actions class="py-3">
                    <v-spacer></v-spacer>
                    <!-- Tombol Simpan hanya jika BUKAN logo yang di edit -->
                    <v-btn large color="primary" @click="updateSetting" :loading="loading2" elevation="2" v-if="variableEdit != 'logo' && variableEdit != 'app_version' && variableEdit != 'app_release' && variableEdit != 'app_developer'">
                        <v-icon left>mdi-content-save</v-icon> Simpan
                    </v-btn>
                    <v-btn large text @click="modalEditClose">
                        Tutup
                    </v-btn>
                </v-card-actions>
            </v-card>
        </v-dialog>
    </v-row>
</template>

<!-- Dialog Loading -->
<v-dialog v-model="loading2" hide-overlay persistent width="300">
    <v-card class="rounded-lg">
        <v-card-text class="pt-3">
            Memuat, silahkan tunggu...
            <v-progress-linear indeterminate color="primary" class="mb-0"></v-progress-linear>
        </v-card-text>
    </v-card>
</v-dialog>
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

    // 1. Gabungkan Data Spesifik Setting General ke window.dataVue
    Object.assign(window.dataVue, {
        // --- Properti Default Layout yang WAJIB ada (Anti-ReferenceError) ---
        snackbar: false,
        timeout: 4000, 
        snackbarType: '',
        snackbarMessage: '',
        valid: true, 
        search: "",
        
        // --- Properti Spesifik View Setting ---
        modalEdit: false,
        loading: false, // Loading utama
        loading2: false, // Loading upload/update
        settingData: [], // Data dari API
        
        // Data Headers
        dataTable: [{
            text: 'No.',
            value: 'index',
            width: '5%',
            sortable: false
        }, {
            text: 'Variable',
            value: 'variable_setting',
            width: '15%'
        }, {
            text: 'Nilai Setting',
            value: 'value_setting'
        }, {
            text: 'Deskripsi',
            value: 'deskripsi_setting'
        }, {
            text: 'Tgl Update',
            value: 'updated_at',
            width: '15%'
        }, {
            text: 'Aksi',
            value: 'actions',
            sortable: false,
            width: '10%'
        }, ],
        
        // Data Edit
        settingId: "",
        groupEdit: "",
        variableEdit: "",
        deskripsiEdit: "",
        valueEdit: "",
        deskripsi_settingError: "",
        value_settingError: "",
        
        // Image Upload State
        image: null,
        imagePreview: null,
        overlay: false,
        
        // Data Icon (Contoh)
        dataIcon: [{
            text: 'School 1',
            value: 'school1.png'
        }, {
            text: 'School 2',
            value: 'school2.png'
        }, {
            text: 'School 3',
            value: 'school3.png'
        }, {
            text: 'School 4',
            value: 'school4.png'
        }, {
            text: 'School 5',
            value: 'school5.png'
        }],
    });

    // 2. Gabungkan Computed Properties
    Object.assign(window.computedVue, {
        dataSettingWithIndex() {
            return this.settingData.map(
                (items, index) => ({
                    ...items,
                    index: index + 1
                }))
        },
    });


    // 3. Gabungkan Methods Spesifik Setting ke window.methodsVue
    Object.assign(window.methodsVue, {
        // Method helper untuk menampilkan Snackbar
        showSnackbar: function(message, type = 'success') {
            this.snackbarMessage = message;
            this.snackbarType = type;
            this.snackbar = true;
        },

        // --- UPLOAD METHODS (LOGO) ---
        onFileChange() {
            if (!this.image) {
                this.onFileClear();
                return;
            }
            const reader = new FileReader();
            reader.readAsDataURL(this.image);
            reader.onload = e => {
                this.imagePreview = e.target.result;
                this.uploadFile(this.imagePreview); // Langsung upload setelah preview
            }
        },
        onFileClear() {
            this.image = null;
            this.imagePreview = null;
            this.overlay = false;
        },
        uploadFile: function(file) {
            var formData = new FormData() 
            var block = file.split(";"); 
            var contentType = block[0].split(":")[1]; 
            var realData = block[1].split(",")[1]; 

            var blob = b64toBlob(realData, contentType);
            formData.append('image', blob, this.image.name); // Tambahkan nama file
            formData.append('id', this.settingId); // ID setting yang di-update
            this.loading2 = true;
            
            // 💥 KOREKSI ENDPOINT UPLOAD
            axios.post(`<?= base_url() ?>/api/setting/upload`, formData)
                .then(res => {
                    this.loading2 = false;
                    var data = res.data;
                    if (data.status == true) {
                        this.showSnackbar(data.message, 'success');
                        this.valueEdit = data.data[0]; // Ambil URL dari array respons
                        this.overlay = true;
                        this.getSetting(); // Refresh data table
                        setTimeout(() => this.overlay = false, 1500);
                    } else {
                        this.showSnackbar(data.message, 'error');
                        this.image = null;
                        this.imagePreview = null;
                    }
                })
                .catch(err => {
                    console.error("Error uploading file:", err.response);
                    this.showSnackbar('Gagal upload file.', 'error');
                    this.loading2 = false;
                })
        },

        // Get Data
        getSetting: function() {
            this.loading = true;
            // 💥 KOREKSI ENDPOINT GET
            axios.get('<?= base_url() ?>/api/setting/general') 
                .then(res => {
                    this.loading = false;
                    var data = res.data;
                    if (data.status == true) {
                        this.settingData = data.data || [];
                    } else {
                        this.showSnackbar(data.message, 'error');
                    }
                })
                .catch(err => {
                    console.error("Error fetching settings:", err.response);
                    this.showSnackbar('Gagal memuat pengaturan. (Cek Route API)', 'error');
                    this.loading = false;
                })
        },

        // Get Item Edit
        editItem: function(item) {
            this.modalEdit = true;
            this.settingId = item.id;
            this.groupEdit = item.group_setting;
            this.variableEdit = item.variable_setting;
            this.deskripsiEdit = item.deskripsi_setting;
            this.valueEdit = item.value_setting;
            
            // Reset image state saat modal dibuka
            this.image = null;
            this.imagePreview = null;
            this.overlay = false;
            
            if(this.$refs.form) this.$refs.form.resetValidation();
        },

        modalEditClose: function() {
            this.modalEdit = false;
            // Reset image state saat modal ditutup
            this.image = null;
            this.imagePreview = null;
            this.overlay = false;
            if(this.$refs.form) this.$refs.form.resetValidation();
        },

        //Update Setting (Non-Logo)
        updateSetting: function() {
            if (!this.$refs.form.validate()) return;
            this.loading2 = true;
            axios.put(`<?= base_url() ?>/api/setting/update/${this.settingId}`, {
                    value_setting: this.valueEdit
                })
                .then(res => {
                    this.loading2 = false;
                    var data = res.data;
                    if (data.status == true) {
                        this.showSnackbar(data.message, 'success');
                        this.modalEdit = false;
                        this.getSetting(); // Refresh data table
                    } else {
                        this.showSnackbar(data.message, 'error');
                        errorKeys = Object.keys(data.data);
                        errorKeys.forEach((el) => { this[`${el}Error`] = data.data[el]; });
                        setTimeout(() => { errorKeys.forEach((el) => { this[`${el}Error`] = ""; }); }, 4000);
                    }
                })
                .catch(err => {
                    console.error("Error updating settings:", err.response);
                    this.showSnackbar('Gagal update pengaturan.', 'error');
                    this.loading2 = false;
                })
        },
    });

    // 3. Created Hook
    window.createdVue = function() {
        // Panggil created hook default dari layout jika ada
        if (typeof window.defaultCreatedVue === 'function') {
            window.defaultCreatedVue.call(this);
        }
        this.getSetting();
        console.log("Setting General View: Data Loaded");
    };
</script>
<?php $this->endSection("js") ?>