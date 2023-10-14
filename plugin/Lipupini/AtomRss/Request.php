<?php

namespace Plugin\Lipupini\AtomRss;

use Plugin\Lipupini;
use Plugin\Lipupini\Collection;

class Request extends Lipupini\Http\Request {
	public function initialize(): void {
		if (empty($this->system->requests[Collection\FolderRequest::class]->collectionFolderName)) {
			return;
		}

		if (
			!$this->validateRequestMimeTypes('HTTP_ACCEPT', $this->mimeTypes())
			&& !isset($_GET['atom'])
		) {
			return;
		}

		$this->renderRss();
		$this->system->shutdown = true;
	}

	protected function renderRss(): void {
		$dom = new \DOMDocument('1.0','UTF-8');
		$dom->formatOutput = true;
		$feed = $dom->createElement('feed');
		$feed->setAttribute('xmlns', 'http://www.w3.org/2005/Atom');
		$feed->setAttribute('xmlns:media', 'http://search.yahoo.com/mrss/');
		$dom->appendChild($feed);

		$collectionFolderName = $this->system->requests[Collection\FolderRequest::class]->collectionFolderName;

		$feed->appendChild($dom->createElement('id', $this->system->baseUri . '@' . $collectionFolderName));
		$feed->appendChild($dom->createElement('title', $collectionFolderName . '@' . $this->system->host));

		$linkSelf = $dom->createElement('link');
		$linkSelf->setAttribute('rel', 'self');
		$linkSelf->setAttribute('href', $this->system->baseUri . '@' . $collectionFolderName . '?atom');
		$feed->appendChild($linkSelf);

		$linkAlternate = $dom->createElement('link');
		$linkAlternate->setAttribute('rel', 'alternate');
		$linkAlternate->setAttribute('href', $this->system->baseUri . '@' . $collectionFolderName);
		$feed->appendChild($linkAlternate);

		$author = $dom->createElement('author');
		$author->appendChild($dom->createElement('name', $collectionFolderName));
		$author->appendChild($dom->createElement('uri', $collectionFolderName . '@' . $this->system->host));
		$feed->appendChild($author);

		$this->renderRssEntries($dom, $feed, $collectionFolderName);

		header('Content-type: application/atom+xml');
		echo  $dom->saveXML();
	}

	public function renderRssEntries(\DOMDocument $dom, \DOMElement $feed, string $collectionFolderName): void {
		$collectionData = (new Collection\Utility($this->system))->getCollectionDataRecursive($collectionFolderName);
		$latestUpdated = '';
		foreach ($collectionData as $filePath => &$metaData) {
			if (empty($metaData['date'])) {
				$metaData['date'] = (new \DateTime)
					->setTimestamp(filemtime($this->system->dirCollection . '/' . $collectionFolderName . '/' . $filePath))
					->format(\DateTime::ATOM);
				if (empty($latestUpdated) || $latestUpdated < $metaData['date']) {
					$latestUpdated = $metaData['date'];
				}
			}
		} unset($metaData);

		$entries = [];
		foreach ($collectionData as $filePath => $metaData) {
			// Excluding directories
			if (!($extension = pathinfo($filePath, PATHINFO_EXTENSION))) {
				continue;
			}

			if (in_array($extension, array_keys(Collection\MediaProcessor\ImageRequest::mimeTypes()))) {
				$metaData['medium'] = 'image';
				$metaData['mime'] = Collection\MediaProcessor\ImageRequest::mimeTypes()[$extension];
				$metaData['cacheUrl'] = $this->system->baseUri . 'c/file/' . $collectionFolderName . '/image/large/' . $filePath;
				$metaData['content'] = 	'<p>' . htmlentities($metaData['caption'] ?? $filePath) . '</p>' . "\n"
					. '<img src="' . $metaData['cacheUrl'] . '" alt="' . $filePath . '"/>';
			} else if (in_array($extension, array_keys(Collection\MediaProcessor\VideoRequest::mimeTypes()))) {
				$metaData['medium'] = 'video';
				$metaData['mime'] = Collection\MediaProcessor\VideoRequest::mimeTypes()[$extension];
				$metaData['cacheUrl'] = $this->system->baseUri . 'c/file/' . $collectionFolderName . '/video/' . $filePath;
				$metaData['content'] = 	'<p>' . htmlentities($metaData['caption'] ?? $filePath) . '</p>' . "\n"
					. '<video controls loop><source src="' . $metaData['cacheUrl'] . '" type="' . $metaData['mime'] . '"/></video>';
			} else if (in_array($extension, array_keys(Collection\MediaProcessor\AudioRequest::mimeTypes()))) {
				$metaData['medium'] = 'audio';
				$metaData['mime'] = Collection\MediaProcessor\AudioRequest::mimeTypes()[$extension];
				$metaData['cacheUrl'] = $this->system->baseUri . 'c/file/' . $collectionFolderName . '/audio/' . $filePath;
				$metaData['content'] = 	'<p>' . htmlentities($metaData['caption'] ?? $filePath) . '</p>' . "\n"
					. '<audio controls><source src="' . $metaData['cacheUrl'] . '" type="' . $metaData['mime'] . '"/></audio>';
			} else if (in_array($extension, array_keys(Collection\MediaProcessor\MarkdownRequest::mimeTypes()))) {
				$metaData['medium'] = 'document';
				$metaData['mime'] = Collection\MediaProcessor\MarkdownRequest::mimeTypes()[$extension];
				$metaData['cacheUrl'] = $this->system->baseUri . 'c/file/' . $collectionFolderName . '/markdown/' . $filePath . '.html';
				$metaData['content'] = 	'<p><a href="' . $metaData['cacheUrl'] . '">' . htmlentities($metaData['caption'] ?? $filePath) . '</a></p>';
			} else {
				throw new Exception('Unexpected file extension: ' . $extension);
			}

			$entry = $dom->createElement('entry');
			$entry->appendChild($dom->createElement('id', $this->system->baseUri . '@' . $collectionFolderName . '/' . $filePath . '.html'));
			$entry->appendChild($dom->createElement('title', $filePath));

			$link = $dom->createElement('link');
			$link->setAttribute('href', $this->system->baseUri . '@' . $collectionFolderName . '/' . $filePath . '.html');
			$entry->appendChild($link);

			$entry->appendChild($dom->createElement('summary', $metaData['caption'] ?? $filePath));

			$content = $dom->createElement('content');
			$content->setAttribute('type', 'html');
			$content->appendChild($dom->createCDATASection($metaData['content']));
			$entry->appendChild($content);

			$media = $dom->createElement('media:content');
			$media->setAttribute('url', $metaData['cacheUrl']);
			$media->setAttribute('type', $metaData['mime']);
			$media->setAttribute('medium', $metaData['medium']);
			$entry->appendChild($media);

			$entry->appendChild($dom->createElement('updated', $metaData['date']));
			$entries[] = $entry;
		}

		$feed->appendChild($dom->createElement('updated', $latestUpdated));
		foreach ($entries as $entry) {
			$feed->append($entry);
		}
	}

	public function mimeTypes(): array {
		return [
			'application/atom+xml',
			'application/rss+xml',
			'application/xml',
		];
	}
}
