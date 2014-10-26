<?php

/**
 * Get or set a setting for an app.
 *
 * Usage:
 *
 *     # Get the title
 *     ./elefant appconf events.Events.title     # "Event Calendar"
 *
 *     # Set the title to "Event Calendar"
 *     ./elefant appconf events.Events.title "Event Calendar"
 *
 *     # List configuration sections of events app
 *     ./elefant appconf events                    # Admin, Events
 *
 *     # List the configuration options in the
 *     # Admin section of the events app
 *     ./elefant appconf events.Admin              # install, handler, name, etc.
 */

if (! $this->cli) die ('Must be run from the command line.');

$page->layout = false;

$valid_app_name = '/^[a-zA-Z0-9_-]+$/';
$valid_section_name = '/^[a-zA-Z0-9 _-]+$/';
$valid_setting_name = '/^[a-zA-Z0-9\/ _-]+$/';

if (! isset ($_SERVER['argv'][2])) {
	Cli::out ('Usage: ./elefant appconf <app.Section.setting> ["New Value"]', 'info');
	return;
}

$parts = explode ('.', $_SERVER['argv'][2]);

// there's a value to update
if (isset ($_SERVER['argv'][3])) {
	$value = $_SERVER['argv'][3];

	// make sure they provide a specific and valid setting name
	if (count ($parts) !== 3) {
		Cli::out ('Please provide a setting name to update its value.', 'error');
		return;
	}
	
	list ($app, $section, $setting) = $parts;
	
	if (! preg_match ($valid_app_name, $app) || ! is_dir ('apps/' . $app)) {
		Cli::out ('Invalid app name: ' . $app, 'error');
		return;
	}

	if (! preg_match ($valid_section_name, $section)) {
		Cli::out ('Invalid section name: ' . $section, 'error');
		return;
	}

	if (! preg_match ($valid_setting_name, $setting)) {
		Cli::out ('Invalid setting name: ' . $setting, 'error');
		return;
	}
	
	// build an updated config to save
	$merged = Appconf::merge ($app, array ($section => array ($setting => $value)));

	if (! Ini::write ($merged, 'conf/app.' . $app . '.' . ELEFANT_ENV . '.php')) {
		Cli::out ('Unable to save changes to: conf/app.' . $app . '.' . ELEFANT_ENV . '.php', 'error');
	}

// show setting info
} else {

	// list sections
	if (count ($parts) === 1) {
		$app = $parts[0];

		if (! preg_match ($valid_app_name, $app) || ! is_dir ('apps/' . $app)) {
			Cli::out ('Invalid app name: ' . $app, 'error');
			return;
		}
		
		$settings = Appconf::get ($app);
		$sections = array_keys ($settings);
		sort ($sections);
		echo join (', ', $sections) . "\n";

	// list settings in section
	} elseif (count ($parts) === 2) {
		list ($app, $section) = $parts;

		if (! preg_match ($valid_app_name, $app) || ! is_dir ('apps/' . $app)) {
			Cli::out ('Invalid app name: ' . $app, 'error');
			return;
		}

		if (! preg_match ($valid_section_name, $section)) {
			Cli::out ('Invalid section name: ' . $section, 'error');
			return;
		}
		
		$settings = Appconf::get ($app, $section);
		$names = array_keys ($settings);
		sort ($names);
		echo join (', ', $names) . "\n";

	// show specific setting (encoded as JSON value)
	} elseif (count ($parts) === 3) {
		list ($app, $section, $setting) = $parts;
	
		if (! preg_match ($valid_app_name, $app) || ! is_dir ('apps/' . $app)) {
			Cli::out ('Invalid app name: ' . $app, 'error');
			return;
		}

		if (! preg_match ($valid_section_name, $section)) {
			Cli::out ('Invalid section name: ' . $section, 'error');
			return;
		}

		if (! preg_match ($valid_setting_name, $setting)) {
			Cli::out ('Invalid setting name: ' . $setting, 'error');
			return;
		}
		
		$value = Appconf::get ($app, $section, $setting);
		if (! defined ('JSON_PRETTY_PRINT')) {
			define ('JSON_PRETTY_PRINT', 0);
		}
		echo json_encode ($value, JSON_PRETTY_PRINT) . "\n";
	
	} else {
		Cli::out ('Invalid setting value: ' . $_SERVER['argv'][2], 'error');
	}
}

?>