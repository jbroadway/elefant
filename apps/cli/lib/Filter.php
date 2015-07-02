<?php

namespace cli;

class Filter {
	/**
	 * Replace underscores with spaces. Doesn't change
	 * capitalization.
	 *
	 * Usage:
	 *
	 *     {{field->name|cli\Filter::spaces}}
	 */
	public static function spaces ($name) {
		return str_replace ('_', ' ', $name);
	}

	/**
	 * Create a label from a field name. Replaces underscores,
	 * and capitalizes the first word.
	 *
	 * Usage:
	 *
	 *     {{field->name|cli\Filter::label}}
	 */
	public static function label ($name) {
		return ucfirst (str_replace ('_', ' ', $name));
	}
	
	/**
	 * Create a label from a field name. Replaces underscores,
	 * capitalizes all words, and sanitizes.
	 *
	 * Usage:
	 *
	 *     {{field->name|cli\Filter::title}}
	 */
	public static function title ($name) {
		return ucwords (str_replace ('_', ' ', $name));
	}
	
	/**
	 * Create a class name from a string, converting underscores
	 * to CamelCase.
	 *
	 * Usage:
	 *
	 *     {{appname|cli\Filter::camel}}
	 */
	public static function camel ($name) {
		return str_replace (' ', '', ucwords (str_replace ('_', ' ', $name)));
	}
}
