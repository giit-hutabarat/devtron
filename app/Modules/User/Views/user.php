<?php $this->extend("layouts/backend"); ?>
<?php $this->section("content"); ?>
<template>
    <!-- Card Kontainer Utama -->
    <v-card class="rounded-xl elevation-4">
        
        <!-- Header Halaman -->
        <v-card-title class="pa-4 grey lighten-5">
            <v-icon left color="indigo">mdi-account-multiple</v-icon>
            <h1 class="font-weight-bold text-h5 grey--text text--darken-3"><?= $title; ?></h1>
        </v-card-title>
        
        <!-- Toolbar Responsif -->
        <v-card-text class="pa-4">
            <v-row class="mb-2">
                <!-- Kolom Button Tambah -->
                <v-col cols="12" md="4" class="py-0">
                    <v-btn 
                        color="indigo" 
                        dark 
                        large 
                        @click="modalAddOpen" 
                        elevation="2" 
                        class="rounded-pill"
                        block
                    >
                        <v-icon left>mdi-account-plus-outline</v-icon> <?= lang('App.add') ?>
                    </v-btn>
                </v-col>
                
                <!-- Kolom Search Field -->
                <v-col cols="12" md="8" class="py-0">
                    <v-text-field 
                        v-model="search" 
                        append-icon="mdi-magnify" 
                        label="Cari Username atau Email..." 
                        single-line 
                        hide-details 
                        outlined 
                        dense 
                        class="mt-2"
                        clearable
                    >
                    </v-text-field>
                </v-col>
            </v-row>
        </v-card-text>
        
        <!-- Data Table (Responsive & Rapi) -->
        <v-data-table 
            :headers="headers" 
            :items="users" 
            :items-per-page="10" 
            :loading="loading" 
            :search="search" 
            class="elevation-0 px-4 pb-4 my-4 pt-0" 
            loading-text="Sedang memuat... Harap tunggu" 
            dense
        >
            <template v-slot:item.id="{ item, index }">
                {{ index + 1 }}
            </template>
            <template v-slot:item.user_type="{ item }">
                <!-- Select Role (Tampilan lebih clean) -->
                <span v-if="item.username == 'admin'">
                    <v-select v-model="item.user_type" :items="roles" item-text="label" item-value="value" dense disabled hide-details></v-select>
                </span>
                <span v-else>
                    <v-select v-model="item.user_type" :items="roles" item-text="label" item-value="value" dense @change="setRole(item)" hide-details></v-select>
                </span>
            </template>
            <template v-slot:item.is_active="{ item }">
                <!-- Switch Aktif -->
                <span v-if="item.username == 'admin'">
                    <v-switch v-model="item.is_active" false-value="0" true-value="1" color="success" disabled hide-details class="mt-0"></v-switch>
                </span>
                <span v-else>
                    <v-switch v-model="item.is_active" false-value="0" true-value="1" color="success" @change="setActive(item)" hide-details class="mt-0"></v-switch>
                </span>
            </template>
            <template v-slot:item.actions="{ item }">
                <v-btn color="indigo" class="mr-1" small icon @click="editItem(item)" title="Edit Profil">
                    <v-icon small>mdi-pencil-outline</v-icon>
                </v-btn>
                <v-btn color="blue-grey" class="mr-1" small icon @click="changePassword(item)" title="Ganti Password">
                    <v-icon small>mdi-key-variant</v-icon>
                </v-btn>
                <span v-if="item.username == 'admin'">
                    <v-btn color="red" small icon disabled>
                        <v-icon small>mdi-delete</v-icon>
                    </v-btn>
                </span>
                <span v-else>
                    <v-btn color="red" small icon @click="deleteItem(item)" title="Hapus Pengguna">
                        <v-icon small>mdi-delete</v-icon>
                    </v-btn>
                </span>
            </template>
        </v-data-table>
    </v-card>
</template>

