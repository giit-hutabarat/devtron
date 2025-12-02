<?php 
// Ambil variabel logo dari setting agar konsisten dengan backend.php
$logo = $settings->info['logo'] ?? 'logo.png';
$base_url = base_url();
?>
<style>
/* 1. LAYER UTAMA (WAJIB) */
#app-preloader {
    position: fixed !important; 
    top: 0 !important; 
    left: 0 !important; 
    width: 100% !important; 
    height: 100% !important; 
    background-color: #1e293b !important; /* Biru Tua/Dark */
    z-index: 99999 !important; 
    display: flex !important;
    flex-direction: column; /* Untuk menata Dot dan Text */
    align-items: center !important; 
    justify-content: center !important;
    transition: opacity 0.5s ease-in-out; 
}

/* 2. CONTAINER TITIK (Menggunakan Flexbox/Grid untuk posisi) */
.loader-liquid {
    display: flex;
    justify-content: center;
    align-items: center;
    position: relative;
    width: 120px; /* Lebar total animasi */
    height: 30px;
}

/* 3. STYLING TITIK */
.dot {
    width: 18px;
    height: 18px;
    border-radius: 50%;
    background-color: #38bdf8; /* Warna Cyan/Blue */
    margin: 0 5px;
    /* Kunci: Efek liquid dengan filter blur */
    filter: blur(4px); 
    animation: liquid-move 1.5s infinite ease-in-out;
}

/* 4. PENUNDAAN ANIMASI */
.dot-1 { animation-delay: 0s; }
.dot-2 { animation-delay: 0.2s; }
.dot-3 { animation-delay: 0.4s; }

/* 5. TEKS LOADING */
.loader-text { 
    color: #a0a8b4; /* Warna abu-abu terang */
    font-size: 0.9rem; 
    letter-spacing: 2px; 
    text-transform: uppercase;
    font-weight: 500;
    margin-top: 30px;
}

/* 6. KEYFRAMES */
@keyframes liquid-move {
    0%, 100% {
        transform: scale(0.8);
        opacity: 0.7;
    }
    50% {
        transform: scale(1.2);
        opacity: 1;
    }
}
</style>

<div id="app-preloader">
    <div class="loader-liquid">
        <div class="dot dot-1"></div>
        <div class="dot dot-2"></div>
        <div class="dot dot-3"></div>
    </div>
    <div class="loader-text">MEMPROSES AKSES</div>
</div>