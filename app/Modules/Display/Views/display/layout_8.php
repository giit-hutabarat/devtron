<?= $this->extend('layouts/display') ?>

<?= $this->section('style') ?>
<style>
    /* Styling Spesifik Layout 8 */
    #temperature { color: yellow !important; font-weight: bold; }
    #tanggal { color: white; font-size: 1.2rem; }
    #waktu { color: #0AA0B3; font-size: 1.2rem; margin-left: 10px; }
    
    /* Footer Container (Fixed Bottom) */
    #tanggal-jam { 
        position: absolute; top: 90vh; width: 20%; height: 10vh; 
        padding: 5px; background: #111; z-index: 3; overflow: hidden; 
        border-top: 2px solid #0AA0B3;
        display: flex; flex-direction: column; align-items: center; justify-content: center;
    }

    #news-container { 
        position: absolute; top: 90vh; left: 20%; width: 80%; height: 10vh; 
        background: #222; z-index: 2; overflow: hidden; 
        border-top: 2px solid #FFC107;
        display: flex; align-items: center;
    }
    
    /* Utility */
    .transparan { background: rgba(0,0,0,0.5) !important; backdrop-filter: blur(5px); }
    .bg-cyan { background-color: rgba(0, 188, 212, 0.2) !important; }
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<nav class="navbar navbar-dark bg-cyan mb-4 pt-3 px-4 shadow-sm">
    <div class="container-fluid">
        <a class="navbar-brand d-flex align-items-center" href="#">
            <img style="margin-right: 15px; filter: drop-shadow(0 0 5px rgba(255,255,255,0.5));" 
                 id="logo" 
                 src="<?= base_url('/' . ($logo == "" ? 'logo.png' : $logo)); ?>" 
                 width="70" height="70" />
            <div class="text-white">
                <h2 class="fw-bold mb-0 text-uppercase" style="letter-spacing: 1px; font-size: 1.8rem;"><?= $nama_instansi; ?></h2>
                <h5 class="fw-light mb-0 text-white-50"><?= $alamat; ?></h5>
            </div>
        </a>

        <div class="text-end text-white d-flex align-items-center">
            <div class="me-3">
                <h6 class="mb-0 text-uppercase fw-bold">{{ dataCuaca.name }}, {{ dataCuaca_sys.country }}</h6>
                <div v-for="item in dataCuaca.weather" :key="item.id">
                    <span class="text-info">{{ item.main }}</span>
                    <img :src="'http://openweathermap.org/img/wn/' + item.icon + '.png'" height="40" style="vertical-align: middle;">
                </div>
            </div>
            <div class="display-4" id="temperature">{{ bulatkan(dataCuaca_main.temp_max) }}&deg;C</div>
        </div>
    </div>
</nav>

