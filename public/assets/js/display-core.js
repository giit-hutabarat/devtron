/**
 * DISPLAY CORE LOGIC
 * Khusus untuk halaman Display TV (Public View)
 */

window.onload = function() {
    // Fadeout Preloader (Jika ada)
    const preloader = document.getElementById('global-preloader');
    if(preloader) {
        setTimeout(() => {
            preloader.classList.add('fade-out');
        }, 800);
    }
};

// 1. Vue Instance Setup
const app = new Vue({
    el: '#app',
    vuetify: new Vuetify(), // Jika pakai Vuetify (opsional, sesuaikan dgn css yg di-load)
    
    // Data Global
    data: {
        loading: false,
        snackbar: false,
        timeout: 4000,
        snackbarType: '',
        snackbarMessage: '',
        // Placeholder data konten
        videoData: [],
        newsData: [],
        agendaData: []
    },

    // Computed Properties
    computed: {
        // ... (Tambahkan jika perlu)
    },

    // Methods
    methods: {
        // Method helper untuk menampilkan notifikasi
        showNotify(msg, type = 'info') {
            this.snackbarMessage = msg;
            this.snackbarType = type;
            this.snackbar = true;
        },
        
        // Fungsi refresh halaman otomatis (opsional untuk display TV)
        autoReload(interval = 300000) { // Default 5 menit
            setTimeout(() => window.location.reload(), interval);
        }
    },

    // Lifecycle Hooks
    created() {
        axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
    },
    
    mounted() {
        // Init komponen player video jika ada
        if (typeof VuePlyr !== 'undefined') {
            // Vue.component('vue-plyr', VuePlyr); // Biasanya di-register global sebelum new Vue
        }
        
        // Jalankan auto-reload jika di-set
        // this.autoReload(); 
    }
});