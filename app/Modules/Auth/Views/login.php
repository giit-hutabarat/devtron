<?php $this->extend("layouts/frontend"); ?>
<?php $this->section("content"); ?>
<v-container class="px-4 py-0 fill-height" fluid>
    <v-layout flex align-center justify-center>
        <v-flex xs12 sm8 md8>
            <v-card>
                <v-card-text>
                    <v-row>
                        <v-col md="5" style="background-image: url('https://picsum.photos/510/300?random') !important;background-position: center;background-repeat: no-repeat;-webkit-background-size: cover;-moz-background-size: cover;-o-background-size: cover;background-size: cover;">

                        </v-col>
                        <v-col md="7" class="pa-10">
                            <h2 class="font-weight-medium text-center mb-5 black--text">Selamat datang di DISFO</h2>
                            <v-form v-model="valid" ref="form">
                                <p class="mb-2 black--text">Email</p>
                                <v-text-field label="<?= lang('App.labelEmail') ?>" v-model="email" :rules="[rules.email]" :error-messages="emailError" outlined></v-text-field>

                                <p class="mb-2 black--text">Password</p>
                                <v-text-field v-model="password" :append-icon="show1 ? 'mdi-eye' : 'mdi-eye-off'" :rules="[rules.min]" :type="show1 ? 'text' : 'password'" label="Password" hint="<?= lang('App.minChar') ?>" @click:append="show1 = !show1" :error-messages="passwordError" counter outlined></v-text-field>

                                <v-layout justify-space-between>
                                    <v-checkbox v-model="remember" label="Remember me" class="mt-0"></v-checkbox>
                                    <v-spacer></v-spacer>
                                    <a href="<?= base_url('/password/reset') ?>"><?= lang('App.forgotPass') ?></a>
                                </v-layout>

                                <v-btn large block @click="submit" color="primary" :loading="loading" elevation="1">Login</v-btn>
                            </v-form>
                        </v-col>
                    </v-row>
                </v-card-text>
            </v-card>
        </v-flex>
    </v-layout>
</v-container>
<?php $this->endSection("content") ?>

<?php $this->section("js") ?>
<script>
    var errorKeys = []
    computedVue = {
        ...computedVue,
    }
    dataVue = {
        ...dataVue,
        show1: false,
        email: "",
        emailError: "",
        password: "",
        passwordError: "",
        remember: true,
    }
    methodsVue = {
        ...methodsVue,
        submit() {
            this.loading = true;
            axios.post('<?= base_url() ?>/auth/login', {
                    email: this.email,
                    password: this.password,
                })
                .then(res => {
                    // handle success
                    this.loading = false
                    var data = res.data;
                    if (data.status == true) {
                        localStorage.setItem('access_token', JSON.stringify(data.access_token));
                        this.snackbar = true;
                        this.snackbarMessage = data.message;
                        this.$refs.form.resetValidation();
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
                        this.$refs.form.validate();
                    }
                })
                .catch(err => {
                    // handle error
                    console.log(err);
                    this.loading = false
                })
        },
        clear() {
            this.$refs.form.reset()
        }
    }
</script>

<?php $this->endSection("js") ?>