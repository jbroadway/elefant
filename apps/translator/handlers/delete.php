<?php

/**
 * Delete a language.
 */

$this->require_admin ();

global $i18n;

require_once ('apps/translator/lib/Functions.php');

if (isset ($i18n->languages[$_GET['lang']])) {
	if (file_exists ('lang/' . $_GET['lang'] . '.php')) {
		unlink ('lang/' . $_GET['lang'] . '.php');
	}
	unset ($i18n->languages[$_GET['lang']]);
	file_put_contents ('lang/languages.php', translator_ini_write ($i18n->languages));
}

$this->add_notification (i18n_get ('Language deleted.'));
$this->redirect ('/translator/index');

?>