<!-- Modal Add User -->
<template>
    <v-row justify="center">
        <v-dialog v-model="modalAdd" persistent max-width="700px">
            <v-card class="rounded-xl">
                <v-card-title class="text-h6 white--text primary">
                    <v-icon left dark>mdi-account-plus-outline</v-icon> <?= lang('App.add') ?> User
                    <v-spacer></v-spacer>
                    <v-btn icon dark @click="modalAddClose">
                        <v-icon>mdi-close</v-icon>
                    </v-btn>
                </v-card-title>
                <v-card-text class="py-5">
                    <v-form v-model="valid" ref="form">
                        <v-text-field v-model="email" :rules="[rules.email]" label="E-mail" :error-messages="emailError" outlined dense></v-text-field>

                        <v-text-field v-model="userName" label="Username" maxlength="20" :error-messages="usernameError" outlined dense required></v-text-field>

                        <v-text-field label="Nama Lengkap" v-model="fullname" :error-messages="fullnameError" outlined dense></v-text-field>
                        
                        <v-divider class="my-4"></v-divider>
                        <p class="mb-2 text-subtitle-1 font-weight-medium grey--text text--darken-2">Password</p>

                        <v-text-field v-model="password" :append-icon="show1 ? 'mdi-eye' : 'mdi-eye-off'" :rules="[rules.min]" :type="show1 ? 'text' : 'password'" label="Password" hint="<?= lang('App.minChar') ?>" counter @click:append="show1 = !show1" :error-messages="passwordError" outlined dense></v-text-field>

                        <v-text-field block v-model="verify" :append-icon="show1 ? 'mdi-eye' : 'mdi-eye-off'" :rules="[passwordMatch]" :type="show1 ? 'text' : 'password'" label="Confirm Password" counter @click:append="show1 = !show1" outlined dense :error-messages="verifyError"></v-text-field>
                    </v-form>
                </v-card-text>
                <v-divider></v-divider>
                <v-card-actions class="py-3">
                    <v-spacer></v-spacer>
                    <v-btn large color="primary" @click="saveUser" :loading="loading" elevation="2">
                        <v-icon left>mdi-content-save</v-icon> <?= lang('App.save') ?>
                    </v-btn>
                </v-card-actions>
            </v-card>
        </v-dialog>
    </v-row>
</template>

<!-- Modal Edit User -->
<template>
    <v-row justify="center">
        <v-dialog v-model="modalEdit" persistent max-width="700px">
            <v-card class="rounded-xl">
                <v-card-title class="text-h6 white--text teal darken-1">
                    <v-icon left dark>mdi-pencil-box-multiple</v-icon> <?= lang('App.editUser') ?> {{emailEdit}}
                    <v-spacer></v-spacer>
                    <v-btn icon dark @click="modalEditClose">
                        <v-icon>mdi-close</v-icon>
                    </v-btn>
                </v-card-title>
                <v-card-text class="py-5">
                    <v-form ref="form" v-model="valid">
                        <v-text-field label="E-mail" v-model="emailEdit" :rules="[rules.email]" outlined dense :error-messages="emailError"></v-text-field>

                        <v-text-field label="Username" v-model="userNameEdit" :error-messages="usernameError" outlined dense disabled></v-text-field>

                        <v-text-field label="Nama Lengkap" v-model="fullnameEdit" :error-messages="fullnameError" outlined dense></v-text-field>
                    </v-form>
                </v-card-text>
                <v-divider></v-divider>
                <v-card-actions class="py-3">
                    <v-spacer></v-spacer>
                    <v-btn large color="teal darken-1" @click="updateUser" :loading="loading" elevation="2">
                        <v-icon left>mdi-content-save</v-icon> <?= lang('App.update') ?>
                    </v-btn>
                </v-card-actions>
            </v-card>
        </v-dialog>
    </v-row>
</template>

