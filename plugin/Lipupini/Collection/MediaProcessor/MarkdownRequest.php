<?php

namespace Plugin\Lipupini\Collection\MediaProcessor;

use Plugin\Lipupini;
use Plugin\Lipupini\Collection;

class MarkdownRequest extends MediaProcessorRequest {
	public function initialize(): void {
		$extMimes = [
			'md' => 'text/markdown',
			'html' => 'text/html',
		];

		if (!preg_match('#^/c/file/([^/]+)/markdown/(original|rendered)(.+\.(' . implode('|', array_keys($extMimes)) . '))$#', $_SERVER['REQUEST_URI'], $matches)) {
			return;
		}

		// If the URL has matched, we're going to shutdown after this plugin returns no matter what
		$this->system->shutdown = true;

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
			$pathOriginal = $this->system->dirCollection . '/' . $collectionFolderName . $mdFilePath;
		} else {
			$markdownWebPath = $_SERVER['REQUEST_URI'];
			$htmlFilePath = preg_replace('#\.md$#', '.html', $filePath);
			$htmlWebPath = '/c/file/' . $collectionFolderName . '/markdown/rendered' . $htmlFilePath;
			$pathOriginal = $this->system->dirCollection . '/' . $collectionFolderName . $filePath;
		}

		(new Collection\Utility($this->system))->validateCollectionFolderName($collectionFolderName);

		if (!file_exists($pathOriginal)) {
			http_response_code(404);
			echo 'Not found';
			return;
		}

		if (!is_dir($this->system->dirWebroot . pathinfo($markdownWebPath, PATHINFO_DIRNAME))) {
			mkdir($this->system->dirWebroot . pathinfo($markdownWebPath, PATHINFO_DIRNAME), 0755, true);
		}

		if (!is_dir($this->system->dirWebroot . pathinfo($htmlWebPath, PATHINFO_DIRNAME))) {
			mkdir($this->system->dirWebroot . pathinfo($htmlWebPath, PATHINFO_DIRNAME), 0755, true);
		}

		copy($pathOriginal, $this->system->dirWebroot . $markdownWebPath);

		try {
			$rendered = Collection\MediaProcessor\Parsedown::instance()->text(file_get_contents($pathOriginal));
		} catch (\Exception $e) {
			throw new Exception('Could not render markdown file');
		}

		$rendered = '<!DOCTYPE html><html><head><meta charset="utf-8"></head><body>' . "\n"
			. $rendered . "\n"
			. '</body></html>' . "\n";

		file_put_contents($this->system->dirWebroot . $htmlWebPath, $rendered);

		header('Content-type: ' . $extMimes[$extension]);
		readfile($this->system->dirWebroot . $_SERVER['REQUEST_URI']);
	}
}

