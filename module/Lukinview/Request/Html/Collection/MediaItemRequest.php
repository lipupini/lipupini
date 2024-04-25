<?php

namespace Module\Lukinview\Request\Html\Collection;

use Module\Lipupini\Collection;
use Module\Lipupini\Exception;
use Module\Lipupini\Request;

class MediaItemRequest extends Request\Html {
	public string|null $pageImagePreviewUri = null;
	private array $fileData = [];
	private string|null $parentPath = null;
	public string|null $collectionFilePath = null;

	use Collection\Trait\CollectionRequest;

	public function initialize(): void {
		// URLs start with `/@` (but must be followed by something and something other than `/` or `?`)
		if (!preg_match('#^' . preg_quote($this->system->baseUriPath) . '@(?!/|\?|$)#', $_SERVER['REQUEST_URI_DECODED'])) return;
		// Media item HTML requests must have a `.html` extension
		// Remove any query with `preg_replace`, cannot use `PHP_URL_PATH` because of special character support
		if (pathinfo($_SERVER['REQUEST_URI_DECODED'], PATHINFO_EXTENSION) !== 'html') return;

		$this->collectionNameFromSegment(1, '@');

		$this->collectionFilePath =
			preg_replace('#\.html$#', '',
				preg_replace(
					'#^/@' . preg_quote($this->collectionName) . '/?#', '',
					$_SERVER['REQUEST_URI_DECODED'] // Automatically strips query string
				)
		);

		// Make sure file in collection exists before proceeding
		if (!file_exists($this->system->dirCollection . '/' . $this->collectionName . '/' . $this->collectionFilePath)) {
			return;
		}

		if (!$this->loadViewData()) {
			return;
		}

		$this->renderHtml();
	}

	public function renderHtml(): void {
		ob_start();
		require($this->system->dirModule . '/' . $this->system->frontendModule . '/Html/Collection/MediaItem.php');
		$this->system->responseContent = ob_get_clean();
		$this->system->responseType = 'text/html';
	}

	private function loadViewData(): bool {
		$this->pageTitle = $this->collectionFilePath . '@' . $this->collectionName . '@' . $this->system->host;
		$collectionUtility = new Collection\Utility($this->system);
		// `$this->collectionFilePath` has a filename, we want to know what directory it's in
		$collectionFileDirname = pathinfo($this->collectionFilePath, PATHINFO_DIRNAME);
		$collectionFileDirname = $collectionFileDirname === '.' ? '' : $collectionFileDirname;
		$collectionData = $collectionUtility->getCollectionData($this->collectionName, $collectionFileDirname, true);
		if (array_key_exists($this->collectionFilePath, $collectionData)) {
			$this->fileData = $collectionData[$this->collectionFilePath];
		} else {
			$this->fileData = [];
		}
		if (($this->fileData['visibility'] ?? null) === 'hidden') {
			return false;
		}

		$this->pageImagePreviewUri = $collectionUtility->thumbnailUrl($this->collectionName, $this->fileData['mediaType'] . '/thumbnail', $this->collectionFilePath, true);
		$parentFolder = dirname($this->collectionFilePath);
		$this->parentPath = '@' . $this->collectionName . ($parentFolder !== '.' ? '/' . $parentFolder : '');
		if (!empty($_SERVER['HTTP_REFERER']) && preg_match('#' . preg_quote($this->parentPath) . '\?page=([0-9]+)$#', $_SERVER['HTTP_REFERER'], $matches)) {
			$this->parentPath .= '?page=' . $matches[1];
		}

		$this->addStyle('/css/Global.css');

		switch ($this->fileData['mediaType']) {
			case 'audio':
				$this->preloadMedia($collectionUtility->assetUrl($this->collectionName, 'audio', $this->collectionFilePath), $this->fileData['mediaType']);
				if (!empty($this->fileData['thumbnail'])) {
					$this->preloadMedia($this->fileData['thumbnail'], 'image');
				}
				if (!empty($this->fileData['waveform'])) {
					$this->preloadMedia($this->fileData['waveform'], 'image');
					$this->addScript('/js/AudioWaveformSeek.js');
				}
				$this->addScript('/js/Audio.js');
				break;
			case 'image':
				$this->preloadMedia($collectionUtility->assetUrl($this->collectionName, 'image/medium', $this->collectionFilePath), $this->fileData['mediaType']);
				break;
			case 'text':
				$this->preloadMedia($collectionUtility->assetUrl($this->collectionName, 'text/html', $this->collectionFilePath), $this->fileData['mediaType']);
				if (!empty($this->fileData['thumbnail'])) {
					$this->preloadMedia($this->fileData['thumbnail'], 'image');
				}
				break;
			case 'video':
				$this->preloadMedia($collectionUtility->assetUrl($this->collectionName, 'video', $this->collectionFilePath), $this->fileData['mediaType']);
				if (!empty($this->fileData['thumbnail'])) {
					$this->preloadMedia($this->fileData['thumbnail'], 'image');
				}
				$this->addStyle('/lib/videojs/video-js.min.css');
				$this->addScript('/lib/videojs/video.min.js');
				break;
			default:
				throw new Exception('Could not determine `mediaType');
		}

		$this->addStyle('/css/MediaItem.css');
		$this->addStyle('/css/MediaType/' . htmlentities(ucfirst($this->fileData['mediaType'])) . '.css');

		$this->preloadReady();

		return true;
	}
}
