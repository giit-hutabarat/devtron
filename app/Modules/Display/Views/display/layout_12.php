<?php $this->section("style"); ?>
<style>
    #tanggal {
        font-size: 2vw;
        font-weight: bold;
        line-height: 1;
        border-bottom: 1px solid #aab7b8
    }

    #tanggal_arab {
        font-style: italic;
        font-weight: bold;
        font-family: serif;
        font-size: 1.8vw;
        line-height: 0.5;
    }


    #waktu {
        color: yellow;
    }

    /* text scroller */
    #news-container-full {
        position: absolute;
        top: 90vh;
        left: 0;
        width: 100%;
        height: 10vh;
        background: #000;
        z-index: 2;
        overflow: hidden;
        /*transform: translate3d(0, 0, 0);*/
    }

    #quotes {
        font-family: serif;
        color: yellow;
        text-shadow: 2px 2px #212121;
    }

    #info-agenda {
        position: absolute;
        bottom: 22vh;
        width: 100%;
        z-index: 3;
        overflow: hidden;
    }
</style>
<?php $this->endSection("style") ?>

<nav class="navbar navbar-dark bg-dark transparan mb-5">
    <div class="container-fluid">
        <!--tanggal dan jam-->
        <div class="text-center fw-bold">
            <p id="tanggal">{{tanggal}}</p>
            <p id="tanggal_arab">{{hijriah}}</p>
        </div>

        <a class="navbar-brand d-flex align-items-center my-2 my-lg-0 me-lg-auto text-decoration-none mx-auto" href="#">
            <img style="margin:0 auto;margin-right: 10px;" id="logo" class="img-responsive" src="<?php echo base_url('/' . ($logo == "" ? 'logo.png' : $logo)); ?>" width="80" height="80" />
            <span id="judul_1" class="h2 fw-bold"><?= $nama_instansi; ?><br />
                <span id="judul_2" class="h6"><?= $alamat; ?></span>
            </span>
        </a>

        <!--tanggal dan jam-->
        <div class="text-center fw-bold">
            <p id="waktu">{{jam}}</p>
        </div>

    </div>
</nav>

<div class="container-fluid mb-5">
    <div id="carousel" class="carousel slide" data-bs-ride="carousel">
        <!-- <div class="carousel-indicators">
                    <button type="button" data-bs-target="#carousel" v-for="(item, i ) in dataAgamaQuotes" :key="i" :data-bs-slide-to="i" :class="{ active: i==0 }" aria-current="true" :aria-label="'Slide' + i"></button>
                </div> -->
        <div class="carousel-inner">
            <div class="carousel-item" v-for="(item, i ) in dataAgamaQuotes" :key="i" :class="{ active: i==0 }">
                <div class="text-center">
                    <h1 id="quotes" class="display-4 fw-bold">"{{ item.isi_quotes }}". ({{ item.suratriwayat }})</h1>
                </div>
            </div>
        </div>
        <button class="carousel-control-prev" type="button" data-bs-target="#carousel" data-bs-slide="prev">
            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
            <span class="visually-hidden">Previous</span>
        </button>
        <button class="carousel-control-next" type="button" data-bs-target="#carousel" data-bs-slide="next">
            <span class="carousel-control-next-icon" aria-hidden="true"></span>
            <span class="visually-hidden">Next</span>
        </button>
    </div>
</div>

