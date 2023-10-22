<?php

namespace Module\Lipupini\L18n;

class A {
	public static string $viewLanguage = 'en';
	public static string $path = __DIR__;
	private static array $_languageData = [];

	public static function z(string $key, bool $raw = false): string {
		$translation = static::_languageData()[$key] ?? $key;
		return $raw ? $translation : htmlentities($translation);
	}

	private static function _languageData() {
		if (static::$_languageData) {
			return static::$_languageData;
		}

		return static::$_languageData = require(static::$path . '/L18n/' . static::$viewLanguage . '.php');
	}
}
