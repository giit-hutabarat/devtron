<?php $this->extend("layouts/backend"); ?>
<?php $this->section("content"); ?>
<template>
    <v-card>
        <v-card-title class="text-h5">
            <i class="mdi mdi-weather-cloudy"></i> &nbsp;<?= $title; ?>
        </v-card-title>
        <v-card-text>
            <h2>{{ dataCuaca.name }}, {{ dataCuaca_sys.country }}</h2>
            <div v-for="item in dataCuaca.weather" :key="item.id">
                <h2><img :src="'http://openweathermap.org/img/wn/' + item.icon + '.png'"> {{ item.main }}, {{ item.description }}</h2>
            </div>
            <br />
            <h1 class="text-h2"><strong>{{ Math.ceil(dataCuaca_main.temp_max) }}</strong>&deg;<span>C</span></h1>

            <p>Feels like {{ Math.ceil(dataCuaca_main.feels_like) }}&deg;<span>C</span>. Humidity {{ dataCuaca_main.humidity }}%</p>

            <small class="text-muted">Data API openweathermap.org</small>

            <v-alert type="warning" light text>
            Ganti Kota di Pengaturan Aplikasi
            </v-alert>
        </v-card-text>
    </v-card>
</template>

<?php $this->endSection("content") ?>

<?php $this->section("js") ?>
<script>

    window.dataVue = {
        ...window.dataVue,
        dataCuaca: [],
        dataCuaca_weather: [],
        dataCuaca_main: [],
        dataCuaca_sys: [],
    }
    window.createdVue = function() {
       if (typeof window.defaultCreatedVue !== 'undefined') {
            window.defaultCreatedVue.call(this); // Mengatur konteks 'this'
        }
        this.getCuaca();
    }

    window.methodsVue = {
        ...window.methodsVue,
        // Get
        getCuaca: function() {
            this.loading = true;
            axios.get('<?= base_url() ?>/api/cuaca')
                .then(res => {
                    // handle success
                    this.loading = false;
                    var data = res.data;
                    if (data.status == true) {
                        this.snackbar = true;
                        this.snackbarMessage = data.message;

                        //data krisis
                        this.dataCuaca = data.data;
                        this.dataCuaca_weather = this.dataCuaca.weather;
                        this.dataCuaca_main = this.dataCuaca.main;
                        this.dataCuaca_sys = this.dataCuaca.sys;
                        console.log(this.dataCuaca);
                    } else {
                        this.snackbar = true;
                        this.snackbarMessage = data.message;
                    }
                })
                .catch(err => {
                    // handle error
                    console.error("Error fetching weather:", err.response);
                    this.loading = false;
                    this.snackbar = true;
                    this.snackbarMessage = "Gagal memuat data cuaca dari API.";
                })
        },

    }
</script>
<?php $this->endSection("js") ?>