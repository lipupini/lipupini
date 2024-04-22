#!/usr/bin/env php
<?php

use Module\Lipupini\State;
use Module\Lipupini\Collection;
use Module\Lipupini\Encryption;

/** @var State $systemState */
$systemState = require(__DIR__ . '/../system/config/state.php');
$collectionUtility = new Collection\Utility($systemState);

if (empty($argv[1])) {
	error('Missing action');
}

switch ($argv[1]) {
	case 'determineFfmpegSupport' :
		echo json_encode($collectionUtility->hasFfmpeg());
		exit(0);
	case 'analyzeCache' :
		$collectionName = $argv[2];
		analyzeCache($systemState, $collectionUtility, $collectionName);
		exit(0);
	case 'generateKeys' :
		$collectionName = $argv[2];

		try {
			$collectionUtility->validateCollectionName($collectionName);
		} catch (Collection\Exception $e) {
			error([$e->getMessage()]);
		}

		$lipupiniPath = $systemState->dirCollection . '/' . $collectionName . '/.lipupini';

		(new Encryption\Key)->generateAndSave(
			privateKeyPath: $lipupiniPath . '/rsakey.private',
			publicKeyPath: $lipupiniPath . '/rsakey.public',
			privateKeyBits: 2048,
		);
		echo json_encode(['result' => 'success']);
		exit(0);
	case 'getItemsPerPage' :
		echo $systemState->itemsPerPage;
		exit(0);
	default:
		throw new Exception('No action specified');
}

function error($errors) {
	echo json_encode([
		'result' => 'error',
		'messages' => $errors,
	]);
	// Have to exit 0 or else the test will not see the error message
	exit(0);
}

