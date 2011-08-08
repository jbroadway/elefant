<?php

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