<!-- Modal Change Password -->
<template>
    <v-row justify="center">
        <v-dialog v-model="modalPassword" persistent max-width="700px">
            <v-card class="rounded-xl">
                <v-card-title class="text-h6 white--text blue-grey darken-2">
                    <v-icon left dark>mdi-key-variant</v-icon> Ganti Password {{emailEdit}}
                    <v-spacer></v-spacer>
                    <v-btn icon dark @click="changePassClose">
                        <v-icon>mdi-close</v-icon>
                    </v-btn>
                </v-card-title>
                <v-card-text class="py-5">
                    <v-form ref="form" v-model="valid">
                        <v-text-field label="E-mail" v-model="emailEdit" :rules="[rules.email]" outlined dense disabled></v-text-field>

                        <v-text-field v-model="password" :append-icon="show1 ? 'mdi-eye' : 'mdi-eye-off'" :rules="[rules.min]" :type="show1 ? 'text' : 'password'" label="Password Baru" hint="<?= lang('App.minChar') ?>" counter @click:append="show1 = !show1" :error-messages="passwordError" outlined dense></v-text-field>

                        <v-text-field block v-model="verify" :append-icon="show1 ? 'mdi-eye' : 'mdi-eye-off'" :rules="[passwordMatch]" :type="show1 ? 'text' : 'password'" label="Confirm Password" counter @click:append="show1 = !show1" outlined dense :error-messages="verifyError"></v-text-field>
                    </v-form>
                </v-card-text>
                <v-divider></v-divider>
                <v-card-actions class="py-3">
                    <v-spacer></v-spacer>
                    <v-btn large color="blue-grey darken-2" @click="updatePassword" :loading="loading" elevation="2">
                        <v-icon left>mdi-content-save</v-icon> <?= lang('App.update') ?>
                    </v-btn>
                </v-card-actions>
            </v-card>
        </v-dialog>
    </v-row>
</template>

<!-- Modal Delete -->
<template>
    <v-row justify="center">
        <v-dialog v-model="modalDelete" persistent max-width="450px">
            <v-card class="rounded-xl pa-2">
                <v-card-title>
                    <v-icon color="error" class="mr-2" x-large>mdi-alert-octagon</v-icon> Konfirmasi Hapus
                </v-card-title>
                <v-card-text>
                    <div class="mt-2">
                        <h3 class="font-weight-regular">Yakin hapus pengguna <b>{{ userNameDelete }}</b>? Tindakan ini tidak bisa dibatalkan.</h3>
                    </div>
                </v-card-text>
                <v-card-actions>
                    <v-spacer></v-spacer>
                    <v-btn large text @click="modalDelete = false">Batal</v-btn>
                    <v-btn large color="red darken-2" dark @click="deleteUser" :loading="loading">Ya, Hapus</v-btn>
                </v-card-actions>
            </v-card>
        </v-dialog>
    </v-row>
</template>
<!-- End Modal Delete -->
<?php $this->endSection("content") ?>

