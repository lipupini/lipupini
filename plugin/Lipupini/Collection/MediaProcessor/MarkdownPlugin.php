<?php

namespace Plugin\Lipupini\Collection\MediaProcessor;

use Plugin\Lipupini\Exception;
use Plugin\Lipupini\State;
use System\Lipupini;
use System\Plugin;
use Plugin\Lipupini\Collection\MediaProcessor\Parsedown;

class MarkdownPlugin extends Plugin {
	public function start(State $state): State {
		$extMimes = [
			'md' => 'text/markdown',
			'html' => 'text/html',
		];

		if (!preg_match('#^/c/file/([^/]+)/markdown/(original|rendered)(.+\.(' . implode('|', array_keys($extMimes)) . '))$#', $_SERVER['REQUEST_URI'], $matches)) {
			return $state;
		}

		// If the URL has matched, we're going to shutdown after this plugin returns no matter what
		$state->lipupiniMethod = 'shutdown';

		$collectionFolderName = $matches[1];
		$mode = $matches[2];
		$filePath = $matches[3];
		$extension = $matches[4];

		if ($mode === 'rendered') {
			if ($extension !== 'html') {
				throw new Exception('Invalid rendered markdown extension');
			}
			$htmlWebPath = $_SERVER['REQUEST_URI'];
			$mdFilePath = preg_replace('#\.html$#', '.md', $filePath);
			$markdownWebPath = '/c/file/' . $collectionFolderName . '/markdown/original' . $mdFilePath;
			$pathOriginal = DIR_COLLECTION . '/' . $collectionFolderName . $mdFilePath;
		} else {
			$markdownWebPath = $_SERVER['REQUEST_URI'];
			$htmlFilePath = preg_replace('#\.md$#', '.html', $filePath);
			$htmlWebPath = '/c/file/' . $collectionFolderName . '/markdown/rendered' . $htmlFilePath;
			$pathOriginal = DIR_COLLECTION . '/' . $collectionFolderName . $filePath;
		}

		Lipupini::validateCollectionFolderName($collectionFolderName);

		if (!file_exists($pathOriginal)) {
			http_response_code(404);
			echo 'Not found';
			return $state;
		}

		if (!is_dir(DIR_WEBROOT . pathinfo($markdownWebPath, PATHINFO_DIRNAME))) {
			mkdir(DIR_WEBROOT . pathinfo($markdownWebPath, PATHINFO_DIRNAME), 0755, true);
		}

		if (!is_dir(DIR_WEBROOT . pathinfo($htmlWebPath, PATHINFO_DIRNAME))) {
			mkdir(DIR_WEBROOT . pathinfo($htmlWebPath, PATHINFO_DIRNAME), 0755, true);
		}

		copy($pathOriginal, DIR_WEBROOT . $markdownWebPath);

		try {
			$rendered = Parsedown::instance()->text(file_get_contents($pathOriginal));
		} catch (\Exception $e) {
			throw new Exception('Could not render markdown file');
		}

		file_put_contents(DIR_WEBROOT . $htmlWebPath, $rendered);

		header('Content-type: ' . $extMimes[$extension]);
		readfile(DIR_WEBROOT . $_SERVER['REQUEST_URI']);

		return $state;
	}
}

