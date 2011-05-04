<?php

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

file_put_contents ('apps/' . $argv[1] . '/handlers/index.php', '<?php $page->template = \'' . $argv[1] . '/index\'; ?>');
file_put_contents ('apps/' . $argv[1] . '/views/index.html', '<p>{{ body|none }}</p>');

echo "Done.\n";

?>