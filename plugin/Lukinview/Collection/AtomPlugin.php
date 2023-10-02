<?php

namespace Plugin\Lukinview\Collection;

use Plugin\Lipupini\Collection\MediaProcessor\AudioPlugin;
use Plugin\Lipupini\Collection\MediaProcessor\ImagePlugin;
use Plugin\Lipupini\Collection\MediaProcessor\MarkdownPlugin;
use Plugin\Lipupini\Collection\MediaProcessor\VideoPlugin;
use Plugin\Lipupini\Exception;
use Plugin\Lipupini\Http;
use Plugin\Lipupini\State;
use System\Plugin;

class AtomPlugin extends Plugin {
	public function start(State $state): State {
		if (empty($state->collectionFolderName)) {
			return $state;
		}

		if (!Http::getClientAccept('AtomXML')) {
			return $state;
		}

		// Only applies to, e.g. http://locahost/@example
		// Does not apply to http://locahost/@example/memes/cat-computer.jpg.html
		if ($state->collectionPath !== '') {
			return $state;
		}

		header('Content-type: application/atom+xml');
		$this->getAtomXml($state);

		$state->lipupiniMethod = 'shutdown';
		return $state;
	}

	public function getAtomXml(State $state) {
		echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n" . '<feed xmlns="http://www.w3.org/2005/Atom" xmlns:media="http://search.yahoo.com/mrss/">' . "\n" ?>

	<id>https://<?php echo htmlentities(HOST) ?>/@<?php echo htmlentities($state->collectionFolderName) ?></id>
	<title><?php echo htmlentities($state->collectionFolderName . '@' . HOST) ?></title>
	<subtitle type="html"></subtitle>
	<updated>2023-09-21T11:18:18.000Z</updated>
	<author>
		<name><?php echo htmlentities($state->collectionFolderName) ?></name>
		<uri><?php echo htmlentities($state->collectionUrl) ?></uri>
	</author>
	<link rel="alternate" type="text/html" href="<?php echo htmlentities($state->collectionFolderName) ?>"/>
	<link rel="self" type="application/atom+xml" href="<?php echo htmlentities($state->collectionFolderName) ?>"/>
	<?php
	$this->getEntriesXml($state);
	?>

</feed>
	<?php
	}

	public function getEntriesXml(State $state) {
		exec('cd ' . escapeshellarg(DIR_COLLECTION . '/' . $state->collectionFolderName) . ' && find -path "*/.lipupini/.files.json"', $output, $returnCode);
		if (!$output || $returnCode !== 0) {
			return '';
		}

		$filesJsonPaths = [];
		foreach ($output as $filesJsonPath) {
			$collectionPath = preg_replace('#^\.#', '', $filesJsonPath);
			if ($collectionPath[0] !== '/') {
				throw new Exception('Unexpected file path');
			}
			if (!file_exists(DIR_COLLECTION . '/' . $state->collectionFolderName . $collectionPath)) {
				throw new Exception('Could not find files JSON');
			}
			$filesJsonPaths[] = $collectionPath;
		}

		$allFiles = [];
		foreach ($filesJsonPaths as $filesJsonPath) {
			$subFolder = ltrim(preg_replace('#/\.lipupini/\.files\.json$#', '', $filesJsonPath), '/');
			$filesJson = json_decode(file_get_contents(DIR_COLLECTION . '/' . $state->collectionFolderName . $filesJsonPath), true);
			foreach ($filesJson as $filename => $filedata) {
				if (!file_exists(DIR_COLLECTION . '/' . $state->collectionFolderName . '/' . $subFolder . '/' . $filename)) {
					throw new Exception('File mismatch in JSON data');
				}
				$extension = pathinfo($filename, PATHINFO_EXTENSION);
				if (!$extension) {
					continue;
				}
				$filepath = $subFolder ? $subFolder . '/' . $filename : ltrim($filename, '/');
				$allFiles[$filepath] = $filedata;
				$allFiles[$filepath]['date'] = $filedata['date'] ?? (new \DateTime)->setTimestamp(filemtime(DIR_COLLECTION . '/' . $state->collectionFolderName . '/' . $subFolder . '/' . $filename))->format(\DateTime::ATOM);

				if (in_array($extension, array_keys(ImagePlugin::$extMimes))) {
					$allFiles[$filepath]['medium'] = 'image';
					$allFiles[$filepath]['mime'] = ImagePlugin::$extMimes[$extension];
					$allFiles[$filepath]['cachePath'] = '/c/file/' . $state->collectionFolderName . '/large/' . $filepath;
				} else if (in_array($extension, array_keys(VideoPlugin::$extMimes))) {
					$allFiles[$filepath]['medium'] = 'video';
					$allFiles[$filepath]['mime'] = VideoPlugin::$extMimes[$extension];
					$allFiles[$filepath]['cachePath'] = '/c/file/' . $state->collectionFolderName . '/large/' . $filepath;
				} else if (in_array($extension, array_keys(AudioPlugin::$extMimes))) {
					$allFiles[$filepath]['medium'] = 'audio';
					$allFiles[$filepath]['mime'] = AudioPlugin::$extMimes[$extension];
					$allFiles[$filepath]['cachePath'] = '/c/file/' . $state->collectionFolderName . '/large/' . $filepath;
				} else if (in_array($extension, array_keys(MarkdownPlugin::$extMimes))) {
					$allFiles[$filepath]['medium'] = 'text';
					$allFiles[$filepath]['mime'] = MarkdownPlugin::$extMimes[$extension];
					$allFiles[$filepath]['cachePath'] = '/c/file/' . $state->collectionFolderName . '/original/' . $filepath;
				} else {
					throw new Exception('Unexpected file extension: ' . $extension);
				}
			}
		}

		foreach ($allFiles as $filename => $filedata) : ?>
	<entry>
		<id>https://<?php echo htmlentities(HOST) ?>/@<?php echo htmlentities($state->collectionFolderName) ?>/<?php echo htmlentities($filename) ?>.html</id>
		<title><?php echo $filename ?></title>
		<updated><?php echo $filedata['date'] ?></updated>
		<author>
			<name><?php echo htmlentities($state->collectionFolderName) ?></name>
			<uri>https://<?php echo htmlentities(HOST) ?>/@<?php echo htmlentities($state->collectionFolderName) ?></uri>
		</author>
		<content type="html">
			<![CDATA[
			<img src="https://<?php echo htmlentities(HOST . $filedata['cachePath']) ?>" alt="<?php echo $filename ?>">
			<div><?php echo $filedata['caption'] ?? $filename ?></div>
			]]>
		</content>
		<link rel="alternate" href="https://<?php echo htmlentities(HOST) ?>/@<?php echo htmlentities($state->collectionFolderName) ?>/<?php echo htmlentities($filename) ?>.html"/>
		<summary type="html"><?php echo $filedata['caption'] ?? $filename ?></summary>
		<?php echo '<media:content' ?>
			url="https://<?php echo htmlentities(HOST . $filedata['cachePath']) ?>"
			type="<?php echo $filedata['mime'] ?>" medium="<?php echo $filedata['medium'] ?>"
		/>'
	</entry>
		<?php
		endforeach;
	}
}
