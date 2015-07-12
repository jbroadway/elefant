<?php

/**
 * Returns a JSON-encoded list of all dynamically embeddable
 * objects to the WYSIWYG editor. Parses `conf/embed.php` files
 * from each app to build this list. Also applies any callbacks
 * found to grab a list of values for select box inputs.
 */

$page->layout = false;
header ('Content-Type: application/json');

if (! User::require_admin ()) {
	echo json_encode (array ());
	return;
}

$files = glob ('apps/*/conf/embed.php');
$embeds = array ();
foreach ($files as $file) {
	$ini = parse_ini_file ($file, true);
	if (is_array ($ini)) {
		$embeds = array_merge ($embeds, $ini);
	}
}

function admin_embed_sort ($a, $b) {
	if ($a['label'] == $b['label']) {
		return 0;
	}
	return ($a['label'] < $b['label']) ? -1 : 1;
}

foreach ($embeds as $k => $e) {
	if (isset ($embeds[$k]['acl'])) {
		$acl = preg_split ('/, ?/', $embeds[$k]['acl']);
		if (! call_user_func_array ('User::require_acl', $acl)) {
			unset ($embeds[$k]);
			continue;
		}
	}
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
				if (file_exists ($val)) {
					require_once ($val);
				}
			} elseif ($opt == 'callback') {
				try {
					$embeds[$k]['fields'][$field]['values'] = call_user_func ($val);
				} catch (Exception $e) {
					error_log (sprintf ('Invalid embed callback %s() for handler %s', $val, $k));
					unset ($embeds[$k]);
				}
			} else {
				$embeds[$k]['fields'][$field][$opt] = $val;
			}
		}
	}
}

usort ($embeds, 'admin_embed_sort');

echo json_encode ($embeds);
