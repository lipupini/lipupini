#!/usr/bin/env php
<?php

ini_set('max_execution_time', 0);
ini_set('memory_limit', '512M');

if (empty($argv[1]) || empty($argv[2])) {
	echo 'Expected usage: `./ffmpeg-audio-waveform.php <inputVideoFilepath> <outputPngFilepath>`' . "\n";
	exit(1);
}

$inputFile = $argv[1];
$outputPngPath = $argv[2];

if (!file_exists($inputFile)) {
	echo 'Could not find input video file: ' . $videoFile . "\n";
	exit(1);
}

saveAudioWaveform($inputFile, $outputPngPath, 'FFFFFF');

function saveAudioWaveform($inputFile, $outputPngPath, $color) {
	if (!preg_match('#^[0-9A-F]{6}$#', $color)) {
		throw new Exception('Invalid HEX color value: ' . $color);
	}
	$dimensions = '1200x75';
	$command ='ffmpeg -i ' . escapeshellarg($inputFile) . ' -lavfi showwavespic=split_channels=0:draw=full:s=' . $dimensions . ':colors=' . $color . ' ' . escapeshellarg($outputPngPath);
	passthru(escapeshellcmd($command));
}