<div class="container-fluid px-4">
    <div class="row g-4">
        
        <div class="col-sm-3">
            <div class="card bg-success text-white border-0 h-100 shadow">
                <div class="card-header fw-bold bg-success border-bottom border-success-subtle">
                    <i class="mdi mdi-information-outline me-2"></i> INFORMASI
                </div>
                <div class="card-body p-0">
                    <div style="height: 60vh; overflow: hidden; position: relative;">
                        <ul class="list-group list-group-flush bg-transparent">
                            <li v-for="(item, i) in dataInfo" :key="i" class="list-group-item bg-transparent text-white border-bottom border-white-50">
                                <small class="text-warning"><i class="mdi mdi-clock-outline"></i> {{ item.tgl_news }}</small>
                                <h6 class="fw-bold mt-1 mb-0">{{ item.text_news }}</h6>
                            </li>
                            <li v-if="dataInfo.length == 0" class="list-group-item bg-transparent text-center py-4 text-white-50">Belum ada informasi.</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-sm-3">
            <div class="card bg-dark text-white transparan border-0 h-100 shadow">
                <div class="card-header fw-bold bg-primary border-bottom border-primary-subtle">
                    <i class="mdi mdi-calendar-clock me-2"></i> AGENDA
                </div>
                <div class="card-body p-0">
                    <ul class="list-group list-group-flush bg-transparent">
                        <li v-for="(item, i) in dataAgenda" :key="i" class="list-group-item bg-transparent text-white border-bottom border-secondary">
                            <h6 class="fw-bold text-info">{{ item.nama_agenda }}</h6>
                            <div class="small text-white-50 mb-1">
                                <i class="mdi mdi-calendar-blank"></i> {{ item.tgl_agenda }}
                            </div>
                            <div class="d-flex justify-content-between small text-white-50">
                                <span><i class="mdi mdi-map-marker"></i> {{ item.tempat_agenda }}</span>
                                <span><i class="mdi mdi-clock"></i> {{ item.waktu }}</span>
                            </div>
                        </li>
                        <li v-if="dataAgenda.length == 0" class="list-group-item bg-transparent text-center py-4 text-muted">Tidak ada agenda.</li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="col-sm-6">
            <div class="card bg-black border-0 shadow h-100" style="border-radius: 12px; overflow: hidden;">
                <div class="card-header fw-bold bg-secondary text-white">
                    <i class="mdi mdi-video me-2"></i> VIDEO
                </div>
                <div class="ratio ratio-16x9 h-100 bg-black d-flex align-items-center justify-content-center">
                    <?php if ($video_youtube == 'no') { ?>
                        <video id="myplayer" class="w-100 h-100" style="object-fit: contain;" controls <?= $video_muted; ?>></video>
                    <?php } else { ?>
                        <vue-plyr>
                            <div class="plyr__video-embed" id="player">
                                <iframe src="https://www.youtube.com/embed/<?= $videoId; ?>?origin=<?= base_url(); ?>&autoplay=1&loop=1&iv_load_policy=3&modestbranding=1&playsinline=1&showinfo=0&rel=0&enablejsapi=1" allowfullscreen allowtransparency allow="autoplay"></iframe>
                            </div>
                        </vue-plyr>
                    <?php } ?>
                </div>
            </div>
        </div>
    </div>

    <div class="mt-4 pb-5">
        <div class="row g-2 text-center">
            <div class="col"><div class="p-2 rounded bg-secondary text-white fw-bold bg-opacity-75 shadow-sm">IMSAK <br><span class="h4 text-warning">{{ dataJadwalsholat.imsak }}</span></div></div>
            <div class="col"><div class="p-2 rounded bg-danger text-white fw-bold bg-opacity-75 shadow-sm">SUBUH <br><span class="h4 text-warning">{{ dataJadwalsholat.subuh }}</span></div></div>
            <div class="col"><div class="p-2 rounded bg-info text-white fw-bold bg-opacity-75 shadow-sm">DZUHUR <br><span class="h4 text-warning">{{ dataJadwalsholat.dzuhur }}</span></div></div>
            <div class="col"><div class="p-2 rounded bg-success text-white fw-bold bg-opacity-75 shadow-sm">ASHAR <br><span class="h4 text-warning">{{ dataJadwalsholat.ashar }}</span></div></div>
            <div class="col"><div class="p-2 rounded bg-warning text-dark fw-bold bg-opacity-75 shadow-sm">MAGHRIB <br><span class="h4 text-danger">{{ dataJadwalsholat.maghrib }}</span></div></div>
            <div class="col"><div class="p-2 rounded bg-primary text-white fw-bold bg-opacity-75 shadow-sm">ISYA <br><span class="h4 text-warning">{{ dataJadwalsholat.isya }}</span></div></div>
        </div>
    </div>
</div>

<div id="tanggal-jam">
    <div id="tanggal" class="fw-bold">{{tanggal}}</div>
    <div id="waktu" class="fw-bold text-info">{{jam}}</div>
</div>

<div id="news-container">
    <marquee class="h3 mb-0 text-white fw-bold" scrollamount="8" style="line-height: 10vh;">
        <span v-if="dataNews.length > 0">
            <span v-for="(item, i) in dataNews" :key="i" class="mx-5">
                <i class="mdi mdi-newspaper text-warning"></i> {{ item.text_news }}
            </span>
        </span>
        <span v-else>Selamat Datang di <?= $nama_instansi ?>. Melayani dengan sepenuh hati.</span>
    </marquee>
</div>
<?= $this->endSection() ?>

