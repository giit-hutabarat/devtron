<?= $this->extend("layouts/backend"); ?>

<?php $this->section("content"); ?>
<template>
    <v-container fluid class="pa-6">

        <!-- 1. HEADER SAMBUTAN (ELEVASI & FONT KUAT) -->
        <v-card 
            class="mb-6 rounded-xl pa-5" 
            elevation="10" 
            color="indigo darken-1"
            dark
        >
            <v-card-title class="text-h4 font-weight-bold white--text pb-1">
                <v-icon left large>mdi-hand-wave-outline</v-icon>
                Selamat Datang, <?= session()->get('fullname'); ?>
            </v-card-title>
            <v-card-subtitle class="white--text pt-2 text-subtitle-1">
                Dashboard Administrasi TRON System.
            </v-card-subtitle>
        </v-card>

        <!-- 2. WIDGET STATISTIK (DIBUAT LEBIH ELEGAN DAN SHADOW LEMBUT) -->
        <v-row class="mb-5">
            <!-- Widget 1: Total Konten -->
            <v-col cols="12" sm="6" md="3">
                <v-card elevation="6" class="rounded-lg pa-4 text-center">
                    <v-icon x-large color="indigo" size="48">mdi-file-document-multiple</v-icon>
                    <div class="text-h4 font-weight-black pt-2">86</div>
                    <div class="text-subtitle-2 grey--text text--darken-1">Total Konten Display</div>
                </v-card>
            </v-col>
            <!-- Widget 2: Total Pengguna -->
            <v-col cols="12" sm="6" md="3">
                <v-card elevation="6" class="rounded-lg pa-4 text-center">
                    <v-icon x-large color="teal" size="48">mdi-account-multiple</v-icon>
                    <div class="text-h4 font-weight-black pt-2">12</div>
                    <div class="text-subtitle-2 grey--text text--darken-1">Pengguna Aktif</div>
                </v-card>
            </v-col>
            <!-- Widget 3: Data Backup -->
            <v-col cols="12" sm="6" md="3">
                <v-card elevation="6" class="rounded-lg pa-4 text-center">
                    <v-icon x-large color="blue-grey" size="48">mdi-database-check</v-icon>
                    <div class="text-h4 font-weight-black pt-2">Terbaru</div>
                    <div class="text-subtitle-2 grey--text text--darken-1">Backup DB</div>
                </v-card>
            </v-col>
            <!-- Widget 4: Status Cuaca -->
            <v-col cols="12" sm="6" md="3">
                <v-card elevation="6" class="rounded-lg pa-4 text-center">
                    <v-icon x-large color="orange darken-1" size="48">mdi-weather-sunny</v-icon>
                    <div class="text-h4 font-weight-black pt-2">28&deg;C</div>
                    <div class="text-subtitle-2 grey--text text--darken-1">Jakarta (Cerah)</div>
                </v-card>
            </v-col>
        </v-row>


        <!-- 3. PANDUAN PENGGUNAAN (Elegan dan Fokus) -->
        <v-row>
            <v-col cols="12">
                <v-card elevation="2" class="rounded-lg">
                    <v-card-title class="text-h6 font-weight-bold grey--text text--darken-3 pb-0 pt-4 px-4">
                        <v-icon left color="blue-grey">mdi-book-open-page-variant</v-icon>
                        PANDUAN PENGGUNAAN SISTEM
                    </v-card-title>
                    <v-card-text>
                        <v-expansion-panels accordion class="mt-3">
                            
                            <!-- Panel 1: Setting Aplikasi -->
                            <v-expansion-panel class="rounded-lg my-1 elevation-1">
                                <v-expansion-panel-header class="font-weight-medium primary--text text-subtitle-1 py-3">1. Pengaturan Aplikasi</v-expansion-panel-header>
                                <v-expansion-panel-content class="pt-3">
                                    <p class="body-2 grey--text text--darken-2">
                                        Kelola konfigurasi dasar sistem (Logo, Nama Aplikasi, Kredensial, dll.) di bagian pengaturan umum dan aplikasi.
                                    </p>
                                    <div class="d-flex flex-wrap pt-2 pb-2" style="gap: 10px;">
                                        <v-btn small depressed color="indigo" dark link href="<?= base_url('setting/general'); ?>" class="rounded-pill">
                                            <v-icon left small>mdi-cog-outline</v-icon> UMUM
                                        </v-btn>
                                        <v-btn small depressed color="teal" dark link href="<?= base_url('setting/app'); ?>" class="rounded-pill">
                                            <v-icon left small>mdi-tune</v-icon> APLIKASI
                                        </v-btn>
                                    </div>
                                </v-expansion-panel-content>
                            </v-expansion-panel>

                            <!-- Panel 2: Input Data Display -->
                            <v-expansion-panel class="rounded-lg my-1 elevation-1">
                                <v-expansion-panel-header class="font-weight-medium primary--text text-subtitle-1 py-3">2. Input Data Display</v-expansion-panel-header>
                                <v-expansion-panel-content class="pt-3">
                                    <p class="body-2 grey--text text--darken-2">
                                        Konten yang akan ditampilkan di Layar Publik (Display TV) dikelola melalui menu-menu berikut.
                                    </p>
                                    <div class="d-flex flex-wrap pt-2 pb-2" style="gap: 10px;">
                                        <v-chip color="indigo" small dark link href="<?= base_url('news'); ?>" class="rounded-pill">BERITA</v-chip>
                                        <v-chip color="indigo" small dark link href="<?= base_url('agenda'); ?>" class="rounded-pill">AGENDA</v-chip>
                                        <v-chip color="indigo" small dark link href="<?= base_url('galeri'); ?>" class="rounded-pill">GALERI</v-chip>
                                        <v-chip color="indigo" small dark link href="<?= base_url('video'); ?>" class="rounded-pill">VIDEO</v-chip>
                                    </div>
                                </v-expansion-panel-content>
                            </v-expansion-panel>
                            
                            <!-- Panel 3: Jalankan Display -->
                            <v-expansion-panel class="rounded-lg my-1 elevation-1">
                                <v-expansion-panel-header class="font-weight-medium primary--text text-subtitle-1 py-3">3. Jalankan Display</v-expansion-panel-header>
                                <v-expansion-panel-content class="pt-3">
                                    <p class="body-2 grey--text text--darken-2">
                                        Buka layar display untuk publik. Layar ini disarankan untuk dijalankan dalam mode Fullscreen (F11).
                                    </p>
                                    <v-btn color="success" dark block large link href="<?= base_url('display'); ?>" target="_blank" elevation="2" class="rounded-pill mt-3">
                                        <v-icon left>mdi-monitor</v-icon> BUKA LAYAR DISPLAY
                                    </v-btn>
                                </v-expansion-panel-content>
                            </v-expansion-panel>

                        </v-expansion-panels>
                    </v-card-text>
                </v-card>
            </v-col>
        </v-row>

    </v-container>
</template>
<?php $this->endSection("content") ?>

<?php $this->section("js") ?>
<script>
    window.createdVue = function() {
        console.log("Dashboard View: Dashboard Loaded");
    };
</script>
<?php $this->endSection("js") ?>