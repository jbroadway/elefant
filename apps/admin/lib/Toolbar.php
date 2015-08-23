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
	private static $defaults = array(
		'Content' => array(
			'admin/pages' => 'Web Pages',
			'blog/admin' => 'Blog Posts',
			'blocks/admin' => 'Content Blocks',
			'filemanager/index' => 'Files'
		),
		'Administration' => array(
			'user/admin' => 'Members',
			'navigation/admin' => 'Navigation',
			'designer/admin' => 'Designer',
			'translator/index' => 'Languages',
			'admin/versions' => 'Versions'
		),
		'Extras' => array(
			'*' => '*'
		)
	);

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
	public static function compile ($c, $editing = false, $recache = false) {
		if (self::$compiled !== false && !$recache) 
			return self::$compiled;
		
		$apps = self::apps($c, $editing, $recache);
		$tools = self::tools ($c, $editing, $recache);
		foreach ($tools as $column => $group) {
			// filter out resources that are already in use
			$apps = array_diff_key($apps, $group);
		}
		return self::$compiled = array($tools, $apps);
	}
	
	/**
	 * Parse and cache custom tools list.
	 * Creates file from defaults if tools file
	 * doesn't exist or is unspecified.
	 */
	public static function tools ($c, $editing = false, $recache = false) {
		if (self::$tools !== false && !$recache)
			return self::$tools;
		
		$path = (conf('Paths','toolbar'))?conf('Paths','toolbar'):'conf/tools.php';
		// if file doesn't exist, build it from hardcoded default
		if (!file_exists ($path)) Toolbar::save(Toolbar::$defaults);
		$_tools = parse_ini_file ($path, true);
		$tools = array();
		$first = false;

		if ($_tools !== false) {
			foreach ($_tools as $column => $group) {
				$tools[$column] = array();
				if ($first === false) {
					$first = true;
					// check if we need to add an upgrade link
					$ver = $c->installed ('elefant', ELEFANT_VERSION);
					if ($ver !== true && !$editing) {
						$tools[$column]['admin/upgrade'] = array (
							'handler' => 'admin/upgrade',
							'name' => 'Website Core',
							'class' => 'needs-upgrade'
						);
					}
				}
				$_column = $column;
				$i = 0;
				$j = 2;
				foreach ($group as $handler => $name) {
					if ($handler === '*') {
						self::$autofill = $_column;
						break;
					}
					
					if (++$i > 7 && !$editing) {
						$i = 0;
						$column = $_column .' ('. $j++ .')';
						$tools[$column] = array();
					}
					
					$app = substr ($handler, 0, strpos ($handler, '/'));

					// for app/admin and app/index handlers, verify acl on app alone
					// for app/custom-name handlers, verify acl on both app and handler
					if ($editing || (
						(preg_match ('/^'. preg_quote ($app, '/') .'\/(admin|index)$/', $handler) && User::require_acl ($app))
						|| 
						User::require_acl ($app, $handler)
					)) {
						// Ok
					} else {
						// Can't access this app
						continue;
					}

					$appconf = Appconf::get ($app);

					if (! self::is_compatible ($appconf)) {
						// App not compatible with this platform 
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
							$tools[$column][$appconf['Admin']['install']] = array (
								'handler' => $handler,
								'name' => $name,
								'class' => 'not-installed'
							);
						} else {
							// needs upgrade
							$tools[$column][$appconf['Admin']['upgrade']] = array (
								'handler' => $handler,
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
	public static function apps ($controller, $editing = false, $recache = false) {
		if (self::$apps !== null && !recache) return self::$apps;
		
		$apps = array();
		$tools = array();
		
		$ver = $controller->installed ('elefant', ELEFANT_VERSION);
		if ($ver !== true && !$editing) {
			$tools['admin/upgrade'] = array(
				'handler' => 'admin/upgrade',
				'name' => 'Website Core',
				'class' => 'needs-upgrade'
			);
		}
		
		// Grab tools the new way.
		$res = glob ('apps/*/conf/tools.php');
		foreach ($res as $file) {
			$app = preg_replace ('/^apps\/(.*)\/conf\/tools\.php$/i', '\1', $file);
			$appconf = Appconf::get ($app);

			if (! self::is_compatible ($appconf)) {
				// App not compatible with this platform
				continue;
			}
			
			if (isset($appconf['Admin']['install'])) {
				$ver = $controller->installed ($app, $appconf['Admin']['version']);
			} else $ver = true;
			
			// Do not allow uninstalled apps to autofill from tools.php
			if ($ver === false) continue;
			
			$apps[] = $app;
			$resources = parse_ini_file($file);
			
			foreach($resources as $handler => $name) {
				// ACL check.
				$app = substr ($handler, 0, strpos ($handler, '/'));
				if ($editing || (
					(preg_match ('/^'. preg_quote ($app, '/') .'\/(admin|index)$/', $handler) && User::require_acl ($app))
					|| 
					User::require_acl ($handler)
				)) {/* Ok */} else continue;
				
				if (preg_match ('/\/(admin|index)$/', $handler)) {
						if ($ver === true) {
							$tools[$handler] = array(
								'handler' => $handler,
								'name' => __($name),
								'class' => false
							);
						} else {
							$tools[$appconf['Admin']['upgrade']] = array(
								'handler' => $handler,
								'name' => __($name),
								'class' => 'needs-upgrade'
							);
						}
				} else {
					$tools[$handler] = array(
						'handler' => $handler,
						'name' => __($name),
						'class' => false
					);
				}
			}
		}
		
		if ((! Appconf::admin ('General', 'show_all_pages') || ! User::require_acl ('admin/pages')) && !$editing) {
			unset ($tools['admin/pages']);
		}
		
		// Grab tools the old way
		// parse each app to determine whether to add it to the list
		$res = glob ('apps/*/conf/config.php');
		foreach ($res as $file) {
			$app = preg_replace ('/^apps\/(.*)\/conf\/config\.php$/i', '\1', $file);
			
			// Skip if app already autoloaded from tools.php
			if (in_array($app,$apps)) continue;
			
			// Can't access this app
			if (! User::require_acl ($app) && !$editing) continue;
			
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
				if (! preg_match ('/\/(admin|index)$/', $appconf['Admin']['handler']) && ! User::require_acl ($appconf['Admin']['handler']) && !$editing) {
					// A non /admin or /index handler should get an additional
					// access check (e.g., admin/versions).
					continue;
				}
				
				if (isset ($appconf['Admin']['install'])) {
					$ver = $controller->installed ($app, $appconf['Admin']['version']);

					if ($ver === true) {
						// installed
						$tools[$appconf['Admin']['handler']] = $appconf['Admin'];
						$tools[$appconf['Admin']['handler']]['class'] = false;
					} elseif ($ver === false) {
						// not installed
						$tools[$appconf['Admin']['install']] = $appconf['Admin'];
						$tools[$appconf['Admin']['install']]['class'] = 'not-installed';
					} else {
						// needs upgrade
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
		uasort ($tools, function ($a, $b) {
			if ($a['name'] == $b['name']) {
				return 0;
			}
			return ($a['name'] < $b['name']) ? -1 : 1;
		});
		return self::$apps = $tools;
	}
	
	/**
	 * Simple wrapper for saving the toolbar list.
	 */
	public static function save ($data){
		$path = (conf('Paths','toolbar'))?conf('Paths','toolbar'):'conf/tools.php';
		return (bool) Ini::write($data, $path);
	}
}
