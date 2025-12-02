<?php $this->extend("layouts/backend"); ?>
<?php $this->section("content"); ?>
<template>
    <v-card>
        <v-card-title>
            <h1 class="font-weight-medium"><?= $title; ?></h1>
            <v-spacer></v-spacer>
            <!-- Switch Youtube -->
            <v-switch v-model="videoYoutube" value="videoYoutube" false-value="no" true-value="yes" label="Aktifkan Youtube" color="error" @click="setYoutube" hide-details></v-switch>
        </v-card-title>
        <v-toolbar flat>
            <!-- Button Tambah -->
            <v-btn color="primary" dark large @click="modalAddOpen" elevation="1">
                <v-icon>mdi-plus</v-icon> <?= lang('App.add') ?>
            </v-btn>
            <v-spacer></v-spacer>
            <v-text-field v-model="pencarian" append-icon="mdi-magnify" label="<?= lang('App.search') ?>" single-line hide-details>
            </v-text-field>
        </v-toolbar>
        <v-data-table :headers="dataHeader" :items="dataVideo" :items-per-page="5" :loading="loading" :search="pencarian" class="elevation-0" loading-text="<?= lang('App.loadingWait'); ?>">
            <template v-slot:item="{ item }">
                <tr>
                    <td width="80">{{item.id}}</td>
                    <td width="150">{{item.judul}}<br />{{item.upload_time}}</td>
                    <td>
                        <div v-if="item.source == 1">
                            <video width="220" controls>
                                <source :src="'<?= base_url() ?>' + '/' + item.video_url" type="video/mp4">
                                Browser Anda tidak mendukung video HTML5.
                            </video>
                        </div>
                        <div v-else>
                            <iframe width="220" :src="'https://www.youtube.com/embed/' + item.kode_youtube" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
                        </div>
                    </td>
                    <td>
                        <v-switch v-model="item.status" value="status" false-value="0" true-value="1" color="success" @click="setAktif(item)"></v-switch>
                    </td>
                    <td width="200">
                        <v-btn color="primary" class="mr-2" icon @click="editItem(item)">
                            <v-icon>mdi-pencil</v-icon>
                        </v-btn>
                        <v-btn color="error" icon @click="deleteItem(item)">
                            <v-icon>mdi-delete</v-icon>
                        </v-btn>
                    </td>
                </tr>
            </template>
        </v-data-table>
    </v-card>
</template>

<!-- Modal Save -->
<template>
    <v-row justify="center">
        <v-dialog v-model="modalAdd" max-width="700px" persistent scrollable>
            <v-card>
                <v-card-title>
                    <?= lang('App.add') ?> <?= $title; ?>
                    <v-spacer></v-spacer>
                    <v-btn icon @click="modalAddClose"><v-icon>mdi-close</v-icon></v-btn>
                </v-card-title>
                <v-divider></v-divider>
                <v-card-text class="pt-5">
                    <v-form ref="form" v-model="valid">
                        <p class="mb-3 text-subtitle-1">Judul</p>
                        <v-text-field v-model="judul" label="Judul Video" :error-messages="judulError" outlined></v-text-field>

                        <p class="mb-3 text-subtitle-1">Source Video</p>
                        <v-select v-model="source" :items="dataSource" item-text="text" item-value="value" placeholder="Pilih Source Video" :error-messages="sourceError" outlined></v-select>

                        <div v-if="source == 1">
                            <p class="mb-0 text-subtitle-1">Video</p>
                            <v-file-input v-model="video" show-size label="Video Upload" id="file" class="mb-2" accept=".mp4" prepend-icon="mdi-camera" @change="onFileChange" :loading="loading2" :error-messages="video_urlError"></v-file-input>
                        </div>
                        <div v-else-if="source == 2">
                            <p class="mb-0 text-subtitle-1">Video Youtube</p>
                            <v-text-field v-model="kodeYoutube" label="Kode Video Youtube" :error-messages="kode_youtubeError" outlined hint="Contoh: https://www.youtube.com/watch?v=IvjxrQ8c4-w. Copy kode IvjxrQ8c4-w sebagai kode Youtube." persistent-hint></v-text-field>
                            <v-alert color="yellow lighten-2" icon="mdi-information" light class="text-body-2" dense>
                            Infomasi! menggunakan video youtube membutuhkan resource memory yang lebih banyak.
                            </v-alert>
                        </div>

                        <p class="mb-3 text-subtitle-1">Status</p>
                        <v-checkbox v-model="status" label="Aktif" class="mt-0" :error-messages="statusError"></v-checkbox>
                    </v-form>
                </v-card-text>
                <v-divider></v-divider>
                <v-card-actions>
                    <v-spacer></v-spacer>
                    <v-btn large color="primary" @click="saveVideo" :loading="loading" elevation="1" :disabled="videoUrl == '' && kodeYoutube == ''">
                        <v-icon>mdi-content-save</v-icon> <?= lang('App.save') ?>
                    </v-btn>
                </v-card-actions>
            </v-card>
        </v-dialog>
    </v-row>
