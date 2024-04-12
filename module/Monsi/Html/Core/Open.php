<?php
use Module\Lipupini\L18n\A;
A::$path = realpath(__DIR__ . '/../../');
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<?php if (!empty($this->pageImagePreviewUri)) : ?>
<meta property="og:image" content="<?php echo htmlentities($this->pageImagePreviewUri) ?>">
<?php endif ?>
<title><?php echo htmlentities($this->pageTitle) ?></title>
<link rel="stylesheet" href="/css/Global.css">
<?php echo $this->htmlHead ?? '' ?>
<link rel="stylesheet" href="/lib/videojs/video-js.min.css">
<link rel="stylesheet" href="/lib/videojs-wavesurfer/css/videojs.wavesurfer.min.css">
<script src="/lib/videojs/video.min.js"></script>
<script src="/lib/wavesurfer/wavesurfer.min.js"></script>
<script src="/lib/videojs-wavesurfer/videojs.wavesurfer.min.js"></script>
<script>
let wavesurferOptions = {
	controls: true,
	fluid: true,
	bigPlayButton: false,
	plugins: {
		wavesurfer: {
			backend: 'MediaElement',
			displayMilliseconds: false,
			debug: false,
			waveColor: 'grey',
			progressColor: 'lightgrey',
			cursorColor: 'black',
			interact: true,
			hideScrollbar: true
		}
	}
}
</script>
</head>
<body>
