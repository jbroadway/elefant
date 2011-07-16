<?php

$page->template = false;
header ('Content-Type: application/json');

if (! User::require_admin ()) {
	echo json_encode (array ());
	return;
}

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

foreach ($embeds as $k => $e) {
	$embeds[$k]['handler'] = $k;
	$embeds[$k]['fields'] = array ();
	foreach ($e as $field => $opts) {
		if ($field == 'label' || ! is_array ($opts)) {
			continue;
		}
		$embeds[$k]['fields'][$field] = array ('name' => $field);
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

usort ($embeds, 'admin_embed_sort');

echo json_encode ($embeds);

?>