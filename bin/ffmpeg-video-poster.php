#!/usr/bin/env php
<?php

if (empty($argv[1]) || empty($argv[2])) {
	echo 'Expected usage: `./ffmpeg-video-poster.php <inputVideoFilepath> <outputPngFilepath>`' . "\n";
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
	runShellCommand('ffmpeg -ss ' . escapeshellarg($time) . ' -i ' . escapeshellarg($videoFile) . ' -frames:v 1 ' . escapeshellarg($outputPngPath));
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
