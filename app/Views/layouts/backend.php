<!DOCTYPE html>
<html lang="en">
<?php

use App\Libraries\Settings;

$setting = new Settings();
$appname = $setting->info['nama_aplikasi'];
$logo = $setting->info['logo'];

// PENTING: Dapatkan instance URI untuk perbandingan URL di menu
$uri = service('uri'); 
?>

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no, minimal-ui">
    <title><?= $title ?> - <?= $appname ?></title>
    <link rel="shortcut icon" href="<?= base_url() . "/" . $logo; ?>" />
    <!-- Load CSS Libraries -->
    <link href="https://fonts.googleapis.com/css?family=Roboto:100,300,400,500,700,900" rel="stylesheet">
    <link href="<?= base_url('assets/css/materialdesignicons.min.css') ?>" rel="stylesheet">
    <link href="<?= base_url('assets/css/vuetify.min.css') ?>" rel="stylesheet">
    <link href="<?= base_url('assets/css/styles.css') ?>" rel="stylesheet">
</head>

<body>
<?= view('\App\Modules\Loading\Views\loading_template'); ?>
<div id="app">
        <v-app>
            <v-app-bar app color="white" light elevation="2">
                <v-app-bar-nav-icon @click.stop="sidebarMenu = !sidebarMenu"></v-app-bar-nav-icon>
                <v-toolbar-title></v-toolbar-title>
                <v-spacer></v-spacer>
                
                <?php if (!empty(session()->get('username'))) : ?>
                    
                    <!-- 🔑 PERBAIKAN MENU DROPDOWN USER -->
                    <v-menu offset-y left>
                        <template v-slot:activator="{ on, attrs }">
                            <v-btn text v-bind="attrs" v-on="on" class="font-weight-medium">
                                <v-icon left>mdi-account-circle</v-icon> <?= session()->get('username') ?> <v-icon right>mdi-chevron-down</v-icon>
                            </v-btn>
                        </template>

                        <v-card class="rounded-lg elevation-4" min-width="250">
                            <!-- Header Info Pengguna -->
                            <v-list-item class="pa-4">
                                <v-list-item-avatar color="indigo lighten-5">
                                    <v-icon color="indigo">mdi-account-circle</v-icon>
                                </v-list-item-avatar>
                                <v-list-item-content>
                                    <v-list-item-title class="font-weight-bold grey--text text--darken-3">
                                        <?= session()->get('username') ?>
                                    </v-list-item-title>
                                    <v-list-item-subtitle>
                                        <v-chip color="teal" small dark class="mt-1">
                                            <?= session()->get('user_type') == 1 ? 'ADMIN' : 'USER'; ?>
                                        </v-chip>
                                    </v-list-item-subtitle>
                                </v-list-item-content>
                            </v-list-item>
                            
                            <v-divider></v-divider>
                            
                            <!-- Aksi Utama -->
                            <v-list dense>
                                <v-list-item link href="<?= base_url(); ?>">
                                    <v-list-item-icon><v-icon color="blue-grey">mdi-home</v-icon></v-list-item-icon>
                                    <v-list-item-content><v-list-item-title>Kembali ke Beranda</v-list-item-title></v-list-item-content>
                                </v-list-item>
                                
                                <!-- Tombol Logout AJAX -->
                                <v-list-item link @click="logoutUser">
                                    <v-list-item-icon><v-icon color="red">mdi-logout</v-icon></v-list-item-icon>
                                    <v-list-item-content><v-list-item-title>Logout</v-list-item-title></v-list-item-content>
                                </v-list-item>
                            </v-list>
                        </v-card>
                    </v-menu>
                    <!-- AKHIR PERBAIKAN MENU DROPDOWN USER -->
                    
                <?php endif; ?>
                <v-divider class="mx-1" vertical></v-divider>
                <v-btn icon @click.stop="rightMenu = !rightMenu">
                    <v-icon>mdi-cog-outline</v-icon>
                </v-btn>
            </v-app-bar>

            <!-- 🔑 PERUBAHAN WARNA SIDEBAR UTAMA -->
            <v-navigation-drawer color="blue-grey darken-3" dark v-model="sidebarMenu" app floating :permanent="sidebarMenu" :mini-variant.sync="mini" v-if="!isMobile" class="elevation-3">
                <v-list color="blue-grey darken-3" dense>
                    <v-list-item>
                        <v-list-item-action>
                            <v-icon @click.stop="toggleMini = !toggleMini">mdi-chevron-left</v-icon>
                        </v-list-item-action>
                        <!-- 🔑 PERBAIKAN HEADER LOGO DI SINI -->
                        <v-list-item-content>
                            <v-list-item-title class="text-h6">
                                <img src="<?= base_url() . "/" . $logo; ?>" alt="<?= $appname ?>" height="32" class="mr-2" style="vertical-align: middle;">
                                <span v-if="!mini">TRON</span> <!-- 🔑 FIXED: Menampilkan Teks 'TRON' saja -->
                            </v-list-item-title>
                        </v-list-item-content>  
                        <!-- AKHIR PERBAIKAN HEADER LOGO -->
                    </v-list-item>
                </v-list>
                <v-divider></v-divider>
                
                <!-- SIDEBAR NAVIGATION -->
                <!-- Menambahkan prop 'dense' pada v-list nav agar lebih ringkas -->
                <v-list nav dense> 
                    
                    <v-list-item link href="<?= base_url('display'); ?>" alt="Lihat Display" title="Lihat Display" target="_blank">
                        <v-list-item-icon><v-icon>mdi-arrow-right</v-icon></v-list-item-icon>
                        <v-list-item-content><v-list-item-title>Tampil</v-list-item-title></v-list-item-content>
                    </v-list-item>

                    <!-- Menghapus prop 'color' pada list group agar tidak merah -->
                    <v-list-item link href="<?= base_url('dashboard'); ?>" <?php if ($uri->getSegment(1) == "dashboard") : ?><?php echo 'class="v-item--active v-list-item--active"'; ?><?php endif; ?> alt="Dashboard" title="Dashboard">
                        <v-list-item-icon><v-icon>mdi-home</v-icon></v-list-item-icon>
                        <v-list-item-content><v-list-item-title>Dashboard</v-list-item-title></v-list-item-content>
                    </v-list-item>

                    <?php if (session()->get('user_type') == 1) : ?>
                        <!-- Menu Group Display (Tambahkan Ikon pada Submenu) -->
                        <v-list-group :value="false" prepend-icon="mdi-monitor-dashboard">
                            <template v-slot:activator>
                                <v-list-item-content><v-list-item-title>Display</v-list-item-title></v-list-item-content>
                            </template>
                            <!-- 🔑 Ikon ditambahkan di sini. Class 'pl-8' untuk indentasi -->
                            <v-list-item link href="<?= base_url('news'); ?>" class="pl-8"><v-list-item-icon><v-icon small>mdi-newspaper</v-icon></v-list-item-icon><v-list-item-title>News</v-list-item-title></v-list-item>
                            <v-list-item link href="<?= base_url('agenda'); ?>" class="pl-8"><v-list-item-icon><v-icon small>mdi-calendar</v-icon></v-list-item-icon><v-list-item-title>Agenda</v-list-item-title></v-list-item>
                            <v-list-item link href="<?= base_url('galeri'); ?>" class="pl-8"><v-list-item-icon><v-icon small>mdi-image-multiple</v-icon></v-list-item-icon><v-list-item-title>Galeri</v-list-item-title></v-list-item>
                            <v-list-item link href="<?= base_url('video'); ?>" class="pl-8"><v-list-item-icon><v-icon small>mdi-video</v-icon></v-list-item-icon><v-list-item-title>Video</v-list-item-title></v-list-item>
                            <v-list-item link href="<?= base_url('cuaca'); ?>" class="pl-8"><v-list-item-icon><v-icon small>mdi-weather-cloudy</v-icon></v-list-item-icon><v-list-item-title>Cuaca</v-list-item-title></v-list-item>
                        </v-list-group>
                        
                        <!-- Menu Group Masjid (Tambahkan Ikon pada Submenu) -->
                        <v-list-group prepend-icon="mdi-mosque">
                            <template v-slot:activator>
                                <v-list-item-content><v-list-item-title>Masjid</v-list-item-title></v-list-item-content>
                            </template>
                            <!-- 🔑 Ikon ditambahkan di sini. Class 'pl-8' untuk indentasi -->
                            <v-list-item link href="<?= base_url('jadwalsholat'); ?>" class="pl-8"><v-list-item-icon><v-icon small>mdi-clock-check-outline</v-icon></v-list-item-icon><v-list-item-title>Jadwal Sholat</v-list-item-title></v-list-item>
                            <v-list-item link href="<?= base_url('agamaquotes'); ?>" class="pl-8"><v-list-item-icon><v-icon small>mdi-format-quote-open</v-icon></v-list-item-icon><v-list-item-title>Quotes Agama</v-list-item-title></v-list-item>
                            <v-list-item link href="<?= base_url('keuanganmasjid'); ?>" class="pl-8"><v-list-item-icon><v-icon small>mdi-cash-multiple</v-icon></v-list-item-icon><v-list-item-title>Keuangan Masjid</v-list-item-title></v-list-item>
                        </v-list-group>

                        <v-list-item link href="<?= base_url('user'); ?>">
                            <v-list-item-icon><v-icon>mdi-account-multiple</v-icon></v-list-item-icon>
                            <v-list-item-content><v-list-item-title>Pengguna</v-list-item-title></v-list-item-content>
                        </v-list-item>

                        <v-list-item link href="<?= base_url('backup'); ?>">
                            <v-list-item-icon><v-icon>mdi-database</v-icon></v-list-item-icon>
                            <v-list-item-content><v-list-item-title>Backup DB</v-list-item-title></v-list-item-content>
                        </v-list-item>
                        
                        <!-- Menu Group Pengaturan -->
                        <v-list-group prepend-icon="mdi-cog">
                            <template v-slot:activator>
                                <v-list-item-content><v-list-item-title>Pengaturan</v-list-item-title></v-list-item-content>
                            </template>
                            <v-list-item link href="<?= base_url('setting/general'); ?>" class="pl-8"><v-list-item-icon><v-icon small>mdi-cog-outline</v-icon></v-list-item-icon><v-list-item-title>Umum</v-list-item-title></v-list-item>
                            <v-list-item link href="<?= base_url('setting/app'); ?>" class="pl-8"><v-list-item-icon><v-icon small>mdi-application-cog</v-icon></v-list-item-icon><v-list-item-title>Aplikasi</v-list-item-title></v-list-item>
                            <v-list-item link href="<?= base_url('layout'); ?>" class="pl-8"><v-list-item-icon><v-icon small>mdi-layers-outline</v-icon></v-list-item-icon><v-list-item-title>Layout</v-list-item-title></v-list-item>
                        </v-list-group>

                    <?php endif; ?>
                    
                    <?php if ((session()->get('user_type') == 2) || (session()->get('user_type') == 3)) : ?>
                        <v-list-item link href="<?= base_url('member'); ?>">
                            <v-list-item-icon><v-icon>mdi-home</v-icon></v-list-item-icon>
                            <v-list-item-content><v-list-item-title>Dashboard</v-list-item-title></v-list-item-content>
                        </v-list-item>
                    <?php endif; ?>
                </v-list>

                <template v-slot:append>
                    <v-divider></v-divider>
                    <div class="text-center">
                        <v-list-item dense>
                            <v-list-item-icon style="font-size:12px;" v-if="toggleMini">
                            &copy; {{ new Date().getFullYear() }} Tron
                            </v-list-item-icon>
                            <v-list-item-content style="font-size:12px;" v-else>&copy; {{ new Date().getFullYear() }} Tron</v-list-item-content>
                        </v-list-item>
                    </div>
                </template>

            </v-navigation-drawer>
            
            <!-- ... (Sisa kode v-navigation-drawer, v-main, dan footer JS) ... -->
            
            <v-navigation-drawer v-model="rightMenu" app right bottom temporary>
                <template v-slot:prepend>
                    <v-list-item>
                        <v-list-item-content>
                            <v-list-item-title>Pengaturan</v-list-item-title>
                        </v-list-item-content>
                    </v-list-item>
                </template>

                <v-divider></v-divider>

                <v-list-item>
                    <v-list-item-avatar><v-icon>mdi-theme-light-dark</v-icon></v-list-item-avatar>
                    <v-list-item-content>Tema {{themeText}}</v-list-item-content>
                    <v-list-item-action><v-switch v-model="dark" inset @click="toggleTheme"></v-switch></v-list-item-action>
                </v-list-item>

                <v-list-item>
                    <v-list-item-avatar><v-icon>mdi-earth</v-icon></v-list-item-avatar>
                    <v-list-item-content>Lang</v-list-item-content>
                    <v-list-item-action>
                        <v-btn-toggle>
                            <v-btn text small link href="<?= base_url('lang/id'); ?>">ID</v-btn>
                            <v-btn text small link href="<?= base_url('lang/en'); ?>">EN</v-btn>
                        </v-btn-toggle>
                    </v-list-item-action>
                </v-list-item>
            </v-navigation-drawer>

            <!-- 🔑 PERUBAHAN WARNA LATAR BELAKANG CONTAINER -->
            <v-main class="grey lighten-4">
                <v-container class="pa-5" fluid>
                    <?= $this->renderSection('content') ?>
                </v-container>
            </v-main>

            <v-snackbar v-model="snackbar" :timeout="timeout" color="indigo" style="bottom:20px;">
                <span v-if="snackbar">{{snackbarMessage}}</span>
                <template v-slot:action="{ attrs }">
                    <v-btn text v-bind="attrs" @click="snackbar = false">ok</v-btn>
                </template>
            </v-snackbar>
        </v-app>
    </div>
    
    <!-- Bagian Footer JS dipertahankan seperti yang terakhir kita perbaiki -->
    <script src="<?= base_url('assets/js/vue.min.js') ?>" type="text/javascript"></script>
    <script src="<?= base_url('assets/js/vuetify.min.js') ?>" type="text/javascript"></script>
    <script src="<?= base_url('assets/js/axios.min.js') ?>" type="text/javascript"></script>
    <script src="<?= base_url('assets/js/main.js') ?>" type="text/javascript"></script>
    <script src="<?= base_url('assets/js/Chart.min.js') ?>" type="text/javascript"></script>
    <script src="<?= base_url('assets/js/vue-chartjs.min.js') ?>" type="text/javascript"></script>
    <script src="<?= base_url('assets/js/apexcharts.js') ?>" type="text/javascript"></script>
    <script src="<?= base_url('assets/js/vue-apexcharts.js') ?>" type="text/javascript"></script>

    <!-- 🟢 2. DEFINISI HOOK & DATA GLOBAL -->
    <script>
        // 1. Definisikan Computed Properties
        window.computedVue = {
            mini: {
                get() {
                    return this.$vuetify.breakpoint.xsOnly || this.toggleMini;
                },
                set(value) {
                    this.toggleMini = value;
                }
            },
            isMobile() {
                if (this.$vuetify.breakpoint.xsOnly) {
                    return this.sidebarMenu = false
                }
            },
            themeText() {
                return this.$vuetify.theme.dark ? '<?= lang('App.dark') ?>' : '<?= lang('App.light') ?>'
            }
        };

        // 2. Definisikan Default Hooks (Agar modul bisa memanggilnya/menimpanya)
        window.defaultCreatedVue = function() {
            // Logic umum yang berjalan di awal
            axios.defaults.headers.common['X-requested-with'] = 'XMLHttpRequest';
        };
        
        window.mountedVue = function() {
            // Logic mounted Vue global (Tema, dll.)
            const theme = localStorage.getItem("dark_theme");
            if (theme) {
                if (theme === "true") {
                    this.$vuetify.theme.dark = true;
                    this.dark = true;
                } else {
                    this.$vuetify.theme.dark = false;
                    this.dark = false;
                }
            } else if (
                window.matchMedia &&
                window.matchMedia("(prefers-color-scheme: dark)").matches
            ) {
                this.$vuetify.theme.dark = false;
                localStorage.setItem(
                    "dark_theme",
                    this.$vuetify.theme.dark.toString()
                );
            }
        };
        
        window.watchVue = {};
        
        // --- 3. DATA GLOBAL (PLACEHOLDER) ---
        window.dataVue = {
            sidebarMenu: true,
            rightMenu: false,
            toggleMini: false,
            dark: false,
            group: null,
            search: '',
            pencarian: '', 
            loading: false,
            loading2: false,
            loading3: false,
            valid: true,
            notifMessage: '',
            notifType: '',
            snackbar: false,
            timeout: 4000,
            snackbarType: '',
            snackbarMessage: '',
            show: false,
            show1: false,
            show2: false,
            rules: {
                email: v => !!(v || '').match(/@/) || '<?= lang('App.emailValid'); ?>',
                length: len => v => (v || '').length <= len || `<?= lang('App.invalidLength'); ?> ${len}`,
                password: v => !!(v || '').match(/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*(_|[^\w])).+$/) ||
                    '<?= lang('App.strongPassword'); ?>',
                min: v => (v || '').length >= 8 || '<?= lang('App.minChar'); ?>',
                required: v => !!v || '<?= lang('App.isRequired'); ?>',
                number: v => Number.isInteger(Number(v)) || "<?= lang('App.isNumber'); ?>",
                zero: v => v > 0 || "<?= lang('App.isZero'); ?>",
                varchar: v => (v || '').length <= 255 || 'Maks 255 Karakter'
            },
            // Sidebar Navigation Data
            displays: [], 
            masjid: [],
            settings: [],
            // Placeholder Modal/State
            modalAdd: false,    
            modalEdit: false,   
            modalDelete: false, 
            modalShow: false,   
            date: false,        
            date2: false,       
            time: false,        
            time2: false,       
            // Placeholder Data Utama
            dataHeader: [],     
            dataAgenda: [],     
            dataJenis: [],      
            // Placeholder Form (FIX ReferenceError di template)
            idAgenda: "",
            namaAgenda: "",
            nama_agendaError: "",
            tempatAgenda: "",
            tempat_agendaError: "",
            tglAgenda: "",
            tgl_agendaError: "",
            waktu: "",
            waktuError: "",
            jenisAgenda: "",
            jenis_agendaError: "",
        };
        
        // 4. Definisikan FUNGSI UMUM dan LOGOUT (DEFAULT METHODS)
        var defaultMethods = {
            toggleTheme() {
                this.$vuetify.theme.dark = !this.$vuetify.theme.dark;
                localStorage.setItem("dark_theme", this.$vuetify.theme.dark.toString());
            },
            logoutUser: async function() {
                try {
                    const response = await axios.get('<?= base_url('api/auth/logout'); ?>');
                    if (response.data.status === true) {
                        const redirectUrl = response.data.data.url;
                        window.location.href = redirectUrl;
                    } else {
                        window.location.href = '<?= base_url('auth/loading'); ?>';
                    }
                } catch (error) {
                    window.location.href = '<?= base_url('auth/loading'); ?>';
                }
            }
        };

        // 5. Gabungkan methods Vue yang di-override oleh View spesifik
        var finalMethods = { ...defaultMethods, ...(window.methodsVue || {}) };
        
    </script>
    
    <!-- 🟢 3. EKSEKUSI SCRIPT MODULE -->
    <?= $this->renderSection('js') ?> 

    <!-- 🟢 4. FINAL BOOTSTRAP VUE -->
    <script>
        if (window.methodsVue) {
             finalMethods = { ...finalMethods, ...window.methodsVue };
        }

        var app = new Vue({
            el: '#app',
            vuetify: new Vuetify(),
            computed: window.computedVue,
            data: window.dataVue,
            mounted: window.mountedVue,
            created: window.createdVue || window.defaultCreatedVue, 
            watch: window.watchVue,
            methods: finalMethods, 
            components: {
                apexchart: VueApexCharts,
            },
        });
    </script>
</body>

</html>