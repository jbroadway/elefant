<?php

define ('ELEFANT_ENV', 'test');
require_once ('lib/Functions.php');
require_once ('lib/Autoloader.php');

if (file_exists ('lib/vendor/autoload.php')) {
	require_once ('lib/vendor/autoload.php');
}
