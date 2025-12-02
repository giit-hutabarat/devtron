<?php $this->extend("layouts/backend"); ?>
<?php $this->section("content"); ?>
<template>
    <v-card>
        <v-card-title>
            <h2><?= $title; ?></h2>
            <v-spacer></v-spacer>
            <v-text-field v-model="search" append-icon="mdi-magnify" label="Search" single-line hide-details>
            </v-text-field>
        </v-card-title>
        <v-data-table :headers="dataTable" :items="dataSettingWithIndex" :items-per-page="10" :loading="loading" :search="search" loading-text="Sedang memuat... Harap tunggu">
            <template v-slot:item="{ item }">
                <tr>
                    <td>{{item.index}}</td>
                    <td>{{item.variable_setting}}</td>
                    <td>
                        <div v-if="item.variable_setting == 'kota'">
                            <v-autocomplete v-model="item.value_setting" :items="dataKota" item-text="lokasi" item-value="id" readonly>
                            </v-autocomplete>
                        </div>
                        <div v-else>
                            {{item.value_setting}}
                        </div>
                    </td>
                    <td><i>{{item.deskripsi_setting}}</i></td>
                    <td>{{item.updated_at}}</td>
                    <td>
                        <div v-if="item.variable_setting == 'background' || item.variable_setting == 'background_masjid'">
                            <v-btn color="primary" @click="editItem(item)" icon>
                                <v-icon>mdi-camera</v-icon>
                            </v-btn>
                        </div>
                        <div v-else-if="item.variable_setting == 'app_version' || item.variable_setting == 'app_release' || item.variable_setting == 'app_developer'">
                        </div>
                        <div v-else>
                            <v-btn color="primary" @click="editItem(item)" icon>
                                <v-icon>mdi-pencil</v-icon>
                            </v-btn>
                        </div>
                    </td>
                </tr>
            </template>
        </v-data-table>
    </v-card>

</template>

<!-- Modal Edit -->
<template>
    <v-row justify="center">
        <v-dialog v-model="modalEdit" persistent scrollable width="600px">
            <v-card>
                <v-card-title>Edit '{{variableEdit}}'
                    <v-spacer></v-spacer>
                    <v-btn icon @click="modalEditClose">
                        <v-icon>mdi-close</v-icon>
                    </v-btn>
                </v-card-title>
                <v-divider></v-divider>
                <v-card-text class="py-3">
                    <v-form ref="form" v-model="valid">
                        <v-alert v-if="notifType != ''" dismissible dense outlined :type="notifType">{{notifMessage}}</v-alert>
                        <p class="mb-2 text-subtitle-1">Deskripsi Setting</p>
                        <v-text-field v-model="deskripsiEdit" :error-messages="deskripsi_settingError" outlined disabled></v-text-field>
                        <p class="mb-2 text-subtitle-1">Value Setting</p>

                        <div v-if="variableEdit == 'ver'">
                            <v-select v-model="valueEdit" :items="dataVersi" label="Pilih Versi" item-text="text" item-value="value" :error-messages="value_settingError" outlined>
                            </v-select>
                        </div>

                        <div v-else-if="variableEdit == 'layout'">
                            <v-select v-model="valueEdit" :items="dataLayout" label="Pilih Layout" item-text="nama_layout" item-value="value" :error-messages="value_settingError" outlined>
                            </v-select>
                        </div>

                        <div v-else-if="variableEdit == 'background' || variableEdit == 'background_masjid' ">
                            <img v-bind:src="'<?= base_url() ?>' + '/' + valueEdit" width="150" class="mb-2" />
                            <v-file-input v-model="image" show-size label="Image Upload" id="file" class="mb-2" accept=".jpg, .jpeg, .png" prepend-icon="mdi-camera" @change="onFileChange" @click:clear="onFileClear" :loading="loading2" outlined dense></v-file-input>
                            <v-img :src="imagePreview" max-width="100">
                                <v-overlay v-model="overlay" absolute :opacity="0.1">
                                    <v-btn small class="ma-2" color="success" dark>
                                        OK
                                        <v-icon dark right>
                                            mdi-checkbox-marked-circle
                                        </v-icon>
                                    </v-btn>
                                </v-overlay>
                            </v-img>
                        </div>

                        <div v-else-if="variableEdit == 'kota'">
                            <v-autocomplete v-model="valueEdit" :items="dataKota" item-text="lokasi" item-value="id" :error-messages="value_settingError" outlined>
                            </v-autocomplete>
                        </div>

                        <div v-else-if="variableEdit == 'video_muted' || variableEdit == 'video_youtube'">
                            <v-select v-model="valueEdit" :items="dataYesNo" item-text="text" item-value="value" :error-messages="value_settingError" outlined>
                            </v-select>
                        </div>

                        <div v-else-if="variableEdit == 'video_plugin'">
                            <v-select v-model="valueEdit" :items="dataPlugin" item-text="text" item-value="value" :error-messages="value_settingError" outlined>
                            </v-select>
                        </div>
                   
                        <div v-else-if="variableEdit == 'jadwal_sholat'">
                            <v-select v-model="valueEdit" :items="dataSholat" label="Pilih Jadwal Sholat" item-text="text" item-value="value" :error-messages="value_settingError" outlined>
                            </v-select>
                            <v-alert type="info" text>
                                <strong>Informasi!</strong> API jadwal sholat yang digunakan adalah milik pihak ketiga yaitu website https://api.myquran.com (MyQuran.com). Klik <a href="https://api.myquran.com/v1/sholat/kota/semua" class="" target="_blank" alt="api.myquran.com">Disini</a> untuk melihat status API sedang bisa diakses atau tidak (Error 500 dsb), jika Error 500 maka tampilan Display Jadwal Sholat akan Error.
                            </v-alert>
                        </div>

                        <div v-else>
                            <v-textarea v-model="valueEdit" :error-messages="value_settingError" rows="3" outlined></v-textarea>
                        </div>
                    </v-form>
                </v-card-text>
                <v-divider></v-divider>
                <v-card-actions>
                    <v-spacer></v-spacer>
                    <div v-if="variableEdit == 'background'">
                        <v-btn large @click="modalEditClose" elevation="0">
                            Tutup
                        </v-btn>
                    </div>
                    <div v-else>
                        <v-btn large color="primary" @click="updateSetting" :loading="loading2" elevation="1">
                            <v-icon>mdi-content-save</v-icon> Simpan
                        </v-btn>
                    </div>
                </v-card-actions>
            </v-card>
        </v-dialog>
    </v-row>
