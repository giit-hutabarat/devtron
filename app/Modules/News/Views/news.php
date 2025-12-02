<?php $this->extend("layouts/backend"); ?>
<?php $this->section("content"); ?>
<template>
    <v-card>
        <v-card-title>
            <h1 class="font-weight-medium"><?= $title; ?></h1>
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
        <v-data-table :headers="dataHeader" :items="dataNews" :items-per-page="10" :loading="loading" :search="pencarian" class="elevation-0" loading-text="<?= lang('App.loadingWait'); ?>">
            <template v-slot:item="{ item }">
                <tr>
                    <td width="80">{{item.id}}</td>
                    <td width="120">{{item.tgl_news}}</td>
                    <td>{{item.text_news}}</td>
                    <td width="100">
                        <v-chip v-if="item.jenis_news == '1'">News Ticker</v-chip>
                        <v-chip v-else-if="item.jenis_news == '2'">Info Umum</v-chip>
                        <v-chip v-else>Info Masjid</v-chip>
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

<!-- Modal -->
<!-- Modal Save -->
<template>
    <v-row justify="center">
        <v-dialog v-model="modalAdd" max-width="700px" persistent scrollable>
            <v-card>
                <v-card-title>
                    <?= lang('App.add') ?> <?= $title; ?>
                    <v-spacer></v-spacer>
                    <v-btn icon @click="modalAddClose">
                        <v-icon>mdi-close</v-icon>
                    </v-btn>
                </v-card-title>
                <v-divider></v-divider>
                <v-card-text class="pt-5">
                    <v-form ref="form" v-model="valid">
                        <v-row>
                            <v-col>
                                <p class="mb-3 text-subtitle-1">Tanggal</p>
                                <v-menu ref="menu" v-model="menu" :close-on-content-click="false" transition="scale-transition" offset-y min-width="auto">
                                    <template v-slot:activator="{ on, attrs }">
                                        <v-text-field v-model="tglNews" label="Pilih Tanggal" prepend-inner-icon="mdi-calendar" readonly v-bind="attrs" v-on="on" :error-messages="tgl_newsError" outlined></v-text-field>
                                    </template>
                                    <v-date-picker v-model="tglNews" @input="menu = false" color="primary"></v-date-picker>
                                </v-menu>
                            </v-col>

                            <v-col>
                                <p class="mb-3 text-subtitle-1">Jenis</p>
                                <v-select v-model="jenisNews" :items="dataJenis" item-text="text" item-value="value" placeholder="Pilih Jenis" :error-messages="jenis_newsError" outlined></v-select>
                            </v-col>
                        </v-row>

                        <p class="mb-3 text-subtitle-1">Isi</p>
                        <v-textarea v-model="textNews" :rules="[rules.varchar]" rows="5" auto-grow counter :error-messages="text_newsError" outlined></v-textarea>

                    </v-form>
                </v-card-text>
                <v-divider></v-divider>
                <v-card-actions>
                    <v-spacer></v-spacer>
                    <v-btn large color="primary" @click="saveNews" :loading="loading" elevation="1">
                    <v-icon>mdi-content-save</v-icon> <?= lang('App.save') ?>
                    </v-btn>
                </v-card-actions>
            </v-card>
        </v-dialog>
    </v-row>
