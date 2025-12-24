/**
 * LANDING PAGE LOGIC (Fixed & Optimized)
 * Menggunakan Vue.js 2.x & Vuetify
 */

// A. Handle Preloader (Vanilla JS) - Jalan sebelum Vue siap
window.addEventListener('load', function() {
    var preloader = document.getElementById('preloader');
    if(preloader) {
        // Efek fade out halus
        preloader.style.transition = 'opacity 0.5s ease';
        preloader.style.opacity = '0';
        setTimeout(function() { 
            preloader.style.display = 'none'; 
        }, 500);
    }
});

// B. Vue Instance
new Vue({
    el: '#app',
    vuetify: new Vuetify({
        icons: { iconfont: 'fa' }, // Pastikan FontAwesome aktif
    }),
    data: () => ({
        // State Modal & Form
        modalAuth: false,
        valid: true, 
        loading: false, 
        showPass: false,
        
        // Data Input
        loginUsername: "", 
        loginPassword: "", 
        errorMsg: "", 
        
        // Validasi Form
        rules: { 
            required: v => !!v || 'Wajib diisi.'
        }
    }),
    methods: {
        // 1. Proses Login Admin
        // 1. Proses Login Admin
        loginProcess() {
            if (this.$refs.formLogin.validate()) {
                this.loading = true; 
                this.errorMsg = "";
                
                var params = new URLSearchParams();
                params.append('username', this.loginUsername);
                params.append('password', this.loginPassword);

                if (typeof appConfig === 'undefined') {
                    this.errorMsg = "Config Error: Refresh halaman.";
                    this.loading = false;
                    return;
                }

                axios.post(appConfig.urls.loginAdmin, params)
                    .then(res => {

                        var isSuccess = (res.data.status === 'success' || res.data.success === true || res.data.message.includes("Berhasil"));
                        if (isSuccess) {
                            // SUKSES -> REDIRECT
                            // Gunakan replace agar user gak bisa back ke login
                            window.location.replace(appConfig.urls.dashboard);
                        } else {
                            // GAGAL -> Tampilkan Pesan
                            this.loading = false;
                            this.errorMsg = res.data.message || 'Login gagal. Cek username/password.';
                            this.triggerShake();
                        }
                    })
                    .catch(err => { 
                        console.error("Login Error:", err);
                        this.loading = false; 
                        
                        // Cek apakah ada pesan error dari server (misal 401 Unauthorized)
                        if (err.response && err.response.data && err.response.data.message) {
                            this.errorMsg = err.response.data.message;
                        } else {
                            this.errorMsg = "Gagal koneksi server. Cek internet anda."; 
                        }
                        this.triggerShake();
                    });
            }
        },

        // 2. Proses Logout (Universal)
        logoutUser: function() {
            if (typeof appConfig === 'undefined') return;
            
            // Redirect langsung ke controller logout
            window.location.href = appConfig.urls.logout;
        },

        // 3. Helper: Efek Getar (Shake) saat Error
        triggerShake() {
            const card = document.querySelector('.v-dialog .v-card');
            if(card) {
                card.classList.add('v-shake');
                setTimeout(() => card.classList.remove('v-shake'), 500);
            }
        }
    }
});