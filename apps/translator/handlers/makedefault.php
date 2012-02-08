<?php

/**
 * Change the default language.
 */

$this->require_admin ();

global $i18n;

require_once ('apps/translator/lib/Functions.php');

if (isset ($i18n->languages[$_GET['lang']])) {
	foreach ($i18n->languages as $key => $lang) {
		if ($key === $_GET['lang']) {
			$i18n->languages[$key]['default'] = true;
		} else {
			$i18n->languages[$key]['default'] = false;
		}
	}
	file_put_contents ('lang/languages.php', translator_ini_write ($i18n->languages));
}

$this->add_notification (i18n_get ('Default language updated.'));
$this->redirect ('/translator/index');

?>