</template>

<!-- Modal Edit -->
<template>
    <v-row justify="center">
        <v-dialog v-model="modalEdit" max-width="700px" persistent scrollable>
            <v-card>
                <v-card-title>
                    <?= lang('App.edit') ?> <?= $title; ?>
                    <v-spacer></v-spacer>
                    <v-btn icon @click="modalEditClose"><v-icon>mdi-close</v-icon></v-btn>
                </v-card-title>
                <v-card-text class="pt-5">
                    <v-form ref="form" v-model="valid">
                        <p class="mb-3 text-subtitle-1">Judul</p>
                        <v-text-field v-model="judul" label="Judul Video" :error-messages="judulError" outlined></v-text-field>
                    </v-form>
                </v-card-text>
                <v-divider></v-divider>
                <v-card-actions>
                    <v-spacer></v-spacer>
                    <v-btn large color="primary" @click="updateVideo" :loading="loading">
                        <v-icon>mdi-content-save</v-icon> <?= lang('App.update') ?>
                    </v-btn>
                </v-card-actions>
            </v-card>
        </v-dialog>
    </v-row>
</template>

<!-- Modal Delete -->
<template>
    <v-row justify="center">
        <v-dialog v-model="modalDelete" persistent max-width="600px">
            <v-card class="pa-2">
                <v-card-title>
                    <v-icon color="error" class="mr-2" x-large>mdi-alert-octagon</v-icon> <?= lang('App.delConfirm') ?>
                </v-card-title>
                <v-card-text>
                    <div class="mt-5 py-4"><h2 class="font-weight-regular">Apakah anda yakin ingin menghapus?</h2></div>
                </v-card-text>
                <v-card-actions>
                    <v-spacer></v-spacer>
                    <v-btn large text @click="modalDeleteClose"><?= lang('App.no') ?></v-btn>
                    <v-btn large color="primary" dark @click="deleteVideo" :loading="loading"><?= lang('App.yes') ?></v-btn>
                    <v-spacer></v-spacer>
                </v-card-actions>
            </v-card>
        </v-dialog>
    </v-row>
</template>

<?php $this->endSection("content") ?>

