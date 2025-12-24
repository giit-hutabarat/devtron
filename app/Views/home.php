<?php
// PHP Logic
$isLoggedIn       = session()->get('isLoggedIn') ?? false; 
$userFullname     = session()->get('fullname') ?? 'ADMIN'; 
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

    <div id="app">
        <v-app style="background: transparent;">
            <v-main class="pa-0">
                
                <div class="bg-container">
                    <div class="bg-shape shape-1"></div>
                    <div class="bg-shape shape-2"></div>
                    <div class="bg-shape shape-3"></div>

                    <div class="bg-overlay">
                        
                        <div class="flex-wrapper pa-4">
                            
                            <div class="content-grow">
                                <div style="width: 100%; max-width: 1100px;">
                                    
                                    <v-row justify="center" align="center" class="fade-up delay-1 mb-6">
                                        <v-col cols="12" class="text-center">
                                            <img src="<?= base_url('images/logo_kejaksaan.png'); ?>" 
                                                 width="100" 
                                                 class="mb-3 elevation-10 rounded-circle logo-img" 
                                                 style="border: 2px solid rgba(255,255,255,0.2); padding: 4px; background: rgba(255,255,255,0.05);">
                                            
                                            <h1 class="text-h5 text-md-h4 font-weight-black mb-1 text-uppercase text-gradient-gold">
                                                Sistem Informasi
                                            </h1>
                                            <div style="width: 80px; height: 3px; background: linear-gradient(90deg, transparent, #ef4444, transparent); margin: 8px auto;"></div>
                                            <h3 class="text-body-1 text-md-h6 grey--text text--lighten-3 font-weight-light tracking-wide">
                                                <?= strtoupper($nama_instansi ?? 'Kejaksaan Negeri Boyolali'); ?>
                                            </h3>
                                        </v-col>
                                    </v-row>

                                    <v-row justify="center" class="fade-up delay-2">
                                        
                                        <v-col cols="12" sm="6" md="4" class="d-flex">
                                            <v-card class="card-menu mx-auto fill-height pa-5 text-center d-flex flex-column align-center" width="100%" dark>
                                                <div class="icon-circle mb-4 d-flex align-center justify-center" style="width: 70px; height: 70px; border-radius:50%; background:rgba(56, 189, 248, 0.1);">
                                                    <v-icon size="32" color="#38bdf8">fas fa-shield-alt</v-icon>
                                                </div>
                                                <h2 class="menu-title text-h6 font-weight-bold white--text mb-1">AKSES ADMIN</h2>
                                                <p class="menu-desc caption grey--text text--lighten-1 mb-4" style="line-height: 1.2;">Pengelolaan sistem.</p>
                                                <div style="margin-top: auto; width: 100%;">
                                                    <?php if ($isLoggedIn): ?>
                                                        <v-btn block small color="#38bdf8" class="btn-akses black--text mb-2" href="<?= base_url('dashboard'); ?>">Dashboard</v-btn>
                                                        <v-btn block small text color="grey lighten-1" @click="logoutUser">Logout</v-btn>
                                                    <?php else: ?>
                                                        <v-btn block small outlined color="#38bdf8" class="btn-akses" @click="modalAuth = true">Login Admin</v-btn>
                                                    <?php endif; ?>
                                                </div>
                                            </v-card>
                                        </v-col>

                                        <v-col cols="12" sm="6" md="4" class="d-flex">
                                            <v-card class="card-menu mx-auto fill-height pa-5 text-center d-flex flex-column align-center" width="100%" dark <?= $isSidangLoggedIn ? '' : 'href="'.site_url('sidang/access').'"' ?> ripple>
                                                <div class="icon-circle mb-4 d-flex align-center justify-center" style="width: 70px; height: 70px; border-radius:50%; background:rgba(239, 68, 68, 0.1);">
                                                    <v-icon size="32" color="#ef4444">fas fa-gavel</v-icon>
                                                </div>
                                                <h2 class="menu-title text-h6 font-weight-bold white--text mb-1">E-SIDANG</h2>
                                                <p class="menu-desc caption grey--text text--lighten-1 mb-4" style="line-height: 1.2;">Cetak dokumen sidang.</p>
                                                <div style="margin-top: auto; width: 100%;">
                                                    <?php if ($isSidangLoggedIn): ?>
                                                        <v-btn block small color="#ef4444" class="btn-akses white--text mb-2" href="<?= base_url('sidang'); ?>">Buka</v-btn>
                                                        <v-btn block small text color="grey lighten-1" href="<?= base_url('sidang/logout'); ?>" onclick="return confirm('Keluar?');">Logout</v-btn>
                                                    <?php else: ?>
                                                        <v-btn block small color="#ef4444" class="btn-akses white--text" href="<?= site_url('sidang/access') ?>">Masuk</v-btn>
                                                    <?php endif; ?>
                                                </div>
                                            </v-card>
                                        </v-col>

                                        <v-col cols="12" sm="6" md="4" class="d-flex">
                                            <v-card class="card-menu mx-auto fill-height pa-5 text-center d-flex flex-column align-center" width="100%" dark href="<?= base_url('display'); ?>" target="_blank" ripple>
                                                <div class="icon-circle mb-4 d-flex align-center justify-center" style="width: 70px; height: 70px; border-radius:50%; background:rgba(74, 222, 128, 0.1);">
                                                    <v-icon size="32" color="#4ade80">fas fa-tv</v-icon>
                                                </div>
                                                <h2 class="menu-title text-h6 font-weight-bold white--text mb-1">DISPLAY TV</h2>
                                                <p class="menu-desc caption grey--text text--lighten-1 mb-4" style="line-height: 1.2;">Informasi publik.</p>
                                                <div style="margin-top: auto; width: 100%;">
                                                    <v-btn block small outlined color="#4ade80" class="btn-akses">Lihat</v-btn>
                                                </div>
                                            </v-card>
                                        </v-col>
                                    </v-row>
                                </div>
                            </div>

                            <div class="text-center caption grey--text text--lighten-1 fade-up delay-3 mt-4" style="width: 100%;">
                                &copy; <?= date('Y') ?> Kejaksaan Negeri Boyolali.
                            </div>

                        </div>
                        </div>
                </div>

                <v-dialog v-model="modalAuth" persistent max-width="360px">
                    <v-card color="#0f172a" dark class="rounded-xl pa-5 elevation-24" style="border: 1px solid rgba(56, 189, 248, 0.3);">
                        <v-card-title class="justify-center text-h6 font-weight-bold text-blue-lighten-3 mb-2">LOGIN ADMIN</v-card-title>
                        <v-card-text class="text-center pb-0">
                            <v-form ref="formLogin" v-model="valid" @submit.prevent="loginProcess">
                                <v-text-field v-model="loginUsername" :rules="[rules.required]" label="Username" outlined dense rounded color="blue lighten-3" prepend-inner-icon="fas fa-user" class="mb-2"></v-text-field>
                                <v-text-field v-model="loginPassword" :rules="[rules.required]" :type="showPass ? 'text' : 'password'" label="Password" outlined dense rounded color="blue lighten-3" prepend-inner-icon="fas fa-lock" :append-icon="showPass ? 'fas fa-eye' : 'fas fa-eye-slash'" @click:append="showPass = !showPass"></v-text-field>
                                <v-expand-transition>
                                    <v-alert v-if="errorMsg" type="error" dense text class="mt-2 caption text-left" icon="fas fa-exclamation-circle">{{ errorMsg }}</v-alert>
                                </v-expand-transition>
                                <v-btn block color="blue accent-4" class="white--text font-weight-bold mt-4 rounded-pill btn-akses" :loading="loading" :disabled="!valid" type="submit">MASUK</v-btn>
                            </v-form>
                        </v-card-text>
                        <v-card-actions class="justify-center mt-2">
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
                afterLogout: '<?= base_url(); ?>'
            }
        };
    </script>

    <script src="<?= base_url('assets/js/landing.js') ?>"></script>
</body>
</html>