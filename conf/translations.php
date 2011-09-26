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
 * This script builds a translation file for a specified language
 * by grabbing all the translatable text from source code files,
 * view templates, and layouts. You will find the output in a new
 * file named `lang/{{lang}}.php` where `{{lang}}` is the language
 * you specified when running the script. You can then use this
 * file to add translations for that language.
 *
 * Usage:
 *
 *     Usage: php conf/translations.php lang
 */

if (! $argv[1]) {
	echo "Usage: php conf/translations.php lang\n";
	exit;
}

$sources = array ('layouts/*.html', 'apps/*/views/*.html', 'apps/*/handlers/*.php');
$list = array ();

foreach ($sources as $source) {
	printf ("Parsing %s...\n", $source);
	$files = glob ($source);
	foreach ($files as $file) {
		$data = file_get_contents ($file);
		if (preg_match ('/\.html/', $file)) {
			// parse for {""} syntax
			preg_match_all ('/\{[\'"] ?(.*?) ?[\'"]\}/', $data, $matches);
			foreach ($matches[1] as $str) {
				$list[$str] = $str;
			}
		} else {
			// parse for i18n_getf?() syntax
			preg_match_all ('/i18n_getf? ?\([\'"](.*?)[\'"]\)/', $data, $matches);
			foreach ($matches[1] as $str) {
				$list[$str] = $str;
			}
		}
	}
}
asort ($list);

if (@file_exists ('lang/' . $argv[1] . '.php')) {
	printf ("File lang/%s.php already exists, saving backup to lang/%s.bak\n", $argv[1], $argv[1]);
	copy ('lang/' . $argv[1] . '.php', 'lang/' . $argv[1] . '.bak');
}

$out = sprintf ("<?php\n\n\$this->lang_hash['%s'] = array (\n", $argv[1]);
foreach ($list as $k => $v) {
	$v = str_replace ('\'', '\\\'', $v);
	$out .= sprintf ("\t'%s' => '%s',\n", $v, $v);
}
$out = substr ($out, 0, -2);
$out .= "\n);\n\n?>";

file_put_contents ('lang/' . $argv[1] . '.php', $out);
printf ("Translation list saved to lang/%s.php\n", $argv[1]);

?>