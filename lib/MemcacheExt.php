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
 * Extends Memcache with a method that takes a key, timeout, and a
 * function that will be called to produce the value if it is not
 * found in the cache.
 *
 * Based on Zane Ashby's idea posted here:
 *
 * [http://demonastery.org/72/tiny-memcached-wrapper/](http://demonastery.org/72/tiny-memcached-wrapper/)
 */
class MemcacheExt extends Memcache {
	/**
	 * Takes a callback function that generates the value 
	 * if it's not found in the cache.
	 *
	 * - `$key` - Cache key
	 * - `$timeout` - Seconds to cache for
	 * - `$function` - Callback function to generate cache data
	 *
	 * Returns the data from cache, or from the callback and caches the results
	 * for the next call.
	 */
	public function cache ($key, $timeout, $function) {
		if (($val = $this->get ($key)) === false) {
			if (is_callable ($function)) {
				$val = call_user_func ($function);
			} else {
				$val = $function;
			}
			$this->set ($key, $val, 0, $timeout);
		}
		return $val;
	}
}

?>