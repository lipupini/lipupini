#!/usr/bin/env php
<?php

ini_set('max_execution_time', 0);
ini_set('memory_limit', '512M');

if (empty($argv[1]) || empty($argv[2])) {
	echo 'Expected usage: `./ffmpeg-video-thumbnail.php <inputVideoFilepath> <outputPngFilepath>`' . "\n";
	exit(1);
}

$videoFile = $argv[1];
$outputPngPath = $argv[2];

if (!file_exists($videoFile)) {
	echo 'Could not find input video file: ' . $videoFile . "\n";
	exit(1);
}

// Adapted from https://stackoverflow.com/a/35026487

saveHalfwayFrame($videoFile, $outputPngPath);

function saveHalfwayFrame($videoFile, $outputPngPath) {
	$totalDuration = getVideoTotalDuration($videoFile);
	saveVideoFrame($videoFile, $outputPngPath, $totalDuration / 2);
}

function saveVideoFrame($videoFile, $outputPngPath, $time) {
	/*
	`ffmpeg` takes a few extra steps for filename-safe operations, and Lipupini aims to offer as much as the filesystem can give regarding filenames
	So the process becomes:
	1) Use a SHA1 sum of the file, since `ffmpeg` will definitely input and output that format
	2) Symlink the input file to the system's temp dir
	4) Run `ffmpeg` on that symlink, and output the result to the system temp dir
	5) Delete the symlink from (2) and move the output file to its intended destination
	*/
	$fileSha1 = sha1_file($videoFile);
	$tmpInputFilepath = sys_get_temp_dir() . '/' . $fileSha1 . '_input.' . pathinfo($videoFile, PATHINFO_EXTENSION);
	$tmpOutputFilepath = sys_get_temp_dir() . '/' . $fileSha1 . '_output.png';
	symlink($videoFile, $tmpInputFilepath);
	runShellCommand('ffmpeg -ss ' . escapeshellarg($time) . ' -i ' . escapeshellarg($tmpInputFilepath) . ' -frames:v 1 ' . escapeshellarg($tmpOutputFilepath));
	unlink($tmpInputFilepath);
	rename($tmpOutputFilepath, $outputPngPath);
}

function getVideoTotalDuration($videoFile) {
	return (float)getShellCommandOutput('ffprobe -loglevel error -of csv=p=0 -show_entries format=duration ' . escapeshellarg($videoFile));
}

function getShellCommandOutput($command) {
	return trim(shell_exec(escapeshellcmd($command)));
}

function runShellCommand($command) {
	passthru(escapeshellcmd($command));
}
