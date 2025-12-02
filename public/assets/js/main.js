// main.js:
window.onload = function () {
  // Biarkan delay 200ms untuk memastikan semua aset awal sudah dimuat
  window.setTimeout(fadeout, 200); 
};

function fadeout() {
  const preloader = document.getElementById('app-preloader');
  
  if (preloader) { 
      // 1. Set opacity ke 0 (memulai fade out jika CSS transition ada)
      preloader.style.opacity = "0"; 

      // 2. Beri waktu 500ms agar transisi selesai (atau sekadar cukup waktu)
      window.setTimeout(() => {
          // ✅ PERBAIKAN: Hapus elemen dari DOM agar tidak ada lagi yang memblokir klik
          preloader.remove(); 
          
          // CATATAN: Jika browser tidak mendukung .remove(), gunakan ini:
          // preloader.parentNode.removeChild(preloader);
          
      }, 500); 
  }
}