function analyzeCache(State $systemState, Collection\Utility $collectionUtility, string $collectionName) {
	try {
		$collectionUtility->validateCollectionName($collectionName);
	} catch (Collection\Exception $e) {
		error([$e->getMessage()]);
	}

	try {
		$collectionFolder = $systemState->dirCollection . '/' . $collectionName;
		$collectionHashTable = getCollectionHashTableByMediaType($collectionUtility, $collectionFolder);
	} catch (Exception $e) {
		error([$e->getMessage()]);
	}

	try {
		$lipupiniFolder = $collectionFolder . '/.lipupini';
		$lipupiniFolderHashTable = getLipupiniFolderHashTable($lipupiniFolder);
	} catch (Exception $e) {
		error([$e->getMessage()]);
	}

	$cache = new Collection\Cache($systemState, $collectionName);

	try {
		$cacheFolderHashTable = getCacheFolderHashTable($collectionUtility, $cache->path());
	} catch (Exception $e) {
		error([$e->getMessage()]);
	}

	$errors = [];
	$hasFfmpeg = $collectionUtility->hasFfmpeg();

	foreach ($collectionHashTable as $mediaType => $fileInfo) {
		switch ($mediaType) {
			case 'audio':
				if (count($fileInfo) !== count($cacheFolderHashTable[$mediaType]['file'] ?? [])) {
					$errors[] = 'Audio file cache mismatch';
					$errors[] = '$fileInfo = ' . var_export($fileInfo, true);
					$errors[] = '$cacheFolderHashTable[$mediaType][file] = ' . var_export($cacheFolderHashTable[$mediaType]['file'] ?? [], true);
				}
				if (count($fileInfo) && $hasFfmpeg && empty($lipupiniFolderHashTable[$mediaType]['waveform'])) {
					$errors[] = 'Missing one or more audio waveforms (using `ffmpeg`)';
				}
				break;

			case 'image':
				foreach (array_keys($systemState->mediaSize) as $mediaSize) {
					if (count($fileInfo) !== count($cacheFolderHashTable[$mediaType][$mediaSize] ?? [])) {
						$errors[] = 'Image ' . $mediaSize . ' cache mismatch';
						$errors[] = '$fileInfo = ' . var_export($fileInfo, true);
						$errors[] = '$cacheFolderHashTable[$mediaType][$mediaSize] = ' . var_export($cacheFolderHashTable[$mediaType][$mediaSize] ?? [], true);
					}
				}
				break;

			case 'text':
				if (count($fileInfo) !== count($cacheFolderHashTable[$mediaType]['html'] ?? [])) {
					$errors[] = 'Text HTML cache mismatch';
					$errors[] = '$fileInfo = ' . var_export($fileInfo, true);
					$errors[] = '$cacheFolderHashTable[$mediaType][$mediaSize] = ' . var_export($cacheFolderHashTable[$mediaType][$mediaType]['html'] ?? [], true);
				}
				if (count($fileInfo) !== count($cacheFolderHashTable[$mediaType]['markdown']) ?? []) {
					$errors[] = 'Text markdown cache mismatch';
					$errors[] = '$fileInfo = ' . var_export($fileInfo, true);
					$errors[] = '$cacheFolderHashTable[$mediaType][$mediaSize] = ' . var_export($cacheFolderHashTable[$mediaType][$mediaType]['markdown'] ?? [], true);
				}
				break;

			case 'video':
				if (count($fileInfo) !== count($cacheFolderHashTable[$mediaType]['file'] ?? [])) {
					$errors[] = 'Video file cache mismatch';
					$errors[] = '$fileInfo = ' . var_export($fileInfo, true);
					$errors[] = '$cacheFolderHashTable[$mediaType][file] = ' . var_export($cacheFolderHashTable[$mediaType]['file'] ?? [], true);
				}
				if (count($fileInfo) && $hasFfmpeg && empty($lipupiniFolderHashTable[$mediaType]['thumbnail'])) {
					$errors[] = 'Missing one or more video thumbnails (using `ffmpeg`)';
					$errors[] = '$fileInfo = ' . var_export($fileInfo, true);
					$errors[] = '$lipupiniFolderHashTable[$mediaType][thumbnail] = ' . var_export($lipupiniFolderHashTable[$mediaType]['thumbnail'] ?? [], true);
				}
				break;
		}
	}

	// Custom and generated assets
	foreach ($lipupiniFolderHashTable as $mediaType => $classificationInfo) {
		switch ($mediaType) {
			case 'audio':
				if (count($classificationInfo['thumbnail'] ?? []) !== count($cacheFolderHashTable[$mediaType]['thumbnail'] ?? [])) {
					$errors[] = 'Audio thumbnail cache mismatch';
					$errors[] = '$classificationInfo[thumbnail] = ' . var_export($classificationInfo['thumbnail'] ?? [], true);
					$errors[] = '$cacheFolderHashTable[$mediaType][thumbnail] = ' . var_export($cacheFolderHashTable[$mediaType]['thumbnail'] ?? [], true);
				}
				if (count($classificationInfo['waveform'] ?? []) !== count($cacheFolderHashTable[$mediaType]['waveform'] ?? [])) {
					$errors[] = 'Audio waveform cache mismatch';
					$errors[] = '$classificationInfo[waveform] = ' . var_export($classificationInfo['waveform'] ?? [], true);
					$errors[] = '$cacheFolderHashTable[$mediaType][waveform] = ' . var_export($cacheFolderHashTable[$mediaType]['waveform'] ?? [], true);
				}
				break;

			case 'image':
				foreach ($classificationInfo as $mediaSize => $fileInfo) {
					foreach ($fileInfo as $collectionPath => $sha256) {
						if ($sha256 !== ($cacheFolderHashTable[$mediaType][$mediaSize][$collectionPath] ?? null)) {
							$errors[] = 'Image custom size ' . $mediaSize . ' cache SHA256 mismatch';
							$errors[] = '$collectionPath = ' . $collectionPath;
						}
					}
				}
				break;

			case 'text':
				/*if (count($classificationInfo['thumbnail']) !== $cacheFolderHashTable[$mediaType]['thumbnail']) {
					$errors[] = 'Text thumbnail cache mismatch';
				}*/
				break;

			case 'video':
				if (count($classificationInfo['thumbnail'] ?? []) !== count($cacheFolderHashTable[$mediaType]['thumbnail'] ?? [])) {
					$errors[] = 'Video thumbnail cache mismatch';
					$errors[] = '$classificationInfo[thumbnail] = ' . var_export($classificationInfo['thumbnail'] ?? [], true);
					$errors[] = '$cacheFolderHashTable[$mediaType][thumbnail] = ' . var_export($cacheFolderHashTable[$mediaType]['thumbnail'] ?? [], true);
				}
				break;
		}
	}

	if (count($errors)) {
		error($errors);
	} else {
		echo json_encode(['result' => 'success']);
	}
}

