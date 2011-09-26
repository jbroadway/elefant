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
 * Autoloader for classes. Checks in lib and models folders.
 */
function elefant_autoloader ($class) {
	if (strpos ($class, '\\') !== false) {
		list ($app, $class) = explode ('\\', $class, 2);
		if (@file_exists ('apps/' . $app . '/lib/' . $class . '.php')) {
			require_once ('apps/' . $app . '/lib/' . $class . '.php');
			return true;
		} elseif (@file_exists ('apps/' . $app . '/models/' . $class . '.php')) {
			require_once ('apps/' . $app . '/models/' . $class . '.php');
			return true;
		}
	} elseif (file_exists ('lib/' . $class . '.php')) {
		require_once ('lib/' . $class . '.php');
		return true;
	} else {
		$res = glob ('apps/*/{models,lib}/' . $class . '.php', GLOB_BRACE);
		if (is_array ($res) && count ($res) > 0) {
			require_once ($res[0]);
			return true;
		}
	}
	return false;
}

spl_autoload_register ('elefant_autoloader');

?>