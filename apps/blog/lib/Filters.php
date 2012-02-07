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
 * Filter blog post titles for use in URLs. Makes them lowercase,
 * and changes any non A-Z or 0-9 character series into a dash.
 * Also removes any leading or trailing whitespace.
 */
function blog_filter_title ($title) {
	return trim (preg_replace ('/[^a-z0-9-]+/', '-', strtolower ($title)), ' -');
}

/**
 * Formats a date in the format YYYY-MM-DD HH:MM:SS into the
 * specifed format using `gmdate()`. The default format is
 * `F j, Y - g:ia`.
 */
function blog_filter_date ($ts, $format = 'F j, Y - g:ia') {
	$t = strtotime ($ts);
	return gmdate ($format, $t);
}

function blog_filter_csv_line ($line) {
	$o = '';
	foreach ($line as $field) {
		if (strlen ($field) > 50) {
			$field = substr ($field, 47) . '...';
		}
		$o .= '<td>' . Template::sanitize ($field) . '</td>';
	}
	return $o;
}

?>