<?= $this->section('js') ?>
<script>
    function addZeroBefore(n) { return (n < 10 ? '0' : '') + n; }

    // 1. EXTEND DATA GLOBAL (Gunakan Object.assign agar aman)
    Object.assign(window.dataVue, {
        tanggal: "", jam: "",
        dataNews: [], dataInfo: [], dataAgenda: [], dataVideo: [],
        dataJadwalsholat: { imsak:'-', subuh:'-', dzuhur:'-', ashar:'-', maghrib:'-', isya:'-' }, 
        dataCuaca: { name: '-', weather: [] },
        dataCuaca_main: { temp_max: 0 }, 
        dataCuaca_sys: { country: '' }
    });

    // 2. EXTEND LIFECYCLE HOOKS
    window.createdVue = function() {
        setInterval(this.getDate, 1000);
        setInterval(this.getTime, 1000);
        
        // Load Data Pertama Kali
        this.getVideo();
        this.getNews();
        this.getInfo();
        this.getAgenda();
        this.getJadwalsholat();
        this.getCuaca();
    };

    window.mountedVue = function() {
        // Auto Refresh Data
        const refreshNews   = <?= $news_refresh ?? 60; ?> * 1000;
        const refreshAgenda = <?= $agenda_refresh ?? 60; ?> * 1000;

        setInterval(() => this.getNews(), refreshNews);
        setInterval(() => this.getInfo(), refreshNews);
        setInterval(() => this.getAgenda(), refreshAgenda);
    };

    // 3. EXTEND METHODS
    Object.assign(window.methodsVue, {
        // HELPER PENTING: Untuk menghindari error Math.ceil di template HTML
        bulatkan: function(val) {
            return Math.ceil(val || 0);
        },

        getDate: function() {
            const weekday = ["Minggu", "Senin", "Selasa", "Rabu", "Kamis", "Jumat", "Sabtu"];
            const today = new Date();
            this.tanggal = weekday[today.getDay()] + ', ' + addZeroBefore(today.getDate()) + '-' + (addZeroBefore(today.getMonth() + 1)) + '-' + today.getFullYear();
        },
        
        getTime: function() {
            const today = new Date();
            this.jam = addZeroBefore(today.getHours()) + ":" + addZeroBefore(today.getMinutes()) + ":" + addZeroBefore(today.getSeconds());
        },

        // --- API CALLS ---
        getNews: function() {
            axios.get('<?= base_url() ?>/api/news/news').then(res => {
                if (res.data.status == true) this.dataNews = res.data.data;
            });
        },
        getInfo: function() {
            axios.get('<?= base_url() ?>/api/news/info').then(res => {
                if (res.data.status == true) this.dataInfo = res.data.data;
            });
        },
        getAgenda: function() {
            axios.get('<?= base_url() ?>/api/display/agenda').then(res => {
                if (res.data.status == true) this.dataAgenda = res.data.data;
            });
        },
        getJadwalsholat: function() {
            axios.get('<?= base_url() ?>/api/display/jadwalsholat').then(res => {
                if (res.data.status == true) this.dataJadwalsholat = res.data.data;
            });
        },
        getCuaca: function() {
            axios.get('<?= base_url() ?>/api/display/cuaca').then(res => {
                if (res.data.status == true) {
                    this.dataCuaca = res.data.data;
                    this.dataCuaca_main = this.dataCuaca.main || {};
                    this.dataCuaca_sys  = this.dataCuaca.sys || {};
                    this.dataCuaca_weather = this.dataCuaca.weather || [];
                }
            });
        },
        getVideo: function() {
            axios.get('<?= base_url(); ?>/api/display/video').then(res => {
                if (res.data.status == true) {
                    this.dataVideo = res.data.data;
                    <?php if ($video_youtube == 'no') : ?> 
                        // Tunggu DOM render sebelum play video
                        this.$nextTick(() => { this.playVideo(); });
                    <?php endif; ?>
                }
            });
        },
        playVideo: function() {
            var player = document.getElementById("myplayer");
            // Cek apakah player dan data video ada
            if(!player || !this.dataVideo || this.dataVideo.length === 0) return;
            
            var i = 0;
            var videoSource = this.dataVideo;
            
            // Set source awal
            player.src = videoSource[0];
            player.play().catch(e => console.log("Autoplay blocked:", e)); // Tangkap error autoplay
            
            // Loop video playlist
            player.onended = () => {
                i++;
                if (i >= videoSource.length) i = 0;
                player.src = videoSource[i];
                player.play();
            };
        }
    });
</script>
<?= $this->endSection() ?>