<?php

/**
 * Returns a list of validation rules as a JSON object so that
 * `js/jquery.verify_values.js` can use the same validation rules
 * client-side that are used by `lib/Form.php` in server-side
 * validation.
 */

if (count ($this->params) < 2) {
	die ('Usage: /admin/validator/app/form');
}

$app = array_shift ($this->params);

if (count ($this->params) > 1) {
	$form = join ('/', $this->params);
} else {
	$form = $this->params[0];
}

if (! preg_match ('/^[a-zA-Z0-9_-]+$/', $app) || ! preg_match ('/^[a-zA-Z0-9\/_-]+$/', $form)) {
	die ('Invalid app or form name');
} elseif (! @file_exists ('apps/' . $app . '/forms/' . $form . '.php')) {
	die ('Form not found');
}

$rules = file_get_contents ('apps/' . $app . '/forms/' . $form . '.php');
$rules = preg_replace ('/\$_(GET|POST|REQUEST)\[\'?(.+?)\'?\]/', '\2', $rules);
$rules = parse_ini_string ($rules, true);

$page->layout = false;
header ('Content-Type: application/json');
echo json_encode ($rules);
