<?php

/**
 * Outputs the admin toolbar if the user is an admin,
 * otherwise simply loads jQuery for other scripts that
 * may rely on it.
 */

echo "<script src=\"/js/jquery-1.7.1.min.js\"></script>\n";

if (User::is_valid () && User::is ('admin') && $page->preview == false) {
	echo $tpl->render ('admin/head');
}

?>