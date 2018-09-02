<?php

/**
 * Elefant CMS - http://www.elefantcms.com/
 *
 * Copyright (c) 2011 Johnny Broadway
 * 
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 * 
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 * 
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */

/**
 * I18n is a class that makes it easier to add multiple language support
 * to PHP programs.  It is lightweight and not very sophisticated, attempting
 * to keep it straight-forward while emulating some of the more elegant features
 * of other internationalization systems, such as gettext.
 *
 * Translation files are PHP, so they benefit from language-level caching,
 * and string replacement is simply an associative array lookup.
 */
class I18n {
	/**
	 * The language code, corresponding to the name of the language
	 * file.
	 */
	public $language;

	/**
	 * The locale code, corresponding to the name of the country
	 * to localize numbers and dates for.
	 */
	public $locale;

	/**
	 * The location of the language files.
	 */
	public $directory = 'lang';

	/**
	 * The language hash, a key/value list.
	 */
	public $lang_hash = array ();

	/**
	 * The language fallback order.
	 */
	public $hash_order = array ();

	/**
	 * The charset of the current language, which can be used to tell
	 * the browser, or any language-aware PHP functions, which to use.
	 */
	public $charset = 'UTF-8';

	/**
	 * The full name of language in use (ie. 'English' for 'en').
	 */
	public $fullname = '';

	/**
	 * The full date format to send to jQuery.localize.
	 */
	public $day_date_format = 'dddd, mmmm d, yyyy';

	/**
	 * The full date format to send to jQuery.localize.
	 */
	public $short_day_date_format = 'ddd, mmm d';

	/**
	 * The full date format to send to jQuery.localize.
	 */
	public $date_format = 'mmmm d, yyyy';

	/**
	 * The short date format to send to jQuery.localize.
	 */
	public $short_format = 'mmm d';

	/**
	 * The time format to send to jQuery.localize.
	 */
	public $time_format = 'h:MMa';
	
	/**
	 * The year format to send to jQuery.localize.
	 */
	public $year_format = 'yyyy';

	/**
	 * Contains fallback text replacements.
	 */
	public $fallbacks = array ();

	/**
	 * 2-D list of available languages, retrieved from `getLanguages()`.
	 */
	public $languages = array ();

	/**
	 * The name of the language cookie for `$negotiation=cookie`.
	 */
	public $cookieName = 'lang';

	/**
	 * Tells the controller that the first level of the URL is the language
	 * and not the page ID when `$negotiation=url`.
	 */
	public $url_includes_lang = false;

	/**
	 * If `$url_includes_lang` is true, this will include the request with
	 * the language stripped out.
	 */
	public $new_request_uri = '';

	/**
	 * If `$url_includes_lang` is true, this will include the stripped out
	 * part of the URL. Otherwise it will be empty. This is useful for
	 * including language URL prefixes in views via `{{ i18n.prefix }}`.
	 */
	public $prefix = '';

	/**
	 * The negotiation method used to determine the current language.
	 */
	public $negotiation = 'url';

	/**
	 * If an error occurs during any portion of this class, this
	 * will contain the message.
	 */
	public $error;

	/**
	 * Includes the appropriate language file in the provided `$directory`.
	 * This file is intended to fill out the `$lang_hash` array.
	 * `$negotiationMethod` determines the method whereby the language of
	 * choice is determined. See the `negotiate()` method for more info on this.
	 */
	public function __construct ($directory = 'lang', $conf = array ()) {
		$this->directory = $directory;
		if (isset ($conf['negotiation_method'])) {
			$this->negotiation = $conf['negotiation_method'];
		}
		if (isset ($conf['cookie_name'])) {
			$this->cookieName = $conf['cookie_name'];
		}

		$this->languages = $this->getLanguages ();
		if (! is_array ($this->languages)) {
			$this->languages = array ();
			return;
		} else {
			foreach ($this->languages as $lang => $props) {
				if ($props['default'] == true) {
					$this->default = $lang;
					break;
				}
			}
		}
		$this->language = $this->negotiate ();
		$this->charset = $this->languages[$this->language]['charset'];
		$this->fullname = $this->languages[$this->language]['name'];
		$this->date_format = isset ($this->languages[$this->language]['date_format']) ? $this->languages[$this->language]['date_format'] : 'mmmm d, yyyy';
		$this->short_format = isset ($this->languages[$this->language]['short_format']) ? $this->languages[$this->language]['short_format'] : 'mmm d';
		$this->time_format = isset ($this->languages[$this->language]['time_format']) ? $this->languages[$this->language]['time_format'] : 'h:MMa';
		$this->setLocale ();
		$this->getIndex ();

		if (extension_loaded ('mbstring')) {
			mb_internal_encoding ($this->charset);
		}
	}

