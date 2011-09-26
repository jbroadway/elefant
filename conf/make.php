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
 * This script generates the scaffolding for a new Elefant
 * app in the apps folder. This includes the folder structure
 * of an app, as well as a default config.php, default index
 * and admin handlers, and a default index view template.
 *
 * Usage:
 *
 *     Usage: php conf/make.php appname
 */

if (php_sapi_name () !== 'cli') {
	die ("For use on the command line only.\n");
}

if ($argc == 1) {
	die ("Usage: php conf/make.php appname\n");
}

if (basename (getcwd ()) == 'conf' || basename (getcwd ()) == 'apps') {
	chdir ('..');
}

mkdir ('apps/' . $argv[1] . '/conf', 0755, true);
mkdir ('apps/' . $argv[1] . '/forms', 0755, true);
mkdir ('apps/' . $argv[1] . '/handlers', 0755, true);
mkdir ('apps/' . $argv[1] . '/lib', 0755, true);
mkdir ('apps/' . $argv[1] . '/models', 0755, true);
mkdir ('apps/' . $argv[1] . '/views', 0755, true);

file_put_contents ('apps/' . $argv[1] . '/handlers/index.php', sprintf (
	"<?php\n\n\$page->title = '%s home';\n\$page->template = '%s/index';\n\n?>",
	ucfirst ($argv[1]),
	$argv[1]
));
file_put_contents ('apps/' . $argv[1] . '/handlers/admin.php', sprintf (
	"<?php\n\nif (! User::require_admin ()) {\n\t\$this->redirect ('/admin');\n}\n\n\$page->title = '%s admin';\n\$page->layout = 'admin';\n\n?>",
	ucfirst ($argv[1])
));
file_put_contents ('apps/' . $argv[1] . '/views/index.html', '<p>{{ body|none }}</p>');
file_put_contents ('apps/' . $argv[1] . '/conf/config.php', sprintf (
	"; <?php\n\n[Admin]\n\nhandler = %s/admin\nname = %s\n\n; */ ?>",
	$argv[1],
	ucfirst ($argv[1])
));

echo "Done.\n";

?>