<?php

/**
 * GEt or set a global setting.
 *
 * Usage:
 *
 *     # Get the default_layout
 *     ./elefant conf General.default_layout     # "default"
 *
 *     # Set the default_layout to "minimal"
 *     ./elefant conf General.default_layout "minimal"
 *
 *     # List configuration sections
 *     ./elefant conf                            # Cache, Database, etc.
 *
 *     # List configuration options in the Mailer section
 *     ./elefant conf Mailer                     # email_from, email_name, etc.
 */

if (! $this->cli) die ('Must be run from the command line.');

$page->layout = false;

$valid_section_name = '/^[a-zA-Z0-9 _-]+$/';
$valid_setting_name = '/^[a-zA-Z0-9\/ _-]+$/';

// there's a value to update
if (isset ($_SERVER['argv'][3])) {
	$parts = explode ('.', $_SERVER['argv'][2]);
	$value = $_SERVER['argv'][3];

	// make sure they provide a specific and valid setting name
	if (count ($parts) !== 2) {
		Cli::out ('Please provide a setting name to update its value.', 'error');
		return;
	}
	
	list ($section, $setting) = $parts;
	
	if (! preg_match ($valid_section_name, $section)) {
		Cli::out ('Invalid section name: ' . $section, 'error');
		return;
	}

	if (! preg_match ($valid_setting_name, $setting)) {
		Cli::out ('Invalid setting name: ' . $setting, 'error');
		return;
	}
	
	// build an updated config to save
	$settings = conf ();
	$merged = array_replace_recursive ($settings, array ($section => array ($setting => $value)));

	if (! Ini::write ($merged, 'conf/' . ELEFANT_ENV . '.php')) {
		Cli::out ('Unable to save changes to: conf/' . ELEFANT_ENV . '.php', 'error');
	}

// show setting info
} else {

	// list sections
	if (! isset ($_SERVER['argv'][2])) {
		
		$settings = conf ();
		$sections = array_keys ($settings);
		sort ($sections);
		echo join (', ', $sections) . "\n";
		return;
	}
	
	$parts = explode ('.', $_SERVER['argv'][2]);

	// list settings in section
	if (count ($parts) === 1) {
		list ($section) = $parts;

		if (! preg_match ($valid_section_name, $section)) {
			Cli::out ('Invalid section name: ' . $section, 'error');
			return;
		}
		
		$settings = conf ($section);
		$names = array_keys ($settings);
		sort ($names);
		echo join (', ', $names) . "\n";

	// show specific setting (encoded as JSON value)
	} elseif (count ($parts) === 2) {
		list ($section, $setting) = $parts;
	
		if (! preg_match ($valid_section_name, $section)) {
			Cli::out ('Invalid section name: ' . $section, 'error');
			return;
		}

		if (! preg_match ($valid_setting_name, $setting)) {
			Cli::out ('Invalid setting name: ' . $setting, 'error');
			return;
		}
		
		$value = conf ($section, $setting);
		if (! defined ('JSON_PRETTY_PRINT')) {
			define ('JSON_PRETTY_PRINT', 0);
		}
		echo json_encode ($value, JSON_PRETTY_PRINT) . "\n";
	
	} else {
		Cli::out ('Invalid setting value: ' . $_SERVER['argv'][2], 'error');
	}
}

?>