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