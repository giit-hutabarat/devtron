<?php $this->extend("layouts/display"); ?>
<?php $this->section("style"); ?>
<style>
	html,
	body {
		width: 100%;
		margin: 0px;
		overflow: auto;
		-webkit-touch-callout: none;
		-webkit-user-select: none;
	}

	body {
		color: white;
	}

	.transparan {
		background-color: rgba(0, 0, 0, 0.8) !important;
	}

	.transparan-abu {
		background-color: rgba(208, 211, 212, 0.8) !important;
		color: black;
	}

	.bg-red {
		background-color: rgba(244, 67, 54, 0.8) !important;
	}

	.bg-blue-grey {
		background-color: rgba(96, 125, 139, 0.8) !important;
	}

	.bg-cyan {
		background-color: rgba(0, 188, 212, 0.8) !important;
	}

	.bg-green {
		background-color: rgba(76, 175, 80, 0.8) !important;
	}

	.bg-orange {
		background-color: rgba(255, 152, 0, 0.8) !important;
	}

	.bg-pink {
		background-color: rgba(233, 30, 99, 0.8) !important;
	}

	#judul_1 {
		line-height: 30px;
		margin-bottom: 0
	}

	#judul_2 {
		margin-bottom: 0
	}

	/*bottom container*/
	#tanggal-jam {
		position: absolute;
		top: 90vh;
		width: 20%;
		height: 10vh;
		padding: 5px;
		background: #0d6efd;
		z-index: 3;
		overflow: hidden;
	}

	#tanggal {
		font-weight: bold;
		font-size: 22px;
		color: #fdfefe;
		line-height: 1;
	}

	#waktu {
		font-weight: bold;
		font-size: 50px;
		color: #fdfefe;
		line-height: 0.3;
	}

	/* text scroller */
	#news-container {
		position: absolute;
		top: 90vh;
		left: 20%;
		width: 80%;
		height: 10vh;
		background: #dc3545;
		z-index: 2;
		overflow: hidden;
		/*transform: translate3d(0, 0, 0);*/
	}

	/* Make it a marquee */
	.marquee {
		color: #f2f2f2;
		width: 100%;
		font-size: 40px;
		font-weight: 600;
		margin: 0 auto;
		overflow: hidden;
		white-space: nowrap;
		box-sizing: border-box;
		animation: marquee 60s linear infinite;
	}

	.marquee:hover {
		animation-play-state: paused
	}

	/* Make it move */
	@keyframes marquee {
		0% {
			text-indent: 27.5em
		}

		100% {
			text-indent: -105em;
			/*transform: translateX(-66.6666%);*/
		}
	}
	
	#waktu-sholat {
        position: absolute;
        bottom: 10vh;
        width: 100%;
        z-index: 3;
        overflow: hidden;
    }

	.nama-solat {
		font-size: 1.8vw;
		font-weight: bold;
		line-height: 0.8;
	}

    .waktu-solat {
		font-size: 3.5vw;
		font-weight: bold;
		line-height: 1;
	}
</style>
<?php $this->endSection("style") ?>

<?php $this->section("content"); ?>
<?= $this->include($content); ?>
<?php $this->endSection("content") ?>