	/**
	 * Calls `setlocale()` based on the current language.
	 */
	public function setLocale () {
		$params = array (LC_TIME); // | LC_MONETARY | LC_CTYPE | LC_COLLATE);
		if (! empty ($this->languages[$this->language]['locale'])) {
			$params[] = $this->languages[$this->language]['code'] . '_' . strtoupper ($this->languages[$this->language]['locale']) . '.' . str_replace ('ISO-', 'ISO', $this->charset);
			$params[] = $this->languages[$this->language]['code'] . '_' . strtoupper ($this->languages[$this->language]['locale']);
		}
		$params[] = $this->languages[$this->language]['code'];
		return call_user_func_array ('setlocale', $params);
	}

	/**
	 * Includes the language index.
	 */
	public function getIndex () {
		if ((! empty ($this->language)) && (file_exists ($this->directory . '/' . $this->language . '.php'))) {
			include_once ($this->directory . '/' . $this->language . '.php');
		}

		$this->hash_order = array ($this->language);
		$curlang = $this->language;

		while ($this->languages[$curlang]['fallback']) {
			$curlang = $this->languages[$curlang]['fallback'];
			$this->hash_order[] = $curlang;
			include_once ($this->directory . '/' . $curlang . '.php');
		}
	}

	/**
	 * Includes the language index for an app.
	 */
	public function initApp ($app) {
		if ((! empty ($this->language)) && (file_exists ('apps/' . $app . '/lang/' . $this->language . '.php'))) {
			include_once ('apps/' . $app . '/lang/' . $this->language . '.php');
		}

		foreach ($this->hash_order as $curlang) {
			if (file_exists ('apps/' . $app . '/lang/' . $curlang . '.php')) {
				include_once ('apps/' . $app . '/lang/' . $curlang . '.php');
			}
		}
	}

	/**
	 * Takes a string, serializes it to generate a key, and performs
	 * a key/value lookup on the `$lang_hash` array.  Returns the value found,
	 * or the original string if not found.  This is the method used in I18n
	 * to return translated text.
	 */
	public function get ($original = '') {
		if (empty ($original)) {
			return '';
		}

		foreach ($this->hash_order as $lang) {
			if (! empty ($this->lang_hash[$lang][$original])) {
				return $this->lang_hash[$lang][$original];
			}
		}
		return $original;
	}

	/**
	 * Takes a string, serializes it to generate a key, and performs
	 * a key/value lookup on the `$lang_hash` array.  Returns the value found,
	 * or the original string if not found.  This method is similar to
	 * `get()`, except it uses `vsprintf()` to insert values.
	 * If you pass an array as the second value, it will use that instead of
	 * however many additional arguments you fed it.  This is handy
	 * because if you already have all your values in an array, you can
	 * simply say `getf($original, $array)` instead of `getf($original, $array[0],
	 * $array[1], $array[2])`.
	 */
	public function getf () {
		$args = func_get_args ();

		$original = array_shift ($args);

		if (! $original) {
			return '';
		}

		if (is_array ($args[0])) {
			$args = $args[0];
		}

		foreach (array_keys ($this->lang_hash) as $lang) {
			if (! empty ($this->lang_hash[$lang][$original])) {
				return vsprintf ($this->lang_hash[$lang][$original], $args);
			}
		}

		return vsprintf ($original, $args);
	}

	/**
	 * Returns a 2-D array from the specified language file, which
	 * is an INI file.  Each section name in the file corresponds to a
	 * different available language.  Keys in each section include
	 * 'name', 'code', 'locale', 'charset', 'fallback', and 'default'.
	 */
	public function getLanguages () {
		if (file_exists ($this->directory . '/languages.php')) {
			return parse_ini_file ($this->directory . '/languages.php', true);
		} else {
			$this->error = 'Language file (' . $this->directory . '/languages.php) does not exist!';
			return false;
		}
	}

