<!DOCTYPE html>
<html lang="id">
<?php
use App\Libraries\Settings;
$setting = new Settings();
$appname = $setting->info['nama_aplikasi'];
$logo    = base_url() . "/" . $setting->info['logo'];
?>
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no, minimal-ui">
    <title><?= $title ?> - <?= $appname ?></title>
    <link rel="shortcut icon" href="<?= $logo ?>" />
    
    <link href="https://fonts.googleapis.com/css?family=Roboto:100,300,400,500,700,900" rel="stylesheet">
    <link href="<?= base_url('assets/css/materialdesignicons.min.css') ?>" rel="stylesheet">
    <link href="<?= base_url('assets/css/vuetify.min.css') ?>" rel="stylesheet">
    <link href="<?= base_url('assets/css/styles.css') ?>" rel="stylesheet">
</head>

<body>
    <?= $this->include('art/preloader') ?>

    <div id="app">
        <v-app>
            <?= $this->include('layouts/partials/topbar') ?>

            <?= $this->setData(['logo_url' => $logo, 'app_name' => $appname])->include('layouts/partials/sidebar') ?>
            
            <v-navigation-drawer v-model="rightMenu" app right bottom temporary>
                <template v-slot:prepend>
                    <v-list-item><v-list-item-content><v-list-item-title>Pengaturan Tampilan</v-list-item-title></v-list-item-content></v-list-item>
                </template>
                <v-divider></v-divider>
                <v-list-item>
                    <v-list-item-avatar><v-icon>mdi-theme-light-dark</v-icon></v-list-item-avatar>
                    <v-list-item-content>Tema {{themeText}}</v-list-item-content>
                    <v-list-item-action><v-switch v-model="dark" inset @click="toggleTheme"></v-switch></v-list-item-action>
                </v-list-item>
                <v-list-item>
                    <v-list-item-avatar><v-icon>mdi-earth</v-icon></v-list-item-avatar>
                    <v-list-item-content>Bahasa</v-list-item-content>
                    <v-list-item-action>
                        <v-btn-toggle>
                            <v-btn text small link href="<?= base_url('lang/id'); ?>">ID</v-btn>
                            <v-btn text small link href="<?= base_url('lang/en'); ?>">EN</v-btn>
                        </v-btn-toggle>
                    </v-list-item-action>
                </v-list-item>
            </v-navigation-drawer>

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
    
    <script src="<?= base_url('assets/js/vue.min.js') ?>"></script>
    <script src="<?= base_url('assets/js/vuetify.min.js') ?>"></script>
    <script src="<?= base_url('assets/js/axios.min.js') ?>"></script>
    <script src="<?= base_url('assets/js/main.js') ?>"></script>
    <script src="<?= base_url('assets/js/Chart.min.js') ?>"></script>
    <script src="<?= base_url('assets/js/vue-chartjs.min.js') ?>"></script>
    <script src="<?= base_url('assets/js/apexcharts.js') ?>"></script>
    <script src="<?= base_url('assets/js/vue-apexcharts.js') ?>"></script>

    <script>
        const AppConfig = {
            base_url: '<?= base_url() ?>',
            urls: {
                logout: '<?= base_url('api/auth/logout'); ?>',
                loading: '<?= base_url('auth/loading'); ?>',
            },
            lang: {
                dark: '<?= lang('App.dark') ?>',
                light: '<?= lang('App.light') ?>',
                emailValid: '<?= lang('App.emailValid'); ?>',
                isRequired: '<?= lang('App.isRequired'); ?>',
                isNumber: "<?= lang('App.isNumber'); ?>",
                // ...tambahkan lang lain jika perlu
            }
        };
    </script>

    <script src="<?= base_url('assets/js/backend-core.js') ?>"></script>

    <?= $this->renderSection('js') ?> 

    <script>
        // Jalankan Aplikasi setelah semua script termuat
        document.addEventListener('DOMContentLoaded', () => {
            initVueApp();
        });
    </script>
</body>
</html>