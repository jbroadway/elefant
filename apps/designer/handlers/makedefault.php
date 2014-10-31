<?php

/**
 * Changes the default layout template.
 */

$this->require_acl ('admin', 'designer');

$confdata = file_get_contents ('conf/' . ELEFANT_ENV . '.php');

if (strpos ($confdata, 'default_layout = "' . conf ('General', 'default_layout') . '"') !== false) {
	// default_layout setting found
	$confdata = str_replace (
		'default_layout = "' . conf ('General', 'default_layout') . '"',
		'default_layout = "' . $_GET['layout'] . '"',
		$confdata
	);

} elseif (strpos ($confdata, '[General]') !== false) {
	// no default_layout setting found, append to [General] section
	$confdata = str_replace (
		'[General]',
		"[General]\n\ndefault_layout = \"" . $_GET['layout'] . "\"\n\n",
		$confdata
	);

} else {
	// no [General] section found, add section and setting before closing line
	$confdata = str_replace (
		'; */',
		"\n\n[General]\n\ndefault_layout = \"" . $_GET['layout'] . "\"\n\n; */",
		$confdata
	);
}

file_put_contents ('conf/' . ELEFANT_ENV . '.php', $confdata);

$this->add_notification (__ ('Default layout updated.'));
$this->redirect ('/designer');
