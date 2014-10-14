<?php

namespace admin;

use \Appconf;
use \DB;
use \User;

/**
 * Fetches apps for the Elefant toolbar.
 */
class Toolbar {
	public static $custom_tools_file = 'conf/tools.php';
	public static $custom_tools = null;

	public static $tools = null;

	public static $installed_apps = null;

	/**
	 * Callback to sort links by their `name` key.
	 */
	public static function link_sort ($a, $b) {
		if ($a['name'] == $b['name']) {
			return 0;
		}
		return ($a['name'] < $b['name']) ? -1 : 1;
	}

	/**
	 * Check if an app is compatible with the current user's platform.
	 */
	public static function is_compatible ($appconf) {
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

	public static function custom_tools ($controller) {
		if (self::$custom_tools !== null) {
			return self::$custom_tools;
		}

		if (! file_exists (self::$custom_tools_file)) {
			self::$custom_tools = false;
			return self::$custom_tools;
		}

		self::$custom_tools = parse_ini_file (self::$custom_tools_file, true);
		$add_extras_under = false;
		$first_column = false;
		$original_tools = array ();

		if (self::$custom_tools !== false) {
			foreach (self::$custom_tools as $column => $tools) {
				if ($first_column === false) {
					$first_column = $column;
				}

				foreach ($tools as $handler => $name) {
					if ($handler === '*') {
						$add_extras_under = $column;
						unset (self::$custom_tools[$column][$handler]);
						continue;
					}

					$original_tools[] = $handler;
		
					if ($handler === 'admin/pages' && (! Appconf::admin ('General', 'show_all_pages') || ! User::require_acl ('admin/pages'))) {
						unset (self::$custom_tools[$column][$handler]);
						continue;
					}
					
					$app = substr ($handler, 0, strpos ($handler, '/'));
					$appconf = Appconf::get ($app);

					if (! self::is_compatible ($appconf)) {
						// App not compatible with this platform
						unset (self::$custom_tools[$column][$handler]);
						continue;
					}

					if (isset ($appconf['Admin']['install'])) {
						$ver = $controller->installed ($app, $appconf['Admin']['version']);

						if ($ver === true) {
							// installed
							self::$custom_tools[$column][$handler] = array (
								'handler' => $handler,
								'name' => __ ($name),
								'class' => false
							);
						} elseif ($ver === false) {
							// not installed
							$name = __ ($name) . ' (' . __ ('click to install') . ')';
							unset (self::$custom_tools[$column][$handler]);
							self::$custom_tools[$column][$handler] = array (
								'handler' => $handler,
								'install' => $appconf['Admin']['install'],
								'name' => $name,
								'class' => 'needs-upgrade'
							);
						} else {
							// needs upgrade
							$name = __ ($name) . ' (' . __ ('click to upgrade') . ')';
							self::$custom_tools[$column][$handler] = array (
								'handler' => $handler,
								'upgrade' => $appconf['Admin']['upgrade'],
								'name' => $name,
								'class' => 'needs-upgrade'
							);
						}
					} else {
						// no installer, as you were
						self::$custom_tools[$column][$handler] = array (
							'handler' => $handler,
							'name' => __ ($name),
							'class' => false
						);
					}
				}
			}
		}
		
		// check if we need to add an upgrade link
		$ver = $controller->installed ('elefant', ELEFANT_VERSION);
		if ($ver !== true) {
			self::$custom_tools[$first_column]['admin/upgrade'] = array (
				'handler' => 'admin/upgrade',
				'name' => ' ' . __ ('Click to upgrade'),
				'class' => 'needs-upgrade'
			);
		}
		
		// check if we need to add additional apps
		if ($add_extras_under !== false) {
			// parse each and determine whether to add it to the list
			$extras = array ();
			$res = glob ('apps/*/conf/config.php');
			foreach ($res as $file) {
				$app = preg_replace ('/^apps\/(.*)\/conf\/config\.php$/i', '\1', $file);

				if (! User::require_acl ($app)) {
					// Can't access this app
					continue;
				}

				$appconf = Appconf::get ($app);

				if (! self::is_compatible ($appconf)) {
					// App not compatible with this platform
					continue;
				}
				
				if (isset ($appconf['Admin']['handler'])) {
					if (in_array ($appconf['Admin']['handler'], $original_tools)) {
						continue;
					}

					if (! preg_match ('/\/(admin|index)$/', $appconf['Admin']['handler']) && ! User::require_acl ($appconf['Admin']['handler'])) {
						// A non /admin or /index handler should get an additional
						// access check (e.g., admin/versions).
						continue;
					}
				
					if (isset ($appconf['Admin']['install'])) {
						$ver = $controller->installed ($app, $appconf['Admin']['version']);

						if ($ver === true) {
							// installed
							$extras[$appconf['Admin']['handler']] = array (
								'handler' => $appconf['Admin']['handler'],
								'name' => $appconf['Admin']['name'],
								'class' => false
							);
						} elseif ($ver === false) {
							// not installed
							$appconf['Admin']['name'] .= ' (' . __ ('click to install') . ')';
							$extras[$appconf['Admin']['install']] = array (
								'handler' => $appconf['Admin']['handler'],
								'install' => $appconf['Admin']['install'],
								'name' => $appconf['Admin']['name'],
								'class' => 'needs-upgrade'
							);
						} else {
							// needs upgrade
							$appconf['Admin']['name'] .= ' (' . __ ('click to upgrade') . ')';
							$extras[$appconf['Admin']['upgrade']] = array (
								'handler' => $appconf['Admin']['handler'],
								'upgrade' => $appconf['Admin']['upgrade'],
								'name' => $appconf['Admin']['name'],
								'class' => 'needs-upgrade'
							);
						}
					} else {
						// no installer, as you were
						$extras[$appconf['Admin']['handler']] = array (
							'handler' => $appconf['Admin']['handler'],
							'name' => $appconf['Admin']['name'],
							'class' => false
						);
					}
				}
			}

			uasort ($extras, 'admin\Toolbar::link_sort');
			$count = 0;
			foreach ($extras as $handler => $extra) {
				if ($count >= 14) {
					self::$custom_tools['&nbsp;'][$handler] = $extra;
				} elseif ($count >= 7) {
					self::$custom_tools['&nbsp;&nbsp;'][$handler] = $extra;
				} else {
					self::$custom_tools[$add_extras_under][$handler] = $extra;
				}
				$count++;
			}
		}
		
		return self::$custom_tools;
	}
	
	/**
	 * Parse the apps for a list of tools.
	 */
	public static function parse_apps ($controller) {
		if (self::$tools !== null) {
			return self::$tools;
		}

		$ver = $controller->installed ('elefant', ELEFANT_VERSION);
		if ($ver === true) {
			self::$tools = array (
				'admin/pages' => array (
					'handler' => 'admin/pages',
					'name' => ' ' . __ ('Web Pages'),
					'class' => false
				)
			);
		} else {
			self::$tools = array (
				'admin/upgrade' => array (
					'handler' => 'admin/upgrade',
					'name' => ' ' . __ ('Click to upgrade'),
					'class' => 'needs-upgrade'
				),
				'admin/pages' => array (
					'handler' => 'admin/pages',
					'name' => ' ' . __ ('Web Pages')
				)
			);
		}
		
		if (! Appconf::admin ('General', 'show_all_pages') || ! User::require_acl ('admin/pages')) {
			unset (self::$tools['admin/pages']);
		}

		// parse each app to determine whether to add it to the list
		$res = glob ('apps/*/conf/config.php');
		foreach ($res as $file) {
			$app = preg_replace ('/^apps\/(.*)\/conf\/config\.php$/i', '\1', $file);

			if (! User::require_acl ($app)) {
				// Can't access this app
				continue;
			}
			
			$appconf = Appconf::get ($app);

			if (! self::is_compatible ($appconf)) {
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
					$ver = $controller->installed ($app, $appconf['Admin']['version']);

					if ($ver === true) {
						// installed
						self::$tools[$appconf['Admin']['handler']] = $appconf['Admin'];
						self::$tools[$appconf['Admin']['handler']]['class'] = false;
					} elseif ($ver === false) {
						// not installed
						$appconf['Admin']['name'] .= ' (' . __ ('click to install') . ')';
						self::$tools[$appconf['Admin']['install']] = $appconf['Admin'];
						self::$tools[$appconf['Admin']['install']]['class'] = 'not-installed';
					} else {
						// needs upgrade
						$appconf['Admin']['name'] .= ' (' . __ ('click to upgrade') . ')';
						self::$tools[$appconf['Admin']['upgrade']] = $appconf['Admin'];
						self::$tools[$appconf['Admin']['upgrade']]['class'] = 'needs-upgrade';
					}
				} else {
					// no installer, as you were
					self::$tools[$appconf['Admin']['handler']] = $appconf['Admin'];
					self::$tools[$appconf['Admin']['handler']]['class'] = false;
				}
			}
		}
		
		uasort (self::$tools, 'admin\Toolbar::link_sort');

		return self::$tools;
	}
}

?>