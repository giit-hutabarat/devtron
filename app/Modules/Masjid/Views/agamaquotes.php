<?php $this->extend("layouts/backend"); ?>
<?php $this->section("content"); ?>
<template>
    <!-- Card Kontainer Utama -->
    <v-card class="rounded-xl elevation-4">
        
        <!-- Header Halaman -->
        <v-card-title class="pa-4 grey lighten-5">
            <v-icon left color="indigo">mdi-format-quote-open</v-icon>
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
                        label="Cari Quotes atau Surat Riwayat..." 
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
            :items="dataQuotes" 
            :items-per-page="10" 
            :loading="loading" 
            :search="pencarian" 
            class="elevation-0 px-4 pb-4 my-4 pt-0" 
            loading-text="<?= lang('App.loadingWait'); ?>"
        >
            <template v-slot:item.isi_quotes="{ item }">
                <!-- Quotes dipersingkat -->
                {{ item.isi_quotes.substring(0, 150) + (item.isi_quotes.length > 150 ? '...' : '') }}
            </template>
            
            <template v-slot:item.suratriwayat="{ item }">
                <!-- Chip Riwayat -->
                <v-chip color="blue-grey lighten-5" small class="font-weight-medium">
                    {{ item.suratriwayat }}
                </v-chip>
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
                        <p class="mb-2 text-subtitle-1 font-weight-medium grey--text text--darken-2">Isi Quotes</p>
                        <v-textarea 
                            v-model="isiQuotes" 
                            :rules="[rules.varchar]" 
                            rows="4" 
                            auto-grow 
                            counter 
                            :error-messages="isi_quotesError" 
                            outlined
                        ></v-textarea>

                        <p class="mb-2 text-subtitle-1 font-weight-medium grey--text text--darken-2">Surat Riwayat</p>
                        <v-text-field 
                            v-model="suratRiwayat" 
                            label="Contoh: QS. Al-Baqarah: 152 / HR. Bukhari" 
                            :error-messages="suratriwayatError" 
                            outlined
                            dense
                        ></v-text-field>
                    </v-form>
                </v-card-text>
                <v-divider></v-divider>
                
                <!-- Tombol Aksi -->
                <v-card-actions class="py-3">
                    <v-spacer></v-spacer>
                    <v-btn large text @click="modalAddClose" class="mr-2">Tutup</v-btn>
                    
                    <v-btn large :color="isEdit ? 'teal darken-1' : 'primary'" @click="isEdit ? updateQuotes() : saveQuotes()" :loading="loading" elevation="2">
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
                    <v-btn large color="red darken-2" dark @click="deleteQuotes" :loading="loading">Ya, Hapus</v-btn>
                </v-card-actions>
            </v-card>
        </v-dialog>
    </v-row>
</template>
<?php $this->endSection("content") ?>

<?php $this->section("js") ?>
<script>
    // 💥 KOREKSI WAJIB: Amankan objek global sebelum merging
    window.dataVue = window.dataVue || {};
    window.methodsVue = window.methodsVue || {};
    window.computedVue = window.computedVue || {};

    // 1. Gabungkan Data Spesifik Quotes ke window.dataVue
    Object.assign(window.dataVue, {
        // --- Properti Default Layout yang WAJIB ada (Anti-ReferenceError) ---
        snackbar: false,
        timeout: 4000, 
        snackbarType: '',
        snackbarMessage: '',
        valid: true, // Untuk Form Validasi
        
        // --- Properti Spesifik View Quotes ---
        pencarian: "",
        modalAdd: false, 
        modalDelete: false,
        loading: false, 
        isEdit: false, // State untuk menentukan mode Edit atau Add
        
        // Data Headers
        dataHeader: [
            { text: "No.", value: "id", sortable: false, width: '5%' },
            { text: "Isi Quotes", value: "isi_quotes" },
            { text: "Surat Riwayat", value: "suratriwayat", width: '25%' },
            { text: "<?= lang('App.action') ?>", value: "actions", sortable: false, width: '15%' },
        ],
        dataQuotes: <?= json_encode($dataQuotes ?? []) ?> || [], 
        
        // Data Form (untuk Add/Edit)
        idQuotes: "",
        isiQuotes: "",
        isi_quotesError: "",
        suratRiwayat: "",
        suratriwayatError: "",
    });

    // 2. Gabungkan Methods Spesifik Quotes ke window.methodsVue
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
            this.idQuotes = "";
            this.isiQuotes = "";
            this.suratRiwayat = "";
        },
        
        // Get Data
        getAgamaquotes: function() {
            this.loading = true;
            axios.get('<?= base_url(); ?>/api/agamaquotes')
                .then(res => {
                    this.loading = false;
                    var data = res.data;
                    if (data.status == true) {
                        this.dataQuotes = data.data;
                    } else {
                        this.showSnackbar(data.message, 'error');
                    }
                })
                .catch(err => {
                    console.error("Error fetching quotes:", err.response);
                    this.loading = false;
                })
        },

        // Save Quotes
        saveQuotes: function() {
            if (!this.$refs.form.validate()) return;
            this.loading = true;
            axios.post(`<?= base_url(); ?>/api/agamaquotes/save`, {
                    isi_quotes: this.isiQuotes,
                    suratriwayat: this.suratRiwayat,
                })
                .then(res => {
                    this.loading = false;
                    var data = res.data;
                    if (data.status == true) {
                        this.showSnackbar(data.message, 'success');
                        this.getAgamaquotes();
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
                    console.error("Error saving quotes:", err.response);
                    this.showSnackbar('Gagal menyimpan data. Cek server.', 'error');
                    this.loading = false;
                })
        },

        // Get Item Edit
        editItem: function(item) {
            this.isEdit = true; // Mode Edit
            this.modalAdd = true;
            // Map data
            this.idQuotes = item.id;
            this.isiQuotes = item.isi_quotes;
            this.suratRiwayat = item.suratriwayat;
            if (this.$refs.form) {
                this.$refs.form.resetValidation();
            }
        },

        //Update Quotes
        updateQuotes: function() {
            if (!this.$refs.form.validate()) return;
            this.loading = true;
            axios.put(`<?= base_url(); ?>/api/agamaquotes/update/${this.idQuotes}`, {
                    isi_quotes: this.isiQuotes,
                    suratriwayat: this.suratRiwayat,
                })
                .then(res => {
                    this.loading = false;
                    var data = res.data;
                    if (data.status == true) {
                        this.showSnackbar(data.message, 'success');
                        this.getAgamaquotes();
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
                    console.error("Error updating quotes:", err.response);
                    this.showSnackbar('Gagal update data. Cek server.', 'error');
                    this.loading = false;
                })
        },

        // Get Item Delete
        deleteItem: function(item) {
            this.modalDelete = true;
            this.idQuotes = item.id;
        },

        modalDeleteClose: function() {
            this.modalDelete = false;
        },

        // Delete Quotes
        deleteQuotes: function() {
            this.loading = true;
            axios.delete(`<?= base_url(); ?>/api/agamaquotes/delete/${this.idQuotes}`)
                .then(res => {
                    this.loading = false;
                    var data = res.data;
                    if (data.status == true) {
                        this.showSnackbar(data.message, 'success');
                        this.getAgamaquotes();
                        this.modalDeleteClose();
                    } else {
                        this.showSnackbar(data.message, 'error');
                    }
                })
                .catch(err => {
                    console.error("Error deleting quotes:", err.response);
                    this.showSnackbar('Gagal hapus data. Cek server.', 'error');
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
        this.getAgamaquotes();
        console.log("Agama Quotes View: Data Loaded");
    };
</script>
<?php $this->endSection("js") ?>