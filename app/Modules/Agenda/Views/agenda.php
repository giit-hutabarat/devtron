<?php $this->extend("layouts/backend"); ?>
<?php $this->section("content"); ?>
<template>
    <!-- Card Kontainer Utama (Lebih elegan) -->
    <v-card class="rounded-xl elevation-4">
        
        <!-- Header Halaman -->
        <v-card-title class="pa-4 grey lighten-5">
            <v-icon left color="indigo">mdi-calendar-month-outline</v-icon>
            <h1 class="font-weight-bold text-h5 grey--text text--darken-3"><?= $title; ?></h1>
        </v-card-title>
        
        <!-- 💥 KOREKSI: Mengganti v-toolbar flat dengan v-card-text dan v-row untuk kontrol layout mobile -->
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
                        label="Cari Agenda..." 
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
        
        <!-- 💥 KOREKSI: Data Table sekarang memiliki jarak vertikal yang baik -->
        <v-data-table 
            :headers="dataHeader" 
            :items="dataAgenda" 
            :items-per-page="10" 
            :loading="loading" 
            :search="pencarian" 
            class="elevation-0 px-4 pb-4 pt-0" 
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
            <template v-slot:item.jenis_agenda="{ item }">
                <!-- Chip berwarna -->
                <v-chip :color="item.jenis_agenda == '1' ? 'teal' : 'grey'" dark small>
                    {{ item.jenis_agenda == '1' ? 'Agenda' : 'Lainnya' }}
                </v-chip>
            </template>
            <!-- 💥 KOREKSI: Slot untuk menampilkan nomor urut (No.) -->
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
                        <p class="mb-2 text-subtitle-1 font-weight-medium grey--text text--darken-2">Nama Agenda</p>
                        <v-text-field v-model="namaAgenda" label="Nama Agenda" :error-messages="nama_agendaError" outlined dense></v-text-field>
                        
                        <p class="mb-2 text-subtitle-1 font-weight-medium grey--text text--darken-2">Tempat</p>
                        <v-text-field v-model="tempatAgenda" label="Tempat Agenda" :error-messages="tempat_agendaError" outlined dense></v-text-field>
                        
                        <!-- 💥 KOREKSI: Responsiveness: cols=12 (mobile) md=6 (desktop) -->
                        <v-row>
                            <!-- Kolom Tanggal -->
                            <v-col cols="12" md="6">
                                <p class="mb-2 text-subtitle-1 font-weight-medium grey--text text--darken-2">Tanggal</p>
                                <v-menu ref="dateRef" v-model="date" :close-on-content-click="false" transition="scale-transition" offset-y min-width="auto">
                                    <template v-slot:activator="{ on, attrs }">
                                        <v-text-field v-model="tglAgenda" label="Pilih Tanggal" prepend-inner-icon="mdi-calendar" readonly v-bind="attrs" v-on="on" :error-messages="tgl_agendaError" outlined dense></v-text-field>
                                    </template>
                                    <v-date-picker v-model="tglAgenda" @input="date = false" color="primary"></v-date-picker>
                                </v-menu>
                            </v-col>

                            <!-- Kolom Jam -->
                            <v-col cols="12" md="6">
                                <p class="mb-2 text-subtitle-1 font-weight-medium grey--text text--darken-2">Jam</p>
                                <v-menu ref="timeRef" v-model="time" :close-on-content-click="false" :return-value.sync="waktu" transition="scale-transition" offset-y max-width="290px" min-width="290px">
                                    <template v-slot:activator="{ on, attrs }">
                                        <v-text-field v-model="waktu" label="Pilih Waktu" prepend-inner-icon="mdi-clock-time-four-outline" readonly v-bind="attrs" v-on="on" :error-messages="waktuError" outlined dense></v-text-field>
                                    </template>
                                    <v-time-picker v-if="time" v-model="waktu" full-width @click:minute="$refs.timeRef.save(waktu)" format="24hr"></v-time-picker>
                                </v-menu>
                            </v-col>
                        </v-row>
                        
                        <!-- Kolom Jenis Agenda -->
                        <p class="mb-2 text-subtitle-1 font-weight-medium grey--text text--darken-2">Jenis Agenda</p>
                        <v-select v-model="jenisAgenda" :items="dataJenis" item-text="text" item-value="value" placeholder="Pilih Jenis" :error-messages="jenis_agendaError" outlined dense></v-select>

                    </v-form>
                </v-card-text>
                <v-divider></v-divider>
                
                <!-- Tombol Aksi (Gunakan isEdit untuk menentukan save/update) -->
                <v-card-actions class="py-3">
                    <v-spacer></v-spacer>
                    <v-btn large text @click="modalAddClose" class="mr-2">Tutup</v-btn>
                    
                    <v-btn large :color="isEdit ? 'teal darken-1' : 'primary'" @click="isEdit ? updateAgenda() : saveAgenda()" :loading="loading" elevation="2">
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
                    <v-btn large color="red darken-2" dark @click="deleteAgenda" :loading="loading">Ya, Hapus</v-btn>
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

    // 1. Gabungkan Data Spesifik Agenda ke window.dataVue
    Object.assign(window.dataVue, {
        // --- Properti Default Layout yang WAJIB ada (Anti-ReferenceError) ---
        snackbar: false,
        timeout: 4000, 
        snackbarType: '',
        snackbarMessage: '',
        valid: true, // Untuk Form Validasi
        
        // --- Properti Spesifik View Agenda ---
        pencarian: "",
        modalAdd: false, 
        modalDelete: false,
        loading: false, 
        isEdit: false, // State untuk menentukan mode Edit atau Add
        date: false,
        time: false,
        
        // 💥 KOREKSI: Hapus kolom '#' dari header
        dataHeader: [
            { text: "No.", value: "id", sortable: false, width: '5%' }, // Kita tampilkan 'No.' tapi akan di-render manual
            { text: "Nama", value: "nama_agenda" },
            { text: "Tempat", value: "tempat_agenda" },
            { text: "Tanggal", value: "tgl_agenda", width: '15%' },
            { text: "Waktu", value: "waktu", width: '10%' },
            { text: "Jenis", value: "jenis_agenda", width: '15%' },
            { text: "<?= lang('App.action') ?>", value: "actions", sortable: false, width: '15%' },
        ],
        dataAgenda: <?= json_encode($dataAgenda ?? []) ?> || [], 
        dataJenis: [
            { text: "Agenda", value: "1" }, 
            { text: "Lainnya", value: "2" }
        ],
        
        // Data Form (untuk Add/Edit)
        idAgenda: "",
        namaAgenda: "",
        nama_agendaError: "",
        tempatAgenda: "",
        tempat_agendaError: "",
        tglAgenda: "",
        tgl_agendaError: "",
        waktu: "",
        waktuError: "",
        jenisAgenda: "1", // Default ke 'Agenda'
        jenis_agendaError: "",
    });

    // 2. Gabungkan Methods Spesifik Agenda ke window.methodsVue
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
            this.idAgenda = "";
            this.jenisAgenda = "1"; // Set default lagi
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
        getAgenda: function() {
            this.loading = true;
            axios.get('<?= base_url(); ?>/api/agenda')
                .then(res => {
                    this.loading = false;
                    var data = res.data;
                    if (data.status == true) {
                        this.dataAgenda = data.data;
                    } else {
                        this.showSnackbar(data.message, 'error');
                    }
                })
                .catch(err => {
                    console.error("Error fetching agenda:", err.response);
                    this.loading = false;
                })
        },

        // Save Agenda
        saveAgenda: function() {
            if (!this.$refs.form.validate()) return;
            this.loading = true;
            axios.post(`<?= base_url(); ?>/api/agenda/save`, {
                    nama_agenda: this.namaAgenda,
                    tempat_agenda: this.tempatAgenda,
                    tgl_agenda: this.tglAgenda,
                    waktu: this.waktu,
                    jenis_agenda: this.jenisAgenda,
                })
                .then(res => {
                    this.loading = false;
                    var data = res.data;
                    if (data.status == true) {
                        this.showSnackbar(data.message, 'success');
                        this.getAgenda();
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
                    console.error("Error saving agenda:", err.response);
                    this.showSnackbar('Gagal menyimpan data. Cek server.', 'error');
                    this.loading = false;
                })
        },

        // Get Item Edit Agenda
        editItem: function(item) {
            this.isEdit = true; // Mode Edit
            this.modalAdd = true;
            // Map data
            this.idAgenda = item.id;
            this.namaAgenda = item.nama_agenda;
            this.tempatAgenda = item.tempat_agenda;
            this.tglAgenda = item.tgl_agenda;
            this.waktu = item.waktu;
            this.jenisAgenda = item.jenis_agenda;
            if (this.$refs.form) {
                this.$refs.form.resetValidation();
            }
        },

        //Update Agenda
        updateAgenda: function() {
            if (!this.$refs.form.validate()) return;
            this.loading = true;
            axios.put(`<?= base_url(); ?>/api/agenda/update/${this.idAgenda}`, {
                    nama_agenda: this.namaAgenda,
                    tempat_agenda: this.tempatAgenda,
                    tgl_agenda: this.tglAgenda,
                    waktu: this.waktu,
                    jenis_agenda: this.jenisAgenda,
                })
                .then(res => {
                    this.loading = false;
                    var data = res.data;
                    if (data.status == true) {
                        this.showSnackbar(data.message, 'success');
                        this.getAgenda();
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
                    console.error("Error updating agenda:", err.response);
                    this.showSnackbar('Gagal update data. Cek server.', 'error');
                    this.loading = false;
                })
        },

        // Get Item Delete
        deleteItem: function(item) {
            this.modalDelete = true;
            this.idAgenda = item.id;
        },

        modalDeleteClose: function() {
            this.modalDelete = false;
        },

        // Delete Agenda
        deleteAgenda: function() {
            this.loading = true;
            axios.delete(`<?= base_url(); ?>/api/agenda/delete/${this.idAgenda}`)
                .then(res => {
                    this.loading = false;
                    var data = res.data;
                    if (data.status == true) {
                        this.showSnackbar(data.message, 'success');
                        this.getAgenda();
                        this.modalDeleteClose();
                    } else {
                        this.showSnackbar(data.message, 'error');
                    }
                })
                .catch(err => {
                    console.error("Error deleting agenda:", err.response);
                    this.showSnackbar('Gagal hapus data. Cek server.', 'error');
                    this.loading = false;
                })
        },
    });

    // 3. Created Hook
    window.createdVue = function() {
        this.getAgenda();
        console.log("Agenda View: Data Loaded");
    };
</script>
<?php $this->endSection("js") ?>