function getCollectionHashTableByMediaType(Collection\Utility $collectionUtility, string $startPath) {
	$hashTable = [];
	$mediaTypesByExtension = $collectionUtility->mediaTypesByExtension();
	if (!is_dir($startPath)) return $hashTable;
	foreach (new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($startPath), \RecursiveIteratorIterator::SELF_FIRST) as $filePath => $fileInfo) {
		if ($fileInfo->getFilename()[0] === '.' || $fileInfo->isDir()) continue;
		if (str_contains($filePath, '/.lipupini/')) continue;
		$mediaType = $mediaTypesByExtension[pathinfo($filePath, PATHINFO_EXTENSION)]['mediaType'];
		$hashTable[$mediaType][preg_replace('#^' . preg_quote($startPath) . '#', '', $filePath)] = hash_file('sha256', $filePath);
	}
	return $hashTable;
}

function getLipupiniFolderHashTable(string $lipupiniFolder) {
	$hashTable = [];
	if (!is_dir($lipupiniFolder)) return $hashTable;
	foreach (new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($lipupiniFolder), \RecursiveIteratorIterator::SELF_FIRST) as $filePath => $fileInfo) {
		if ($fileInfo->getFilename()[0] === '.' || $fileInfo->isDir()) continue;
		if (str_contains($filePath, $lipupiniFolder . '/' . Collection\Cache::DIRNAME . '/')) continue;
		$relativePath = preg_replace('#^' . preg_quote($lipupiniFolder) . '#', '', $filePath);
		if (!preg_match('#^/([^/]+)/([^/]+)/#', $relativePath, $matches)) {
			continue;
		}
		$mediaType = $matches[1];
		$classification = $matches[2];
		$relativePath = preg_replace('#^' . preg_quote('/' . $mediaType . '/' . $classification) . '#', '', $relativePath);
		$hashTable[$mediaType][$classification][$relativePath] = hash_file('sha256', $filePath);
	}
	return $hashTable;
}

function getCacheFolderHashTable(Collection\Utility $collectionUtility, string $cacheFolder) {
	$hashTable = [];
	$mediaTypesByExtension = $collectionUtility->mediaTypesByExtension();
	if (!is_dir($cacheFolder)) return $hashTable;
	foreach (new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($cacheFolder), \RecursiveIteratorIterator::SELF_FIRST) as $filePath => $fileInfo) {
		if ($fileInfo->getFilename()[0] === '.' || $fileInfo->isDir()) continue;
		$relativePath = preg_replace('#^' . preg_quote($cacheFolder) . '#', '', $filePath);
		if (!preg_match('#^/([^/]+)/#', $relativePath, $matches)) {
			throw new Exception('Could not parse cache path: ' . $relativePath);
		}
		$mediaType = $matches[1];
		$relativePath = preg_replace('#^/' . preg_quote($mediaType) . '#', '', $relativePath);
		$potentialClassification = preg_match('#^/([^/]+)/#', $relativePath, $matches) ? $matches[1] : null;
		$classification = null;
		switch ($mediaTypesByExtension[pathinfo($filePath, PATHINFO_EXTENSION)]['mediaType']) {
			case 'audio' :
			case 'video':
				if ($mediaType === 'image') {
					// E.g. `thumbnail` or `waveform`
					$classification = $potentialClassification;
					$relativePath = preg_replace('#^/' . preg_quote($classification) . '#', '', $relativePath);
				} else {
					$classification = 'file';
				}
				break;
			case 'image':
			case 'text':
				$classification = $potentialClassification;
				$relativePath = preg_replace('#^/' . preg_quote($classification) . '#', '', $relativePath);
			break;

			default:
				throw new Exception('Invalid media type folder: ' . $mediaType);
		}
		$hashTable[$mediaType][$classification][$relativePath] = hash_file('sha256', $filePath);
	}
	return $hashTable;
}
