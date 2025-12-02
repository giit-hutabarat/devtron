<?php $this->section("style"); ?>
<style>
	#tanggal {
		color: white;
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
</style>
<?php $this->endSection("style") ?>

<nav class="navbar navbar-dark bg-dark transparan mb-5">
	<div class="container-fluid">
		<a class="navbar-brand d-flex align-items-center my-2 my-lg-0 me-lg-auto text-decoration-none" href="#">
			<img style="margin:0 auto;margin-right: 10px;" id="logo" class="img-responsive" src="<?php echo base_url('/' . ($logo == "" ? 'logo.png' : $logo)); ?>" width="80" height="80" />
			<span id="judul_1" class="h1 fw-bold"><?= $nama_instansi; ?><br />
				<span id="judul_2" class="h5"><?= $alamat; ?></span>
			</span>
		</a>

		<!--tanggal dan jam-->
		<div class="text-center fw-bold">
			<p id="tanggal">{{tanggal}}</p>
			<p id="waktu">{{jam}}</p>
		</div>

	</div>
</nav>

<div class="container-fluid">

	<div class="row">
		<div class="col-sm-8">
			<div class="card bg-dark text-white transparan border-0">
				<div class="card-header h5">
					<i class="mdi mdi-video"></i> Video
				</div>
				<?php if ($video_youtube == 'no') { ?>
					<!-- mp4 -->
					<video id="myplayer" class="ratio ratio-16x9" controls <?= $video_muted; ?>>

					</video>
				<?php } else { ?>
					<!-- youtube -->
					<vue-plyr>
						<div class="plyr__video-embed" id="player">
							<iframe src="https://www.youtube.com/embed/<?= $videoId; ?>?origin=<?= base_url(); ?>&amp;autoplay=1&amp;loop=1&amp;iv_load_policy=3&amp;modestbranding=1&amp;playsinline=1&amp;showinfo=0&amp;rel=0&amp;enablejsapi=1" allowfullscreen allowtransparency allow="autoplay"></iframe>
						</div>
					</vue-plyr>
				<?php } ?>
			</div>
		</div>

		<div class="col-sm-4">
			<div class="card bg-success text-white border-0 mb-3">
				<div class="card-header h5">
					<i class="mdi mdi-information"></i> Informasi
				</div>
				<div class="card-body">
					<ul class="list-unstyled">
						<li v-for="item in dataInfo" :key="item.id">
							{{ item.tgl_news }}
							<h6 class="fw-bold">{{ item.text_news }}</h6>
							<hr />
						</li>
					</ul>
				</div>
			</div>
			<div class="card bg-warning text-dark border-0 mb-3">
				<div class="card-header h5">
					<i class="mdi mdi-calendar"></i> Agenda
				</div>
				<div class="card-body bg-white text-dark">
					<ul class="list-unstyled">
						<li v-for="item in dataAgenda" :key="item.id">
							<h6 class="fw-bold">{{ item.nama_agenda }}, {{ item.tgl_agenda }}</h6>
							{{ item.tempat_agenda }}, {{ item.waktu }} - Selesai
							<hr />
						</li>
					</ul>
				</div>
			</div>
		</div>
	</div>

	<div class="mt-3">
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

<?php $this->section("modal") ?>

<?php $this->endSection("modal") ?>

<?php $this->section("js") ?>
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
		dataVideo: [],
		dataJadwalsholat: [],
	}

	createdVue = function() {
		setInterval(this.getDate, 1000);
		setInterval(this.getTime, 1000);
		this.getVideo();
		this.getNews();
		this.getInfo();
		this.getAgenda();
		this.getJadwalsholat();
	}

	mountedVue = function() {
		setInterval(() => this.getNews(), <?= $news_refresh; ?> * 1000);
		setInterval(() => this.getInfo(), <?= $news_refresh; ?> * 1000);
		setInterval(() => this.getAgenda(), <?= $agenda_refresh; ?> * 1000);
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
			axios.get('<?= base_url() ?>/api/news/info')
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

		// Get Video
		getVideo: function() {
			this.loading = true;
			axios.get('<?= base_url(); ?>/api/display/video')
				.then(res => {
					// handle success
					this.loading = false;
					var data = res.data;
					if (data.status == true) {
						//this.snackbar = true;
						//this.snackbarMessage = data.message;
						this.dataVideo = data.data;
						<?php if ($video_youtube == 'no') : ?>
							this.playVideo();
						<?php endif; ?>
					} else {
						this.snackbar = true;
						this.snackbarMessage = data.message;
					}
				})
				.catch(err => {
					// handle error
					console.log(err);
					var error = err.response
					if (error.data.expired == true) {
						this.snackbar = true;
						this.snackbarMessage = error.data.message;
						setTimeout(() => window.location.href = error.data.data.url, 1000);
					}
				})
		},

		//Play Video MP4
		playVideo: function() {
			//Video Player
			var player = document.getElementById("myplayer");
		
			var i = 0;
			var videoSource = this.dataVideo;
			var videoCount = videoSource.length;
			player.setAttribute("src", videoSource[0]);
			player.autoplay = true;
        	player.load();

			function videoPlay(videoNum) {
				player.setAttribute("src", videoSource[videoNum]);
				player.load();
				player.play();
			}

			player.addEventListener('ended', myHandler, false);

			function myHandler() {
				if (i == (videoCount - 1)) {
					i = 0;
					axios.get('<?= base_url(); ?>/api/display/video')
						.then(res => {
							if (data.status == true) {
								this.dataVideo = data.data;
								videoSource = this.dataVideo;
								videoCount = videoSource.length;
							}
						});
					videoPlay(i);
				} else {
					i++;
					videoPlay(i);
				}
			}
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

		// Get Jadwal Sholat
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
	}
</script>
<?php $this->endSection("js") ?>