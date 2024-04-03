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
	echo 'Could not find input video file: ' . $inputFile . "\n";
	exit(1);
}

saveAudioWaveform($inputFile, $outputPngPath, 'FFFFFF');

function saveAudioWaveform($inputFile, $outputPngPath, $color) {
	if (!preg_match('#^[0-9A-F]{6}$#', $color)) {
		throw new Exception('Invalid HEX color value: ' . $color);
	}
	$dimensions = '700x200';
	/*
	`ffmpeg` takes a few extra steps for filename-safe operations, and Lipupini aims to offer as much as the filesystem can give regarding filenames
	So the process becomes:
	1) Use a SHA1 sum of the file, since `ffmpeg` will definitely input and output that format
	2) Symlink the input file to the system's temp dir
	4) Run `ffmpeg` on that symlink, and output the result to the system temp dir
	5) Delete the symlink from (2) and move the output file to its intended destination
	*/
	$fileSha1 = sha1_file($inputFile);
	$tmpInputFilepath = sys_get_temp_dir() . '/' . $fileSha1 . '_input.' . pathinfo($inputFile, PATHINFO_EXTENSION);
	$tmpOutputFilepath = sys_get_temp_dir() . '/' . $fileSha1 . '_output.png';
	symlink($inputFile, $tmpInputFilepath);
	$command ='ffmpeg -i ' . escapeshellarg($tmpInputFilepath) . ' -lavfi showwavespic=split_channels=0:draw=full:s=' . $dimensions . ':colors=' . $color . ' ' . escapeshellarg($tmpOutputFilepath);
	passthru(escapeshellcmd($command));
	unlink($tmpInputFilepath);
	rename($tmpOutputFilepath, $outputPngPath);
}
