<?php 
// Ambil variabel logo dari setting agar konsisten dengan backend.php
$setting = config('setting');
$logo = $setting->info['logo'] ?? 'logo.png';
$base_url = base_url(); // Dapatkan base_url untuk logo
?>
<style>
/* Style ini khusus untuk Modul Loading (dipanggil saat redirect Logout) */
#app-preloader {
position: fixed !important; 
    top: 0 !important; 
    left: 0 !important; 
    width: 100% !important; 
    height: 100% !important; 
    background-color: #1e293b !important; 
    z-index: 99999 !important; 
    display: flex !important;
    align-items: center !important; 
    justify-content: center !important;
    /* Tambahkan transition CSS jika belum ada di template */
    transition: opacity 0.5s ease-in-out;
}

/* CONTAINER UTAMA */
.loader {
    display: flex;
    flex-direction: column; 
    align-items: center; 
    margin-bottom: 25px; 
}

/* Logo Fix */
.loader-logo {
    position: relative;
    z-index: 10;
    margin-bottom: -55px !important; 
    line-height: 0 !important; 
}
.loader-logo img {
    width: 80px; 
    height: 80px;
    display: block !important; 
    margin: 0 auto !important; 
    filter: drop-shadow(0 0 10px rgba(255,255,255,0.4));
}

/* Spinner */
.spinner {
    position: relative;
    width: 70px;
    height: 70px;
    margin-top: 15px !important; 
}
.spinner-container {
    width: 100%;
    height: 100%;
    animation: container-rotate 1.5s infinite linear;
}
.spinner-rotator { position: relative; width: 100%; height: 100%; }
.spinner-left, .spinner-right { position: absolute; top: 0; height: 100%; width: 50%; overflow: hidden; }
.spinner-left { left: 0; }
.spinner-right { right: 0; }

.spinner-circle {
    position: absolute;
    top: 0;
    width: 200%;
    height: 100%;
    border-radius: 50%;
    border: 6px solid rgba(255, 255, 255, 0.1); 
    border-color: #38bdf8 transparent transparent #38bdf8; 
    animation: fill-unfill-rotate 5s cubic-bezier(0.4, 0, 0.2, 1) infinite, glow-pulse 1.5s ease-in-out infinite alternate;
}
.spinner-left .spinner-circle { left: 0; border-right: 6px solid #38bdf8; transform: rotate(-130deg); animation: left-rotate 1.5s cubic-bezier(0.4, 0, 0.2, 1) infinite; }
.spinner-right .spinner-circle { left: -100%; border-left: 6px solid #38bdf8; transform: rotate(-130deg); animation: right-rotate 1.5s cubic-bezier(0.4, 0, 0.2, 1) infinite; }

/* Animasi Rotasi dan Glow */
@keyframes container-rotate { 100% { transform: rotate(360deg); } }
@keyframes left-rotate { 0% { transform: rotate(-130deg); } 50% { transform: rotate(-5deg); } 100% { transform: rotate(-130deg); } }
@keyframes right-rotate { 0% { transform: rotate(-130deg); } 50% { transform: rotate(-5deg); } 100% { transform: rotate(-130deg); } }
@keyframes fill-unfill-rotate { 12.5% { transform: rotate(135deg); } 25% { transform: rotate(270deg); } 37.5% { transform: rotate(405deg); } 50% { transform: rotate(540deg); } 62.5% { transform: rotate(675deg); } 75% { transform: rotate(810deg); } 87.5% { transform: rotate(945deg); } 100% { transform: rotate(1080deg); } }
@keyframes glow-pulse {
    0% { box-shadow: 0 0 10px rgba(56, 189, 248, 0.6); }
    100% { box-shadow: 0 0 25px rgba(56, 189, 248, 1); }
}

/* Teks loading */
.loader-text { 
    color: #e2e8f0; 
    font-size: 1.1rem; 
    letter-spacing: 3px; 
    text-transform: uppercase;
    font-weight: 700;
    margin-top: 20px;
    text-shadow: 0 0 8px rgba(226, 232, 240, 0.3);
}
</style>

<div id="app-preloader">
    <div class="loader">
        <div class="loader-logo">
            <img src="<?= $base_url . "/" . $logo; ?>" alt="Preloader" width="80">
        </div>
        
        <div class="spinner">
            <div class="spinner-container">
                <div class="spinner-rotator">
                    <div class="spinner-left">
                        <div class="spinner-circle"></div>
                    </div>
                    <div class="spinner-right">
                        <div class="spinner-circle"></div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="loader-text">MEMPROSES AKSES...</div>
    </div>
</div>