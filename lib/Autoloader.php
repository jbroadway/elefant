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
 * Autoloader for classes. Checks in the following order:
 *
 * 1. If `\` is found, using namespaces:
 * 1.1. Look for `apps/{{app}}/lib/{{class}}.php`
 * 1.2. Look for `apps/{{app}}/models/{{class}}.php`
 * 1.3. PSR-0 fallback, look in `lib/vendors/`
 * 2. Look for `lib/{{class}}.php`
 * 3. Glob for `{{models,lib}}/{{class}}.php` in all apps
 * 4. Return false
 *
 * For PSR-0 compatible frameworks, put them in `lib/vendors/`.
 */
function elefant_autoloader ($class) {
	$orig = $class;
	if (strpos ($class, '\\') !== false) {
		// Namespace is present
		list ($app, $class) = explode ('\\', $class, 2);
		$class = str_replace ('\\', DIRECTORY_SEPARATOR, $class);

		// Check for app\Class in lib and models folders
		if (file_exists ('apps/' . $app . '/lib/' . $class . '.php')) {
			require_once ('apps/' . $app . '/lib/' . $class . '.php');
			return true;
		} elseif (file_exists ('apps/' . $app . '/models/' . $class . '.php')) {
			require_once ('apps/' . $app . '/models/' . $class . '.php');
			return true;
		}

		// Fall back to PSR-0
		if (! empty ($app)) {
			$file = 'lib/vendor/' . ltrim ($app, '\\') . '/' . str_replace ('\\', '/', $class) . '.php';
		} else {
			$file = 'lib/vendor/' . str_replace ('\\', '/', ltrim ($class, '\\')) . '.php';
		}
		$file = str_replace ('_', '/', $file);
		if (file_exists ($file)) {
			require_once ($file);
			return true;
		}
	} elseif (file_exists ('lib/' . $class . '.php')) {
		// No namespace, check in lib/ first
		require_once ('lib/' . $class . '.php');
		return true;
	} elseif (file_exists ('lib/vendor/' . str_replace ('_', '/', $class) . '.php')) {
		// No namespace, check in lib/vendor/ next
		require_once ('lib/vendor/' . str_replace ('_', '/', $class) . '.php');
		return true;
	} else {
		// No namespace, check in app lib and models folders
		$res = glob ('apps/*/{models,lib}/' . $class . '.php', GLOB_BRACE);
		if (is_array ($res) && count ($res) > 0) {
			require_once ($res[0]);
			return true;
		}
	}
	if (count (spl_autoload_functions ()) > 1) {
		// Leave it to another autoloader
		return false;
	}
	throw new LogicException ("Class '$orig' not found.");
}

spl_autoload_register ('elefant_autoloader');

//and now we include the composer autoloader
require "vendor/autoload.php";

?>