<?php

namespace Plugin\Lipupini\Collection\Render;

use Plugin\Lipupini\State;
use System\Lipupini;
use System\Plugin;

class Atom extends Plugin {
	public function start(State $state): State {
		if (empty($state->collectionFolderName)) {
			return $state;
		}

		if (!Lipupini::getClientAccept('AtomXML')) {
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
		echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n"; ?>
<feed xmlns="http://www.w3.org/2005/Atom" xmlns:media="http://search.yahoo.com/mrss/">
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
		/*$collectionPath = DIR_COLLECTION . '/' . $state->collectionFolderName;
		$dir = new \DirectoryIterator('glob://' . $collectionPath . '/.lipupini/*.json');
		foreach ($dir as $fileinfo) {
			$sourceFile = $collectionPath . '/' . preg_replace('#\.json$#', '', $fileinfo->getFilename());
			if (!file_exists($sourceFile)) {
				continue;
			}
			var_dump($sourceFile);
		}*/
		?>
	<entry>
		<id>https://localhost/@example/cat-hat.jpg.html</id>
		<title>Hat Cat</title>
		<updated>2023-09-21T11:18:18.000Z</updated>
		<author>
			<name>example</name>
			<uri>https://localhost/@example</uri>
		</author>
		<content type="html">
			<![CDATA[
<img src="https://localhost/c/file/example/cat-hat.jpg" alt="Hat Cat">
<p>Hat Cat</p>
			]]>
		</content>
		<link rel="alternate" href="https://localhost/@example/cat-hat.jpg.html"/>
		<summary type="html">Hat Cat</summary>
		<media:content
			url="https://localhost/c/file/example/cat-hat.jpg"
			type="image/jpeg" medium="image"
		/>
	</entry>
		<?php
	}
}