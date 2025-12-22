/**
 * BACKEND CORE LOGIC
 * Menangani inisialisasi Vue, Vuetify, dan Global Methods
 */

// 1. Computed Properties Global
window.computedVue = {
    mini: {
        get() { return this.$vuetify.breakpoint.xsOnly || this.toggleMini; },
        set(value) { this.toggleMini = value; }
    },
    isMobile() {
        if (this.$vuetify.breakpoint.xsOnly) return this.sidebarMenu = false;
    },
    themeText() {
        return this.$vuetify.theme.dark ? AppConfig.lang.dark : AppConfig.lang.light;
    }
};

// 2. Default Hooks
window.defaultCreatedVue = function() {
    axios.defaults.headers.common['X-requested-with'] = 'XMLHttpRequest';
};

window.mountedVue = function() {
    const theme = localStorage.getItem("dark_theme");
    if (theme) {
        this.$vuetify.theme.dark = (theme === "true");
        this.dark = (theme === "true");
    } else if (window.matchMedia && window.matchMedia("(prefers-color-scheme: dark)").matches) {
        this.$vuetify.theme.dark = false; // Default preference
        localStorage.setItem("dark_theme", "false");
    }
};

window.watchVue = {};

// 3. Data Global
window.dataVue = {
    sidebarMenu: true,
    rightMenu: false,
    toggleMini: false,
    dark: false,
    group: null,
    search: '',
    pencarian: '', 
    loading: false,
    valid: true,
    snackbar: false,
    timeout: 4000,
    snackbarType: '',
    snackbarMessage: '',
    // ... (Sisa variabel data lainnya bisa ditambahkan sesuai kebutuhan modul) ...
    // Placeholder untuk data yang sering dipakai agar tidak error di console
    modalAdd: false, modalEdit: false, modalDelete: false, 
    dataHeader: [], dataAgenda: [],
    
    // Rules Validasi (Mengambil text dari AppConfig PHP)
    rules: {
        email: v => !!(v || '').match(/@/) || AppConfig.lang.emailValid,
        required: v => !!v || AppConfig.lang.isRequired,
        number: v => Number.isInteger(Number(v)) || AppConfig.lang.isNumber,
        // Tambahkan rules lain sesuai kebutuhan
    }
};

// 4. Default Methods
const defaultMethods = {
    toggleTheme() {
        this.$vuetify.theme.dark = !this.$vuetify.theme.dark;
        localStorage.setItem("dark_theme", this.$vuetify.theme.dark.toString());
    },
    logoutUser: async function() {
        try {
            const response = await axios.get(AppConfig.urls.logout);
            if (response.data.status === true) {
                window.location.href = response.data.data.url;
            } else {
                window.location.href = AppConfig.urls.loading;
            }
        } catch (error) {
            window.location.href = AppConfig.urls.loading;
        }
    }
};

// 5. Init Vue App (Dipanggil setelah DOM Ready di backend.php)
function initVueApp() {
    // Merge methods dari view spesifik jika ada
    let finalMethods = { ...defaultMethods, ...(window.methodsVue || {}) };

    new Vue({
        el: '#app',
        vuetify: new Vuetify(),
        computed: window.computedVue,
        data: window.dataVue,
        mounted: window.mountedVue,
        created: window.createdVue || window.defaultCreatedVue, 
        watch: window.watchVue,
        methods: finalMethods, 
        components: {
            apexchart: (typeof VueApexCharts !== 'undefined') ? VueApexCharts : null,
        },
    });
}