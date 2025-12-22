<?= $this->extend('layouts/display') ?>

<?= $this->section('content') ?>
<v-container fluid class="fill-height pa-0 d-flex flex-column" style="height: 100vh;">

    <v-row no-gutters class="glass-header px-4 py-2 shrink">
        <v-col cols="6" class="d-flex align-center">
            <img src="<?= base_url('images/logo_kejaksaan.png') ?>" height="50" class="mr-3 filter-shadow">
            <div>
                <h2 class="text-h5 font-weight-bold white--text mb-0 text-shadow">KEJAKSAAN NEGERI</h2>
                <div class="subtitle-2 blue--text text--lighten-3 text-shadow">SISTEM INFORMASI DIGITAL</div>
            </div>
        </v-col>
        <v-col cols="6" class="d-flex align-center justify-end">
            <div class="text-right">
                <h1 class="text-h4 font-weight-bold white--text mb-0 clock-time">{{ currentTime }}</h1>
                <div class="subtitle-2 grey--text text--lighten-2">{{ currentDate }}</div>
            </div>
        </v-col>
    </v-row>

    <v-row no-gutters class="flex-grow-1 pa-4 overflow-hidden">
        
        <v-col cols="12" md="8" class="pr-md-4 h-100">
            <v-card class="rounded-xl elevation-10 h-100 d-flex align-center justify-center glass-panel overflow-hidden">
                <vue-plyr v-if="videoData.length > 0" :options="{ autoplay: true, loop: { active: true } }">
                    <video controls crossorigin playsinline>
                        <source :src="currentVideoUrl" type="video/mp4" />
                    </video>
                </vue-plyr>
                
                <v-carousel v-else cycle hide-delimiters show-arrows-on-hover height="100%" class="rounded-xl">
                    <v-carousel-item src="<?= base_url('images/bg-default.jpg') ?>">
                        <v-row class="fill-height" align="center" justify="center">
                            <div class="text-h4 white--text font-weight-bold">SELAMAT DATANG</div>
                        </v-row>
                    </v-carousel-item>
                </v-carousel>
            </v-card>
        </v-col>

        <v-col cols="12" md="4" class="d-flex flex-column h-100">
            
            <v-card class="rounded-xl glass-panel mb-4 flex-grow-1 elevation-5 d-flex flex-column">
                <v-card-title class="py-2 px-4 teal darken-3 white--text">
                    <v-icon left small color="white">mdi-calendar-clock</v-icon>
                    <span class="text-subtitle-1 font-weight-bold">AGENDA HARI INI</span>
                </v-card-title>
                
                <v-card-text class="pa-0 overflow-hidden flex-grow-1 position-relative">
                    <div class="scrolling-content pa-3">
                        <v-alert v-for="(item, i) in agendaData" :key="i" border="left" colored-border color="teal accent-3" elevation="2" class="mb-2 glass-item">
                            <div class="font-weight-bold white--text text-body-2">{{ item.judul }}</div>
                            <div class="caption grey--text text--lighten-1">
                                <v-icon x-small color="grey">mdi-clock</v-icon> {{ item.waktu }} | {{ item.lokasi }}
                            </div>
                        </v-alert>
                        
                        <div v-if="agendaData.length === 0" class="text-center mt-10 grey--text">
                            Tidak ada agenda hari ini.
                        </div>
                    </div>
                </v-card-text>
            </v-card>

            <v-card class="rounded-xl glass-panel flex-grow-1 elevation-5 d-flex flex-column">
                <v-card-title class="py-2 px-4 indigo darken-3 white--text">
                    <v-icon left small color="white">mdi-newspaper</v-icon>
                    <span class="text-subtitle-1 font-weight-bold">INFORMASI TERKINI</span>
                </v-card-title>
                
                <v-carousel cycle vertical hide-delimiters :show-arrows="false" height="100%" interval="5000">
                    <v-carousel-item v-for="(news, n) in newsData" :key="n">
                        <v-sheet color="transparent" height="100%" class="pa-4 d-flex align-center">
                            <div>
                                <div class="text-h6 font-weight-bold white--text mb-2 line-clamp-2">{{ news.judul }}</div>
                                <div class="caption grey--text text--lighten-2 line-clamp-3">{{ news.isi }}</div>
                            </div>
                        </v-sheet>
                    </v-carousel-item>
                    <v-carousel-item v-if="newsData.length === 0">
                        <v-sheet color="transparent" height="100%" class="d-flex align-center justify-center">
                            <span class="grey--text">Belum ada informasi.</span>
                        </v-sheet>
                    </v-carousel-item>
                </v-carousel>
            </v-card>

        </v-col>
    </v-row>

    <v-footer app padless color="transparent" class="shrink">
        <v-card flat tile width="100%" class="glass-footer white--text">
            <v-card-text class="pa-2 d-flex align-center">
                <v-chip label color="red darken-3" class="mr-3 font-weight-bold elevation-4">INFO PENTING</v-chip>
                <marquee scrollamount="6" class="font-weight-bold text-uppercase text-h6" style="letter-spacing: 1px;">
                    {{ runningText || 'Selamat Datang di Sistem Informasi Kejaksaan Negeri. Melayani dengan Sepenuh Hati.' }}
                </marquee>
            </v-card-text>
        </v-card>
    </v-footer>