	/**
	 * Returns the preferred language of the current visitor.
	 * If the `$method` is `'http'` then it uses the HTTP Accept-Language
	 * string for this info.  If the `$method` is `'cookie'` it uses a
	 * cookie (specified by the `$cookieName` property) to determine,
	 * if the `$method` is `'subdomain'` then it looks for it in the
	 * subdomain of the site (e.g., `en.sitename.com`, `fr.sitename.com`),
	 * and if the `$method` is `'url'` then it uses the start of the URL
	 * to determine the language (e.g., `/fr/` or `/en/`).
	 * Default is `'url'`.
	 */
	public function negotiate ($method = false) {
		if (! $method) {
			$method = $this->negotiation;
		}

		if ($method === 'http') {
			$accepted = array ();
			$keys = explode (',', $_SERVER['HTTP_ACCEPT_LANGUAGE']);

			foreach ($keys as $lang) {
				// Remove trailing ";q=" data
				if ($pos = strpos ($lang, ';')) {
					$lang = trim (substr ($lang, 0, $pos));
				}

				// Check for country code
				if ($pos = strpos ($lang, '-')) {
					list ($lang, $cn) = explode ('-', $lang);

					if ($lang === 'i') {
						$lang = $cn;
						unset ($cn);
					}

					if (isset ($cn)) {
						$cn = strtolower ($cn);
						if (isset ($accepted[$lang]) && is_array ($accepted[$lang])) {
							$accepted[$lang][] = $cn;
						} else {
							$accepted[$lang] = array ($cn);
						}
					} elseif (! isset ($accepted[$lang]) || ! is_array ($accepted[$lang])) {
						$accepted[$lang] = array ('');
					}
				} else {
					if (isset ($accepted[$lang]) && is_array ($accepted[$lang])) {
						$accepted[$lang][] = '';
					} else {
						$accepted[$lang] = array ('');
					}
				}
			}

			foreach ($accepted as $lang => $cnlist) {
				foreach ($cnlist as $cn) {
					if (! empty ($cn) && $cn !== $lang) {
						$name = strtolower ($lang . '_' . $cn);
					} else {
						$name = strtolower ($lang);
					}
					if (isset ($this->languages[$name])) {
						// Found
						return $name;
					}
				}
			}

		} elseif ($method === 'cookie') {
			if (
				isset ($_COOKIE[$this->cookieName]) &&
				isset ($this->languages[$_COOKIE[$this->cookieName]])
			) {
				return $_COOKIE[$this->cookieName];
			}

		} elseif ($method === 'url') {
			if (preg_match ('/^\/(' . join ('|', array_keys ($this->languages)) . ')\/?$/', $_SERVER['REQUEST_URI'], $matches)) {
				// matched /lang or /lang/ -> /lang [language=lang]
				$this->url_includes_lang = true;
				$this->new_request_uri = rtrim ($_SERVER['REQUEST_URI'], '/');
				$this->prefix = '/' . $matches[1];
				return $matches[1];
			} elseif (preg_match ('/^\/(' . join ('|', array_keys ($this->languages)) . ')\//', $_SERVER['REQUEST_URI'], $matches)) {
				// matched /lang/page-id -> /page-id [language=lang]
				$this->url_includes_lang = true;
				$this->new_request_uri = preg_replace ('/^\/' . $matches[1] . '/', '', $_SERVER['REQUEST_URI']);
				$this->prefix = '/' . $matches[1];
				return $matches[1];
			}

		} elseif ($method === 'subdomain') {
			if (preg_match ('/^(' . join ('|', array_keys ($this->languages)) . ')\./', Appconf::admin ('Site Settings', 'site_domain'), $matches)) {
				return $matches[1];
			}
		}

		return $this->default;
	}

	/**
	 * Export an array of strings into a JavaScript block that calls
	 * `$.i18n_add()` which can be passed to `$page->add_script()`.
	 * Can also be called from a view template on an array of strings
	 * like this: `{{ my_array|I18n::export }}`.
	 *
	 * Note that you may pass either a single array, or each string
	 * as a separate parameter, which allows you to omit the `array()`
	 * wrapper when calling it in PHP code.
	 */
	public static function export ($strings) {
		if (func_num_args () > 1 || ! is_array ($strings)) {
			$strings = func_get_args ();
		}

		$out = "<script>\$(function(){\$.i18n_append({\n";
		$sep = '';
		foreach ($strings as $string) {
			$out .= $sep . "\t'" . str_replace ("'", "\\'", $string) . "': '" . str_replace ("'", "\\'", __ ($string)) . "'";
			$sep = ",\n";
		}
		return $out . "\n});});</script>\n";
	}

	/**
	 * Applies the specified class and format to a date for
	 * the `date()`, `short_date()`, `time()`, `date_time()` and
	 * `short_date_time()` methods.
	 */
	private static function _date ($date, $class, $format) {
		$date = is_numeric ($date) ? $date : strtotime ($date);
		return sprintf (
			'<time class="%s" datetime="%s">%s</time>',
			$class,
			gmdate ('Y-m-d\TH:i:sP', $date),
			gmdate ($format, $date)
		);
	}

