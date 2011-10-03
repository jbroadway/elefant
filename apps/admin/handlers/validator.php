<?php

if (count ($this->params) != 2) {
	die ('Usage: /admin/validator/app/form');
} elseif (! preg_match ('/^[a-zA-Z0-9_-]+$/', $this->params[0]) || ! preg_match ('/^[a-zA-Z0-9_-]+$/', $this->params[1])) {
	die ('Invalid app or form name');
} elseif (! @file_exists ('apps/' . $this->params[0] . '/forms/' . $this->params[1] . '.php')) {
	die ('Form not found');
}

$rules = file_get_contents ('apps/' . $this->params[0] . '/forms/' . $this->params[1] . '.php');
$rules = preg_replace ('/\$_(GET|POST|REQUEST)\[\'?(.+?)\'?\]/', '\2', $rules);
$rules = parse_ini_string ($rules, true);

$page->layout = false;
header ('Content-Type: application/json');
echo json_encode ($rules);

?>