<div id="info-agenda" class="container-fluid mb-5">
    <div class="row">
        <div class="col-sm-6">
            <div class="card transparan text-white border-0 mb-4 h-100">
                <div class="card-header h5">
                    <i class="mdi mdi-mosque"></i> Info Masjid
                </div>
                <div class="card-body">
                    <div v-if="dataInfo != ''">
                        <ul class="list-unstyled">
                            <li v-for="item in dataInfo" :key="item.id">
                                {{ item.tgl_news }}
                                <h6 class="fw-bold">{{ item.text_news }}</h6>
                                <hr />
                            </li>
                        </ul>
                    </div>
                    <div v-else>
                        <h5 class="fw-normal"><i class="mdi mdi-information"></i> Belum Ada Info Masjid</h5>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-sm-6">
            <div class="card transparan text-white border-0 mb-4 h-100">
                <div class="card-header h5">
                    <i class="mdi mdi-calendar"></i> Agenda Masjid
                </div>
                <div class="card-body">
                    <div v-if="dataAgenda != ''">
                        <ul class="list-unstyled">
                            <li v-for="item in dataAgenda" :key="item.id">
                                <h6 class="fw-bold">{{ item.nama_agenda }}, {{ item.tgl_agenda }}</h6>
                                {{ item.tempat_agenda }}, {{ item.waktu }} - Selesai
                                <hr />
                            </li>
                        </ul>
                    </div>
                    <div v-else>
                        <h5 class="fw-normal"><i class="mdi mdi-information"></i> Belum Ada Agenda Masjid</h5>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div id="waktu-sholat" class="mt-5">
    <div class="card card-body bg-dark text-white transparan py-0">
        <span><i class="fa fa-info-circle"></i> Waktu sholat:
            <?php if ($jadwal_sholat == 'excel') { ?>
                Import Excel
            <?php } else { ?>
                API api.myquran.com
            <?php } ?>
        </span>
    </div>
    <div class="row g-0">
        <div class="col-lg-2 col-md-2 col-sm-2 col-xs-2">
            <div class="card transparan border-0">
                <div class="card-body bg-blue-grey text-center">
                    <h2 class="nama-solat">Imsak</h2>
                    <span class="waktu-solat" id="imsak">{{ dataJadwalsholat.imsak }}</span>
                </div>
            </div>
        </div>
        <div class="col-lg-2 col-md-2 col-sm-2 col-xs-2">
            <div class="card transparan border-0">
                <div class="card-body bg-red text-center">
                    <h2 class="nama-solat">Subuh</h2>
                    <span class="waktu-solat" id="subuh">{{ dataJadwalsholat.subuh }}</span>
                </div>
            </div>
        </div>
        <div class="col-lg-2 col-md-2 col-sm-2 col-xs-2">
            <div class="card transparan border-0">
                <div class="card-body bg-cyan text-center">
                    <h2 class="nama-solat">Dzuhur</h2>
                    <span class="waktu-solat" id="dzuhur">{{ dataJadwalsholat.dzuhur }}</span>
                </div>
            </div>
        </div>
        <div class="col-lg-2 col-md-2 col-sm-2 col-xs-2">
            <div class="card transparan border-0">
                <div class="card-body bg-green text-center">
                    <h2 class="nama-solat">Ashar</h2>
                    <span class="waktu-solat" id="ashar">{{ dataJadwalsholat.ashar }}</span>
                </div>
            </div>
        </div>
        <div class="col-lg-2 col-md-2 col-sm-2 col-xs-2">
            <div class="card transparan border-0">
                <div class="card-body bg-orange text-center">
                    <h2 class="nama-solat">Maghrib</h2>
                    <span class="waktu-solat" id="maghrib">{{ dataJadwalsholat.maghrib }}</span>
                </div>
            </div>
        </div>
        <div class="col-lg-2 col-md-2 col-sm-2 col-xs-2">
            <div class="card transparan border-0">
                <div class="card-body bg-pink text-center">
                    <h2 class="nama-solat">Isya</h2>
                    <span class="waktu-solat" id="isya">{{ dataJadwalsholat.isya }}</span>
                </div>
            </div>
        </div>
    </div>
</div>

<!--teks berjalan-->
<div id="news-container-full">
    <div class="position-absolute top-50 start-50 translate-middle w-100">
        <ul class="marquee news-text">
            <li v-for="item in dataNews" :key="item.id" style="display: inline;">
                {{ item.text_news }} &bull;
            </li>
        </ul>
    </div>
</div>

<div class="position-fixed bottom-0 end-0 p-3" style="bottom: 23% !important;z-index: 11">
    <div id="liveToast" class="toast align-items-center text-dark border-0" :class="toast" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="toast-header bg-warning text-dark">
            <strong class="me-auto">Menuju Waktu Sholat</strong>
            <small></small>
            <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
        <div class="toast-body h3">
            {{jelangSholat}}
        </div>
    </div>
</div>

<?php $this->section("modal") ?>

<?php $this->endSection("modal") ?>

