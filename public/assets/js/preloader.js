/**
 * GLOBAL PRELOADER CONTROLLER (FIXED VERSION)
 */
const Loader = {
    element: null,
    
    init: function() {
        this.element = document.getElementById('global-preloader');
        
        // Otomatis hide saat halaman selesai loading
        window.addEventListener('load', () => {
            this.hide(800); 
        });
    },

    show: function() {
        if(this.element) {
            this.element.style.display = 'flex'; // Munculkan dulu
            // Sedikit delay agar transisi CSS terbaca
            setTimeout(() => {
                this.element.classList.remove('fade-out');
            }, 10);
        }
    },

    hide: function(delay = 0) {
        if(this.element) {
            setTimeout(() => {
                // 1. Mulai animasi fade-out (opacity: 0)
                this.element.classList.add('fade-out');
                
                // 2. TUNGGU sampai animasi selesai, LALU hilangkan div dari layar
                // PENTING: Baris ini jangan dikomentari!
                setTimeout(() => {
                    this.element.style.display = 'none'; // Agar klik tembus ke bawah
                }, 600); 
            }, delay);
        }
    }
};

// Jalankan init
Loader.init();