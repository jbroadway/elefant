<?php

/**
 * Exports translations into an app's lang folder.
 */

if (! $this->cli) {
	die ('Must be run from the command line.');
}

$page->layout = false;

if (! isset ($_SERVER['argv'][2])) {
	echo "Usage: elefant bundle-translations <appname>\n";
	die;
}

$app = $_SERVER['argv'][2];
if (! is_dir ('apps/' . $app)) {
	printf ("App not found: %s\n", $app);
	die;
}

if (! is_dir ('apps/' . $app . '/lang')) {
	mkdir ('apps/' . $app . '/lang');
}

if (! file_exists ('lang/_index.php')) {
	echo "Translation index file not found.\n";
	die;
}

// Build a list of strings from the app
$index = unserialize (file_get_contents ('lang/_index.php'));
$include = array ();

foreach ($index as $string) {
	foreach ($string['src'] as $source) {
		if (strpos ($source, 'apps/' . $app . '/') === 0) {
			$include[] = $string['orig'];
			break;
		}
	}
}

// Include and export each language
foreach ($i18n->languages as $lang) {
	$code = (! empty ($lang['locale']))
		? $lang['code'] . '_' . $lang['locale']
		: $lang['code'];

	require ('lang/' . $code . '.php');

	$export = array ();

	foreach ($include as $string) {
		if (isset ($this->lang_hash[$code][$string])) {
			$export[$string] = $this->lang_hash[$code][$string];
		}
	}

	asort ($export);

	$out = "<?php\n\nif (! isset (\$this->lang_hash['$code'])) {\n";
	$out .= "\t\$this->lang_hash['$code'] = array ();\n}\n\n";
	$out .= "\$this->lang_hash['$code'] = array_merge (\n\t";
	$out .= "\$this->lang_hash['$code'],\n\tarray (\n";
	$sep = '';
	foreach ($export as $k => $v) {
		$out .= sprintf (
			"%s\t\t'%s' => '%s'",
			$sep,
			str_replace ('\'', '\\\'', stripslashes ($k)),
			str_replace ('\'', '&apos;', stripslashes ($v))
		);
		$sep = ",\r";
	}
	$out .= "\n\t)\n);\n\n?>";

	file_put_contents ('apps/' . $app . '/lang/' . $code . '.php', $out);
}

printf ("Translations exported to apps/%s/lang/\n", $app);

?>