<?php $this->extend("layouts/backend"); ?>
<?php $this->section("content"); ?>
<template>
    <!-- Card Kontainer Utama -->
    <v-card class="rounded-xl elevation-4">
        
        <!-- Header Halaman -->
        <v-card-title class="pa-4 grey lighten-5">
            <v-icon left color="indigo">mdi-cash-multiple</v-icon>
            <h1 class="font-weight-bold text-h5 grey--text text--darken-3"><?= $title; ?></h1>
            <v-spacer></v-spacer>
            <!-- Saldo (Dibuat Chip Besar) -->
            <v-chip color="teal" dark class="px-5 py-3 elevation-2">
                <v-icon left>mdi-wallet-outline</v-icon>
                <h3 class="text-subtitle-1 font-weight-bold">Saldo: Rp{{Ribuan(saldo ?? "0")}}</h3>
            </v-chip>
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
                        label="Cari Uraian..." 
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
            :items="dataKeuangan" 
            :items-per-page="10" 
            :loading="loading" 
            :search="pencarian" 
            class="elevation-0 px-4 pb-4 my-4 pt-0" 
            loading-text="<?= lang('App.loadingWait'); ?>"
        >
            <template v-slot:item.id="{ item, index }">
                {{ index + 1 }} 
            </template>
            <template v-slot:item.tanggal="{ item }">
                <v-chip small color="blue-grey lighten-5" class="font-weight-medium">{{ item.tanggal }}</v-chip>
            </template>
            <template v-slot:item.pemasukan="{ item }">
                <span class="green--text font-weight-bold">
                    Rp{{ Ribuan(item.pemasukan) }}
                </span>
            </template>
            <template v-slot:item.pengeluaran="{ item }">
                <span class="red--text font-weight-bold">
                    Rp{{ Ribuan(item.pengeluaran) }}
                </span>
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
                        <!-- Tanggal -->
                        <p class="mb-2 text-subtitle-1 font-weight-medium grey--text text--darken-2">Tanggal</p>
                        <v-menu ref="menuRef" v-model="menu" :close-on-content-click="false" transition="scale-transition" offset-y min-width="auto">
                            <template v-slot:activator="{ on, attrs }">
                                <v-text-field v-model="tanggal" label="Pilih Tanggal" prepend-inner-icon="mdi-calendar" readonly v-bind="attrs" v-on="on" :error-messages="tanggalError" outlined dense></v-text-field>
                            </template>
                            <v-date-picker v-model="tanggal" @input="menu = false" color="primary"></v-date-picker>
                        </v-menu>

                        <!-- Jenis Keuangan -->
                        <p class="mb-2 text-subtitle-1 font-weight-medium grey--text text--darken-2">Jenis</p>
                        <v-select v-model="jenis" :items="dataJenis" item-text="text" item-value="value" placeholder="Pilih Jenis Keuangan" :error-messages="jenisError" outlined dense :disabled="isEdit"></v-select>

                        <!-- Uraian -->
                        <p class="mb-2 text-subtitle-1 font-weight-medium grey--text text--darken-2">Uraian</p>
                        <v-textarea v-model="uraian" :rules="[rules.varchar]" rows="3" auto-grow counter :error-messages="uraianError" outlined></v-textarea>

                        <!-- Nominal -->
                        <p class="mb-2 text-subtitle-1 font-weight-medium grey--text text--darken-2">Nominal (Rp)</p>
                        <v-text-field type="number" v-model="nominal" outlined dense :prefix="'Rp'" :hint="Ribuan(nominal)" persistent-hint></v-text-field>
                    </v-form>
                </v-card-text>
                <v-divider></v-divider>
                
                <!-- Tombol Aksi -->
                <v-card-actions class="py-3">
                    <v-spacer></v-spacer>
                    <v-btn large text @click="modalAddClose" class="mr-2">Tutup</v-btn>
                    
                    <v-btn large :color="isEdit ? 'teal darken-1' : 'primary'" @click="isEdit ? updateKeuangan() : saveKeuangan()" :loading="loading" elevation="2">
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
                    <v-btn large color="red darken-2" dark @click="deleteKeuangan" :loading="loading">Ya, Hapus</v-btn>
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

    // 1. Gabungkan Data Spesifik Keuangan Masjid ke window.dataVue
    Object.assign(window.dataVue, {
        // --- Properti Default Layout yang WAJIB ada (Anti-ReferenceError) ---
        snackbar: false,
        timeout: 4000, 
        snackbarType: '',
        snackbarMessage: '',
        valid: true, // Untuk Form Validasi
        
        // --- Properti Spesifik View Keuangan Masjid ---
        pencarian: "",
        modalAdd: false,
        modalDelete: false,
        loading: false, 
        isEdit: false, // State untuk menentukan mode Edit atau Add
        menu: false,
        
        // Data Headers
        dataHeader: [
            { text: "No.", value: "id", sortable: false, width: '5%' },
            { text: "Tanggal", value: "tanggal", width: '15%' },
            { text: "Uraian", value: "uraian" },
            { text: "Pemasukan (Rp)", value: "pemasukan", width: '15%', align: 'end' },
            { text: "Pengeluaran (Rp)", value: "pengeluaran", width: '15%', align: 'end' },
            { text: "<?= lang('App.action') ?>", value: "actions", sortable: false, width: '15%' },
        ],
        dataKeuangan: [],
        dataJenis: [
            { text: "Pemasukan", value: "1" },
            { text: "Pengeluaran", value: "2" }
        ],
        
        // Data Form (untuk Add/Edit)
        idKeuangan: "",
        tanggal: new Date().toISOString().substr(0, 10), // Default tanggal hari ini
        tanggalError: "",
        uraian: "",
        uraianError: "",
        jenis: "1", // Default Pemasukan
        jenisError: "",
        nominal: 0,
        
        // Data Saldo dari API
        saldo: 0,
    });

    // 2. Gabungkan Methods Spesifik Keuangan Masjid ke window.methodsVue
    Object.assign(window.methodsVue, {
        // Method helper untuk menampilkan Snackbar
        showSnackbar: function(message, type = 'success') {
            this.snackbarMessage = message;
            this.snackbarType = type;
            this.snackbar = true;
        },

        // Helper untuk format ribuan (Ribuan)
        Ribuan(key) {
            if (!key) return "0";
            var number_string = key.toString().replace(/\./g, ''),
                sisa = number_string.length % 3,
                rupiah = number_string.substr(0, sisa),
                ribuan = number_string.substr(sisa).match(/\d{3}/g);

            if (ribuan) {
                separator = sisa ? '.' : '';
                rupiah += separator + ribuan.join('.');
            }

            return rupiah;
        },

        // Method Reset Form
        resetForm: function() {
            if (this.$refs.form) {
                this.$refs.form.resetValidation();
                this.$refs.form.reset();
            }
            this.idKeuangan = "";
            this.tanggal = new Date().toISOString().substr(0, 10);
            this.uraian = "";
            this.jenis = "1";
            this.nominal = 0;
        },

        // Modal Add/Edit
        modalAddOpen: function() {
            this.isEdit = false;
            this.modalAdd = true;
            this.resetForm();
        },
        modalAddClose: function() {
            this.modalAdd = false;
            this.isEdit = false;
            this.resetForm();
        },

        // Get Data
        getKeuangan: function() {
            this.loading = true;
            axios.get('<?= base_url(); ?>/api/keuanganmasjid')
                .then(res => {
                    this.loading = false;
                    var data = res.data;
                    if (data.status == true) {
                        this.dataKeuangan = data.data.keuangan || []; 
                        this.saldo = data.data.pemasukan - data.data.pengeluaran;
                    } else {
                        this.showSnackbar(data.message, 'error');
                        this.dataKeuangan = [];
                        this.saldo = 0;
                    }
                })
                .catch(err => {
                    console.error("Error fetching Keuangan Masjid:", err.response);
                    this.loading = false;
                    this.showSnackbar('Gagal memuat data keuangan.', 'error');
                    this.dataKeuangan = [];
                    this.saldo = 0;
                })
        },

        // Save
        saveKeuangan: function() {
            if (!this.$refs.form.validate() || this.nominal <= 0) return;
            this.loading = true;
            axios.post(`<?= base_url(); ?>/api/keuanganmasjid/save`, {
                    tanggal: this.tanggal,
                    uraian: this.uraian,
                    jenis: this.jenis,
                    nominal: this.nominal,
                })
                .then(res => {
                    this.loading = false;
                    var data = res.data;
                    if (data.status == true) {
                        this.showSnackbar(data.message, 'success');
                        this.getKeuangan();
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
                    console.error("Error saving Keuangan:", err.response);
                    this.showSnackbar('Gagal menyimpan data.', 'error');
                    this.loading = false;
                })
        },

        // Get Item Edit
        editItem: function(item) {
            this.isEdit = true;
            this.modalAdd = true;
            
            // Map data
            this.idKeuangan = item.id;
            this.tanggal = item.tanggal;
            this.uraian = item.uraian;
            
            // Logika menentukan jenis dan nominal
            if (item.pemasukan > 0) {
                this.jenis = "1"; // Pemasukan
                this.nominal = item.pemasukan;
            } else {
                this.jenis = "2"; // Pengeluaran
                this.nominal = item.pengeluaran;
            }
            
            // NOTE: Jenis (this.jenis) harus di-set disabled di modal
            if (this.$refs.form) this.$refs.form.resetValidation();
        },

        //Update
        updateKeuangan: function() {
            if (!this.$refs.form.validate() || this.nominal <= 0) return;
            this.loading = true;
            
            // Jenis tidak perlu dikirim karena update hanya mengubah uraian, tanggal, dan nominal
            // Jenis (1/2) hanya dibutuhkan untuk menentukan kolom mana yang diupdate (pemasukan/pengeluaran) di backend.
            axios.put(`<?= base_url(); ?>/api/keuanganmasjid/update/${this.idKeuangan}`, {
                    tanggal: this.tanggal,
                    uraian: this.uraian,
                    jenis: this.jenis, // Kirim jenis lama (1 atau 2) agar backend tahu kolom mana yang di-update
                    nominal: this.nominal,
                })
                .then(res => {
                    this.loading = false;
                    var data = res.data;
                    if (data.status == true) {
                        this.showSnackbar(data.message, 'success');
                        this.getKeuangan();
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
                    console.error("Error updating Keuangan:", err.response);
                    this.showSnackbar('Gagal update data.', 'error');
                    this.loading = false;
                })
        },

        // Delete
        deleteItem: function(item) {
            this.modalDelete = true;
            this.idKeuangan = item.id;
        },

        modalDeleteClose: function() {
            this.modalDelete = false;
        },

        deleteKeuangan: function() {
            this.loading = true;
            axios.delete(`<?= base_url(); ?>/api/keuanganmasjid/delete/${this.idKeuangan}`)
                .then(res => {
                    this.loading = false;
                    var data = res.data;
                    if (data.status == true) {
                        this.showSnackbar(data.message, 'success');
                        this.getKeuangan();
                        this.modalDeleteClose();
                    } else {
                        this.showSnackbar(data.message, 'error');
                    }
                })
                .catch(err => {
                    console.error("Error deleting Keuangan:", err.response);
                    this.showSnackbar('Gagal hapus data.', 'error');
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
        this.getKeuangan();
        console.log("Keuangan Masjid View: Data Loaded");
    };
</script>
<?php $this->endSection("js") ?>