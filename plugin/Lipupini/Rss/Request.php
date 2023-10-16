<?php

namespace Plugin\Lipupini\Rss;

use Plugin\Lipupini;
use Plugin\Lipupini\Collection;

class Request extends Lipupini\Http\Request {
	public function initialize(): void {
		if (empty($this->system->requests[Collection\FolderRequest::class]->collectionFolderName)) {
			return;
		}

		if (
			!$this->validateRequestMimeTypes('HTTP_ACCEPT', $this->mimeTypes()) &&
			(!isset($_GET['feed']) || $_GET['feed'] !== 'rss')
		) {
			return;
		}

		$this->renderRss();
		$this->system->shutdown = true;
	}

	protected function renderRss(): void {
		$dom = new \DOMDocument('1.0','UTF-8');
		$dom->formatOutput = true;
		$rss = $dom->createElement('rss');
		$rss->setAttribute('version', '2.0');
		$rss->setAttribute('xmlns:atom', 'http://www.w3.org/2005/Atom');
		$rss->setAttribute('xmlns:content', 'http://purl.org/rss/1.0/modules/content/');
		$rss->setAttribute('xmlns:itunes', 'http://www.itunes.com/dtds/podcast-1.0.dtd');
		$rss->setAttribute('xmlns:media', 'http://search.yahoo.com/mrss/');

		$channel = $dom->createElement('channel');
		$rss->appendChild($channel);
		$dom->appendChild($rss);

		$collectionFolderName = $this->system->requests[Collection\FolderRequest::class]->collectionFolderName;

		$channel->appendChild($dom->createElement('title', $collectionFolderName . '@' . $this->system->host));
		$channel->appendChild($dom->createElement('description', $collectionFolderName . '@' . $this->system->host));

		$linkSelf = $dom->createElement('atom:link');
		$linkSelf->setAttribute('rel', 'self');
		$linkSelf->setAttribute('href', $this->system->baseUri . '@' . $collectionFolderName . '?feed=rss');
		$linkSelf->setAttribute('type', 'application/rss+xml');
		$channel->appendChild($linkSelf);

		$link = $dom->createElement('link', $this->system->baseUri . '@' . $collectionFolderName);
		$channel->appendChild($link);

		$image = $dom->createElement('image');
		$image->appendChild($dom->createElement('url', $this->system->cacheBaseUri . 'avatar/' . $collectionFolderName . '.png'));
		$image->appendChild($dom->createElement('title', $collectionFolderName . '@' . $this->system->host));
		$image->appendChild($dom->createElement('link', $this->system->baseUri . '@' . $collectionFolderName));
		$channel->appendChild($image);

		$this->renderRssItems($dom, $channel, $collectionFolderName);

		header('Content-type: application/rss+xml');
		$this->system->responseContent = $dom->saveXML();
	}

