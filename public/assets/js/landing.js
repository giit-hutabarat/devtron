/**
 * LANDING PAGE LOGIC
 * Menggunakan Vue.js 2.x & Vuetify
 */

// 1. Handle Preloader (Vanilla JS)
window.addEventListener('load', function() {
    var preloader = document.getElementById('preloader');
    if(preloader) {
        setTimeout(function() {
            preloader.style.opacity = '0';
            setTimeout(function() { preloader.style.display = 'none'; }, 800);
        }, 800);
    }
});

// 2. Vue Instance
new Vue({
    el: '#app',
    vuetify: new Vuetify({
        icons: { iconfont: 'fa' },
    }),
    data: () => ({
        // State Modal
        modalAuth: false,
        valid: true, 
        loading: false, 
        showPass: false,
        
        // Form Data
        loginUsername: "", 
        loginPassword: "", 
        errorMsg: "", 
        
        // Rules
        rules: { 
            required: v => !!v || 'Wajib diisi.'
        }
    }),
    methods: {
        // Proses Login Admin (Via Modal)
        loginProcess() {
            if (this.$refs.formLogin.validate()) {
                this.loading = true; 
                this.errorMsg = "";
                
                var formData = new FormData();
                formData.append('username', this.loginUsername);
                formData.append('password', this.loginPassword);
                
                // Gunakan appConfig dari PHP untuk URL
                const url = appConfig.urls.loginAdmin;

                axios.post(url, formData)
                    .then(res => {
                        this.loading = false;
                        if (res.data.status === true) {
                            // Sukses: Redirect ke Dashboard
                            window.location.href = appConfig.urls.dashboard;
                        } else {
                            // Gagal: Tampilkan pesan & Shake effect
                            this.errorMsg = res.data.message;
                            this.triggerShake();
                        }
                    })
                    .catch(err => { 
                        this.loading = false; 
                        this.errorMsg = "Gagal koneksi server. Cek internet anda."; 
                    })
            }
        },

        // Proses Logout (Admin & Sidang)
        // Kita buat universal agar bisa dipakai keduanya
        logoutUser: async function() {
            try {
                // Panggil logout API (menghancurkan session)
                await axios.get(appConfig.urls.logout);
                
                // Apapun hasilnya, redirect ke halaman loading/home untuk refresh state
                window.location.href = appConfig.urls.afterLogout;
            } catch (error) {
                window.location.href = appConfig.urls.afterLogout;
            }
        },

        // Helper: Shake Effect untuk Modal Error
        triggerShake() {
            const card = document.querySelector('.v-dialog .v-card');
            if(card) {
                card.classList.add('v-shake');
                setTimeout(() => card.classList.remove('v-shake'), 500);
            }
        }
    }
});