</v-container>
<?= $this->endSection() ?>

<?= $this->section('style') ?>
<style>
    /* Glassmorphism Classes */
    .glass-header { background: rgba(0, 0, 0, 0.6); backdrop-filter: blur(10px); border-bottom: 1px solid rgba(255,255,255,0.1); }
    .glass-panel { background: rgba(30, 30, 30, 0.6) !important; backdrop-filter: blur(15px); border: 1px solid rgba(255,255,255,0.1); }
    .glass-item { background: rgba(255, 255, 255, 0.05) !important; }
    .glass-footer { background: linear-gradient(90deg, #1a1a1a 0%, #0d47a1 100%) !important; border-top: 2px solid #FFC107; }
    
    /* Text Shadow & Utility */
    .text-shadow { text-shadow: 2px 2px 4px rgba(0,0,0,0.8); }
    .filter-shadow { filter: drop-shadow(0 0 5px rgba(255,255,255,0.5)); }
    .line-clamp-2 { display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }
    .line-clamp-3 { display: -webkit-box; -webkit-line-clamp: 3; -webkit-box-orient: vertical; overflow: hidden; }
    .clock-time { font-family: 'Courier New', monospace; letter-spacing: 2px; text-shadow: 0 0 10px rgba(0, 255, 255, 0.5); }
    
    /* Animation Auto Scroll */
    .scrolling-content { height: 100%; overflow: hidden; position: relative; }
</style>
<?= $this->endSection() ?>

<?= $this->section('js') ?>
<script>
    // Extend Vue Data di display-core.js
    Object.assign(app.$data, {
        currentTime: '',
        currentDate: '',
        runningText: '',
        currentVideoUrl: '', // URL Video mp4
        
        // Dummy Data (Nanti diganti API)
        agendaData: [
            { judul: 'Rapat Koordinasi Pidum', waktu: '08:00 WIB', lokasi: 'Ruang Aula' },
            { judul: 'Sidang Perkara No. 123/Pid.B', waktu: '10:00 WIB', lokasi: 'PN Boyolali' },
            { judul: 'Penyuluhan Hukum', waktu: '13:00 WIB', lokasi: 'Kecamatan Musuk' }
        ],
        newsData: [
            { judul: 'Kejaksaan Raih Predikat WBK', isi: 'Kejaksaan Negeri Boyolali berhasil meraih predikat Wilayah Bebas dari Korupsi tahun 2025.' },
            { judul: 'Jadwal Layanan Tilang', isi: 'Layanan pengambilan tilang buka setiap hari kerja pukul 08.00 - 15.00 WIB.' }
        ]
    });

    // Tambahkan Method Clock
    app.$options.methods.startClock = function() {
        setInterval(() => {
            const now = new Date();
            this.currentTime = now.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit', second: '2-digit' }).replace(/\./g, ':');
            this.currentDate = now.toLocaleDateString('id-ID', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' });
        }, 1000);
    };

    // Jalankan Clock saat mounted
    const originalMounted = app.$options.mounted;
    app.$options.mounted = function() {
        if (originalMounted) originalMounted.call(this);
        this.startClock();
        
        // Contoh set video (jika ada file)
        // this.currentVideoUrl = DisplayConfig.baseUrl + '/uploads/video/profil.mp4';
    };
</script>
<?= $this->endSection() ?>