	public function renderRssItems(\DOMDocument $dom, \DOMElement $channel, string $collectionFolderName): void {
		$collectionData = (new Collection\Utility($this->system))->getCollectionDataRecursive($collectionFolderName);
		foreach ($collectionData as $filePath => &$metaData) {
			if (empty($metaData['date'])) {
				$metaData['date'] = (new \DateTime)
					->setTimestamp(filemtime($this->system->dirCollection . '/' . $collectionFolderName . '/' . $filePath))
					->format(\DateTime::RSS);
			} else {
				$metaData['date'] = (new \DateTime($metaData['date']))
					->format(\DateTime::ISO8601);
			}
		} unset($metaData);

		$items = [];
		foreach ($collectionData as $filePath => $metaData) {
			$extension = pathinfo($filePath, PATHINFO_EXTENSION);

			if (in_array($extension, array_keys(Collection\MediaProcessor\ImageRequest::mimeTypes()))) {
				$metaData['medium'] = 'image';
				$metaData['mime'] = Collection\MediaProcessor\ImageRequest::mimeTypes()[$extension];
				$metaData['cacheUrl'] = $this->system->cacheBaseUri . 'file/' . $collectionFolderName . '/image/large/' . $filePath;
				$metaData['content'] = 	'<p>' . htmlentities($metaData['caption'] ?? $filePath) . '</p>' . "\n"
					. '<img src="' . $metaData['cacheUrl'] . '" alt="' . $filePath . '"/>';
			} else if (in_array($extension, array_keys(Collection\MediaProcessor\VideoRequest::mimeTypes()))) {
				$metaData['medium'] = 'video';
				$metaData['mime'] = Collection\MediaProcessor\VideoRequest::mimeTypes()[$extension];
				$metaData['cacheUrl'] = $this->system->cacheBaseUri . 'file/' . $collectionFolderName . '/video/' . $filePath;
				$metaData['content'] = 	'<p>' . htmlentities($metaData['caption'] ?? $filePath) . '</p>' . "\n"
					. '<video controls loop><source src="' . $metaData['cacheUrl'] . '" type="' . $metaData['mime'] . '"/></video>';
			} else if (in_array($extension, array_keys(Collection\MediaProcessor\AudioRequest::mimeTypes()))) {
				$metaData['medium'] = 'audio';
				$metaData['mime'] = Collection\MediaProcessor\AudioRequest::mimeTypes()[$extension];
				$metaData['cacheUrl'] = $this->system->cacheBaseUri . 'file/' . $collectionFolderName . '/audio/' . $filePath;
				$metaData['content'] = 	'<p>' . htmlentities($metaData['caption'] ?? $filePath) . '</p>' . "\n"
					. '<audio controls><source src="' . $metaData['cacheUrl'] . '" type="' . $metaData['mime'] . '"/></audio>';
			} else if (in_array($extension, array_keys(Collection\MediaProcessor\MarkdownRequest::mimeTypes()))) {
				$metaData['medium'] = 'document';
				$metaData['mime'] = Collection\MediaProcessor\MarkdownRequest::mimeTypes()[$extension];
				$metaData['cacheUrl'] = $this->system->cacheBaseUri . 'file/' . $collectionFolderName . '/markdown/' . $filePath . '.html';
				$metaData['content'] = 	'<p><a href="' . $metaData['cacheUrl'] . '">' . htmlentities($metaData['caption'] ?? $filePath) . '</a></p>';
			} else {
				throw new Exception('Unexpected file extension: ' . $extension);
			}

			$item = $dom->createElement('item');
			$item->appendChild($dom->createElement('guid', $this->system->baseUri . '@' . $collectionFolderName . '/' . $filePath . '.html'));
			$item->appendChild($dom->createElement('title', $filePath));

			$link = $dom->createElement('link', $this->system->baseUri . '@' . $collectionFolderName . '/' . $filePath . '.html');
			$item->appendChild($link);

			$item->appendChild($dom->createElement('description', $filePath));

			$content = $dom->createElement('content:encoded');
			$content->appendChild($dom->createCDATASection($metaData['content']));
			$item->appendChild($content);

			$media = $dom->createElement('media:content');
			$media->setAttribute('url', $metaData['cacheUrl']);
			$media->setAttribute('type', $metaData['mime']);
			$media->setAttribute('medium', $metaData['medium']);
			$item->appendChild($media);

			$enclosure = $dom->createElement('enclosure');
			$enclosure->setAttribute('url', $metaData['cacheUrl']);
			$enclosure->setAttribute('length', '0');
			$enclosure->setAttribute('type', $metaData['mime']);
			$item->appendChild($enclosure);

			$item->appendChild($dom->createElement('pubDate', $metaData['date']));
			$items[] = $item;
		}

		foreach ($items as $item) {
			$channel->append($item);
		}
	}

	public function mimeTypes(): array {
		return [
			'application/rss+xml',
			'application/atom+xml',
			'application/xml',
		];
	}
}
