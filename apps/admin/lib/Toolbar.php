<?php

namespace admin;

use \Appconf;
use \DB;
use \User;
use \Ini;

/**
 * Fetches apps for the Elefant toolbar.
 */
class Toolbar {
	public static $apps = false;
	public static $tools = false;
	public static $compiled = false;
	public static $autofill = false;

	/**
	 * Check if an app is compatible with the current user's platform.
	 */
	public static function is_compatible ($appconf) {
		if (isset ($appconf['Admin']['platforms'])) {
			$device_list = explode (',', $appconf['Admin']['platforms']);
			foreach ($device_list as $device) {
				if (detect (trim ($device))) return true;
			}
        } else return true; // No Admin/platforms conf entry so assume app is compatible
        return false;
	}
	
	/**
	 * Simple wrapper for processing custom tools and apps
	 */
	public static function compile ($c, $recache = false) {
		if (self::$compiled !== false && !$recache) 
			return self::$compiled;
		
		$apps = self::apps($c, $recache);
		$tools = self::tools ($c, $recache);
		foreach ($tools as $column => $group) {
			// filter out resources that are already in use
			$apps = array_diff_key($apps, $group);
		}
		return self::$compiled = array($tools, $apps);
	}
	
	/**
	 * Parse and cache custom tools list.
	 * Sets cache to empty array if tools file
	 * doesn't exist or is unspecified.
	 */
	public static function tools ($c, $recache = false) {
		if (self::$tools !== false && !$recache)
			return self::$tools;
		
		if (conf('Paths','toolbar') && !file_exists (conf('Paths','toolbar')))
			return self::$tools = array();
		$path = (conf('Paths','toolbar'))?conf('Paths','toolbar'):'conf/tools.php';
		$tools = parse_ini_file ($path, true);
		$first = false;

		if ($tools !== false) {
			foreach ($tools as $column => $group) {
				if ($first === false) {
					$first = $column;
				}

				foreach ($group as $handler => $name) {
					if ($handler === '*') {
						self::$autofill = $column;
						unset($tools[$column]);
						break;
					}
					
					$app = substr ($handler, 0, strpos ($handler, '/'));

					// for app/admin and app/index handlers, verify acl on app alone
					// for app/custom-name handlers, verify acl on both app and handler
					if ((preg_match ('/^'. preg_quote ($app, '/') .'\/(admin|index)$/', $handler)
							&& User::require_acl ($app)
						) || User::require_acl ($app, $handler)
					) {
						// Ok
					} else {
						// Can't access this app
						unset ($tools[$column][$handler]);
						continue;
					}

					$appconf = Appconf::get ($app);

					if (! self::is_compatible ($appconf)) {
						// App not compatible with this platform
						unset ($tools[$column][$handler]);
						continue;
					}

					if (isset ($appconf['Admin']['install'])) {
						$ver = $c->installed ($app, $appconf['Admin']['version']);

						if ($ver === true) {
							// installed
							$tools[$column][$handler] = array (
								'handler' => $handler,
								'name' => __ ($name),
								'class' => false
							);
						} elseif ($ver === false) {
							// not installed
							$name = __ ($name) . ' (' . __ ('click to install') . ')';
							unset ($tools[$column][$handler]);
							$tools[$column][$handler] = array (
								'handler' => $appconf['Admin']['install'],
								'name' => $name,
								'class' => 'needs-upgrade'
							);
						} else {
							// needs upgrade
							$name = __ ($name) . ' (' . __ ('click to upgrade') . ')';
							$tools[$column][$handler] = array (
								'handler' => $appconf['Admin']['upgrade'],
								'name' => $name,
								'class' => 'needs-upgrade'
							);
						}
					} else {
						// no installer, as you were
						$tools[$column][$handler] = array (
							'handler' => $handler,
							'name' => __ ($name),
							'class' => false
						);
					}
				}
			}
			// check if we need to add an upgrade link
			$ver = $c->installed ('elefant', ELEFANT_VERSION);
			if ($ver !== true) {
				$tools[$first]['admin/upgrade'] = array (
					'handler' => 'admin/upgrade',
					'name' => ' ' . __ ('Click to upgrade'),
					'class' => 'needs-upgrade'
				);
			}
			// Clean out unused sections
			foreach ($tools as $section => $group) {
				if (count ($group) === 0) {
					unset ($tools[$section]);
				}
			}
		} else $tools = array();
		return self::$tools = $tools;
	}
	
	/**
	 * Parse and cache available apps.
	 */
	public static function apps ($controller, $recache = false) {
		if (self::$apps !== null && !recache)
			return self::$apps;
		
		$apps = array();
		$ver = $controller->installed ('elefant', ELEFANT_VERSION);
		if ($ver === true) {
			$apps = array (
				'admin/pages' => array (
					'handler' => 'admin/pages',
					'name' => ' ' . __ ('Web Pages'),
					'class' => false
				)
			);
		} else {
			$apps = array (
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
			unset ($apps['admin/pages']);
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
			
			if (isset($appconf['Admin']['toolbar']) && $appconf['Admin']['toolbar'] == 'Off') {
				// App specifies to be excluded from toolbar auto-parsing
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
						$apps[$appconf['Admin']['handler']] = $appconf['Admin'];
						$apps[$appconf['Admin']['handler']]['class'] = false;
					} elseif ($ver === false) {
						// not installed
						$appconf['Admin']['name'] .= ' (' . __ ('click to install') . ')';
						$apps[$appconf['Admin']['install']] = $appconf['Admin'];
						$apps[$appconf['Admin']['install']]['class'] = 'not-installed';
					} else {
						// needs upgrade
						$appconf['Admin']['name'] .= ' (' . __ ('click to upgrade') . ')';
						$apps[$appconf['Admin']['upgrade']] = $appconf['Admin'];
						$apps[$appconf['Admin']['upgrade']]['class'] = 'needs-upgrade';
					}
				} else {
					// no installer, as you were
					$apps[$appconf['Admin']['handler']] = $appconf['Admin'];
					$apps[$appconf['Admin']['handler']]['class'] = false;
				}
			}
		}
		uasort ($apps, function ($a, $b) {
			if ($a['name'] == $b['name']) {
				return 0;
			}
			return ($a['name'] < $b['name']) ? -1 : 1;
		});
		return self::$apps = $apps;
	}
	
	/**
	 * Simple wrapper for saving the toolbar list.
	 */
	public static function save ($data){
		$path = (conf('Paths','toolbar'))?conf('Paths','toolbar'):'conf/tools.php';
		return (bool) Ini::write($data, $path);
	}
}
