<?php $this->section("style"); ?>
<style>
    /* --- GLOBAL RESET --- */
    html, body {
        height: 100vh;
        margin: 0;
        padding: 0;
        overflow: hidden;
        background-color: #121212;
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }

    #temperature { color: yellow !important; }

    /* --- STRUKTUR UTAMA --- */
    .app-container {
        display: flex;
        flex-direction: column;
        height: 100vh;
        width: 100vw;
    }

    /* 1. HEADER */
    .app-header {
        flex: 0 0 13vh;
        display: flex;
        align-items: center;
        padding: 0 20px;
    }

    /* 2. CONTENT */
    .app-content {
        flex: 1;
        display: flex;
        padding: 0 15px;
        gap: 15px;
        overflow: hidden;
        padding-bottom: 15px;
    }

    /* 3. FOOTER (FLEXBOX BARU) */
    .app-footer {
        flex: 0 0 8vh;
        background: #b71c1c;
        z-index: 9999;
        display: flex; /* Pake Flex biar Jam & Marquee sebelahan rapi */
        align-items: stretch; /* Tinggi sama */
        overflow: hidden;
    }

    /* --- JAM (FIT CONTENT) --- */
    .jam-container {
        flex: 0 0 auto; /* Lebar otomatis sesuai isi */
        min-width: 250px; /* Minimal segini biar gak goyang pas detik jalan */
        background: #0d47a1;
        color: white;
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
        padding: 0 30px; /* Jarak aman kanan kiri teks */
        z-index: 10010;
        border-right: 4px solid rgba(0,0,0,0.2); /* Garis batas */
        box-shadow: 5px 0 15px rgba(0,0,0,0.3);
    }

    /* --- RUNNING TEXT (SISANYA) --- */
    .news-wrapper {
        flex: 1; /* Ambil semua sisa ruang */
        display: flex;
        align-items: center;
        overflow: hidden;
        position: relative;
        background: #b71c1c;
    }
    
    .marquee-content {
        display: inline-block;
        white-space: nowrap;
        padding-left: 100%;
        animation: marquee-scroll 80s linear infinite;
    }
    
    .news-item {
        display: inline-block;
        font-size: 1.6rem; /* Font digedein dikit */
        font-weight: 500;
        color: white;
        margin-right: 100px;
    }
    @keyframes marquee-scroll { 0% { transform: translate(0, 0); } 100% { transform: translate(-100%, 0); } }

    /* --- KOLOM SETTINGS --- */
    .col-kiri, .col-kanan { width: 25%; display: flex; flex-direction: column; gap: 15px; height: 100%; }
    .col-tengah { width: 50%; display: flex; flex-direction: column; gap: 15px; height: 100%; }

    /* --- CARD STYLING --- */
    .card-custom {
        background: rgba(0,0,0,0.6);
        border: 1px solid rgba(255,255,255,0.15);
        border-radius: 8px;
        display: flex; flex-direction: column; overflow: hidden;
    }
    .card-custom-header {
        flex: 0 0 45px;
        display: flex; align-items: center; padding: 0 15px;
        font-size: 1.1rem; font-weight: bold; color: white;
    }
    .card-custom-body {
        flex: 1; position: relative; overflow: hidden; background: white;
    }
    .body-dark { background: transparent; color: white; }
    .body-info-bg { background: rgba(40, 40, 40, 0.95); color: white; }

    /* Warna Header */
    .bg-header-sidang { background-color: #C62828 !important; }
    .bg-header-video { background-color: #212121 !important; }
    .bg-header-sosmed { background-color: #2E7D32 !important; }
    .bg-header-info { background-color: #D32F2F !important; }
    .bg-header-kinerja { background-color: #FFB300 !important; color: black !important; }

    /* --- RASIO TINGGI --- */
    .box-video { flex: 82; } 
    .box-sosmed { flex: 18; } 
    .box-info { flex: 35; } 
    .box-kinerja { flex: 65; }

    /* --- CSS SOSMED (UPDATED: LEBIH RENGGANG) --- */
   /* --- CSS SOSMED REVISI (3 KOLOM) --- */
    .sosmed-container {
        height: 100%; width: 100%;
        background: linear-gradient(135deg, #1a1a1a 0%, #222 100%);
        display: flex;
        align-items: center;
        justify-content: space-between; /* Bagi 3 bagian */
        padding: 5px 30px; /* Padding disesuaikan */
        position: relative;
        overflow: hidden;
        gap: 15px;
    }
    
    /* 1. Bagian Kiri: Ikon */
    .sosmed-icons { display: flex; gap: 15px; }
    .icon-box {
        width: 38px; height: 38px; /* Ukuran pas */
        border-radius: 50%; 
        background: rgba(255,255,255,0.05);
        display: flex; align-items: center; justify-content: center; 
        font-size: 1.2rem; color: white;
        box-shadow: 0 4px 10px rgba(0,0,0,0.5); 
        border: 1px solid rgba(255,255,255,0.1);
        animation: floatIcon 3s ease-in-out infinite;
    }
    /* Warna Brand */
    .icon-box.fb { color: #1877F2; border-color: #1877F2; }
    .icon-box.ig { color: #E4405F; border-color: #E4405F; animation-delay: 0.2s; }
    .icon-box.x { color: #fff; border-color: #fff; animation-delay: 0.4s; }
    .icon-box.yt { color: #FF0000; border-color: #FF0000; animation-delay: 0.6s; }

    /* 2. Bagian Tengah: Teks 3 Tingkat */
   /* 2. Bagian Tengah: Teks 3 Tingkat (Versi Rapi & Aman) */
    .sosmed-center {
        text-align: center;
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
        flex-grow: 1; 
        padding: 0 10px; /* Kasih jarak dikit dari ikon/link */
    }

    /* KEJAKSAAN */
    .title-l1 { 
        font-size: 0.85rem; 
        font-weight: 600; 
        color: #ccc; 
        letter-spacing: 2px; /* Jarak normal elegan */
        margin-bottom: 0;
        line-height: 1.2;
    }

    /* NEGERI */
    .title-l2 { 
        font-size: 1rem; 
        font-weight: 700; 
        color: #fff; 
        letter-spacing: 4px; /* Sedikit renggang biar beda */
        margin-bottom: 0;
        line-height: 1.2;
    }

    /* BOYOLALI */
    .title-l3 { 
        font-size: 1.3rem; 
        font-weight: 900; 
        color: #ffca28; 
        text-transform: uppercase; 
        letter-spacing: 1px;
        line-height: 1.1;
        text-shadow: 0 2px 10px rgba(255, 202, 40, 0.3);
    }
    /* 3. Bagian Kanan: Link Style CSS */
    .sosmed-right {
        display: flex;
        align-items: center;
    }
    .link-badge {
        background: rgba(0, 0, 0, 0.4);
        border: 1px solid #4caf50; /* Border Hijau */
        border-radius: 50px; /* Bentuk Kapsul */
        padding: 8px 20px;
        display: flex;
        align-items: center;
        gap: 10px;
        box-shadow: 0 0 15px rgba(76, 175, 80, 0.15); /* Glow Hijau Tipis */
        transition: all 0.3s ease;
    }
    .link-icon {
        color: #4caf50;
        font-size: 1.2rem;
        animation: pulseGreen 2s infinite;
    }
    .link-text {
        font-family: 'Courier New', monospace; /* Font ala koding/url */
        font-size: 0.9rem;
        font-weight: bold;
        color: #e8f5e9;
        letter-spacing: 0.5px;
    }

    /* Animations */
    @keyframes floatIcon { 0%, 100% { transform: translateY(0); } 50% { transform: translateY(-3px); } }
    @keyframes pulseGreen { 0% { opacity: 1; } 50% { opacity: 0.5; } 100% { opacity: 1; } }

    /* --- CONTENT ELEMENTS --- */
    .full-img { width: 100%; height: 100%; object-fit: fill; background: #fff;} 
    .contain-img { width: 100%; height: 100%; object-fit: contain; padding: 5px; background: #fff;}
    
    .video-wrapper { width: 100%; height: 100%; background: black; }
    .video-wrapper iframe, .video-wrapper video, .video-wrapper .plyr { 
        width: 100% !important; height: 100% !important; 
    }

    /* List Sidang & Info */
    .scroll-list-anim { list-style: none; padding: 0; margin: 0; animation: scrollUp 45s linear infinite; }
    .scroll-info-anim { list-style: none; padding: 0; margin: 0; animation: scrollUp 60s linear infinite; }
    .list-item { padding: 10px 15px; border-bottom: 1px solid rgba(255,255,255,0.2); }
    @keyframes scrollUp { 0% { transform: translateY(0); } 100% { transform: translateY(-50%); } }

</style>
<?php $this->endSection("style") ?>

<div class="app-container">

    <div class="app-header">
        <a class="d-flex align-items-center text-decoration-none text-white" href="#">
            <img style="margin-right: 15px;" id="logo" src="<?php echo base_url('/' . ($logo == "" ? 'logo.png' : $logo)); ?>" width="70" height="70" />
            <span style="line-height: 1.2;">
                <span class="h2 fw-bold d-block mb-0"><?= $nama_instansi; ?></span>
                <span class="h5 fw-normal d-block mb-0" style="opacity:0.8;"><?= $alamat; ?></span>
            </span>
        </a>
    </div>

    <div class="app-content">
        
        <div class="col-kiri">
            <div class="card-custom box-sidang" style="height: 100%;">
                <div class="card-custom-header bg-header-sidang">
                    <i class="mdi mdi-gavel me-2"></i> Sidang Hari Ini
                </div>
                <div class="card-custom-body body-dark">
                    <div v-if="loadingSidang" class="text-center p-3">Loading Data...</div>
                    <div v-if="!loadingSidang && dataSidang.length === 0" class="text-center p-3">Tidak ada jadwal sidang.</div>
                    
                    <div style="height: 100%; overflow: hidden;">
                        <ul v-if="!loadingSidang && dataSidang.length > 0" class="scroll-list-anim">
                            <li v-for="(row, index) in dataSidang" :key="index" class="list-item">
                                <div style="color:#ffca28; font-weight:bold; font-size:1.1rem">{{ row.agenda_sidang }}</div>
                                <div style="font-size:1rem; font-weight:500;">{{ row.nama_terdakwa }}</div>
                                <div style="font-size:0.85rem; color:#ccc;">{{ row.nomor_perkara }} ({{ row.jenis_perkara }})</div>
                                <div style="font-size:0.8rem; color:#aaa;">JPU: {{ row.jpu }} | STATUS: {{ row.status_sidang }}</div>
                            </li>
                            <li v-for="(row, index) in dataSidang" :key="'dup-'+index" class="list-item">
                                <div style="color:#ffca28; font-weight:bold; font-size:1.1rem">{{ row.agenda_sidang }}</div>
                                <div style="font-size:1rem; font-weight:500;">{{ row.nama_terdakwa }}</div>
                                <div style="font-size:0.85rem; color:#ccc;">{{ row.nomor_perkara }} ({{ row.jenis_perkara }})</div>
                                <div style="font-size:0.8rem; color:#aaa;">JPU: {{ row.jpu }} | STATUS: {{ row.status_sidang }}</div>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-tengah">
            <div class="card-custom box-video">
                <div class="card-custom-header bg-header-video">
                    <i class="mdi mdi-video me-2"></i> Video
                </div>
                <div class="card-custom-body" style="background: black;">
                    <div class="video-wrapper">
                        <?php 
                            // --- LOGIKA PEMBERSIH ID YOUTUBE ---
                            $finalVideoId = $videoId; // Default ambil dari controller
                            
                            // 1. Cek apakah ini URL panjang? Kalau iya, ambil ID-nya aja
                            // Regex buat ngambil ID dari link youtube.com atau youtu.be
                            if (preg_match('%(?:youtube(?:-nocookie)?\.com/(?:[^/]+/.+/|(?:v|e(?:mbed)?)/|.*[?&]v=)|youtu\.be/)([^"&?/ ]{11})%i', $videoId, $match)) {
                                $finalVideoId = $match[1];
                            }
                            
                            // 2. Bersihin spasi
                            $finalVideoId = trim($finalVideoId);

                            // 3. Fallback kalau kosong (Pake Video Alam)
                            if (empty($finalVideoId) || $finalVideoId == '0') {
                                $finalVideoId = 'r3wW21ddf9U';
                            }
                        ?>

                        <?php if ($video_youtube == 'no') { ?>
                            <video id="myplayer" controls autoplay muted loop style="width:100%; height:100%; object-fit:contain;"></video>

                        <?php } else { ?>
                            
                            <div style="position:absolute; top:0; left:0; background:red; color:white; padding:2px 5px; font-size:10px; z-index:99;">
                                ID Asli: <?= $videoId; ?> <br> ID Bersih: <?= $finalVideoId; ?>
                            </div>

                            <iframe 
                                width="100%" 
                                height="100%" 
                                src="https://www.youtube.com/embed/<?= $finalVideoId; ?>?autoplay=1&mute=0&loop=1&playlist=<?= $finalVideoId; ?>&controls=0&showinfo=0&rel=0&iv_load_policy=3&playsinline=1" 
                                title="YouTube video player" 
                                frameborder="0" 
                                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" 
                                allowfullscreen
                                style="pointer-events: auto;">
                            </iframe>
                        <?php } ?>
                    </div>
                </div>
            </div>

            <div class="card-custom box-sosmed">
                <div class="card-custom-header bg-header-sosmed">Media Sosial & Website</div>
                <div class="card-custom-body p-0">
                    <div class="sosmed-container">
                        <div class="sosmed-icons">
                            <div class="icon-box fb"><i class="mdi mdi-facebook"></i></div>
                            <div class="icon-box ig"><i class="mdi mdi-instagram"></i></div>
                            <div class="icon-box x"><i class="mdi mdi-twitter"></i></div>
                            <div class="icon-box yt"><i class="mdi mdi-youtube"></i></div>
                        </div>
                        <div class="sosmed-center">
                            <div class="title-l1">KEJAKSAAN</div>
                            <div class="title-l2">NEGERI</div>
                            <div class="title-l3">BOYOLALI</div>
                        </div>
                        <div class="sosmed-right">
                            <div class="link-badge">
                                <i class="mdi mdi-earth link-icon"></i>
                                <span class="link-text">kejari-boyolali.go.id</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-kanan">
            <div class="card-custom box-info">
                <div class="card-custom-header bg-header-info">
                    <i class="mdi mdi-information me-2"></i> Informasi
                </div>
                <div class="card-custom-body body-info-bg">
                    <div style="height: 100%; overflow: hidden;">
                         <ul v-if="dataInfo.length > 0" class="scroll-info-anim"> 
                            <li v-for="item in dataInfo" :key="item.id" class="list-item">
                                <small class="text-warning d-block">{{ item.tgl_news }}</small>
                                <span class="fw-bold" style="font-size: 1rem; line-height:1.2">{{ item.text_news }}</span>
                            </li>
                             <li v-for="item in dataInfo" :key="'d-'+item.id" class="list-item">
                                <small class="text-warning d-block">{{ item.tgl_news }}</small>
                                <span class="fw-bold" style="font-size: 1rem; line-height:1.2">{{ item.text_news }}</span>
                            </li>
                        </ul>
                        <div v-else class="text-center p-3 text-muted">Belum ada informasi.</div>
                    </div>
                </div>
            </div>

            <div class="card-custom box-kinerja">
                <div class="card-custom-header bg-header-kinerja">
                    <i class="mdi mdi-chart-bar me-2"></i> Capaian Kinerja
                </div>
                <div class="card-custom-body p-0">
                    <div id="carouselKinerja" class="carousel slide h-100" data-bs-ride="carousel">
                        <div class="carousel-inner h-100">
                            <div class="carousel-item h-100" v-for="(item, i ) in dataGaleri" :key="i" :class="{ active: i==0 }">
                                <img :src="'<?= base_url(); ?>' + '/' + item.image_url" class="full-img" alt="...">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <div class="app-footer">
        <div class="jam-container">
            <div style="font-size: 0.9rem; opacity: 0.9; margin-bottom: -3px;">{{tanggal}}</div>
            <div style="font-size: 2.5rem; font-weight: bold; line-height: 1;">{{jam}}</div>
        </div>
        <div class="news-wrapper">
            <div class="marquee-content">
                <span v-for="item in dataNews" :key="item.id" class="news-item">
                    {{ item.text_news }} &bull;
                </span>
                <span v-for="item in dataNews" :key="'d-'+item.id" class="news-item">
                    {{ item.text_news }} &bull;
                </span>
            </div>
        </div>
    </div>

</div>

<?php $this->section("modal") ?>
<?php $this->endSection("modal") ?>

<?php $this->section("js") ?>
<script>
    function addZeroBefore(n) { return (n < 10 ? '0' : '') + n; }

    dataVue = {
        ...dataVue,
        tanggal: "", jam: "",
        dataNews: [], dataInfo: [], dataAgenda: [], dataVideo: [], dataGaleri: [],
        loadingSidang: false, headersSidang: [], dataSidang: [],
    }

    createdVue = function() {
        this.getDate(); this.getTime();
        setInterval(this.getDate, 1000); setInterval(this.getTime, 1000);
        setTimeout(() => {
            this.getVideo(); this.getNews(); this.getInfo(); this.getGaleri(); this.getDataSidang();
        }, 500);
    }

    mountedVue = function() {
        setInterval(() => this.getNews(), <?= $news_refresh; ?> * 1000);
        setInterval(() => this.getInfo(), <?= $news_refresh; ?> * 1000);
        setInterval(() => this.getGaleri(), <?= $slide_refresh; ?> * 1000);
        setInterval(() => this.getDataSidang(), 60000);
    }

    methodsVue = {
        ...methodsVue,
        getDate: function() {
            try {
                const w = ["Minggu", "Senin", "Selasa", "Rabu", "Kamis", "Jumat", "Sabtu"];
                const t = new Date();
                this.tanggal = w[t.getDay()] + ', ' + addZeroBefore(t.getDate()) + '-' + (addZeroBefore(t.getMonth() + 1)) + '-' + t.getFullYear();
            } catch(e){}
        },
        getTime: function() {
            try {
                const t = new Date();
                this.jam = addZeroBefore(t.getHours()) + ":" + addZeroBefore(t.getMinutes()) + ":" + addZeroBefore(t.getSeconds());
            } catch(e){}
        },
        getNews: function() { axios.get('<?= base_url() ?>/api/news/news').then(res => { if(res.data.status) this.dataNews = res.data.data; }).catch(e=>{}); },
        getInfo: function() { axios.get('<?= base_url() ?>/api/news/info').then(res => { if(res.data.status) this.dataInfo = res.data.data; }).catch(e=>{}); },
		getGaleri: function() { axios.get('<?= base_url() ?>/api/display/galeri').then(res => { if(res.data.status) this.dataGaleri = res.data.data; }).catch(e=>{}); },
        getVideo: function() {
            axios.get('<?= base_url(); ?>/api/video/display').then(res => {
                if (res.data.status && res.data.data) {
                    this.dataVideo = res.data.data;
                    <?php if ($video_youtube == 'no') : ?> 
                        if(document.getElementById("myplayer")) this.playVideo(); 
                    <?php endif; ?>
                }
            }).catch(e=>{});
        },
        playVideo: function() {
            try {
                var p = document.getElementById("myplayer"); if(!p) return;
                var i = 0; var src = this.dataVideo; if(!src || src.length===0) return;
                var count = src.length;
                p.src = src[0]; p.autoplay = true; p.load();
                function play(n) { if(src[n]) { p.src = src[n]; p.load(); p.play().catch(e=>{}); } }
                p.addEventListener('ended', function() { i = (i == count - 1) ? 0 : i+1; play(i); }, false);
            } catch(e){}
		},
        getDataSidang: function() {
            this.loadingSidang = true;
            axios.get(`<?= base_url(); ?>/datasidang`).then(res => {
                this.loadingSidang = false;
                if (res.data.status) { this.headersSidang = res.data.headers; this.dataSidang = res.data.rows; }
            }).catch(e => { this.loadingSidang = false; });
        },
    }
</script>
<?php $this->endSection("js") ?>