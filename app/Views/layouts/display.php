<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= $title; ?></title>
    <meta name="description" content="<?= $title; ?>">
    <link rel="shortcut icon" href="<?= base_url('images/favicon.png'); ?>" type="image/x-icon">
    <link href="<?= base_url('assets/css/materialdesignicons.min.css') ?>" type="text/css" rel="stylesheet" />
    <link href="<?= base_url('assets/css/bootstrap.min.css') ?>" type="text/css" rel="stylesheet" />
    <link href="<?= base_url('assets/css/styles.css') ?>" rel="stylesheet" />
    <link href="<?= base_url('assets/css/vue-plyr.css') ?>" rel="stylesheet">
    <?= $this->renderSection('style') ?>
</head>

<body style="background: url('<?= base_url() . '/' . $background ?>') no-repeat center center fixed;-webkit-background-size: cover;-moz-background-size: cover;-o-background-size: cover;background-size: cover;">
    <div id="app">

        <main>
            <?= $this->renderSection('content') ?>
        </main>

    </div>

    <?= $this->renderSection('modal') ?>

    <script src="<?= base_url('assets/js/vue.min.js') ?>" type="text/javascript"></script>
    <script src="<?= base_url('assets/js/bootstrap.bundle.min.js') ?>" type="text/javascript"></script>
    <script src="<?= base_url('assets/js/axios.min.js') ?>" type="text/javascript"></script>
    <script src="<?= base_url('assets/js/main.js') ?>" type="text/javascript"></script>
    <script src="<?= base_url('assets/js/vue-plyr.min.js') ?>" type="text/javascript"></script>

    <script>
        var computedVue = {

        }
        var createdVue = function() {
            axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
        }
        var mountedVue = function() {

        }
        var watchVue = {}
        var dataVue = {
            sidebarMenu: true,
            rightMenu: false,
            toggleMini: false,
            dark: false,
            loading: false,
            valid: true,
            notifMessage: '',
            notifType: '',
            snackbar: false,
            timeout: 4000,
            snackbarMessage: '',
            show: false,
            modalAuth: false,
            loginEmail: "",
            loginPassword: "",
            rules: {
                email: v => !!(v || '').match(/@/) || '<?= lang('App.emailValid'); ?>',
                length: len => v => (v || '').length <= len || `<?= lang('App.invalidLength'); ?> ${len}`,
                password: v => !!(v || '').match(/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*(_|[^\w])).+$/) ||
                    '<?= lang('App.strongPassword'); ?>',
                min: v => v.length >= 8 || '<?= lang('App.minChar'); ?>',
                required: v => !!v || '<?= lang('App.isRequired'); ?>',
                number: v => Number.isInteger(Number(v)) || "<?= lang('App.isNumber'); ?>",
                zero: v => v > 0 || "<?= lang('App.isZero'); ?>"
            },
            tab: 0,
            tabs: [{
                name: "Login",
                icon: "mdi-account"
            }, ],
        }
        var methodsVue = {
            modalAuthOpen: function() {
                this.modalAuth = true;
            },
            modalAuthClose: function() {
                this.modalAuth = false;
                this.loginEmail = "";
                this.loginPassword = "";
                this.$refs.formLogin.resetValidation();
            },
            submitLogin() {
                this.loading = true;
                axios.post('<?= base_url(); ?>/auth/login', {
                        email: this.loginEmail,
                        password: this.loginPassword,
                    })
                    .then(res => {
                        // handle success
                        this.loading = false
                        var data = res.data;
                        if (data.status == true) {
                            localStorage.setItem('access_token', JSON.stringify(data.access_token));
                            this.snackbar = true;
                            this.snackbarType = "success";
                            this.snackbarMessage = data.message;
                            this.modalAuth = false;
                            this.$refs.formLogin.resetValidation();
                            setTimeout(() => window.location.reload(), 1000);
                        } else {
                            this.notifType = "error";
                            this.notifMessage = data.message;
                            this.snackbar = true;
                            this.snackbarType = "warning";
                            this.snackbarMessage = data.message.email || data.message.password;
                            this.$refs.formLogin.validate();
                        }
                    })
                    .catch(err => {
                        // handle error
                        console.log(err);
                        this.loading = false
                    })
            },
        }
        Vue.component('vue-plyr', VuePlyr);
    </script>

    <?= $this->renderSection('js') ?>
    <script>
        new Vue({
            el: '#app',
            computed: computedVue,
            data: dataVue,
            mounted: mountedVue,
            created: createdVue,
            watch: watchVue,
            methods: methodsVue,
        })
    </script>
</body>

</html>