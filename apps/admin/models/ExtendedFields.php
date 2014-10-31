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
 * Model for managing extended fields for ExtendedModel classes.
 *
 * Fields:
 *
 * id
 * class
 * sort
 * name
 * label
 * type
 * required
 * options
 */
class ExtendedFields extends Model {
	public $table = '#prefix#extended_fields';

	/**
	 * Get a list of extended fields for the specified class.
	 */
	public static function for_class ($class) {
		return self::query ()
			->where ('class', $class)
			->order ('sort', 'asc')
			->fetch_orig ();
	}

	/**
	 * Get the next number to use for sorting.
	 */
	public static function next_sort ($class) {
		return self::query ()
			->where ('class', $class)
			->count ();
	}
}
