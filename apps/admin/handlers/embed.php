<?php

$page->template = false;
header ('Content-Type: application/json');

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

foreach ($embeds as $k => $e) {
	$embeds[$k]['fields'] = array ();
	foreach ($e as $field => $opts) {
		if ($field == 'label' || ! is_array ($opts)) {
			continue;
		}
		$embeds[$k]['fields'][$field] = array ();
		unset ($embeds[$k][$field]);
		foreach ($opts as $opt => $val) {
			if ($opt == 'require') {
				require_once ($val);
			} elseif ($opt == 'callback') {
				$embeds[$k]['fields'][$field]['values'] = call_user_func ($val);
			} else {
				$embeds[$k]['fields'][$field][$opt] = $val;
			}
		}
	}
}

echo json_encode ($embeds);

?>