<?php $this->section("js") ?>
<script>
    // 💥 KOREKSI WAJIB: Amankan objek global sebelum merging
    window.dataVue = window.dataVue || {};
    window.methodsVue = window.methodsVue || {};
    window.computedVue = window.computedVue || {};

    // 1. Gabungkan Data Spesifik User ke window.dataVue
    Object.assign(window.dataVue, {
        // --- Properti Default Layout yang WAJIB ada (Anti-ReferenceError) ---
        snackbar: false,
        timeout: 4000, 
        snackbarType: '',
        snackbarMessage: '',
        valid: true, // Untuk Form Validasi
        
        // --- Properti Spesifik View User ---
        search: "",
        loading: false, 
        modalAdd: false,
        modalEdit: false,
        modalDelete: false,
        modalPassword: false,
        show1: false, // Untuk toggle password
        
        // Data Headers
        headers: [{
            text: 'No.', // Diubah dari '# '
            value: 'id',
            width: '5%',
            sortable: false, // 💥 KOREKSI: Matikan sorting untuk kolom nomor
        }, {
            text: 'E-mail',
            value: 'email'
        }, {
            text: 'Username',
            value: 'username',
            width: '15%'
        }, {
            text: 'Role',
            value: 'user_type',
            width: '15%'
        }, {
            text: '<?= lang("App.active") ?>',
            value: 'is_active',
            width: '10%'
        }, {
            text: '<?= lang('App.action') ?>',
            value: 'actions',
            sortable: false,
            width: '15%'
        }, ],
        users: [],
        roles: [{
            label: 'Admin',
            value: '1'
        }, {
            label: 'User',
            value: '2'
        }, ],
        
        // Data Form Input
        userName: "",
        email: "",
        fullname: "",
        password: "",
        verify: "",
        
        // Data Edit & Delete
        userIdEdit: "",
        userNameEdit: "",
        emailEdit: "",
        fullnameEdit: "",
        userIdDelete: "",
        userNameDelete: "",
        
        // Data Error Messages
        verifyError: "",
        emailError: "",
        fullnameError: "",
        usernameError: "",
        passwordError: "",
        user_typeError: "",
        is_activeError: "",
    });

    // 2. Gabungkan Computed Properties
    Object.assign(window.computedVue, {
        passwordMatch: function() {
            // Gunakan arrow function jika lo ingin this.password diakses
            return () => this.password === this.verify || "<?= lang('App.samePassword') ?>";
        }
    });

    // 3. Gabungkan Methods Spesifik User ke window.methodsVue
    Object.assign(window.methodsVue, {
        // Method helper untuk menampilkan Snackbar
        showSnackbar: function(message, type = 'success') {
            this.snackbarMessage = message;
            this.snackbarType = type;
            this.snackbar = true;
        },

        // Modal Add
        modalAddOpen: function() {
            this.modalAdd = true;
            if (this.$refs.form) {
                this.$refs.form.resetValidation();
                this.$refs.form.reset();
            }
        },
        modalAddClose: function() {
            this.modalAdd = false;
            if (this.$refs.form) {
                this.$refs.form.resetValidation();
            }
        },
        
        // Get User
        getUsers: function() {
            this.loading = true;
            axios.get('<?= base_url(); ?>/api/user')
                .then(res => {
                    this.loading = false;
                    var data = res.data;
                    if (data.status == true) {
                        // Pastikan is_active di-set sebagai string
                        this.users = data.data.map(user => ({
                            ...user,
                            is_active: String(user.is_active) 
                        }));
                    } else {
                        this.showSnackbar(data.message, 'error');
                    }
                })
                .catch(err => {
                    console.error("Error fetching users:", err.response);
                    this.showSnackbar('Gagal memuat data pengguna.', 'error');
                    this.loading = false;
                })
        },

        // Save User
        saveUser: function() {
            if (!this.$refs.form.validate()) return;
            this.loading = true;
            axios.post('<?= base_url(); ?>/api/user/save', {
                    email: this.email,
                    username: this.userName,
                    fullname: this.fullname,
                    password: this.password,
                })
                .then(res => {
                    this.loading = false;
                    var data = res.data;
                    if (data.status == true) {
                        this.showSnackbar(data.message, 'success');
                        this.getUsers();
                        this.modalAddClose();
                    } else {
                        this.showSnackbar(data.message, 'error');
                        errorKeys = Object.keys(data.data);
                        errorKeys.forEach((el) => { this[`${el}Error`] = data.data[el]; });
                        setTimeout(() => { errorKeys.forEach((el) => { this[`${el}Error`] = ""; }); }, 4000);
                    }
                })
                .catch(err => {
                    console.error("Error saving user:", err.response);
                    this.showSnackbar('Gagal menyimpan pengguna.', 'error');
                    this.loading = false;
                })
        },

        // Get Item Edit
        editItem: function(user) {
            this.modalEdit = true;
            this.userIdEdit = user.id;
            this.userNameEdit = user.username;
            this.emailEdit = user.email;
            this.fullnameEdit = user.fullname;
            if (this.$refs.form) this.$refs.form.resetValidation();
        },
        modalEditClose: function() {
            this.modalEdit = false;
            if (this.$refs.form) this.$refs.form.resetValidation();
        },

        //Update User
        updateUser: function() {
            if (!this.$refs.form.validate()) return;
            this.loading = true;
            axios.put(`<?= base_url(); ?>/api/user/update/${this.userIdEdit}`, {
                    email: this.emailEdit,
                    fullname: this.fullnameEdit,
                })
                .then(res => {
                    this.loading = false;
                    var data = res.data;
                    if (data.status == true) {
                        this.showSnackbar(data.message, 'success');
                        this.getUsers();
                        this.modalEditClose();
                    } else {
                        this.showSnackbar(data.message, 'error');
                        errorKeys = Object.keys(data.data);
                        errorKeys.forEach((el) => { this[`${el}Error`] = data.data[el]; });
                        setTimeout(() => { errorKeys.forEach((el) => { this[`${el}Error`] = ""; }); }, 4000);
                    }
                })
                .catch(err => {
                    console.error("Error updating user:", err.response);
                    this.showSnackbar('Gagal update pengguna.', 'error');
                    this.loading = false;
                })
        },

        // Get Item Delete
        deleteItem: function(item) {
            this.modalDelete = true;
            this.userIdDelete = item.id;
            this.userNameDelete = item.username;
        },

        // Delete
        deleteUser: function() {
            this.loading = true;
            axios.delete(`<?= base_url(); ?>/api/user/delete/${this.userIdDelete}`)
                .then(res => {
                    this.loading = false;
                    var data = res.data;
                    if (data.status == true) {
                        this.showSnackbar(data.message, 'success');
                        this.getUsers();
                        this.modalDelete = false;
                    } else {
                        this.showSnackbar(data.message, 'error');
                    }
                })
                .catch(err => {
                    console.error("Error deleting user:", err.response);
                    this.showSnackbar('Gagal hapus pengguna.', 'error');
                    this.loading = false;
                })
        },

        // Set Active
        setActive: function(item) {
            // Nilai item.is_active sudah berubah karena v-switch @click
            this.loading = true;
            axios.put(`<?= base_url(); ?>/api/user/setactive/${item.id}`, {
                    is_active: item.is_active, 
                })
                .then(res => {
                    this.loading = false;
                    this.showSnackbar(res.data.message, res.data.status ? 'success' : 'error');
                })
                .catch(err => {
                    console.error("Error setting active status:", err.response);
                    this.showSnackbar('Gagal set status aktif.', 'error');
                    this.loading = false;
                })
        },

        // Set Role
        setRole: function(item) {
            this.loading = true;
            axios.put(`<?= base_url(); ?>/api/user/setrole/${item.id}`, {
                    user_type: item.user_type, 
                })
                .then(res => {
                    this.loading = false;
                    this.showSnackbar(res.data.message, res.data.status ? 'success' : 'error');
                })
                .catch(err => {
                    console.error("Error setting role:", err.response);
                    this.showSnackbar('Gagal set role.', 'error');
                    this.loading = false;
                })
        },

        // Change Password
        changePassword: function(user) {
            this.modalPassword = true;
            this.userIdEdit = user.id;
            this.userNameEdit = user.username;
            this.emailEdit = user.email;
            this.fullnameEdit = user.fullname;
            this.password = ""; // Clear form
            this.verify = ""; // Clear form
            if (this.$refs.form) this.$refs.form.resetValidation();
        },
        changePassClose: function() {
            this.modalPassword = false;
            if (this.$refs.form) this.$refs.form.resetValidation();
        },

        updatePassword() {
            if (!this.$refs.form.validate()) return;
            this.loading = true;
            axios.post('<?= base_url() ?>/api/user/changepassword', {
                    // 💥 KOREKSI: Tambahkan ID pengguna yang akan diubah password-nya
                    id: this.userIdEdit, 
                    email: this.emailEdit,
                    password: this.password,
                    verify: this.verify
                })
                .then(res => {
                    this.loading = false
                    var data = res.data;
                    if (data.status == true) {
                        this.showSnackbar(data.message, 'success');
                        this.changePassClose();
                    } else {
                        this.showSnackbar(data.message, 'error');
                        errorKeys = Object.keys(data.data);
                        errorKeys.forEach((el) => { this[`${el}Error`] = data.data[el]; });
                        setTimeout(() => { errorKeys.forEach((el) => { this[`${el}Error`] = ""; }); }, 4000);
                    }
                })
                .catch(err => {
                    console.error("Error changing password:", err.response);
                    this.showSnackbar('Gagal ganti password.', 'error');
                    this.loading = false
                })
        },
    });

    // 3. Created Hook
    window.createdVue = function() {
        // Panggil created hook default dari layout jika ada
        if (typeof window.defaultCreatedVue === 'function') {
            window.defaultCreatedVue.call(this);
        }
        this.getUsers();
        console.log("User View: Data Loaded");
    };
</script>
<?php $this->endSection("js") ?>