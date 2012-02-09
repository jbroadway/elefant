<?php

/**
 * Add a new language to the list, including its name,
 * code, locale, character set, and fallback.
 */

$this->require_admin ();

$page->layout = 'admin';

$page->title = i18n_get ('Add language');

$form = new Form ('post', $this);

require_once ('apps/translator/lib/Functions.php');

echo $form->handle (function ($form) {
	// Add to lang/languages.php

	$_POST['code'] = strtolower ($_POST['code']);
	$_POST['locale'] = strtolower ($_POST['locale']);

	if (! empty ($_POST['locale'])) {
		$lang = $_POST['code'] . '_' . $_POST['locale'];
	} else {
		$lang = $_POST['code'];
	}

	global $i18n;

	$i18n->languages[$lang] = array (
		'name' => $_POST['name'],
		'code' => $_POST['code'],
		'locale' => $_POST['locale'],
		'charset' => $_POST['charset'],
		'fallback' => $_POST['fallback'],
		'default' => 'Off'
	);

	uasort ($i18n->languages, 'translator_sort_languages');

	if (! file_put_contents ('lang/languages.php', translator_ini_write ($i18n->languages))) {
		return false;
	}

	$form->controller->add_notification (i18n_get ('Language added.'));
	$form->controller->redirect ('/translator/index');
});

?>