	/**
	 * Filter for outputting dates. Used with the jQuery
	 * localize plugin to convert dates into the current user's
	 * time zone.
	 *
	 * Usage:
	 *
	 *     {{ date_value|I18n::date }}
	 */
	public static function date ($date) {
		return self::_date ($date, 'date', 'F j, Y');
	}

	/**
	 * Filter for outputting a shortened date. Used with the
	 * jQuery localize plugin to convert dates into the
	 * current user's time zone.
	 *
	 * Usage:
	 *
	 *     {{ date_value|I18n::short_date }}
	 */
	public static function short_date ($date) {
		return self::_date ($date, 'shortdate', 'M j');
	}

	/**
	 * Filter for outputting a shortened date with year included.
	 * Used with the jQuery localize plugin to convert dates into the
	 * current user's time zone.
	 *
	 * Usage:
	 *
	 *     {{ date_value|I18n::short_date_year }}
	 */
	public static function short_date_year ($date) {
		return self::_date ($date, 'shortdateyear', 'M j, Y');
	}

	/**
	 * Filter for outputting a shortened day and date. Used with the
	 * jQuery localize plugin to convert dates into the
	 * current user's time zone.
	 *
	 * Usage:
	 *
	 *     {{ date_value|I18n::day_date }}
	 */
	public static function day_date ($date) {
		return self::_date ($date, 'daydate', 'l, F j, Y');
	}

	/**
	 * Filter for outputting a shortened day and date. Used with the
	 * jQuery localize plugin to convert dates into the
	 * current user's time zone.
	 *
	 * Usage:
	 *
	 *     {{ date_value|I18n::short_day_date }}
	 */
	public static function short_day_date ($date) {
		return self::_date ($date, 'shortdaydate', 'D, M j');
	}

	/**
	 * Filter for outputting times. Used with the jQuery
	 * localize plugin to convert dates into the current user's
	 * time zone.
	 *
	 * Usage:
	 *
	 *     {{ date_value|I18n::time }}
	 */
	public static function time ($date) {
		return self::_date ($date, 'time', 'g:ia');
	}

	/**
	 * Filter for outputting date and time. Used with the jQuery
	 * localize plugin to convert dates into the current user's
	 * time zone.
	 *
	 * Usage:
	 *
	 *     {{ date_value|I18n::date_time }}
	 */
	public static function date_time ($date) {
		return self::_date ($date, 'datetime', 'F j, Y - g:ia');
	}

	/**
	 * Filter for outputting a shortened date and time. Used
	 * with the jQuery localize plugin to convert dates into
	 * the current user's time zone.
	 *
	 * Usage:
	 *
	 *     {{ date_value|I18n::day_date_time }}
	 */
	public static function day_date_time ($date) {
		return self::_date ($date, 'daydatetime', 'l, F j - g:ia');
	}

	/**
	 * Filter for outputting a shortened date and time. Used
	 * with the jQuery localize plugin to convert dates into
	 * the current user's time zone.
	 *
	 * Usage:
	 *
	 *     {{ date_value|I18n::short_date_time }}
	 */
	public static function short_date_time ($date) {
		return self::_date ($date, 'shortdatetime', 'M j - g:ia');
	}

	/**
	 * Filter for outputting a shortened date and time with year.
	 * Used with the jQuery localize plugin to convert dates into
	 * the current user's time zone.
	 *
	 * Usage:
	 *
	 *     {{ date_value|I18n::short_date_year_time }}
	 */
	public static function short_date_year_time ($date) {
		return self::_date ($date, 'shortdateyeartime', 'M j, Y - g:ia');
	}

	/**
	 * Filter for outputting a shortened date and time. Used
	 * with the jQuery localize plugin to convert dates into
	 * the current user's time zone.
	 *
	 * Usage:
	 *
	 *     {{ date_value|I18n::short_day_date_time }}
	 */
	public static function short_day_date_time ($date) {
		return self::_date ($date, 'shortdaydatetime', 'D, M j - g:ia');
	}
}

/**
 * Helper function available globally. Alias of `I18n::get()`.
 */
function i18n_get ($original = '') {
	return $GLOBALS['i18n']->get ($original);
}

/**
 * Helper function available globally. Alias of `I18n::getf()`.
 */
function i18n_getf () {
	$args = func_get_args ();
	return call_user_func_array (array ($GLOBALS['i18n'], 'getf'), $args);
}

/**
 * Helper function available globally. Alias of either `I18n::get()`
 * and `I18n::getf()` depending on whether it's called with one
 * parameter or several.
 */
function __ ($original = '') {
	if (func_num_args () === 1) {
		return $GLOBALS['i18n']->get ($original);
	}
	$args = func_get_args ();
	return call_user_func_array (array ($GLOBALS['i18n'], 'getf'), $args);
}
