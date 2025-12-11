<?php $this->extend("layouts/backend"); ?>
<?php $this->section("content"); ?>
<template>
    <!-- Card Kontainer Utama (Lebih elegan) -->
    <v-card class="rounded-xl elevation-4">
        
        <!-- Header Halaman -->
        <v-card-title class="pa-4 grey lighten-5">
            <v-icon left color="indigo">mdi-newspaper-variant-outline</v-icon>
            <h1 class="font-weight-bold text-h5 grey--text text--darken-3"><?= $title; ?></h1>
        </v-card-title>
        
        <!-- 💥 KOREKSI: Toolbar Responsif (menggunakan v-card-text dan v-row) -->
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
            :items="dataNews" 
            :items-per-page="10" 
            :loading="loading" 
            :search="pencarian" 
            class="elevation-0 px-4 pb-4 my-4 pt-0" 
            loading-text="<?= lang('App.loadingWait'); ?>"
        >
            <template v-slot:item.actions="{ item }">
                <v-btn color="primary" class="mr-1" small @click="editItem(item)">
                    <v-icon small>mdi-pencil-outline</v-icon>
                </v-btn>
                <v-btn color="error" small @click="deleteItem(item)">
                    <v-icon small>mdi-delete-empty-outline</v-icon>
                </v-btn>
            </template>
            <template v-slot:item.jenis_news="{ item }">
                <!-- Chip berwarna dan jelas -->
                <v-chip :color="item.jenis_news == '1' ? 'teal' : (item.jenis_news == '2' ? 'blue' : 'purple')" dark small>
                    {{ item.jenis_news == '1' ? 'News Ticker' : (item.jenis_news == '2' ? 'Info Umum' : 'Info Masjid') }}
                </v-chip>
            </template>
            <!-- Slot untuk menampilkan nomor urut (No.) -->
            <template v-slot:item.id="{ item, index }">
                {{ index + 1 }} 
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
                        <!-- 💥 KOREKSI: Responsiveness untuk Tanggal & Jenis -->
                        <v-row>
                            <!-- Kolom Tanggal -->
                            <v-col cols="12" md="6">
                                <p class="mb-2 text-subtitle-1 font-weight-medium grey--text text--darken-2">Tanggal</p>
                                <v-menu ref="menu" v-model="menu" :close-on-content-click="false" transition="scale-transition" offset-y min-width="auto">
                                    <template v-slot:activator="{ on, attrs }">
                                        <!-- Field ringkas (dense) -->
                                        <v-text-field v-model="tglNews" label="Pilih Tanggal" prepend-inner-icon="mdi-calendar" readonly v-bind="attrs" v-on="on" :error-messages="tgl_newsError" outlined dense></v-text-field>
                                    </template>
                                    <v-date-picker v-model="tglNews" @input="menu = false" color="primary"></v-date-picker>
                                </v-menu>
                            </v-col>

                            <!-- Kolom Jenis -->
                            <v-col cols="12" md="6">
                                <p class="mb-2 text-subtitle-1 font-weight-medium grey--text text--darken-2">Jenis</p>
                                <!-- Field ringkas (dense) -->
                                <v-select v-model="jenisNews" :items="dataJenis" item-text="text" item-value="value" placeholder="Pilih Jenis" :error-messages="jenis_newsError" outlined dense></v-select>
                            </v-col>
                        </v-row>

                        <!-- Isi Berita -->
                        <p class="mb-2 text-subtitle-1 font-weight-medium grey--text text--darken-2">Isi Berita/Judul</p>
                        <v-textarea v-model="textNews" :rules="[rules.varchar]" rows="4" auto-grow counter :error-messages="text_newsError" outlined></v-textarea>

                    </v-form>
                </v-card-text>
                <v-divider></v-divider>
                
                <!-- Tombol Aksi (Gunakan isEdit untuk menentukan save/update) -->
                <v-card-actions class="py-3">
                    <v-spacer></v-spacer>
                    <v-btn large text @click="modalAddClose" class="mr-2">Tutup</v-btn>
                    
                    <v-btn large :color="isEdit ? 'teal darken-1' : 'primary'" @click="isEdit ? updateNews() : saveNews()" :loading="loading" elevation="2">
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
                    <v-btn large color="red darken-2" dark @click="deleteNews" :loading="loading">Ya, Hapus</v-btn>
                </v-card-actions>
            </v-card>
        </v-dialog>
    </v-row>
</template>
<?php $this->endSection("content") ?>

