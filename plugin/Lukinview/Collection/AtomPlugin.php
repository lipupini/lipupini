<?php

namespace Plugin\Lukinview\Collection;

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
