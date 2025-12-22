<?= $this->extend('layouts/display') ?>

<?= $this->section('style') ?>
<style>
    /* --- RESET & FONT --- */
    html, body {
        height: 100vh; margin: 0; padding: 0; overflow: hidden;
        background-color: #121212;
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }

    #temperature { color: yellow !important; font-weight: bold; }

    /* --- LAYOUT GRID UTAMA --- */
    .app-container {
        display: flex; flex-direction: column; height: 100vh; width: 100vw;
    }
    .app-header {
        flex: 0 0 13vh; display: flex; align-items: center; padding: 0 20px;
        background: linear-gradient(to bottom, rgba(0,0,0,0.8), transparent);
    }
    .app-content {
        flex: 1; display: flex; padding: 0 15px; gap: 15px;
        overflow: hidden; padding-bottom: 15px;
    }
    .app-footer {
        flex: 0 0 8vh; background: #b71c1c; z-index: 9999;
        display: flex; align-items: stretch; overflow: hidden;
        box-shadow: 0 -4px 10px rgba(0,0,0,0.5);
    }

    /* --- FOOTER COMPONENTS --- */
    .jam-container {
        flex: 0 0 auto; min-width: 250px;
        background: #0d47a1; color: white;
        display: flex; flex-direction: column; justify-content: center; align-items: center;
        padding: 0 20px; z-index: 10;
        box-shadow: 5px 0 15px rgba(0,0,0,0.3); border-right: 2px solid rgba(255,255,255,0.1);
    }
    .news-wrapper {
        flex: 1; display: flex; align-items: center;
        overflow: hidden; background: #b71c1c; position: relative;
    }
    
    /* Animasi Marquee Halus */
    .marquee-content {
        display: inline-block; white-space: nowrap;
        animation: marquee-scroll 80s linear infinite;
        padding-left: 100%;
        line-height: 8vh; /* Vertikal Center Teks */
    }
    .news-item {
        display: inline-block; font-size: 1.6rem; font-weight: 500;
        color: white; margin-right: 100px;
    }
    @keyframes marquee-scroll { 0% { transform: translate(0, 0); } 100% { transform: translate(-100%, 0); } }

    /* --- GRID KOLOM --- */
    .col-kiri, .col-kanan { width: 25%; display: flex; flex-direction: column; gap: 15px; height: 100%; }
    .col-tengah { width: 50%; display: flex; flex-direction: column; gap: 15px; height: 100%; }

    /* --- CARD STYLING --- */
    .card-custom {
        background: rgba(30, 30, 30, 0.6);
        border: 1px solid rgba(255,255,255,0.15);
        border-radius: 8px;
        display: flex; flex-direction: column; overflow: hidden;
        backdrop-filter: blur(5px);
        box-shadow: 0 4px 15px rgba(0,0,0,0.3);
    }
    .card-custom-header {
        flex: 0 0 45px; display: flex; align-items: center; padding: 0 15px;
        font-size: 1.1rem; font-weight: bold; color: white;
        text-transform: uppercase; letter-spacing: 1px;
    }
    .card-custom-body {
        flex: 1; position: relative; overflow: hidden; background: transparent;
    }
    
    /* Header Colors (Original Style) */
    .bg-header-sidang { background-color: #C62828 !important; }
    .bg-header-video { background-color: #212121 !important; }
    .bg-header-sosmed { background-color: #2E7D32 !important; }
    .bg-header-info { background-color: #0d47a1 !important; }
    .bg-header-kinerja { background-color: #FF6F00 !important; }

    /* Rasio Tinggi Card */
    .box-video { flex: 78; } 
    .box-sosmed { flex: 22; } 
    .box-info { flex: 40; } 
    .box-kinerja { flex: 60; }

    /* --- SOSMED STYLE --- */
    .sosmed-container {
        height: 100%; width: 100%;
        background: linear-gradient(90deg, #1b5e20 0%, #000000 100%);
        display: flex; align-items: center; justify-content: space-between;
        padding: 0 20px;
    }
    .sosmed-icons { display: flex; gap: 15px; }
    .icon-box {
        width: 38px; height: 38px; border-radius: 50%;
        display: flex; align-items: center; justify-content: center;
        font-size: 1.2rem; color: white;
        border: 2px solid white;
        transition: transform 0.2s;
    }
    .icon-box.fb { background: #1877F2; border-color: #1877F2; }
    .icon-box.ig { background: #E4405F; border-color: #E4405F; }
    .icon-box.x  { background: #000; border-color: #fff; }
    .icon-box.yt { background: #FF0000; border-color: #FF0000; }

    .sosmed-center { text-align: center; color: white; flex-grow: 1; }
    .title-follow { font-size: 0.8rem; letter-spacing: 3px; color: #eee; text-transform: uppercase; margin-bottom: 2px; }
    .title-instansi { font-size: 1.4rem; font-weight: 900; color: #FFC107; text-transform: uppercase; line-height: 1; text-shadow: 2px 2px 4px rgba(0,0,0,0.5); }

    .link-badge {
        border: 1px solid #4caf50;
        border-radius: 50px;
        padding: 5px 20px;
        background: rgba(0,0,0,0.3);
    }
    .link-text { font-family: 'Courier New', monospace; font-weight: bold; color: #fff; font-size: 1rem; }

    /* --- VIDEO FULL FIX (MERGED) --- */
    /* Wrapper dibuat relative agar child absolute bisa nempel */
    .video-wrapper { 
        width: 100%; 
        height: 100%; 
        background: #000; 
        position: relative; /* KUNCI UTAMA */
        overflow: hidden;
    }
    
    /* Paksa semua elemen video/iframe/plyr 100% dan Absolute */
    .video-wrapper video, 
    .video-wrapper iframe, 
    .video-wrapper .plyr,
    .video-wrapper .plyr__video-wrapper { 
        width: 100% !important; 
        height: 100% !important; 
        background: transparent !important;
        position: absolute !important; 
        top: 0; left: 0;
    }
    
    /* Agar rasio video terjaga tapi mentok pinggir (tidak gepeng) */
    .video-wrapper video,
    .video-wrapper iframe,
    .plyr video {
        object-fit: contain !important;
    }

    /* List Animasi */
    .scroll-list-anim { list-style: none; padding: 0; margin: 0; animation: scrollUp 45s linear infinite; }
    .list-item { padding: 12px 15px; border-bottom: 1px solid rgba(255,255,255,0.1); }
    @keyframes scrollUp { 0% { transform: translateY(0); } 100% { transform: translateY(-50%); } }
    
    .full-img { width: 100%; height: 100%; object-fit: fill; }
    
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="app-container">

    <div class="app-header">
        <div class="d-flex align-items-center">
            <img style="margin-right: 20px; filter: drop-shadow(0 0 10px rgba(255,255,255,0.3));" 
                 src="<?= base_url('/' . ($logo == "" ? 'images/logo_kejaksaan.png' : $logo)); ?>" 
                 width="80" height="80" />
            <div class="text-white">
                <h1 class="fw-bold mb-0 text-uppercase" style="line-height: 1.1;"><?= $nama_instansi; ?></h1>
                <h5 class="fw-light mb-0 text-white-50"><?= $alamat; ?></h5>
            </div>
        </div>
    </div>

    <div class="app-content">
        
        <div class="col-kiri">
            <div class="card-custom" style="height: 100%;">
                <div class="card-custom-header bg-header-sidang">
                    <i class="mdi mdi-gavel me-2"></i> Sidang Hari Ini
                </div>
                <div class="card-custom-body body-dark">
                    <div v-if="loadingSidang" class="d-flex justify-content-center align-items-center h-100 text-white">
                        <i class="mdi mdi-loading mdi-spin me-2"></i> Memuat data...
                    </div>
                    <div v-if="!loadingSidang && dataSidang.length === 0" class="d-flex justify-content-center align-items-center h-100 text-white-50">
                        Tidak ada jadwal sidang hari ini.
                    </div>
                    
                    <div v-if="!loadingSidang && dataSidang.length > 0" style="height: 100%; overflow: hidden;">
                        <ul class="scroll-list-anim">
                            <li v-for="(row, index) in [...dataSidang, ...dataSidang]" :key="index" class="list-item text-white">
                                <div class="text-warning fw-bold" style="font-size:1.1rem; line-height:1.2; margin-bottom:2px;">
                                    {{ row.agenda_sidang }}
                                </div>
                                <div class="fw-bold text-white" style="font-size:1rem; margin-bottom:2px;">
                                    {{ row.nama_terdakwa }}
                                </div>
                                <div class="text-white-50 small" style="font-size:0.85rem;">
                                    {{ row.nomor_perkara }} | {{ row.jenis_perkara }}
                                </div>
                                <div class="small text-white-50 mt-1" style="font-size:0.8rem;">
                                    JPU: {{ row.jpu }} <span v-if="row.status_sidang">| STATUS: <span class="text-white">{{ row.status_sidang }}</span>
                                    <span v-if="row.status_sidang" class="ms-1">
                                        | STATUS: <span class="text-warning fw-bold">{{ row.status_sidang }}</span>
                                </span>
                                </div>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    <div class="col-tengah">
            
            <div class="card-custom box-video">
                <div class="card-custom-header bg-header-video">
                    <i class="mdi mdi-video me-2"></i> LIVE TV
                </div>
                
                <div class="card-custom-body bg-black d-flex align-items-center justify-content-center" style="position: relative; width: 100%; height: 100%;">
                    
                    <?php if ($video_youtube == 'no') { ?>
                        <video id="myplayer" style="width:100%; height:100%; object-fit: contain;" controls muted autoplay loop></video>
                    
                    <?php } else { 
                        $origin = rtrim(base_url(), '/'); 
                    ?>
                        <div style="width:100%; height:100%; position:absolute; top:0; left:0;">
                            <vue-plyr style="width:100%; height:100%;">
                                <div class="plyr__video-embed" style="width:100%; height:100%;">
                                    <iframe 
                                        src="https://www.youtube.com/embed/<?= $videoId; ?>?origin=<?= $origin; ?>&autoplay=1&loop=1&playlist=<?= $videoId; ?>&iv_load_policy=3&modestbranding=1&playsinline=1&showinfo=0&rel=0&enablejsapi=1" 
                                        allowfullscreen 
                                        allowtransparency 
                                        allow="autoplay"
                                        style="width:100%; height:100%; border:none;">
                                    </iframe>
                                </div>
                            </vue-plyr>
                        </div>
                    <?php } ?>

                </div>
            </div>

            <div class="card-custom box-sosmed">
                <div class="card-custom-body p-0">
                    <div class="sosmed-container">
                        <div class="sosmed-icons">
                            <div class="icon-box fb"><i class="mdi mdi-facebook"></i></div>
                            <div class="icon-box ig"><i class="mdi mdi-instagram"></i></div>
                            <div class="icon-box x"><i class="mdi mdi-twitter"></i></div>
                            <div class="icon-box yt"><i class="mdi mdi-youtube"></i></div>
                        </div>
                        <div class="sosmed-center">
                            <div class="title-follow">FOLLOW US</div>
                            <div class="title-instansi"><?= strtoupper($nama_instansi); ?></div>
                        </div>
                        <div class="sosmed-right">
                            <div class="link-badge">
                                <i class="mdi mdi-earth text-green"></i>
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
                    <i class="mdi mdi-information me-2"></i> Pengumuman
                </div>
                <div class="card-custom-body body-info-bg">
                    <div style="height: 100%; overflow: hidden;">
                         <ul v-if="dataInfo.length > 0" class="scroll-list-anim"> 
                            <li v-for="(item, i) in [...dataInfo, ...dataInfo]" :key="i" class="list-item text-white">
                                <small class="text-warning d-block mb-1">{{ item.tgl_news }}</small>
                                <span class="fw-bold" style="font-size: 1rem; line-height:1.2">{{ item.text_news }}</span>
                            </li>
                        </ul>
                        <div v-else class="d-flex justify-content-center align-items-center h-100 text-white-50">Belum ada pengumuman.</div>
                    </div>
                </div>
            </div>

            <div class="card-custom box-kinerja">
                <div class="card-custom-header bg-header-kinerja">
                    <i class="mdi mdi-chart-bar me-2"></i> Galeri Kegiatan
                </div>
                <div class="card-custom-body p-0">
                    <div id="carouselKinerja" class="carousel slide h-100" data-bs-ride="carousel" data-bs-interval="4000">
                        <div class="carousel-inner h-100">
                            <div v-for="(item, i) in dataGaleri" :key="i" :class="['carousel-item', 'h-100', { active: i==0 }]">
                                <img :src="'<?= base_url(); ?>' + '/' + item.image_url" class="full-img" alt="...">
                            </div>
                            <div v-if="dataGaleri.length === 0" class="carousel-item h-100 active">
                                <div class="d-flex justify-content-center align-items-center h-100 text-white-50 bg-dark">
                                    Belum ada foto galeri.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <div class="app-footer">
        <div class="jam-container">
            <div style="font-size: 0.9rem; opacity: 0.8; margin-bottom: 5px;">{{tanggal}}</div>
            <div style="font-size: 2.5rem; font-weight: bold; line-height: 1;">{{jam}}</div>
        </div>
        <div class="news-wrapper">
            <div class="marquee-content">
                <span v-if="dataNews.length > 0">
                    <span v-for="(item, i) in dataNews" :key="i" class="news-item">
                        {{ item.text_news }} &bull;
                    </span>
                    <span v-for="(item, i) in dataNews" :key="'d-'+i" class="news-item">
                        {{ item.text_news }} &bull;
                    </span>
                </span>
                <span v-else class="news-item">Selamat Datang di Sistem Informasi Digital Kejaksaan Negeri. Melayani dengan Sepenuh Hati. &bull;</span>
            </div>
        </div>
    </div>

</div>
<?= $this->endSection() ?>

<?= $this->section('js') ?>
<script>
    function addZeroBefore(n) { return (n < 10 ? '0' : '') + n; }

    // 1. EXTEND DATA GLOBAL
    Object.assign(window.dataVue, {
        tanggal: "", jam: "",
        dataNews: [], dataInfo: [], dataAgenda: [], dataVideo: [], dataGaleri: [],
        loadingSidang: false, dataSidang: []
    });

    // 2. EXTEND HOOKS
    window.createdVue = function() {
        this.getDate(); 
        this.getTime();
        setInterval(this.getDate, 1000); 
        setInterval(this.getTime, 1000);
        
        // Initial Fetch
        setTimeout(() => {
            this.getAllData();
        }, 500);
    }

    window.mountedVue = function() {
        // Auto Refresh Intervals
        setInterval(() => this.getNews(), <?= $news_refresh ?? 60; ?> * 1000);
        setInterval(() => this.getInfo(), <?= $news_refresh ?? 60; ?> * 1000);
        setInterval(() => this.getGaleri(), <?= $slide_refresh ?? 60; ?> * 1000);
        setInterval(() => this.getDataSidang(), 60000); // Sidang refresh tiap 1 menit
    }

    // 3. EXTEND METHODS
    Object.assign(window.methodsVue, {
        
        getAllData() {
            this.getVideo(); 
            this.getNews(); 
            this.getInfo(); 
            this.getGaleri(); 
            this.getDataSidang();
        },

        getDate() {
            const w = ["Minggu", "Senin", "Selasa", "Rabu", "Kamis", "Jumat", "Sabtu"];
            const t = new Date();
            this.tanggal = w[t.getDay()] + ', ' + addZeroBefore(t.getDate()) + '-' + (addZeroBefore(t.getMonth() + 1)) + '-' + t.getFullYear();
        },
        
        getTime() {
            const t = new Date();
            this.jam = addZeroBefore(t.getHours()) + ":" + addZeroBefore(t.getMinutes()) + ":" + addZeroBefore(t.getSeconds());
        },

        // API Calls
        getNews() { axios.get('<?= base_url() ?>/api/news/news').then(res => { if(res.data.status) this.dataNews = res.data.data; }).catch(e=>{}); },
        
        getInfo() { axios.get('<?= base_url() ?>/api/news/info').then(res => { if(res.data.status) this.dataInfo = res.data.data; }).catch(e=>{}); },
        
        getGaleri() { axios.get('<?= base_url() ?>/api/display/galeri').then(res => { if(res.data.status) this.dataGaleri = res.data.data; }).catch(e=>{}); },
        
        getDataSidang() {
            this.loadingSidang = true;
            axios.get('<?= base_url(); ?>/datasidang').then(res => {
                this.loadingSidang = false;
                if (res.data.status) { 
                    this.dataSidang = res.data.rows; 
                }
            }).catch(e => { this.loadingSidang = false; });
        },

        getVideo() {
            axios.get('<?= base_url(); ?>/api/display/video').then(res => {
                if (res.data.status && res.data.data) {
                    this.dataVideo = res.data.data;
                    <?php if ($video_youtube == 'no') : ?> 
                        this.$nextTick(() => { this.playVideo(); });
                    <?php endif; ?>
                }
            }).catch(e=>{});
        },

        playVideo() {
            const player = document.getElementById("myplayer");
            if(!player || !this.dataVideo.length) return;
            
            let i = 0;
            player.src = this.dataVideo[0];
            player.play().catch(e => console.log("Autoplay blocked"));
            
            player.onended = () => {
                i = (i + 1) % this.dataVideo.length;
                player.src = this.dataVideo[i];
                player.play();
            };
        }
    });
</script>
<?= $this->endSection() ?>