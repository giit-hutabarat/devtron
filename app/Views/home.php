<?php
// 1. DATA PREPARATION (PHP Logic)
$isLoggedIn       = session()->get('isLoggedIn') ?? false; // Session Admin
$userFullname     = session()->get('fullname') ?? 'ADMIN'; 

// Cek Session Sidang
$isSidangLoggedIn = session()->has('nip_user'); 
$sidangNama       = session()->get('nama_pegawai') ?? 'Pegawai';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no, minimal-ui">
    <title><?= $title ?? 'Sistem Informasi TRON'; ?></title>
    
    <link href="https://cdn.jsdelivr.net/npm/vuetify@2.x/dist/vuetify.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v6.4.0/css/all.css" crossorigin="anonymous"> 
    
    <link rel="stylesheet" href="<?= base_url('assets/css/landing.css') ?>">
</head>

<body>
    <?= $this->include('art/preloader') ?>

    <?= $this->renderSection('content') ?>
    <div id="app">
        <v-app style="background: transparent;">
            <v-main class="pa-0">
                <div class="bg-container">
                    <div class="bg-overlay">
                        <v-container>
                            
                            <v-row justify="center">
                                <v-col cols="12" class="text-center mb-8">
                                    <img src="<?= base_url('images/logo_kejaksaan.png'); ?>" width="110" class="mb-4" style="filter: drop-shadow(0 0 15px rgba(255,255,255,0.2));">
                                    <h1 class="text-h4 text-md-h3 font-weight-bold white--text mb-2">SISTEM INFORMASI TRON</h1>
                                    <div style="width: 80px; height: 4px; background: #ef4444; margin: 0 auto 10px auto; border-radius: 2px;"></div>
                                    <h3 class="text-h6 grey--text text--lighten-1"><?= $nama_instansi ?? 'Kejaksaan Negeri'; ?></h3>
                                </v-col>
                            </v-row>

                            <v-row justify="center" spacing="20">
                                
                                <v-col cols="12" sm="6" md="4">
                                    <v-card class="card-menu mx-auto fill-height pt-8 pb-6 px-4 text-center" color="#1e293b" dark elevation="10" style="border: 1px solid rgba(56, 189, 248, 0.1);">
                                        <div class="icon-circle">
                                            <v-icon size="45" color="#38bdf8">fas fa-shield-alt</v-icon>
                                        </div>
                                        <h2 class="menu-title text-blue-lighten-3">AKSES ADMIN</h2>
                                        <p class="menu-desc">Pengaturan dan pengelolaan konten sistem TRON.</p>
                                        
                                        <?php if ($isLoggedIn): ?>
                                            <div class="my-2 white--text caption">Logged in as: <strong><?= $userFullname; ?></strong></div>
                                            <v-btn block color="#38bdf8" class="btn-akses black--text mt-2 mb-2 elevation-5" href="<?= base_url('dashboard'); ?>">
                                                Dashboard <v-icon right small>fas fa-tachometer-alt</v-icon>
                                            </v-btn>
                                            <v-btn block outlined color="white" class="btn-akses mt-2" @click="logoutUser">
                                                Logout <v-icon right small>fas fa-sign-out-alt</v-icon>
                                            </v-btn>
                                        <?php else: ?>
                                            <v-btn block outlined color="#38bdf8" class="btn-akses" @click="modalAuth = true">
                                                Login Admin <v-icon right small>fas fa-lock</v-icon>
                                            </v-btn>
                                        <?php endif; ?>
                                    </v-card>
                                </v-col>

                                <v-col cols="12" sm="6" md="4">
                                    <v-card class="card-menu mx-auto fill-height pt-8 pb-6 px-4 text-center" color="#450a0a" dark <?= $isSidangLoggedIn ? '' : 'href="'.site_url('sidang/access').'"' ?> ripple elevation="15" style="border: 1px solid #ef4444;">
                                        <div class="icon-circle" style="background: rgba(239, 68, 68, 0.15);">
                                            <v-icon size="45" color="#ef4444">fas fa-gavel</v-icon>
                                        </div>
                                        <h2 class="menu-title red--text text--accent-2">Cetak Sidang</h2>
                                        <p class="menu-desc">Cetak dokumen persidangan (P-37 & P-38).</p>

                                        <?php if ($isSidangLoggedIn): ?>
                                            <div class="my-2 white--text caption">Halo, <strong><?= esc($sidangNama) ?></strong></div>
                                            <v-btn block color="#ef4444" class="btn-akses white--text mt-2 mb-2 elevation-5" href="<?= base_url('sidang'); ?>">
                                                Buka Aplikasi <v-icon right small>fas fa-arrow-right</v-icon>
                                            </v-btn>
                                            <v-btn block outlined color="white" class="btn-akses mt-2" href="<?= base_url('sidang/logout'); ?>" onclick="return confirm('Keluar dari sesi sidang?');">
                                                Logout Sesi <v-icon right small>fas fa-sign-out-alt</v-icon>
                                            </v-btn>
                                        <?php else: ?>
                                            <v-btn color="#ef4444" class="btn-akses white--text elevation-5" href="<?= site_url('sidang/access') ?>">
                                                Masuk Menu <v-icon right small>fas fa-sign-in-alt</v-icon>
                                            </v-btn>
                                        <?php endif; ?>
                                    </v-card>
                                </v-col>

                                <v-col cols="12" sm="6" md="4">
                                    <v-card class="card-menu mx-auto fill-height pt-8 pb-6 px-4 text-center" color="#064e3b" dark href="<?= base_url('display'); ?>" target="_blank" ripple elevation="10">
                                        <div class="icon-circle">
                                            <v-icon size="45" color="#4ade80">fas fa-tv</v-icon>
                                        </div>
                                        <h2 class="menu-title text-green-accent-3">Display TV</h2>
                                        <p class="menu-desc">Tampilan informasi publik.</p>
                                        <v-btn outlined color="#4ade80" class="btn-akses">Lihat Display <v-icon right small>fas fa-external-link-alt</v-icon></v-btn>
                                    </v-card>
                                </v-col>
                            </v-row>
                        </v-container>
                    </div><br />
                    
                    <div class="fixed-footer"><span class="footer-text">TRON 2025 - <?= $nama_instansi ?? 'Kejaksaan Negeri'; ?></span></div>
                </div>

                <v-dialog v-model="modalAuth" persistent max-width="400px">
                    <v-card color="#1e293b" dark class="rounded-xl pa-5 elevation-24">
                        <v-card-title class="justify-center text-h5 font-weight-bold text-blue-lighten-3 mb-4">LOGIN ADMIN</v-card-title>
                        <v-card-text class="text-center pb-0">
                            <v-form ref="formLogin" v-model="valid" @submit.prevent="loginProcess">
                                <div class="mb-6 d-flex justify-center">
                                    <div style="width: 80px; height: 80px; border-radius:50%; background:rgba(56, 189, 248, 0.1); display:flex; align-items:center; justify-content:center;">
                                        <v-icon size="40" color="#38bdf8">fas fa-user-shield</v-icon>
                                    </div>
                                </div>
                                <v-text-field v-model="loginUsername" :rules="[rules.required]" label="Username" outlined dense rounded color="light-blue lighten-3" prepend-inner-icon="fas fa-user"></v-text-field>
                                <v-text-field v-model="loginPassword" :rules="[rules.required]" :type="showPass ? 'text' : 'password'" label="Password" outlined dense rounded color="light-blue lighten-3" prepend-inner-icon="fas fa-lock" :append-icon="showPass ? 'fas fa-eye' : 'fas fa-eye-slash'" @click:append="showPass = !showPass"></v-text-field>
                                <v-alert v-if="errorMsg" type="error" dense text class="mt-2 caption text-left" icon="fas fa-exclamation-circle">{{ errorMsg }}</v-alert>
                                <v-btn block color="light-blue accent-3" class="black--text font-weight-bold mt-4 rounded-pill" :loading="loading" :disabled="!valid" type="submit" large>MASUK</v-btn>
                            </v-form>
                        </v-card-text>
                        <v-card-actions class="justify-center mt-3">
                            <v-btn text small color="grey lighten-1" @click="modalAuth = false">Batal</v-btn>
                        </v-card-actions>
                    </v-card>
                </v-dialog>

            </v-main>
        </v-app>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/vue@2.x/dist/vue.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/vuetify@2.x/dist/vuetify.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/axios/0.24.0/axios.min.js"></script>
    
    <script>
        const appConfig = {
            urls: {
                loginAdmin: '<?= base_url('auth/login_admin'); ?>',
                logout: '<?= base_url('api/auth/logout'); ?>',
                dashboard: '<?= base_url('dashboard'); ?>',
                afterLogout: '<?= base_url('auth/loading'); ?>'
            }
        };
    </script>

    <script src="<?= base_url('assets/js/landing.js') ?>"></script>
</body>
</html>