<?php

namespace Plugin\Lipupini\Collection\MediaProcessor;

use Plugin\Lipupini;
use Plugin\Lipupini\Collection;

class MarkdownRequest extends MediaProcessorRequest {
	public static function mimeTypes(): array {
		return [
			'md' => 'text/markdown',
			'html' => 'text/html',
		];
	}

	public function initialize(): void {
		if (!preg_match('#^/c/file/([^/]+)/markdown(/.+\.(' . implode('|', array_keys(self::mimeTypes())) . '))$#', $_SERVER['REQUEST_URI'], $matches)) {
			return;
		}

		// If the URL has matched, we're going to shutdown after this plugin returns no matter what
		$this->system->shutdown = true;

		$collectionFolderName = $matches[1];
		$filePath = $matches[2];
		$extension = $matches[3];

		if ($extension === 'html') {
			$htmlWebPath = $_SERVER['REQUEST_URI'];
			$mdFilePath = preg_replace('#\.html$#', '', $filePath);
			$markdownWebPath = '/c/file/' . $collectionFolderName . '/markdown' . $mdFilePath;
			$pathOriginal = $this->system->dirCollection . '/' . $collectionFolderName . '/' . $mdFilePath;
		} else {
			$markdownWebPath = $_SERVER['REQUEST_URI'];
			$htmlFilePath = $_SERVER['REQUEST_URI'] . '.html';
			$htmlWebPath = '/c/file/' . $collectionFolderName . '/markdown' . $htmlFilePath;
			$pathOriginal = $this->system->dirCollection . '/' . $collectionFolderName . $htmlFilePath;
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

		header('Content-type: ' . self::mimeTypes()[$extension]);
		$this->system->responseContent = $rendered;
	}
}
