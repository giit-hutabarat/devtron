<v-dialog v-model="modalAuth" persistent max-width="400px" min-width="400px" scrollable>
    <v-tabs v-model="tab" show-arrows background-color="primary" icons-and-text dark grow>
        <v-tabs-slider color="primary"></v-tabs-slider>
        <v-tab v-for="(item, i) in tabs" :key="i">
            <div class="subtitle-2">{{ item.name }}</div>
            <v-icon class="pt-2">{{ item.icon }}</v-icon>
        </v-tab>
        <v-tab-item>
            <v-card>
                <v-card-text class="pa-5">
                    <v-form ref="formLogin" v-model="valid">
                        <v-text-field v-model="loginEmail" :rules="[rules.email]" label="E-mail" :error-messages="emailError" outlined></v-text-field>

                        <v-text-field v-model="loginPassword" :append-icon="show?'mdi-eye':'mdi-eye-off'" :rules="[rules.min]" :type="show ? 'text' : 'password'" name="input-10-1" label="Password" hint="At least 8 characters" :error-messages="passwordError" counter @click:append="show = !show" outlined></v-text-field>
                        <p><a class="subtitle-2" href="<?= base_url('/password/reset') ?>"><?= lang('App.forgotPass') ?></a></p>
                        <v-btn color="primary" large :loading="loading" @click="submitLogin" elevation="0" block>
                            Masuk
                        </v-btn>
                        </v-card-actions>
                    </v-form>
                </v-card-text>
                <v-card-actions>
                    <v-spacer></v-spacer>
                    <v-btn text class="mr-2" large @click="modalAuthClose"><?= lang('App.close') ?></v-btn>
            </v-card>
        </v-tab-item>
        <v-tab-item>
            <v-card class="px-4">
                <v-card-text>

                </v-card-text>
                <v-divider></v-divider>
                <v-card-actions>
                    <v-spacer></v-spacer>

                </v-card-actions>
            </v-card>
        </v-tab-item>
    </v-tabs>
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
        tab: 0,
        tabs: [{
            name: "Login",
            icon: "mdi-login-variant"
        }, {
            name: "Reset",
            icon: "mdi-account"
        }, ],
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