<?php $this->section("js") ?>
<script>
    // 💥 KOREKSI WAJIB: Amankan objek global sebelum merging
    // Ini adalah kunci agar property default dari backend.php tidak hilang
    window.dataVue = window.dataVue || {};
    window.methodsVue = window.methodsVue || {};
    window.computedVue = window.computedVue || {};

    // 1. Gabungkan Data Spesifik News ke window.dataVue
    Object.assign(window.dataVue, {
        // --- Properti Default Layout yang WAJIB ada (Anti-ReferenceError) ---
        snackbar: false,
        timeout: 4000, 
        snackbarType: '',
        snackbarMessage: '',
        valid: true, // Untuk Form Validasi
        
        // --- Properti Spesifik View News ---
        pencarian: "",
        modalAdd: false, 
        modalDelete: false,
        menu: false,
        menu2: false,
        loading: false, 
        isEdit: false, // State untuk menentukan mode Edit atau Add
        
        // Data Headers dan Jenis
        dataHeader: [
            { text: "No.", value: "id", sortable: false, width: '5%' }, // Kita tampilkan 'No.' tapi akan di-render manual
            { text: "Tanggal", value: "tgl_news", width: '15%' },
            { text: "Judul", value: "text_news" },
            { text: "Jenis", value: "jenis_news", width: '15%' },
            { text: "<?= lang('App.action') ?>", value: "actions", sortable: false, width: '15%' },
        ],
        dataNews: [], 
        dataJenis: [
            { text: "News Ticker", value: "1" }, 
            { text: "Info Umum", value: "2" }, 
            { text: "Info Masjid", value: "3" }
        ],
        
        // Data Form (untuk Add/Edit)
        idNews: "",
        tglNews: "",
        tgl_newsError: "",
        textNews: "",
        text_newsError: "",
        jenisNews: "",
        jenis_newsError: "",
    });

    // 2. Gabungkan Methods Spesifik News ke window.methodsVue
    Object.assign(window.methodsVue, {
        // Method helper untuk menampilkan Snackbar (ditingkatkan)
        showSnackbar: function(message, type = 'success') {
            this.snackbarMessage = message;
            this.snackbarType = type;
            this.snackbar = true;
        },

        // Method untuk membuka modal Add/Edit (Disatukan)
        modalAddOpen: function() {
            this.isEdit = false; // Mode Add
            this.modalAdd = true;
            if (this.$refs.form) {
                this.$refs.form.resetValidation();
                this.$refs.form.reset();
            }
            // Reset data spesifik
            this.idNews = "";
            this.jenisNews = "";
        },
        // Method untuk menutup modal Add/Edit (Disatukan)
        modalAddClose: function() {
            this.modalAdd = false;
            this.isEdit = false; 
            if (this.$refs.form) {
                this.$refs.form.resetValidation();
                this.$refs.form.reset();
            }
        },
        
        // Get Data
        getNews: function() {
            this.loading = true;
            axios.get('<?= base_url(); ?>/api/news')
                .then(res => {
                    this.loading = false;
                    var data = res.data;
                    if (data.status == true) {
                        this.dataNews = data.data;
                    } else {
                        this.showSnackbar(data.message, 'error');
                    }
                })
                .catch(err => {
                    console.error("Error fetching news:", err.response);
                    this.loading = false;
                })
        },

        // Save News
        saveNews: function() {
            if (!this.$refs.form.validate()) return;
            this.loading = true;
            axios.post(`<?= base_url(); ?>/api/news/save`, {
                    tgl_news: this.tglNews,
                    text_news: this.textNews,
                    jenis_news: this.jenisNews,
                })
                .then(res => {
                    this.loading = false;
                    var data = res.data;
                    if (data.status == true) {
                        this.showSnackbar(data.message, 'success');
                        this.getNews();
                        this.modalAddClose();
                    } else {
                        this.showSnackbar(data.message, 'error');
                        // Handle Error Validation
                        errorKeys = Object.keys(data.data);
                        errorKeys.forEach((el) => {
                            this[`${el}Error`] = data.data[el];
                        });
                        setTimeout(() => { 
                            errorKeys.forEach((el) => {
                                this[`${el}Error`] = "";
                            });
                        }, 4000); 
                    }
                })
                .catch(err => {
                    console.error("Error saving news:", err.response);
                    this.showSnackbar('Gagal menyimpan data. Cek server.', 'error');
                    this.loading = false;
                })
        },

        // Get Item Edit News
        editItem: function(item) {
            this.isEdit = true; // Mode Edit
            this.modalAdd = true;
            // Map data
            this.idNews = item.id;
            this.tglNews = item.tgl_news;
            this.textNews = item.text_news;
            this.jenisNews = item.jenis_news;
            if (this.$refs.form) {
                this.$refs.form.resetValidation();
            }
        },

        //Update News
        updateNews: function() {
            if (!this.$refs.form.validate()) return;
            this.loading = true;
            axios.put(`<?= base_url(); ?>/api/news/update/${this.idNews}`, {
                    tgl_news: this.tglNews,
                    text_news: this.textNews,
                    jenis_news: this.jenisNews,
                })
                .then(res => {
                    this.loading = false;
                    var data = res.data;
                    if (data.status == true) {
                        this.showSnackbar(data.message, 'success');
                        this.getNews();
                        this.modalAddClose();
                    } else {
                        this.showSnackbar(data.message, 'error');
                        // Handle Error Validation
                        errorKeys = Object.keys(data.data);
                        errorKeys.forEach((el) => {
                            this[`${el}Error`] = data.data[el];
                        });
                        setTimeout(() => { 
                            errorKeys.forEach((el) => {
                                this[`${el}Error`] = "";
                            });
                        }, 4000); 
                    }
                })
                .catch(err => {
                    console.error("Error updating news:", err.response);
                    this.showSnackbar('Gagal update data. Cek server.', 'error');
                    this.loading = false;
                })
        },

        // Get Item Delete
        deleteItem: function(item) {
            this.modalDelete = true;
            this.idNews = item.id;
        },

        modalDeleteClose: function() {
            this.modalDelete = false;
        },

        // Delete News
        deleteNews: function() {
            this.loading = true;
            axios.delete(`<?= base_url(); ?>/api/news/delete/${this.idNews}`)
                .then(res => {
                    this.loading = false;
                    var data = res.data;
                    if (data.status == true) {
                        this.showSnackbar(data.message, 'success');
                        this.getNews();
                        this.modalDeleteClose();
                    } else {
                        this.showSnackbar(data.message, 'error');
                    }
                })
                .catch(err => {
                    console.error("Error deleting news:", err.response);
                    this.showSnackbar('Gagal hapus data. Cek server.', 'error');
                    this.loading = false;
                })
        },
    });

    // 3. Created Hook
    window.createdVue = function() {
        this.getNews();
        console.log("News View: Data Loaded");
    };
</script>
<?php $this->endSection("js") ?>