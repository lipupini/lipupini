<?php

namespace Lipupini\Translation;

use Lipupini\Exception;
use Lipupini\State;

class A {
	public static string $viewLanguage = 'en';
	public static string $path = __DIR__;
	private static array $_languageData = [];

	public static function z(string $key, bool $raw = false): string {
		$translation = static::_languageData()[$key] ?? $key;
		return $raw ? $translation : htmlentities($translation);
	}

	public static function initializeViewLanguages(State $system): void {
		// If these are both empty, we're using what was already set in config
		$setLanguage = $_GET['🌐'] ?? $_GET['lang'] ?? $_SESSION['lang'] ?? null;

		if (!$setLanguage) {
			return;
		}

		$languages = scandir($system->dirRoot . '/src/Frontend/Languages');

		if (!in_array($setLanguage . '.php', $languages)) {
			throw new Exception('Could not find l18n information');
		}

		static::$viewLanguage = $_SESSION['lang'] = $system->viewLanguage = $setLanguage;
	}

	private static function _languageData(): array {
		if (static::$_languageData) {
			return static::$_languageData;
		}

		return static::$_languageData = require(static::$path . '/' . static::$viewLanguage . '.php');
	}
}
