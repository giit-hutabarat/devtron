document.addEventListener('DOMContentLoaded', function() {
    
    // 1. Toggle Visibility OTP (Fitur Mata)
    const toggleBtn = document.getElementById('toggleOtp');
    const otpInput = document.getElementById('otp_code');
    const toggleIcon = document.getElementById('iconEye');

    if (toggleBtn && otpInput) {
        toggleBtn.addEventListener('click', function() {
            const type = otpInput.getAttribute('type') === 'password' ? 'text' : 'password';
            otpInput.setAttribute('type', type);
            
            // Ganti Icon (Menggunakan FontAwesome classes)
            if (type === 'text') {
                toggleIcon.classList.remove('fa-eye');
                toggleIcon.classList.add('fa-eye-slash');
            } else {
                toggleIcon.classList.remove('fa-eye-slash');
                toggleIcon.classList.add('fa-eye');
            }
        });
    }

    // 2. Input Masking (Hanya Angka)
    // Mencegah user mengetik huruf di NIP dan OTP
    const numericInputs = document.querySelectorAll('input[inputmode="numeric"]');
    numericInputs.forEach(input => {
        input.addEventListener('input', function(e) {
            this.value = this.value.replace(/[^0-9]/g, '');
        });
    });

});