<?php

/**
 * Outputs the admin toolbar if the user is an admin,
 * otherwise simply loads jQuery for other scripts that
 * may rely on it.
 */

if ($appconf['Scripts']['jquery_source'] === 'local') {
	echo "<script src=\"/js/jquery-1.8.2.min.js\"></script>\n";
} elseif ($appconf['Scripts']['jquery_source'] === 'google') {
	echo "<script src=\"//ajax.googleapis.com/ajax/libs/jquery/1.8.2/jquery.min.js\"></script>\n";
} else {
	echo '<script src="' . $appconf['Scripts']['jquery_source'] . "\"></script>\n";
}

if (User::is_valid () && User::is ('admin') && $page->preview == false) {
	echo $tpl->render ('admin/head');
}

?>