<?php $this->section("js") ?>
<script src="<?= base_url('assets/js/hijricalendar-islam.js') ?>" type="text/javascript"></script>
<script>
    //var myModal = new bootstrap.Modal(document.getElementById('exampleModal'));
    function addZeroBefore(n) {
        return (n < 10 ? '0' : '') + n;
    }

    dataVue = {
        ...dataVue,
        tanggal: "",
        jam: "",
        dataNews: [],
        dataInfo: [],
        dataAgenda: [],
        dataAgamaQuotes: [],
        dataJadwalsholat: [],
        hijriah: "",
        waktuSholat: [],
        jelangSholat: "",
        toast: "hide",
    }

    createdVue = function() {
        setInterval(this.getDate, 1000);
        setInterval(this.getTime, 1000);
        setInterval(this.cekWaktuSholat, 1000);

        this.getNews();
        this.getInfo();
        this.getAgamaQuotes();
        this.getAgenda();
        this.getJadwalsholat();
        this.cekWaktuSholat();
    }

    mountedVue = function() {
        setInterval(() => this.getNews(), <?= $news_refresh; ?> * 1000);
        setInterval(() => this.getInfo(), <?= $news_refresh; ?> * 1000);
        setInterval(() => this.getAgenda(), <?= $agenda_refresh; ?> * 1000);
        setInterval(() => this.getAgamaQuotes(), <?= $news_refresh; ?> * 1000);
    }

    methodsVue = {
        ...methodsVue,
        getDate: function() {
            const weekday = ["Minggu", "Senin", "Selasa", "Rabu", "Kamis", "Jumat", "Sabtu"];
            const today = new Date();
            const date = addZeroBefore(today.getDate()) + '-' + (addZeroBefore(today.getMonth() + 1)) + '-' + today.getFullYear();
            let Hari = weekday[today.getDay()];
            const Tanggal = date;
            this.tanggal = Hari + ', ' + Tanggal;
            this.hijriah = writeIslamicDate();
        },

        getTime: function() {
            const today = new Date();
            const time = addZeroBefore(today.getHours()) + ":" + addZeroBefore(today.getMinutes()) + ":" + addZeroBefore(today.getSeconds());
            const Jam = time;
            this.jam = Jam;
        },

        // Get News
        getNews: function() {
            this.loading = true;
            axios.get('<?= base_url() ?>/api/news/news')
                .then(res => {
                    // handle success
                    this.loading = false;
                    var data = res.data;
                    if (data.status == true) {
                        this.snackbar = true;
                        this.snackbarMessage = data.message;
                        this.dataNews = data.data;
                        //myModal.show();
                    } else {
                        this.snackbar = true;
                        this.snackbarMessage = data.message;
                    }
                })
                .catch(err => {
                    // handle error
                    console.log(err);
                })
        },

        //Get Info
        getInfo: function() {
            this.loading = true;
            axios.get('<?= base_url() ?>/api/news/masjid')
                .then(res => {
                    // handle success
                    this.loading = false;
                    var data = res.data;
                    if (data.status == true) {
                        this.snackbar = true;
                        this.snackbarMessage = data.message;
                        this.dataInfo = data.data;
                        //myModal.show();
                    } else {
                        this.snackbar = true;
                        this.snackbarMessage = data.message;
                    }
                })
                .catch(err => {
                    // handle error
                    console.log(err);
                })
        },

        //Get Quotes
        getAgamaQuotes: function() {
            this.loading = true;
            axios.get('<?= base_url() ?>/api/display/agamaquotes')
                .then(res => {
                    // handle success
                    this.loading = false;
                    var data = res.data;
                    if (data.status == true) {
                        this.snackbar = true;
                        this.snackbarMessage = data.message;
                        this.dataAgamaQuotes = data.data;
                    } else {
                        this.snackbar = true;
                        this.snackbarMessage = data.message;
                    }
                })
                .catch(err => {
                    // handle error
                    console.log(err);
                })
        },

        //Get Agenda
        getAgenda: function() {
            this.loading = true;
            axios.get('<?= base_url() ?>/api/display/agenda')
                .then(res => {
                    // handle success
                    this.loading = false;
                    var data = res.data;
                    if (data.status == true) {
                        this.snackbar = true;
                        this.snackbarMessage = data.message;
                        this.dataAgenda = data.data;
                    } else {
                        this.snackbar = true;
                        this.snackbarMessage = data.message;
                    }
                })
                .catch(err => {
                    // handle error
                    console.log(err);
                })
        },

        //Get Jadwal Sholat
        getJadwalsholat: function() {
            this.loading = true;
            axios.get('<?= base_url() ?>/api/display/jadwalsholat')
                .then(res => {
                    // handle success
                    this.loading = false;
                    var data = res.data;
                    if (data.status == true) {
                        this.snackbar = true;
                        this.snackbarMessage = data.message;
                        this.dataJadwalsholat = data.data;
                        console.log(this.dataJadwalsholat);
                        //myModal.show();
                    } else {
                        this.snackbar = true;
                        this.snackbarMessage = data.message;
                    }
                })
                .catch(err => {
                    // handle error
                    console.log(err);
                })
        },

        //Cek waktu sholat
        cekWaktuSholat: function() {
            this.loading = true;
            axios.get('<?= base_url() ?>/api/display/cekwaktusolat')
                .then(res => {
                    // handle success
                    this.loading = false;
                    var data = res.data;
                    if (data.status == true) {
                        this.snackbar = true;
                        this.snackbarMessage = data.message;
                        this.waktuSholat = data.data;
                        if (this.waktuSholat != "") {
                            var now = new Date().getTime();
                            var countDownDate = new Date(this.waktuSholat.waktu).getTime();
                            var distance = countDownDate - now;
                            var days = Math.floor(distance / (1000 * 60 * 60 * 24));
                            var hours = addZeroBefore(Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60)));
                            var minutes = addZeroBefore(Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60)));
                            var seconds = addZeroBefore(Math.floor((distance % (1000 * 60)) / 1000));

                            this.jelangSholat = this.waktuSholat.jelang + ' ' + hours + ":" + minutes + ":" + seconds;
                            this.toast = "show";
                        } else {
                            this.toast = "hide";
                        }
                        //console.log(this.jelangSholat);
                        //myModal.show();
                    } else {
                        this.snackbar = true;
                        this.snackbarMessage = data.message;
                    }
                })
                .catch(err => {
                    // handle error
                    console.log(err);
                })
        },
    }
</script>
<?php $this->endSection("js") ?>