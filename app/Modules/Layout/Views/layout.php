<?php $this->extend("layouts/backend"); ?>
<?php $this->section("content"); ?>
<template>
    <h1 class="text-h4 font-weight-medium"><?= $title; ?></h1>
    <p>Tampilan Display yang informatif dan indah dibuat dengan sepenuh hati.</p>
    <v-item-group v-model="selected">
        <v-row>
            <v-col v-for="(item, i) in dataLayout" :key="i" cols="12" md="4" @click="update(item)">
                <v-item v-slot="{ active, toggle }">
                    <v-card @click="toggle">
                        <v-img :src="'<?= base_url(); ?>' + '/' + item.preview" class="text-right pa-2">
                            <v-btn icon x-large dark>
                                <v-icon>
                                    {{ active ? 'mdi-heart' : 'mdi-heart-outline' }}
                                </v-icon>
                            </v-btn>
                        </v-img>
                        <v-card-title>
                            {{item.nama_layout}}
                            <v-spacer></v-spacer>
                            <v-chip color="green" class="white--text" small v-show="active">
                                {{ active ? 'Aktif' : '' }}
                            </v-chip>
                        </v-card-title>
                    </v-card>
                </v-item>
            </v-col>
        </v-row>
    </v-item-group>
</template>

<?php $this->endSection("content") ?>

<?php $this->section("js") ?>
<script>
    // --- LOGIKA JWT LAMA DIHAPUS ---
    // const token = JSON.parse(localStorage.getItem('access_token'));
    // const options = { headers: { "Authorization": `Bearer ${token}`, "Content-Type": "application/json" } };
    // --- LOGIKA JWT LAMA DIHAPUS ---

    window.dataVue = {
        ...window.dataVue,
        dataLayout: [],
        selected: <?= $active; ?>,
        idSetting: "8",
        valueLayout: "",
    }
    
    // PENTING: createdVue dan methodsVue harus didefinisikan sebagai window property
    // agar dapat menimpa yang ada di layout backend.php

    window.createdVue = function() {
        this.getLayout();
    }

    window.methodsVue = {
        ...window.methodsVue,
        // Get Data
        getLayout: function() {
            this.loading = true;
            // AXIOS POLOS
            axios.get('<?= base_url(); ?>/api/layout')
                .then(res => {
                    // handle success
                    this.loading = false;
                    var data = res.data;
                    if (data.status == true) {
                        //this.snackbar = true;
                        //this.snackbarMessage = data.message;
                        this.dataLayout = data.data;
                    } else {
                        this.snackbar = true;
                        this.snackbarMessage = data.message;
                    }
                })
                .catch(err => {
                    // Cukup log error. Filter 401 akan menangani redirect global.
                    console.error("Error fetching layout list:", err.response);
                    this.loading = false;
                })
        },

        update(item) {
            this.valueLayout = item.value;
            this.updateLayout();
        },

        //Change Layout
        updateLayout: function() {
            this.loading = true;
            // AXIOS POLOS
            axios.put(`<?= base_url(); ?>/api/setting/change/${this.idSetting}`, {
                    value_setting: this.valueLayout,
                })
                .then(res => {
                    // handle success
                    this.loading = false;
                    var data = res.data;
                    if (data.status == true) {
                        this.snackbar = true;
                        this.snackbarMessage = data.message;
                    } else {
                        this.snackbar = true;
                        this.snackbarMessage = data.message;
                    }
                })
                .catch(err => {
                    // Cukup log error. Filter 401 akan menangani redirect global.
                    console.error("Error updating layout setting:", err.response);
                    this.loading = false;
                })
        },

    }
</script>
<?php $this->endSection("js") ?>