</template>
<!-- End Modal Edit -->

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
    // const options = { headers: { "Content-Type": "application/json" } };
    // --- LOGIKA JWT LAMA DIHAPUS ---

    window.dataVue = {
        ...window.dataVue,
        modalEdit: false,
        settingData: [],
        dataTable: [
            { text: '#', value: 'id' }, 
            { text: 'Variable', value: 'variable_setting' }, 
            { text: 'Value', value: 'value_setting' }, 
            { text: 'Deskripsi', value: 'deskripsi_setting' }, 
            { text: 'Tgl Update', value: 'updated_at' }, 
            { text: 'Aksi', value: 'actions', sortable: false }, 
        ],
        // ... (data lainnya)
        settingId: "",
        groupEdit: "",
        variableEdit: "",
        deskripsiEdit: "",
        valueEdit: "",
        deskripsi_settingError: "",
        value_settingError: "",
        image: null,
        imagePreview: null,
        overlay: false,
        dataVersi: [{ text: 'STANDAR', value: 'STANDAR' }],
        dataYesNo: [{ text: 'Ya', value: 'yes' }, { text: 'Tidak', value: 'no' }],
        dataLayout: [],
        dataKota: [],
        kota: "",
        dataSholat: [{ text: 'REST API MyQuran.com https://api.myquran.com', value: 'api' }, { text: 'Excel (Upload Manual)', value: 'excel' }],
        dataPlugin: [{ text: 'Plyr.io', value: 'Plyr.io' }],
    }

    var errorKeys = []

    window.createdVue = function() {
        // --- LOGIKA JWT LAMA DIHAPUS ---
        // axios.defaults.headers['Authorization'] = 'Bearer ' + token;
        // --- LOGIKA JWT LAMA DIHAPUS ---
        this.getSetting();
        this.getKota();
    }

    window.computedVue = {
        ...window.computedVue,
        dataSettingWithIndex() {
            return this.settingData.map(
                (items, index) => ({
                    ...items,
                    index: index + 1
                }))
        },
    }

    window.methodsVue = {
        ...window.methodsVue,
        onFileChange() {
            // ... (Logic onFileChange tetap sama) ...
        },
        onFileClear() {
            // ... (Logic onFileClear tetap sama) ...
            this.image = null;
            this.imagePreview = null;
            this.overlay = false;
            this.snackbar = true;
            this.snackbarMessage = 'Image dihapus';
        },
        uploadFile: function(file) {
            var formData = new FormData() 
            var block = file.split(";"); 
            var contentType = block[0].split(":")[1]; 
            var realData = block[1].split(",")[1]; 

            var blob = b64toBlob(realData, contentType);
            formData.append('image', blob);
            formData.append('id', this.settingId);
            this.loading2 = true;
            
            axios.post(`<?= base_url() ?>/api/setting/upload`, formData) // Axios Polos
                .then(res => {
                    this.loading2 = false
                    var data = res.data;
                    if (data.status == true) {
                        this.snackbar = true;
                        this.snackbarMessage = data.message;
                        this.valueEdit = data.data
                        this.overlay = true;
                        this.getSetting();
                    } else {
                        this.snackbar = true;
                        this.snackbarMessage = data.message;
                        this.modalEdit = true;
                    }
                })
                .catch(err => {
                    // Cukup log error. Filter 401 akan ditangani oleh global error handler.
                    console.error("Error uploading file:", err.response);
                    this.loading2 = false;
                })
        },
        // Get
        getSetting: function() {
            this.loading = true;
            axios.get('<?= base_url() ?>/api/setting/app') // Axios Polos
                .then(res => {
                    this.loading = false;
                    var data = res.data;
                    if (data.status == true) {
                        this.settingData = data.data;
                    } else {
                        this.snackbar = true;
                        this.snackbarMessage = data.message;
                    }
                })
                .catch(err => {
                    // Cukup log error. Filter 401 akan ditangani oleh global error handler.
                    console.error("Error fetching settings:", err.response);
                    this.loading = false;
                })
        },

        // Get Item Edit
        editItem: function(item) {
            this.modalEdit = true;
            this.notifType = "";
            this.settingId = item.id;
            this.groupEdit = item.group_setting;
            this.variableEdit = item.variable_setting;
            this.deskripsiEdit = item.deskripsi_setting;
            this.valueEdit = item.value_setting;
            this.getLayout();
        },

        modalEditClose: function() {
            this.modalEdit = false;
            this.image = null;
            this.imagePreview = null;
            this.overlay = false;
            this.$refs.form.resetValidation();
        },

        //Update
        updateSetting: function() {
            this.loading2 = true;
            axios.put(`<?= base_url() ?>/api/setting/update/${this.settingId}`, { // Axios Polos
                    deskripsi_setting: this.deskripsiEdit,
                    value_setting: this.valueEdit
                })
                .then(res => {
                    this.loading2 = false;
                    var data = res.data;
                    if (data.status == true) {
                        this.snackbar = true;
                        this.snackbarMessage = data.message;
                        this.modalEdit = false;
                        this.getSetting();
                        this.$refs.form.resetValidation();
                    } else {
                        this.snackbar = true;
                        this.snackbarMessage = data.message;
                        errorKeys = Object.keys(data.data);
                        errorKeys.map((el) => {
                            this[`${el}Error`] = data.data[el];
                        });
                        if (errorKeys.length > 0) {
                            setTimeout(() => this.notifType = "", 4000);
                            setTimeout(() => errorKeys.map((el) => {
                                this[`${el}Error`] = "";
                            }), 4000);
                        }
                        this.modalEdit = true;
                        this.$refs.form.validate();
                    }
                })
                .catch(err => {
                    // Cukup log error. Filter 401 akan ditangani oleh global error handler.
                    console.error("Error updating settings:", err.response);
                    this.loading2 = false;
                })
        },

        getKota: function() {
            this.loading = true;
            axios.get('<?= base_url() ?>/api/setting/kota') // Axios Polos
                .then(res => {
                    this.loading = false;
                    var data = res.data;
                    if (data.status == true) {
                        this.dataKota = data.data;
                    } else {
                        this.snackbar = true;
                        this.snackbarMessage = data.message;
                    }
                })
                .catch(err => {
                    // Cukup log error. Filter 401 akan ditangani oleh global error handler.
                    console.error("Error fetching cities:", err.response);
                    this.loading = false;
                })
        },

        getLayout: function() {
            this.loading = true;
            axios.get('<?= base_url() ?>/api/setting/layout') // Axios Polos
                .then(res => {
                    this.loading = false;
                    var data = res.data;
                    if (data.status == true) {
                        this.dataLayout = data.data;
                    } else {
                        this.snackbar = true;
                        this.snackbarMessage = data.message;
                    }
                })
                .catch(err => {
                    // Cukup log error. Filter 401 akan ditangani oleh global error handler.
                    console.error("Error fetching layout:", err.response);
                    this.loading = false;
                })
        },
    }
</script>
<?php $this->endSection("js") ?>