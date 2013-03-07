<?php

/**
 * Changes the default layout template.
 */

if (! User::require_admin ()) {
	$this->redirect ('/admin');
}

$confdata = file_get_contents ('conf/config.php');
$confdata = str_replace (
	'default_layout = "' . conf ('General', 'default_layout') . '"',
	'default_layout = "' . $_GET['layout'] . '"',
	$confdata
);
file_put_contents ('conf/config.php', $confdata);

$this->add_notification (__ ('Default layout updated.'));
$this->redirect ('/designer');

?>