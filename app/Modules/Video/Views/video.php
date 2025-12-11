<?php $this->extend("layouts/backend"); ?>
<?php $this->section("content"); ?>
<template>
    <!-- Card Kontainer Utama -->
    <v-card class="rounded-xl elevation-4">
        
        <!-- Header Halaman -->
        <v-card-title class="pa-4 grey lighten-5">
            <v-icon left color="indigo">mdi-video-box</v-icon>
            <h1 class="font-weight-bold text-h5 grey--text text--darken-3"><?= $title; ?></h1>
            <v-spacer></v-spacer>
            <!-- Switch Youtube -->
            <v-switch 
                v-model="videoYoutube" 
                :true-value="'yes'" 
                :false-value="'no'" 
                label="Aktifkan Youtube" 
                color="error" 
                @change="setYoutube" 
                hide-details
                class="mt-0"
            ></v-switch>
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
                        label="Cari Judul..." 
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
            :items="dataVideo" 
            :items-per-page="10" 
            :loading="loading" 
            :search="pencarian" 
            class="elevation-0 px-4 pb-4 my-4 pt-0" 
            loading-text="<?= lang('App.loadingWait'); ?>"
        >
            <template v-slot:item.video_preview="{ item }">
                <div class="py-2">
                    <div v-if="item.source == '1'">
                        <!-- Video Lokal -->
                        <video width="200" controls class="rounded-lg">
                            <source :src="'<?= base_url() ?>' + '/' + item.video_url" type="video/mp4">
                            Browser Anda tidak mendukung video HTML5.
                        </video>
                    </div>
                    <div v-else>
                        <!-- Video Youtube -->
                        <iframe width="200" height="112" :src="'https://www.youtube.com/embed/' + item.kode_youtube" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen class="rounded-lg"></iframe>
                    </div>
                </div>
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
                <!-- Tombol Edit hanya untuk judul, karena sumber video tidak bisa diubah -->
                <v-btn color="primary" class="mr-1" small @click="editItem(item)" title="Edit Judul">
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
                        <!-- Judul -->
                        <p class="mb-2 text-subtitle-1 font-weight-medium grey--text text--darken-2">Judul Video</p>
                        <v-text-field v-model="judul" label="Judul Video" :error-messages="judulError" outlined dense></v-text-field>

                        <!-- SOURCE SELECTION (Hanya untuk mode ADD) -->
                        <div v-if="!isEdit">
                            <p class="mb-2 text-subtitle-1 font-weight-medium grey--text text--darken-2">Source Video</p>
                            <v-select v-model="source" :items="dataSource" item-text="text" item-value="value" placeholder="Pilih Source Video" :error-messages="sourceError" outlined dense></v-select>
                        </div>
                        
                        <!-- UPLOAD MP4 (Source 1) -->
                        <div v-if="source == '1' && !isEdit">
                            <p class="mb-2 text-subtitle-1 font-weight-medium grey--text text--darken-2">Video MP4</p>
                            <v-file-input 
                                v-model="video" 
                                show-size 
                                label="Video Upload" 
                                id="file" 
                                class="mb-2" 
                                accept=".mp4" 
                                prepend-icon="mdi-video-wireless" 
                                @change="onFileChange" 
                                :loading="loading2" 
                                :error-messages="video_urlError"
                                outlined
                                dense
                            ></v-file-input>
                        </div>
                        
                        <!-- KODE YOUTUBE (Source 2) -->
                        <div v-else-if="source == '2' && !isEdit">
                            <p class="mb-2 text-subtitle-1 font-weight-medium grey--text text--darken-2">Kode Video Youtube</p>
                            <v-text-field 
                                v-model="kodeYoutube" 
                                label="Kode Video Youtube" 
                                :error-messages="kode_youtubeError" 
                                outlined 
                                dense
                                hint="Contoh: https://www.youtube.com/watch?v=IvjxrQ8c4-w. Copy kode IvjxrQ8c4-w saja." 
                                persistent-hint
                            ></v-text-field>
                            <v-alert color="yellow lighten-2" icon="mdi-information" light class="text-body-2" dense>
                                Infomasi! menggunakan video youtube membutuhkan resource memory yang lebih banyak.
                            </v-alert>
                        </div>
                        
                    </v-form>
                </v-card-text>
                <v-divider></v-divider>
                
                <!-- Tombol Aksi -->
                <v-card-actions class="py-3">
                    <v-spacer></v-spacer>
                    <v-btn large text @click="modalAddClose" class="mr-2">Tutup</v-btn>
                    
                    <v-btn 
                        large 
                        :color="isEdit ? 'teal darken-1' : 'primary'" 
                        @click="isEdit ? updateVideo() : saveVideo()" 
                        :loading="loading" 
                        elevation="2"
                        :disabled="!isEdit && source == '1' && videoUrl == ''"
                    >
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
                    <v-btn large color="red darken-2" dark @click="deleteVideo" :loading="loading">Ya, Hapus</v-btn>
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

    // 1. Gabungkan Data Spesifik Video ke window.dataVue
    Object.assign(window.dataVue, {
        // --- Properti Default Layout yang WAJIB ada (Anti-ReferenceError) ---
        snackbar: false,
        timeout: 4000, 
        snackbarType: '',
        snackbarMessage: '',
        valid: true, // Untuk Form Validasi
        
        // --- Properti Spesifik View Video ---
        pencarian: "",
        modalAdd: false, 
        modalDelete: false,
        loading: false, // Loading utama
        loading2: false, // Loading upload video
        isEdit: false, // State untuk menentukan mode Edit atau Add
        
        // Data Headers
        dataHeader: [
            { text: "No.", value: "id", sortable: false, width: '5%' },
            { text: "Judul", value: "judul", width: '30%' },
            { text: "Preview", value: "video_preview", sortable: false, width: '25%' },
            { text: "Status", value: "status", width: '10%' },
            { text: "<?= lang('App.action') ?>", value: "actions", sortable: false, width: '15%' },
        ],
        dataVideo: <?= json_encode($dataVideo ?? []) ?> || [], 
        dataSource: [
            { text: "MP4 (Local)", value: "1" },
            { text: "Youtube (Embed)", value: "2" }
        ],
        
        // Data Form (untuk Add/Edit)
        idVideo: "",
        judul: "",
        judulError: "",
        source: "1", // Default ke MP4
        sourceError: "",
        videoUrl: "", // URL final video yang disimpan di DB
        video_urlError: "",
        videoUrlEdit: null, // URL video lama (khusus edit)
        kodeYoutube: "",
        kode_youtubeError: "",
        status: "0", 
        video: null, // File input
        videoPreview: null, // URL preview base64
        
        // Data Setting Global (Untuk Switch Youtube)
        idSetting: "19", 
        videoYoutube: "<?= $videoYoutube ?? 'no'; ?>", // Status Youtube dari PHP
    });

    // 2. Gabungkan Methods Spesifik Video ke window.methodsVue
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
            this.idVideo = "";
            this.judul = "";
            this.source = "1"; // Reset ke default MP4
            this.videoUrl = "";
            this.kodeYoutube = "";
            this.video = null;
            this.videoPreview = null;
        },
        
        // Get Data
        getVideo: function() {
            this.loading = true;
            axios.get('<?= base_url(); ?>/api/video')
                .then(res => {
                    this.loading = false;
                    var data = res.data;
                    if (data.status == true) {
                         // Pastikan status di-set ke string untuk v-switch
                        this.dataVideo = data.data.map(item => ({
                            ...item,
                            status: String(item.status), 
                            loadingStatus: false
                        }));
                    } else {
                        this.showSnackbar(data.message, 'error');
                    }
                })
                .catch(err => {
                    console.error("Error fetching video:", err.response);
                    this.loading = false;
                })
        },

        // --- UPLOAD METHODS (Untuk MP4) ---
        onFileChange() {
            if (!this.video) {
                this.videoUrl = ""; // Clear video URL jika file dikosongkan
                return;
            }
            const reader = new FileReader();
            reader.readAsDataURL(this.video);
            reader.onload = e => {
                this.videoPreview = e.target.result;
                this.uploadFile(this.videoPreview);
            }
        },
        uploadFile: function(file) {
            var formData = new FormData(); 
            var block = file.split(";"); 
            var contentType = block[0].split(":")[1]; 
            var realData = block[1].split(",")[1]; 

            var blob = b64toBlob(realData, contentType);
            formData.append('video', blob, this.video.name); 
            this.loading2 = true; // Loading khusus upload
            
            axios.post(`<?= base_url() ?>/api/video/upload`, formData)
                .then(res => {
                    this.loading2 = false;
                    var data = res.data;
                    if (data.status == true) {
                        this.showSnackbar(data.message, 'success');
                        this.videoUrl = data.data[0]; // Ambil URL dari respons API
                    } else {
                        this.showSnackbar(data.message, 'error');
                        this.video = null; 
                    }
                })
                .catch(err => {
                    console.error("Error uploading video:", err.response);
                    this.showSnackbar('Gagal upload file.', 'error');
                    this.loading2 = false;
                    this.video = null; 
                })
        },
        // --- END UPLOAD METHODS ---

        // Save Video
        saveVideo: function() {
            if (!this.$refs.form.validate()) return;
            
            // Cek validasi spesifik source
            if (this.source === '1' && this.videoUrl === '') {
                this.video_urlError = 'Video MP4 wajib di-upload.';
                return;
            }
            if (this.source === '2' && this.kodeYoutube === '') {
                this.kode_youtubeError = 'Kode Youtube wajib diisi.';
                return;
            }

            this.loading = true;
            axios.post(`<?= base_url(); ?>/api/video/save`, {
                    judul: this.judul,
                    source: this.source,
                    video_url: this.videoUrl,
                    kode_youtube: this.kodeYoutube,
                    status: this.status,
                })
                .then(res => {
                    this.loading = false;
                    var data = res.data;
                    if (data.status == true) {
                        this.showSnackbar(data.message, 'success');
                        this.getVideo();
                        this.modalAddClose();
                    } else {
                        this.showSnackbar(data.message, 'error');
                        // Handle Error Validation
                        errorKeys = Object.keys(data.data);
                        errorKeys.forEach((el) => { this[`${el}Error`] = data.data[el]; });
                        setTimeout(() => { errorKeys.forEach((el) => { this[`${el}Error`] = ""; }); }, 4000); 
                    }
                })
                .catch(err => {
                    console.error("Error saving video:", err.response);
                    this.showSnackbar('Gagal menyimpan data. Cek server.', 'error');
                    this.loading = false;
                })
        },

        // Get Item Edit
        editItem: function(item) {
            this.isEdit = true; // Mode Edit
            this.modalAdd = true;
            // Map data
            this.idVideo = item.id;
            this.judul = item.judul;
            // Sumber video tidak bisa diubah, hanya judul
            
            if (this.$refs.form) this.$refs.form.resetValidation();
        },

        //Update Video (Hanya Judul yang diupdate)
        updateVideo: function() {
            if (!this.$refs.form.validate()) return;
            this.loading = true;
            axios.put(`<?= base_url(); ?>/api/video/update/${this.idVideo}`, {
                    judul: this.judul,
                })
                .then(res => {
                    this.loading = false;
                    var data = res.data;
                    if (data.status == true) {
                        this.showSnackbar(data.message, 'success');
                        this.getVideo();
                        this.modalAddClose();
                    } else {
                        this.showSnackbar(data.message, 'error');
                        // Handle Error Validation
                        errorKeys = Object.keys(data.data);
                        errorKeys.forEach((el) => { this[`${el}Error`] = data.data[el]; });
                        setTimeout(() => { errorKeys.forEach((el) => { this[`${el}Error`] = ""; }); }, 4000); 
                    }
                })
                .catch(err => {
                    console.error("Error updating video:", err.response);
                    this.showSnackbar('Gagal update data. Cek server.', 'error');
                    this.loading = false;
                })
        },

        // Get Item Delete
        deleteItem: function(item) {
            this.modalDelete = true;
            this.idVideo = item.id;
        },

        modalDeleteClose: function() {
            this.modalDelete = false;
        },

        // Delete Video
        deleteVideo: function() {
            this.loading = true;
            axios.delete(`<?= base_url(); ?>/api/video/delete/${this.idVideo}`)
                .then(res => {
                    this.loading = false;
                    var data = res.data;
                    if (data.status == true) {
                        this.showSnackbar(data.message, 'success');
                        this.getVideo();
                        this.modalDeleteClose();
                    } else {
                        this.showSnackbar(data.message, 'error');
                    }
                })
                .catch(err => {
                    console.error("Error deleting video:", err.response);
                    this.showSnackbar('Gagal hapus data. Cek server.', 'error');
                    this.loading = false;
                })
        },

        // Set Item Aktif (Toggle Switch)
        setAktif: function(item) {
            const index = this.dataVideo.findIndex(v => v.id === item.id);
            if (index !== -1) {
                this.$set(this.dataVideo[index], 'loadingStatus', true);
            }
            
            axios.put(`<?= base_url(); ?>/api/video/setaktif/${item.id}`, {
                    status: item.status,
                })
                .then(res => {
                    this.showSnackbar(res.data.message, res.data.status ? 'success' : 'error');
                })
                .catch(err => {
                    console.error("Error setting active status:", err.response);
                    this.showSnackbar('Gagal update status.', 'error');
                    item.status = item.status === '1' ? '0' : '1'; 
                })
                .finally(() => {
                    if (index !== -1) {
                        this.$set(this.dataVideo[index], 'loadingStatus', false);
                    }
                })
        },

        // SET YOUTUBE MODE (AXIOS POLOS)
        setYoutube: function() {
            this.loading = true;
            axios.put(`<?= base_url(); ?>/api/setting/change/${this.idSetting}`, {
                    value_setting: this.videoYoutube,
                })
                .then(res => {
                    this.loading = false;
                    this.showSnackbar(res.data.message, res.data.status ? 'success' : 'error');
                })
                .catch(err => {
                    console.error("Error setting Youtube mode:", err.response);
                    this.showSnackbar('Gagal update setting Youtube.', 'error');
                    this.loading = false;
                })
        },
    });

    // 3. Created Hook
    window.createdVue = function() {
        this.getVideo();
        console.log("Video View: Data Loaded");
    };
</script>
<?php $this->endSection("js") ?>