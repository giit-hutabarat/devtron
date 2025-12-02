<?php $this->section("style"); ?>
<style>

</style>
<?php $this->endSection("style") ?>

<nav class="navbar navbar-dark bg-dark mb-5">
	<div class="container-fluid">
		<a class="navbar-brand d-flex align-items-center my-2 my-lg-0 me-lg-auto text-decoration-none" href="#">
			<img style="margin:0 auto;margin-right: 10px;" id="logo" class="img-responsive" src="<?php echo base_url('/' . ($logo == "" ? 'logo.png' : $logo)); ?>" width="80" height="80" />
			<span id="judul_1" class="h1 fw-bold"><?= $nama_instansi; ?><br />
				<span id="judul_2" class="h5"><?= $alamat; ?></span>
			</span>
		</a>
	</div>
</nav>

<div class="container-fluid">

	<div class="row">
		<div class="col-sm-8">
			<div class="card bg-dark text-white border-0">
				<div class="card-header h4 py-3">
					<i class="mdi mdi-video"></i> Jadwal
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
			<div class="card bg-danger text-white border-0 mb-3">
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

			<div class="card bg-warning text-dark border-0 mb-3">
				<div class="card-header h5">
					<i class="mdi mdi-image"></i> Galeri
				</div>
				<div class="card-body bg-white text-dark p-0">
					<div id="carousel" class="carousel slide" data-bs-ride="carousel">
						<div class="carousel-indicators">
							<button type="button" data-bs-target="#carousel" v-for="(item, i ) in dataGaleri" :key="i" :data-bs-slide-to="i" :class="{ active: i==0 }" aria-current="true" :aria-label="'Slide' + i"></button>
						</div>
						<div class="carousel-inner">
							<div class="carousel-item" v-for="(item, i ) in dataGaleri" :key="i" :class="{ active: i==0 }">
								<img :src="'<?= base_url(); ?>' + '/' + item.image_url" class="d-block w-100" alt="...">
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
			</div>

		</div>
	</div>
</div>

<!--tanggal dan jam-->
<div id="tanggal-jam" class="text-center fw-bold">
	<div class="position-absolute top-50 start-50 translate-middle w-100">
		<p id="tanggal">{{tanggal}}</p>
		<p id="waktu">{{jam}}</p>
	</div>
</div>

<!--teks berjalan-->
<div id="news-container">
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
		dataGaleri: [],
		dataAgenda: [],
		dataVideo: [],
	}

	createdVue = function() {
		setInterval(this.getDate, 1000);
		setInterval(this.getTime, 1000);
		this.getVideo();
		this.getNews();
		this.getAgenda();
		this.getGaleri();
	}

	mountedVue = function() {
		setInterval(() => this.getNews(), <?= $news_refresh; ?> * 1000);
		setInterval(() => this.getAgenda(), <?= $agenda_refresh; ?> * 1000);
		setInterval(() => this.getGaleri(), <?= $slide_refresh; ?> * 1000);
	}

	var watchVue = {

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

		//Get Galeri
		getGaleri: function() {
			this.loading = true;
			axios.get('<?= base_url() ?>/api/display/galeri')
				.then(res => {
					// handle success
					this.loading = false;
					var data = res.data;
					if (data.status == true) {
						this.snackbar = true;
						this.snackbarMessage = data.message;
						this.dataGaleri = data.data;
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

		//Play Video Mp4
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
	}
</script>
<?php $this->endSection("js") ?>