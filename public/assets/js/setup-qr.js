document.addEventListener('DOMContentLoaded', function() {
    
    const keyDisplay = document.getElementById('keyDisplay');
    const btnToggle = document.getElementById('btnToggleKey');
    const btnCopy = document.getElementById('btnCopyKey');
    const iconEye = document.getElementById('iconEye');
    
    // Ambil raw key dari data attribute HTML (PHP)
    const rawKey = keyDisplay.getAttribute('data-key');
    const maskedKey = '•••• •••• •••• ••••';
    
    let isVisible = false;

    // 1. Toggle Visibility (Lihat/Sembunyi)
    btnToggle.addEventListener('click', function() {
        isVisible = !isVisible;
        
        if (isVisible) {
            // Format split per 4 karakter agar mudah dibaca
            const formatted = rawKey.match(/.{1,4}/g).join(' ');
            keyDisplay.textContent = formatted;
            keyDisplay.classList.remove('blur-text');
            keyDisplay.style.color = '#d63384'; // Warna pink/merah agar terlihat penting
            
            iconEye.classList.remove('fa-eye');
            iconEye.classList.add('fa-eye-slash');
            this.innerHTML = '<i class="fa-solid fa-eye-slash"></i> Sembunyikan';
        } else {
            keyDisplay.textContent = maskedKey;
            keyDisplay.classList.add('blur-text');
            keyDisplay.style.color = '#adb5bd';
            
            iconEye.classList.remove('fa-eye-slash');
            iconEye.classList.add('fa-eye');
            this.innerHTML = '<i class="fa-solid fa-eye" id="iconEye"></i> Lihat Kunci';
        }
    });

    // 2. Copy to Clipboard
    btnCopy.addEventListener('click', function() {
        navigator.clipboard.writeText(rawKey).then(() => {
            const originalText = this.innerHTML;
            this.innerHTML = '<i class="fa-solid fa-check"></i> Tersalin!';
            this.style.backgroundColor = '#198754';
            this.style.color = '#fff';
            this.style.borderColor = '#198754';
            
            setTimeout(() => {
                this.innerHTML = originalText;
                this.style.backgroundColor = '';
                this.style.color = '';
                this.style.borderColor = '';
            }, 2000);
        }).catch(err => {
            alert('Gagal menyalin text');
        });
    });

});