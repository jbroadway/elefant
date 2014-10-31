<?php

/**
 * Change the default language.
 */

$this->require_acl ('admin', 'translator');

require_once ('apps/translator/lib/Functions.php');

if (isset ($i18n->languages[$_GET['lang']])) {
	foreach ($i18n->languages as $key => $lang) {
		if ($key === $_GET['lang']) {
			$i18n->languages[$key]['default'] = true;
		} else {
			$i18n->languages[$key]['default'] = false;
		}
	}
	Ini::write ($i18n->languages, 'lang/languages.php');
}

$this->add_notification (__ ('Default language updated.'));
$this->redirect ('/translator/index');
