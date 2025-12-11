<?php $this->extend("layouts/backend"); ?>
<?php $this->section("content"); ?>
<template>
    <!-- Card Utama (Modern, Shadow Lembut) -->
    <v-card class="rounded-xl elevation-6" v-if="dataCuaca && dataCuaca_main.temp">
        
        <!-- Header & Judul -->
        <v-card-title class="pa-4 grey lighten-5">
            <v-icon left color="indigo">mdi-weather-cloudy</v-icon>
            <h1 class="font-weight-bold text-h5 grey--text text--darken-3"><?= $title; ?></h1>
            <v-spacer></v-spacer>
            <v-progress-circular v-if="loading" indeterminate color="primary" size="24" class="mr-2"></v-progress-circular>
            <v-btn icon @click="getCuaca" :loading="loading" title="Refresh Data Cuaca">
                <v-icon>mdi-refresh</v-icon>
            </v-btn>
        </v-card-title>

        <!-- Body Cuaca -->
        <v-card-text class="py-6 px-5">
            
            <!-- Lokasi dan Kondisi Utama -->
            <h2 class="text-h4 mb-2 grey--text text--darken-4">
                {{ dataCuaca.name }}, {{ dataCuaca_sys.country }}
            </h2>
            
            <!-- Icon dan Deskripsi Cuaca -->
            <div v-for="item in dataCuaca.weather" :key="item.id" class="d-flex align-center mb-4">
                <img :src="'http://openweathermap.org/img/wn/' + item.icon + '@4x.png'" width="80px" alt="Weather Icon"> 
                <div class="text-h5 font-weight-regular ml-3">
                    {{ item.main }} ({{ item.description }})
                </div>
            </div>
            
            <!-- Suhu Utama -->
            <h1 class="text-h1 font-weight-bold primary--text my-3">
                {{ Math.ceil(dataCuaca_main.temp) }}&deg;<span>C</span>
            </h1>

            <!-- Detail Tambahan -->
            <v-row dense class="mt-4">
                <v-col cols="12" sm="6" md="3">
                    <v-icon small class="mr-1">mdi-thermometer</v-icon> Terasa seperti: <strong>{{ Math.ceil(dataCuaca_main.feels_like) }}&deg;C</strong>
                </v-col>
                <v-col cols="12" sm="6" md="3">
                    <v-icon small class="mr-1">mdi-water-percent</v-icon> Kelembaban: <strong>{{ dataCuaca_main.humidity }}%</strong>
                </v-col>
                <v-col cols="12" sm="6" md="3">
                    <v-icon small class="mr-1">mdi-speedometer</v-icon> Tekanan: <strong>{{ dataCuaca_main.pressure }} hPa</strong>
                </v-col>
            </v-row>

            <v-alert type="warning" light text class="mt-5 rounded-lg">
                <small>Ganti Kota di Pengaturan Aplikasi. Data dari API openweathermap.org.</small>
            </v-alert>

        </v-card-text>
    </v-card>
    <v-card v-else class="rounded-xl elevation-6 pa-5 text-center">
        <v-progress-circular v-if="loading" indeterminate color="primary" size="40"></v-progress-circular>
        <div v-else>
            <v-icon color="error" large>mdi-cloud-off-outline</v-icon>
            <h2 class="mt-2">Gagal Memuat Data Cuaca</h2>
            <p class="text-subtitle-1">Pastikan API Key dan Nama Kota sudah diatur di Pengaturan Aplikasi.</p>
        </div>
    </v-card>
</template>

<?php $this->endSection("content") ?>

<?php $this->section("js") ?>
<script>
    // 💥 KOREKSI WAJIB: Amankan objek global sebelum merging
    window.dataVue = window.dataVue || {};
    window.methodsVue = window.methodsVue || {};
    window.computedVue = window.computedVue || {};

    // 1. Gabungkan Data Spesifik Cuaca ke window.dataVue
    Object.assign(window.dataVue, {
        // --- Properti Default Layout yang WAJIB ada (Anti-ReferenceError) ---
        snackbar: false,
        timeout: 4000, 
        snackbarType: '',
        snackbarMessage: '',
        loading: false, 
        
        // --- Data Spesifik Cuaca ---
        dataCuaca: {},
        dataCuaca_weather: [],
        dataCuaca_main: {},
        dataCuaca_sys: {},
    });

    // 2. Gabungkan Methods Spesifik Cuaca ke window.methodsVue
    Object.assign(window.methodsVue, {
        // Method helper untuk menampilkan Snackbar
        showSnackbar: function(message, type = 'success') {
            this.snackbarMessage = message;
            this.snackbarType = type;
            this.snackbar = true;
        },
        
        // Get Data Cuaca
        getCuaca: function() {
            this.loading = true;
            axios.get('<?= base_url() ?>/api/cuaca')
                .then(res => {
                    this.loading = false;
                    var data = res.data;
                    
                    if (data.status == true && data.data && data.data.main) {
                        // this.showSnackbar('Data cuaca berhasil dimuat.', 'success'); // Opsional
                        
                        // Mapping data ke properti Vue
                        this.dataCuaca = data.data;
                        this.dataCuaca_weather = data.data.weather || [];
                        this.dataCuaca_main = data.data.main || {};
                        this.dataCuaca_sys = data.data.sys || {};
                        
                        // Periksa apakah data utama ada
                        if (Object.keys(this.dataCuaca_main).length === 0) {
                             this.showSnackbar("Data cuaca kosong. Cek API Key/Kota.", 'warning');
                        }
                    } else {
                        // Jika status true tapi data tidak valid (misal, API error tapi status 200)
                        this.showSnackbar(data.message || "Gagal memuat data cuaca.", 'error');
                        this.dataCuaca = {}; // Kosongkan data agar error message muncul
                    }
                })
                .catch(err => {
                    console.error("Error fetching weather:", err.response);
                    this.loading = false;
                    this.showSnackbar("Gagal memuat data cuaca dari API. Cek koneksi atau logs.", 'error');
                    this.dataCuaca = {}; // Kosongkan data
                })
        },
    });

    // 3. Created Hook
    window.createdVue = function() {
        // Panggil created hook default dari layout jika ada
        if (typeof window.defaultCreatedVue === 'function') {
            window.defaultCreatedVue.call(this);
        }
        this.getCuaca();
        console.log("Weather View: Data Loaded");
    };
</script>
<?php $this->endSection("js") ?>