</template>
<!-- End Modal Save -->
<!-- Modal Edit -->
<template>
    <v-row justify="center">
        <v-dialog v-model="modalEdit" max-width="700px" persistent scrollable>
            <v-card>
                <v-card-title>
                    <?= lang('App.edit') ?> <?= $title; ?>
                    <v-spacer></v-spacer>
                    <v-btn icon @click="modalEditClose">
                        <v-icon>mdi-close</v-icon>
                    </v-btn>
                </v-card-title>
                <v-card-text class="pt-5">
                    <v-form ref="form" v-model="valid">
                    <v-row>
                            <v-col>
                                <p class="mb-3 text-subtitle-1">Tanggal</p>
                                <v-menu ref="menu2" v-model="menu2" :close-on-content-click="false" transition="scale-transition" offset-y min-width="auto">
                                    <template v-slot:activator="{ on, attrs }">
                                        <v-text-field v-model="tglNews" label="Pilih Tanggal" prepend-inner-icon="mdi-calendar" readonly v-bind="attrs" v-on="on" :error-messages="tgl_newsError" outlined></v-text-field>
                                    </template>
                                    <v-date-picker v-model="tglNews" @input="menu2 = false" color="primary"></v-date-picker>
                                </v-menu>
                            </v-col>

                            <v-col>
                                <p class="mb-3 text-subtitle-1">Jenis</p>
                                <v-select v-model="jenisNews" :items="dataJenis" item-text="text" item-value="value" placeholder="Pilih Jenis" :error-messages="jenis_newsError" outlined></v-select>
                            </v-col>
                        </v-row>

                        <p class="mb-3 text-subtitle-1">Isi</p>
                        <v-textarea v-model="textNews" :rules="[rules.varchar]" rows="5" auto-grow counter :error-messages="text_newsError" outlined></v-textarea>
                    </v-form>
                </v-card-text>
                <v-divider></v-divider>
                <v-card-actions>
                    <v-spacer></v-spacer>
                    <v-btn large color="primary" @click="updateNews" :loading="loading">
                        <v-icon>mdi-content-save</v-icon> <?= lang('App.save') ?>
                    </v-btn>
                </v-card-actions>
            </v-card>
        </v-dialog>
    </v-row>
</template>
<!-- End Modal Edit -->
<!-- Modal Delete -->
<template>
    <v-row justify="center">
        <v-dialog v-model="modalDelete" persistent max-width="600px">
            <v-card class="pa-2">
                <v-card-title>
                    <v-icon color="error" class="mr-2" x-large>mdi-alert-octagon</v-icon> <?= lang('App.delConfirm') ?>
                </v-card-title>
                <v-card-text>
                    <div class="mt-5 py-4">
                        <h2 class="font-weight-regular">Apakah anda yakin ingin menghapus?</h2>
                    </div>
                </v-card-text>
                <v-card-actions>
                    <v-spacer></v-spacer>
                    <v-btn large text @click="modalDeleteClose"><?= lang('App.no') ?></v-btn>
                    <v-btn large color="primary" dark @click="deleteNews" :loading="loading"><?= lang('App.yes') ?></v-btn>
                    <v-spacer></v-spacer>
                </v-card-actions>
            </v-card>
        </v-dialog>
    </v-row>
</template>
<!-- End Modal Delete -->
<?php $this->endSection("content") ?>

