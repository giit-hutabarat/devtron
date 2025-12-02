<v-dialog v-model="modalAuth" persistent max-width="400px" min-width="400px" scrollable>
    <v-card class="pa-5">
        <v-card-title class="mb-5 text-h5">
            Login
            <v-spacer></v-spacer>
            <v-btn icon @click="modalAuthClose">
                <v-icon>mdi-close</v-icon>
            </v-btn>
        </v-card-title>
        <v-card-text>
            <v-form ref="formLogin" v-model="valid">
                <p class="mb-2 black--text">Email</p>
                <v-text-field v-model="loginEmail" :rules="[rules.email]" label="Email" :error-messages="emailError" outlined></v-text-field>

                <p class="mb-2 black--text">Password</p>
                <v-text-field v-model="loginPassword" :append-icon="show?'mdi-eye':'mdi-eye-off'" :rules="[rules.min]" :type="show ? 'text' : 'password'" name="input-10-1" label="Password" hint="At least 8 characters" :error-messages="passwordError" counter @click:append="show = !show" outlined></v-text-field>
                <v-layout justify-space-between>
                    <v-checkbox v-model="remember" label="Remember me" class="mt-0"></v-checkbox>
                    <v-spacer></v-spacer>
                    <a class="subtitle-2" href="<?= base_url('/password/reset') ?>"><?= lang('App.forgotPass') ?></a>
                </v-layout>
                <v-btn color="primary" large :loading="loading" @click="submitLogin" elevation="1" block>Masuk</v-btn>
            </v-form>
        </v-card-text>
    </v-card>

</v-dialog>

<?php $this->section("js") ?>
<script>
    var errorKeys = []
    computedVue = {
        ...computedVue,
    }
    createdVue = function() {

    }
    watchVue = {

    }
    dataVue = {
        ...dataVue,
        modalAuth: false,
        loginEmail: "",
        emailError: "",
        loginPassword: "",
        passwordError: "",
        remember: true,
    }
    methodsVue = {
        ...methodsVue,
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
            axios.post(`<?= base_url(); ?>/auth/login`, {
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
                        this.snackbar = true;
                        this.snackbarMessage = data.message;
                        errorKeys = Object.keys(data.data);
                        errorKeys.map((el) => {
                            this[`${el}Error`] = data.data[el];
                        });
                        if (errorKeys.length > 0) {
                            setTimeout(() => this.notifType = "", 4000);
                            setTimeout(() => errorKeys.map((el) => {
                                this[`${el}Error`] = "";
                            }), 4000);
                        }
                        this.modalAuth = true;
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
</script>

<?php $this->endSection("js") ?>