<?php $this->section("js") ?>
<script>
    // Helper function upload
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

    // --- LOGIKA JWT LAMA DIHAPUS (Tidak ada token & options dideklarasikan di sini) ---

    window.dataVue = {
        ...window.dataVue,
        pencarian: "",
        modalAdd: false,
        modalEdit: false,
        modalDelete: false,
        dataHeader: [
            { text: "#", value: "id" },
            { text: "Judul", value: "judul" },
            { text: "Video", value: "video_url" },
            { text: "Status", value: "status" },
            { text: "<?= lang('App.action') ?>", value: "actions", sortable: false },
        ],
        dataVideo: [],
        dataSource: [
            { text: "MP4", value: "1" },
            { text: "Youtube", value: "2" }
        ],
        idVideo: "",
        judul: "",
        judulError: "",
        source: "1",
        sourceError: "",
        videoUrl: "",
        video_urlError: "",
        videoUrlEdit: null,
        kodeYoutube: "",
        kode_youtubeError: "",
        status: false,
        statusError: "",
        video: null,
        videoPreview: null,
        overlay: false,
        idSetting: "19", 
        videoYoutube: "<?= $videoYoutube; ?>",
    }

    window.createdVue = function() {
        this.getVideo();
    }

    window.methodsVue = {
        ...window.methodsVue,
        modalAddOpen: function() {
            this.modalAdd = true;
            this.source = "1";
            this.kodeYoutube = "";
        },
        modalAddClose: function() {
            this.modalAdd = false;
        },
        
        // GET DATA VIDEO
        getVideo: function() {
            this.loading = true;
            // AXIOS POLOS
            axios.get('<?= base_url(); ?>/api/video')
                .then(res => {
                    this.loading = false;
                    var data = res.data;
                    if (data.status == true) {
                        this.dataVideo = data.data;
                    } else {
                        this.snackbar = true;
                        this.snackbarMessage = data.message;
                    }
                })
                .catch(err => {
                    console.error("Error fetching video:", err.response);
                    this.loading = false;
                })
        },

        // UPLOAD HANDLER
        onFileChange() {
            const reader = new FileReader()
            reader.readAsDataURL(this.video)
            reader.onload = e => {
                this.videoPreview = e.target.result;
                this.uploadFile(this.videoPreview);
            }
        },
        onFileClear() {
            this.video = null;
            this.videoPreview = null;
            this.snackbar = true;
            this.snackbarMessage = 'Video dihapus';
        },
        uploadFile: function(file) {
            var formData = new FormData() 
            var block = file.split(";"); 
            var contentType = block[0].split(":")[1]; 
            var realData = block[1].split(",")[1]; 
            var blob = b64toBlob(realData, contentType);
            formData.append('video', blob);
            
            this.loading2 = true;
            // AXIOS POLOS
            axios.post(`<?= base_url() ?>/api/video/upload`, formData)
                .then(res => {
                    this.loading2 = false
                    var data = res.data;
                    if (data.status == true) {
                        this.snackbar = true;
                        this.snackbarMessage = data.message;
                        this.uploadData = data.data;
                        this.videoUrl = this.uploadData.url;
                    } else {
                        this.snackbar = true;
                        this.snackbarMessage = data.message;
                    }
                })
                .catch(err => {
                    console.error("Error uploading video:", err.response);
                    this.loading2 = false;
                })
        },

        // SAVE VIDEO
        saveVideo: function() {
            this.loading = true;
            // AXIOS POLOS
            axios.post(`<?= base_url(); ?>/api/video/save`, {
                    judul: this.judul,
                    source: this.source,
                    video_url: this.videoUrl,
                    kode_youtube: this.kodeYoutube,
                    status: this.status,
                })
                .then(res => {
                    this.loading = false
                    var data = res.data;
                    if (data.status == true) {
                        this.snackbar = true;
                        this.snackbarMessage = data.message;
                        this.getVideo();
                        this.judul = "";
                        this.kodeYoutube = "";
                        this.source = "1";
                        this.videoUrl = "";
                        this.video = null;
                        this.modalAdd = false;
                        this.$refs.form.resetValidation();
                        this.$refs.form.reset();
                    } else {
                        this.snackbar = true;
                        this.snackbarMessage = data.message;
                        // Error keys logic...
                        this.modalAdd = true;
                        this.$refs.form.validate();
                    }
                })
                .catch(err => {
                    console.error("Error saving video:", err.response);
                    this.loading = false;
                })
        },

        // EDIT & UPDATE
        editItem: function(item) {
            this.modalEdit = true;
            this.idVideo = item.id;
            this.judul = item.judul;
        },
        modalEditClose: function() {
            this.modalEdit = false;
            this.$refs.form.resetValidation();
        },
        updateVideo: function() {
            this.loading = true;
            // AXIOS POLOS
            axios.put(`<?= base_url(); ?>/api/video/update/${this.idVideo}`, {
                    judul: this.judul,
                })
                .then(res => {
                    this.loading = false;
                    var data = res.data;
                    if (data.status == true) {
                        this.snackbar = true;
                        this.snackbarMessage = data.message;
                        this.getVideo();
                        this.modalEdit = false;
                    } else {
                        this.snackbar = true;
                        this.snackbarMessage = data.message;
                    }
                })
                .catch(err => {
                    console.error("Error updating video:", err.response);
                    this.loading = false;
                })
        },

        // DELETE
        deleteItem: function(item) {
            this.modalDelete = true;
            this.idVideo = item.id;
        },
        modalDeleteClose: function() {
            this.modalDelete = false;
        },
        deleteVideo: function() {
            this.loading = true;
            // AXIOS POLOS
            axios.delete(`<?= base_url(); ?>/api/video/delete/${this.idVideo}`)
                .then(res => {
                    this.loading = false;
                    var data = res.data;
                    if (data.status == true) {
                        this.snackbar = true;
                        this.snackbarMessage = data.message;
                        this.getVideo();
                        this.modalDelete = false;
                    } else {
                        this.modalDelete = true;
                    }
                })
                .catch(err => {
                    console.error("Error deleting video:", err.response);
                    this.loading = false;
                })
        },

        // SET AKTIF (AXIOS POLOS)
        setAktif: function(item) {
            this.loading = true;
            this.idVideo = item.id;
            this.status = item.status;
            // AXIOS POLOS
            axios.put(`<?= base_url(); ?>/api/video/setaktif/${this.idVideo}`, {
                    status: this.status,
                })
                .then(res => {
                    this.loading = false;
                    var data = res.data;
                    if (data.status == true) {
                        this.snackbar = true;
                        this.snackbarMessage = data.message;
                        this.getVideo();
                    }
                })
                .catch(err => {
                    console.error("Error setting active status:", err.response);
                    this.loading = false;
                })
        },

        // SET YOUTUBE MODE (AXIOS POLOS)
        setYoutube: function() {
            this.loading = true;
            // Mengupdate Setting (Aman karena route Setting dilindungi)
            axios.put(`<?= base_url(); ?>/api/setting/change/${this.idSetting}`, {
                    value_setting: this.videoYoutube,
                })
                .then(res => {
                    this.loading = false;
                    var data = res.data;
                    if (data.status == true) {
                        this.snackbar = true;
                        this.snackbarMessage = data.message;
                    }
                })
                .catch(err => {
                    console.error("Error setting Youtube mode:", err.response);
                    this.loading = false;
                })
        },
    }
</script>
<?php $this->endSection("js") ?>