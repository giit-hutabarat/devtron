<?php
// --- DEFINISI VARIABEL YANG HILANG (FIX ErrorException) ---
$isLoggedIn = session()->get('isLoggedIn') ?? false;
$userFullname = session()->get('fullname') ?? 'ADMIN'; 
// Asumsi $nama_instansi dan $title sudah di-pass dari Controller
// --------------------------------------------------------
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no, minimal-ui">
    <title><?= $title; ?></title>

    <link href="https://fonts.googleapis.com/css?family=Roboto:100,300,400,500,700,900" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/@mdi/font@6.x/css/materialdesignicons.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/vuetify@2.x/dist/vuetify.min.css" rel="stylesheet">

    <style>
        /* GLOBAL STYLE */
        html, body { height: 100%; margin: 0; padding: 0; overflow: hidden; font-family: 'Roboto', sans-serif; }

        /* BACKGROUND */
        .bg-container {
            background-image: url('<?= base_url('images/kantor.jpg'); ?>');
            background-size: cover; background-position: center;
            width: 100vw; height: 100vh; position: relative;
        }
        .bg-overlay {
            background: rgba(15, 23, 42, 0.9);
            width: 100%; height: 100%;
            display: flex; flex-direction: column; align-items: center; justify-content: center;
            padding: 20px; overflow-y: auto;
        }

        /* CARD MENU */
        .card-menu {
            border-radius: 24px !important; border: 1px solid rgba(255,255,255,0.05);
            backdrop-filter: blur(10px); transition: all 0.4s; cursor: pointer;
        }
        .card-menu:hover { transform: translateY(-10px); border-color: rgba(255,255,255,0.2); box-shadow: 0 20px 40px rgba(0,0,0,0.6) !important; }
        .icon-circle {
            width: 90px; height: 90px; border-radius: 50%; background: rgba(255,255,255,0.05);
            display: flex; align-items: center; justify-content: center; margin: 0 auto 20px auto; transition: 0.3s;
        }
        .card-menu:hover .icon-circle { transform: scale(1.1) rotate(5deg); background: rgba(255,255,255,0.1); }
        .menu-title { font-size: 1.4rem; font-weight: 900; letter-spacing: 1px; margin-bottom: 5px; text-transform: uppercase; }
        .menu-desc { font-size: 0.85rem; color: #94a3b8; margin-bottom: 25px; min-height: 40px; }
        .btn-akses { font-weight: 700; border-radius: 50px; padding: 0 30px !important; }

        /* FOOTER */
        .fixed-footer { position: absolute; bottom: 20px; left: 0; width: 100%; text-align: center; z-index: 10; }
        .footer-text {
            color: #64748b; font-size: 0.85rem; font-weight: 500; letter-spacing: 2px; text-transform: uppercase;
            border-top: 1px solid rgba(255,255,255,0.1); padding: 15px 30px 0; display: inline-block;
        }
        
        /* PRELOADER */
        #preloader {
            position: fixed; top: 0; left: 0; width: 100%; height: 100%;
            background-color: #0a0a0a; z-index: 99999;
            display: flex; flex-direction: column; align-items: center; justify-content: center;
            transition: opacity 0.8s ease-out;
        }
        .logo-pulse { width: 120px; animation: pulseLogo 1.5s infinite; }
        .loader-text { color: #666; margin-top: 20px; font-size: 0.8rem; letter-spacing: 3px; text-transform: uppercase; animation: blink 1s infinite; }
        @keyframes pulseLogo { 0% { transform: scale(1); opacity: 1; } 50% { transform: scale(1.1); opacity: 0.8; } 100% { transform: scale(1); opacity: 1; } }
        @keyframes blink { 0%, 100% { opacity: 1; } 50% { opacity: 0.5; } }
        @keyframes shake { 0%, 100% { transform: translateX(0); } 25% { transform: translateX(-5px); } 75% { transform: translateX(5px); } }
        .v-shake { animation: shake 0.3s; }
    </style>
</head>

<body>
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
                                    <h3 class="text-h6 grey--text text--lighten-1"><?= $nama_instansi; ?></h3>
                                </v-col>
                            </v-row>

                            <v-row justify="center" spacing="20">
                                
                                <!-- 🔑 KARTU PERTAMA: AKSES ADMIN (REDESIGN) -->
                                <v-col cols="12" sm="6" md="4">
                                    <v-card 
                                        class="card-menu mx-auto fill-height pt-8 pb-6 px-4 text-center" 
                                        color="#1e293b" 
                                        dark 
                                        elevation="10"
                                        style="border: 1px solid rgba(56, 189, 248, 0.1);"
                                    >
                                        <div class="icon-circle">
                                            <v-icon size="45" color="#38bdf8">mdi-shield-account</v-icon>
                                        </div>
                                        <h2 class="menu-title text-blue-lighten-3">AKSES ADMIN</h2>
                                        <p class="menu-desc">Pengaturan dan pengelolaan konten sistem TRON.</p>
                                        
                                        <!-- LOGIKA KONDISIONAL PHP -->
                                        <?php if ($isLoggedIn): ?>
                                            <!-- JIKA SUDAH LOGIN -->
                                            <div class="my-2 white--text caption">
                                                Logged in as: <strong><?= $userFullname; ?></strong>
                                            </div>
                                            <v-btn 
                                                block 
                                                color="#38bdf8" 
                                                class="btn-akses black--text mt-2 mb-2 elevation-5" 
                                                href="<?= base_url('dashboard'); ?>"
                                            >
                                                Dashboard <v-icon right small>mdi-view-dashboard</v-icon>
                                            </v-btn>
                                            <v-btn 
                                                block 
                                                outlined 
                                                color="white" 
                                                class="btn-akses mt-2" 
                                                @click="logoutUser"
                                            >
                                                Logout <v-icon right small>mdi-logout</v-icon>
                                            </v-btn>
                                        <?php else: ?>
                                            <!-- JIKA BELUM LOGIN -->
                                            <v-btn 
                                                block 
                                                outlined 
                                                color="#38bdf8" 
                                                class="btn-akses" 
                                                @click="openLogin('dashboard')"
                                            >
                                                Login Admin <v-icon right small>mdi-lock</v-icon>
                                            </v-btn>
                                        <?php endif; ?>
                                        
                                    </v-card>
                                </v-col>
                                <!-- AKHIR KARTU PERTAMA -->


<v-col cols="12" sm="6" md="4">
    <v-card 
        class="card-menu mx-auto fill-height pt-8 pb-6 px-4 text-center" 
        color="#450a0a" 
        dark 
        @click="openLogin('sidang_otp')"  ripple 
        elevation="15" 
        style="border: 1px solid #ef4444;"
    >
        <div class="icon-circle" style="background: rgba(239, 68, 68, 0.15);"><v-icon size="45" color="#ef4444">mdi-gavel</v-icon></div>
        <h2 class="menu-title red--text text--accent-2">Cetak Sidang</h2>
        <p class="menu-desc">Cetak dokumen persidangan (P-37 & P-38).</p>
        <v-btn color="#ef4444" class="btn-akses white--text elevation-5">Masuk Menu <v-icon right small>mdi-login</v-icon></v-btn>
    </v-card>
</v-col>    

                                <v-col cols="12" sm="6" md="4">
                                    <v-card class="card-menu mx-auto fill-height pt-8 pb-6 px-4 text-center" color="#064e3b" dark href="<?= base_url('display'); ?>" target="_blank" ripple elevation="10">
                                        <div class="icon-circle"><v-icon size="45" color="#4ade80">mdi-monitor-dashboard</v-icon></div>
                                        <h2 class="menu-title text-green-accent-3">Display TV</h2>
                                        <p class="menu-desc">Tampilan informasi publik.</p>
                                        <v-btn outlined color="#4ade80" class="btn-akses">Lihat Display <v-icon right small>mdi-open-in-new</v-icon></v-btn>
                                    </v-card>
                                </v-col>
                            </v-row>
                        </v-container>
                    </div><br />
                    <div class="fixed-footer"><span class="footer-text">TRON 2025 - <?= $nama_instansi; ?></span></div>
                </div>

<v-dialog v-model="modalAuth" persistent max-width="400px">
    <v-card color="#1e293b" dark class="rounded-xl pa-5 elevation-24">
        <v-card-title class="justify-center text-h5 font-weight-bold text-blue-lighten-3 mb-4">
            {{ targetUrl === 'sidang_otp' ? 'VERIFIKASI AKSES (2FA)' : '' }}
        </v-card-title>
        <v-card-text class="text-center pb-0">
            
            <v-form v-if="targetUrl === 'dashboard'" ref="formLogin" v-model="valid" @submit.prevent="loginProcess">
                <div class="mb-6 d-flex justify-center">
                    <div style="width: 80px; height: 80px; border-radius:50%; background:rgba(56, 189, 248, 0.1); display:flex; align-items:center; justify-content:center;">
                        <v-icon size="40" color="#38bdf8">mdi-shield-account</v-icon>
                    </div>
                </div>
                <v-text-field v-model="loginUsername" :rules="[rules.required]" label="Username / Email" outlined dense rounded color="light-blue lighten-3" prepend-inner-icon="mdi-account"></v-text-field>
                <v-text-field v-model="loginPassword" :rules="[rules.required]" :type="showPass ? 'text' : 'password'" label="Password" outlined dense rounded color="light-blue lighten-3" prepend-inner-icon="mdi-lock" :append-icon="showPass ? 'mdi-eye' : 'mdi-eye-off'" @click:append="showPass = !showPass"></v-text-field>
                
                <v-alert v-if="errorMsg" type="error" dense text class="mt-2 caption text-left" icon="mdi-alert-circle">{{ errorMsg }}</v-alert>
                
                <v-btn block color="light-blue accent-3" class="black--text font-weight-bold mt-4 rounded-pill" :loading="loading" :disabled="!valid" type="submit" large>MASUK</v-btn>
            </v-form>
            
            <v-form v-else-if="targetUrl === 'sidang_otp'" ref="formSidangOtp" @submit.prevent="verifySidangOtp">
                <p class="white--text caption mb-4">Masukkan NIP dan Kode OTP 6 digit.</p>
                <div class="mb-6 d-flex justify-center">
                    <div style="width: 80px; height: 80px; border-radius:50%; background:rgba(239, 68, 68, 0.15); display:flex; align-items:center; justify-content:center;">
                        <v-icon size="40" color="#ef4444">mdi-gavel</v-icon>
                    </div>
                </div>
                
                <v-text-field 
                    v-model="sidangNip" 
                    :rules="[rules.required, rules.nipLength]" 
                    label="NIP Petugas" 
                    placeholder="Contoh: 198001012005011001"
                    outlined dense rounded color="red accent-2" 
                    prepend-inner-icon="mdi-account-card-details"
                    maxlength="18"
                    type="text" inputmode="numeric" hide-details="auto"
                    class="mb-3"
                ></v-text-field>

                <v-text-field 
                    v-model="sidangOtpCode" 
                    :rules="[rules.required, rules.otpLength]" 
                    label="Kode OTP 6 Digit" 
                    placeholder="Masukkan kode 6 digit"
                    outlined dense rounded color="red accent-2" 
                    prepend-inner-icon="mdi-key"
                    maxlength="6" type="text" 
                    inputmode="numeric"
                    hide-details="auto"
                ></v-text-field>
                
                <v-alert v-if="errorMsg" type="error" dense text class="mt-2 caption text-left" icon="mdi-alert-circle">{{ errorMsg }}</v-alert>
                
                <v-btn block color="red accent-2" class="white--text font-weight-bold mt-4 rounded-pill" :loading="loading" type="submit" large>Verifikasi</v-btn>
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
        // PRELOADER
        window.addEventListener('load', function() {
            var preloader = document.getElementById('preloader');
            if(preloader) {
                setTimeout(function() {
                    preloader.style.opacity = '0';
                    setTimeout(function() { preloader.style.display = 'none'; }, 800);
                }, 1200);
            }
        });

        new Vue({
            el: '#app',
            vuetify: new Vuetify(),
            data: () => ({
                modalAuth: false,
                valid: true, loading: false, showPass: false,
                loginUsername: "", loginPassword: "", errorMsg: "", 
                targetUrl: "dashboard", // Default Target
                sidangNip: "", 
                sidangOtpCode: "",
                rules: { 
            required: v => !!v || 'Wajib diisi.',
            // ✅ RULE BARU UNTUK NIP (HARUS 18 DIGIT)
        nipLength: v => (v && v.length === 18) || 'NIP harus 18 digit.',
            // ✅ RULE BARU
            otpLength: v => (v && v.length === 6) || 'Kode OTP harus 6 digit.'
        }
            }),
            methods: {
            openLogin(target) {
                    // Karena 'sidang' sekarang redirect langsung, 
                    // kita asumsikan openLogin hanya untuk 'dashboard' (admin)
                    this.targetUrl = target; 
                    this.modalAuth = true;
                    this.errorMsg = ""; 
                    this.loginUsername = ""; this.loginPassword = "";
                    this.sidangNip = ""; this.sidangOtpCode = "";
                    if(this.$refs.formLogin) this.$refs.formLogin.resetValidation();
                    if(this.$refs.formSidangOtp) this.$refs.formSidangOtp.resetValidation(); // ✅ RESET FORM BARU

                },
                loginProcess() {
                    if (this.$refs.formLogin.validate()) {
                        this.loading = true; this.errorMsg = "";
                        var formData = new FormData();
                        formData.append('username', this.loginUsername);
                        formData.append('password', this.loginPassword);

                        // AXIOS KE ROUTE HMVC
                        axios.post('<?= base_url('auth/login_admin'); ?>', formData).then(res => {
                            this.loading = false;
                            if (res.data.status === true) {
                                // SUKSES -> Redirect ke Target
                                window.location.href = '<?= base_url(); ?>/' + this.targetUrl;
                            } else {
                                this.errorMsg = res.data.message;
                                document.querySelector('.v-dialog .v-card').classList.add('v-shake');
                                setTimeout(() => document.querySelector('.v-dialog .v-card').classList.remove('v-shake'), 500);
                            }
                        }).catch(err => { this.loading = false; this.errorMsg = "Gagal koneksi server."; })
                    }
                }, // <--- 🔑 KOMO INI YANG SAYA TAMBAH
                // ✅ FUNGSI BARU UNTUK VERIFIKASI OTP SIDANG
        verifySidangOtp() {
            if (this.$refs.formSidangOtp.validate()) {
                this.loading = true; this.errorMsg = "";
                var formData = new FormData();
                formData.append('nip', this.sidangNip);
                formData.append('otp_code', this.sidangOtpCode);

                // AXIOS KE Controller SidangController::verifyOtp
                // Asumsi: Route Anda adalah 'sidang/verify'
                axios.post('<?= site_url('sidang/verify'); ?>', formData).then(res => {
                    this.loading = false;
                    if (res.data.status === true) {
                        // Sukses, redirect ke halaman utama sidang (sidang/index)
                        window.location.href = res.data.redirect; 
                    } else {
                        this.errorMsg = res.data.message;
                        document.querySelector('.v-dialog .v-card').classList.add('v-shake');
                        setTimeout(() => document.querySelector('.v-dialog .v-card').classList.remove('v-shake'), 500);
                    }
                }).catch(err => { 
                    this.loading = false; 
                    this.errorMsg = "Gagal koneksi server atau NIP/OTP salah. Cek konsol."; 
                })
            }
        },

                // 🔑 FIX LOGOUT REDIRECT
                logoutUser: async function() {
                    try {
                        const response = await axios.get('<?= base_url('api/auth/logout'); ?>');
                        
                        if (response.data.status === true) {
                            // 🟢 Perbaikan: Arahkan ke Controller Loading
                            window.location.href = '<?= base_url('auth/loading'); ?>'; 
                        } else {
                            window.location.href = '<?= base_url('auth/loading'); ?>';
                        }
                    } catch (error) {
                        window.location.href = '<?= base_url('auth/loading'); ?>';
                    }
                }
            }
        })
    </script>
</body>
</html>