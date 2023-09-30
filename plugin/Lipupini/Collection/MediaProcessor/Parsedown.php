<?php

namespace Plugin\Lipupini\Collection\MediaProcessor;

class Parsedown extends \Parsedown {
	private function addTargetBlank(string $method, array $Excerpt): array|null {
		$return = parent::$method($Excerpt);
		if (!$return) {
			return null;
		}
		$return['element']['attributes']['target'] = '_blank';
		$return['element']['attributes']['rel'] = 'noopener noreferrer';
		return $return;
	}

	protected function inlineUrl($Excerpt) {
		return $this->addTargetBlank('inlineUrl', $Excerpt);
	}

	protected function inlineLink($Excerpt) {
		return $this->addTargetBlank('inlineLink', $Excerpt);
	}

	protected function inlineUrlTag($Excerpt) {
		return $this->addTargetBlank('inlineUrlTag', $Excerpt);
	}
}
