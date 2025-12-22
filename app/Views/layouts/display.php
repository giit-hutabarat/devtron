<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
    
    <meta http-equiv="X-Content-Type-Options" content="nosniff">
    <meta http-equiv="Referrer-Policy" content="strict-origin-when-cross-origin">
    
    <title><?= esc($title ?? 'Display Informasi'); ?></title>
    <link rel="shortcut icon" href="<?= base_url('images/favicon.png'); ?>" type="image/x-icon">
    
    <link href="<?= base_url('assets/css/materialdesignicons.min.css') ?>" rel="stylesheet" />
    <link href="<?= base_url('assets/css/bootstrap.min.css') ?>" rel="stylesheet" />
    <link href="<?= base_url('assets/css/vue-plyr.css') ?>" rel="stylesheet">
    <link href="<?= base_url('assets/css/styles.css') ?>" rel="stylesheet" />
    
    <?= $this->renderSection('style') ?>
    
    <style>
        body {
            background-color: #000;
            /* Background diambil dari Controller -> Layout Child -> Master */
            background-image: url('<?= base_url() . '/' . esc($background ?? 'images/bg-default.jpg') ?>');
            background-repeat: no-repeat;
            background-position: center center;
            background-attachment: fixed;
            background-size: cover;
            overflow: hidden; 
        }
        [v-cloak] { display: none !important; }
    </style>
</head>

<body>
    <?= $this->include('art/preloader') ?>

    <div id="app" v-cloak>
        <main class="container-fluid p-0 h-100">
            <?= $this->renderSection('content') ?>
        </main>
        
        <div class="position-fixed bottom-0 end-0 p-3" style="z-index: 9999">
            <div v-if="snackbar" :class="['toast show align-items-center text-white border-0', 'bg-' + snackbarType]" role="alert">
                <div class="d-flex">
                    <div class="toast-body">{{ snackbarMessage }}</div>
                </div>
            </div>
        </div>
    </div>

    <script src="<?= base_url('assets/js/vue.min.js') ?>"></script>
    <script src="<?= base_url('assets/js/axios.min.js') ?>"></script>
    <script src="<?= base_url('assets/js/bootstrap.bundle.min.js') ?>"></script>
    <script src="<?= base_url('assets/js/vue-plyr.min.js') ?>"></script>

    <script>
        // Init Axios
        axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
        
        // Register Component
        if(typeof VuePlyr !== 'undefined') Vue.component('vue-plyr', VuePlyr);

        // Data & Methods Default (Placeholder)
        window.dataVue = {
            loading: false,
            snackbar: false,
            snackbarType: 'info',
            snackbarMessage: '',
            // Placeholder agar tidak error jika layout child belum ready
            dataNews: [], dataInfo: [], dataAgenda: [], dataVideo: [],
            dataJadwalsholat: {}, dataCuaca: {},
            tanggal: '', jam: ''
        };

        window.methodsVue = {
            // Helper umum
        };
        
        window.createdVue = function() {};
        window.mountedVue = function() {};
    </script>

    <?= $this->renderSection('js') ?> 

    <script>
        // Hapus Preloader manual
        window.onload = function() {
            const pre = document.getElementById('global-preloader');
            if(pre) { pre.style.opacity=0; setTimeout(()=>pre.remove(), 600); }
        };

        // Jalankan Vue setelah semua script child dimuat
        new Vue({
            el: '#app',
            data: window.dataVue,
            methods: window.methodsVue,
            created: window.createdVue,
            mounted: window.mountedVue
        });
    </script>
</body>
</html>