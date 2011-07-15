<?php

$files = glob ('apps/*/conf/embed.php');
$embeds = array ();
foreach ($files as $file) {
	$embeds = array_merge ($embeds, parse_ini_file ($file, true));
}

function admin_embed_sort ($a, $b) {
	if ($a['label'] == $b['label']) {
		return 0;
	}
	return ($a['label'] < $b['label']) ? -1 : 1;
}

uasort ($embeds, 'admin_embed_sort');

info ($embeds);

?>