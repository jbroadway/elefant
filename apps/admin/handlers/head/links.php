<?php

/**
 * Returns a list of admin tools links to the admin header
 * linking to all the admin handlers for each app that
 * exposes them.
 */

function admin_head_links_sort ($a, $b) {
	if ($a['name'] == $b['name']) {
		return 0;
	}
	return ($a['name'] < $b['name']) ? -1 : 1;
}

function admin_is_compatible ($appconf) {
        if (isset ($appconf['Admin']['platforms'])) {
                $device_list = explode (',', $appconf['Admin']['platforms']);
                foreach ($device_list as $device) {
                        if (detect (trim ($device))) {
                                return true;
                        }
                }
        } else {
                // No Admin/platforms conf entry so assume app is compatible
                return true;
        }
        return false;
}

$page->layout = false;

if (! User::require_admin ()) {
	return;
}

$custom_tools = file_exists ('conf/tools.php') ? parse_ini_file ('conf/tools.php', true) : false;
if ($custom_tools === false) {
	$tools = array ('admin/pages' => array ('handler' => 'admin/pages', 'name' => __ ('All Pages'), 'class' => false));

	$ver = $this->installed ('elefant', ELEFANT_VERSION);
	if ($ver === true) {
		$tools = array (
			'admin/pages' => array (
				'handler' => 'admin/pages',
				'name' => __ ('All Pages'),
				'class' => false
			)
		);
	} else {
		$tools = array (
			'admin/upgrade' => array (
				'handler' => 'admin/upgrade',
				'name' => __ (' Click to upgrade'),
				'class' => 'needs-upgrade'
			),
			'admin/pages' => array (
				'handler' => 'admin/pages',
				'name' => __ ('All Pages')
			)
		);
	}

	if (! Appconf::admin ('General', 'show_all_pages') || ! User::require_acl ('admin/pages')) {
		unset ($tools['admin/pages']);
	}
}

$apps = DB::pairs ('select * from #prefix#apps');

if ($custom_tools !== false) {
	foreach ($custom_tools as $column => $tools) {
		foreach ($tools as $handler => $name) {
			$custom_tools[$column][$handler] = array (
				'handler' => $hander,
				'name' => $name,
				'class' => false
			);
		}
	}
	
	$tools = $custom_tools;
	$custom_tools = true;

} else {
	$res = glob ('apps/*/conf/config.php');
	foreach ($res as $file) {
		$app = preg_replace ('/^apps\/(.*)\/conf\/config\.php$/i', '\1', $file);

		if (! User::require_acl ($app)) {
			// Can't access this app
			continue;
		}

		$appconf = parse_ini_file ($file, true);

		if (! admin_is_compatible ($appconf)) {
			// App not compatible with this platform
			continue;
		}

		if (isset ($appconf['Admin']['handler'])) {
			if (! preg_match ('/\/(admin|index)$/', $appconf['Admin']['handler']) && ! User::require_acl ($appconf['Admin']['handler'])) {
				// A non /admin or /index handler should get an additional
				// access check (e.g., admin/versions).
				continue;
			}

			if (isset ($appconf['Admin']['install'])) {
				$ver = $this->installed ($app, $appconf['Admin']['version']);

				if ($ver === true) {
					// installed
					$tools[$appconf['Admin']['handler']] = $appconf['Admin'];
					$tools[$appconf['Admin']['handler']]['class'] = false;
				} elseif ($ver === false) {
					// not installed
					$appconf['Admin']['name'] .= ' (' . __ ('click to install') . ')';
					$tools[$appconf['Admin']['install']] = $appconf['Admin'];
					$tools[$appconf['Admin']['install']]['class'] = 'not-installed';
				} else {
					// needs upgrade
					$appconf['Admin']['name'] .= ' (' . __ ('click to upgrade') . ')';
					$tools[$appconf['Admin']['upgrade']] = $appconf['Admin'];
					$tools[$appconf['Admin']['upgrade']]['class'] = 'needs-upgrade';
				}
			} else {
				// no installer, as you were
				$tools[$appconf['Admin']['handler']] = $appconf['Admin'];
				$tools[$appconf['Admin']['handler']]['class'] = false;
			}
		}
	}
	uasort ($tools, 'admin_head_links_sort');
}

$out = array (
	'name' => Product::name (),
	'logo' => Product::logo_toolbar (),
	'links' => $tpl->render ('admin/head/links', array (
		'user' => User::val ('name'),
		'tools' => $tools,
		'custom' => $custom_tools
	))
);

$page->layout = false;
header ('Content-Type: application/json');
echo json_encode ($out);

?>