<?php $this->section("js") ?>
<script>
    // --- LOGIKA JWT LAMA DIHAPUS ---
    // const token = JSON.parse(localStorage.getItem('access_token'));
    // const options = { headers: { "Authorization": `Bearer ${token}`, "Content-Type": "application/json" } };
    // --- LOGIKA JWT LAMA DIHAPUS ---

    window.dataVue = {
        ...window.dataVue,
        pencarian: "",
        modalAdd: false,
        modalEdit: false,
        modalShow: false,
        modalDelete: false,
        menu: false,
        menu2: false,
        dataHeader: [{
            text: "#",
            value: "id"
        }, {
            text: "Tanggal",
            value: "tgl_news"
        }, {
            text: "Judul",
            value: "text_news"
        }, {
            text: "Jenis",
            value: "jenis_news"
        }, {
            text: "<?= lang('App.action') ?>",
            value: "actions",
            sortable: false
        }, ],
        dataNews: [],
        dataJenis: [{
            text: "News",
            value: "1"
        }, {
            text: "Info",
            value: "2"
        }, {
            text: "Masjid",
            value: "3"
        }],
        idNews: "",
        tglNews: "",
        tgl_newsError: "",
        textNews: "",
        text_newsError: "",
        jenisNews: "",
        jenis_newsError: "",
    }
    window.createdVue = function() {
        this.getNews();
    }

    window.methodsVue = {
        ...window.methodsVue,
        modalAddOpen: function() {
            this.modalAdd = true;
            this.notifType = '';
            //this.$refs.form.resetValidation();
            //this.$refs.form.reset();
        },
        modalAddClose: function() {
            this.modalAdd = false;
            //this.$refs.form.resetValidation();
        },
        // Get Data
        getNews: function() {
            this.loading = true;
            // AXIOS POLOS
            axios.get('<?= base_url(); ?>/api/news')
                .then(res => {
                    // handle success
                    this.loading = false;
                    var data = res.data;
                    if (data.status == true) {
                        //this.snackbar = true;
                        //this.snackbarMessage = data.message;
                        this.dataNews = data.data;
                    } else {
                        this.snackbar = true;
                        this.snackbarMessage = data.message;
                    }
                })
                .catch(err => {
                    // Cukup log error. Filter 401 akan menangani redirect global.
                    console.error("Error fetching news:", err.response);
                    this.loading = false;
                })
        },

        // Save News
        saveNews: function() {
            this.loading = true;
            // AXIOS POLOS
            axios.post(`<?= base_url(); ?>/api/news/save`, {
                    tgl_news: this.tglNews,
                    text_news: this.textNews,
                    jenis_news: this.jenisNews,
                })
                .then(res => {
                    // handle success
                    this.loading = false
                    var data = res.data;
                    if (data.status == true) {
                        this.snackbar = true;
                        this.snackbarMessage = data.message;
                        this.getNews();
                        this.tglNews = "";
                        this.textNews = "";
                        this.tglNews = "";
                        this.modalAdd = false;
                        this.$refs.form.resetValidation();
                        this.$refs.form.reset();
                    } else {
                        this.snackbar = true;
                        this.snackbarMessage = data.message;
                        // Handle Error Validation
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
                        this.modalAdd = true;
                        this.$refs.form.validate();
                    }
                })
                .catch(err => {
                    // Cukup log error. Filter 401 akan menangani redirect global.
                    console.error("Error saving news:", err.response);
                    this.loading = false;
                })
        },

        // Get Item Edit News
        editItem: function(item) {
            this.modalEdit = true;
            this.idNews = item.id;
            this.tglNews = item.tgl_news;
            this.textNews = item.text_news;
            this.jenisNews = item.jenis_news;
        },
        modalEditClose: function() {
            this.modalEdit = false;
            this.$refs.form.resetValidation();
            this.$refs.form.reset();
        },

        //Update News
        updateNews: function() {
            this.loading = true;
            // AXIOS POLOS
            axios.put(`<?= base_url(); ?>/api/news/update/${this.idNews}`, {
                    tgl_news: this.tglNews,
                    text_news: this.textNews,
                    jenis_news: this.jenisNews,
                })
                .then(res => {
                    // handle success
                    this.loading = false;
                    var data = res.data;
                    if (data.status == true) {
                        this.snackbar = true;
                        this.snackbarMessage = data.message;
                        this.getNews();
                        this.tglNews = "";
                        this.textNews = "";
                        this.tglNews = "";
                        this.modalEdit = false;
                        this.$refs.form.resetValidation();
                        this.$refs.form.reset();
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
                    // Cukup log error. Filter 401 akan menangani redirect global.
                    console.error("Error updating news:", err.response);
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
            this.$refs.form.resetValidation();
            this.$refs.form.reset();
        },

        // Delete News
        deleteNews: function() {
            this.loading = true;
            // AXIOS POLOS
            axios.delete(`<?= base_url(); ?>/api/news/delete/${this.idNews}`)
                .then(res => {
                    // handle success
                    this.loading = false;
                    var data = res.data;
                    if (data.status == true) {
                        this.snackbar = true;
                        this.snackbarMessage = data.message;
                        this.getNews();
                        this.modalDelete = false;
                        this.$refs.form.resetValidation();
                        this.$refs.form.reset();
                    } else {
                        this.modalDelete = true;
                    }
                })
                .catch(err => {
                    // Cukup log error. Filter 401 akan menangani redirect global.
                    console.error("Error deleting news:", err.response);
                    this.loading = false;
                })
        },
    }
</script>
<?php $this->endSection("js") ?>