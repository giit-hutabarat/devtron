/**
 * GLOBAL PRELOADER CONTROLLER
 * Cara pakai di script lain:
 * - Loader.show() -> Munculin loading
 * - Loader.hide() -> Sembunyiin loading
 */

const Loader = {
    element: null,
    
    init: function() {
        this.element = document.getElementById('global-preloader');
        
        // Otomatis hide saat halaman selesai loading (Native)
        window.addEventListener('load', () => {
            this.hide(800); // Delay dikit biar smooth
        });
    },

    show: function() {
        if(this.element) {
            this.element.classList.remove('fade-out');
            this.element.style.display = 'flex'; // Pastikan display flex
        }
    },

    hide: function(delay = 0) {
        if(this.element) {
            setTimeout(() => {
                this.element.classList.add('fade-out');
                // Optional: set display none after transition to save memory
                setTimeout(() => {
                    // if(this.element.classList.contains('fade-out')) this.element.style.display = 'none';
                }, 600);
            }, delay);
        }
    }
};

// Jalankan init
Loader.init();