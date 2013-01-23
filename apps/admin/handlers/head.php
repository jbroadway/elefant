<?php

/**
 * Outputs the admin toolbar if the user is an admin,
 * otherwise simply loads jQuery for other scripts that
 * may rely on it.
 */

if ($appconf['Scripts']['jquery_source'] === 'local') {
	$page->add_script ('/js/jquery-1.7.1.min.js');
} elseif ($appconf['Scripts']['jquery_source'] === 'google') {
	$page->add_script ('<script src="//ajax.googleapis.com/ajax/libs/jquery/1.7.1/jquery.min.js"></script>');
} else {
	$page->add_script ('<script src="' . $appconf['Scripts']['jquery_source'] . '"></script>');
}

if (User::is_valid () && User::is ('admin') && $page->preview == false) {
	echo $tpl->render ('admin/head');
}

?>