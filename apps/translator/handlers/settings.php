<?php

/**
 * Edit the settings for a language, including
 *
 * - name
 * - code
 * - locale
 * - charset
 * - fallback
 */

$this->require_acl ('admin', 'translator');

$page->layout = 'admin';

$lang = $i18n->languages[$_GET['lang']];

$page->title = __ ('Language settings') . ': ' . $lang['name'];

$form = new Form ('post', $this);

$form->data = $i18n->languages[$_GET['lang']];

require_once ('apps/translator/lib/Functions.php');

echo $form->handle (function ($form) {
	// Update lang/languages.php

	$_POST['code'] = strtolower ($_POST['code']);
	$_POST['locale'] = strtolower ($_POST['locale']);

	if (! empty ($_POST['locale'])) {
		$lang = $_POST['code'] . '_' . $_POST['locale'];
	} else {
		$lang = $_POST['code'];
	}

	$i18n = $form->controller->i18n ();

	if ($lang !== $_GET['lang']) {
		// Language has changed ids
		if (isset ($i18n->languages[$lang])) {
			// Language already exists
			$form->failed = array ('dupe');
			return false;
		}
		$i18n->languages[$lang] = $i18n->languages[$_GET['lang']];
		unset ($i18n->languages[$_GET['lang']]);
		rename ('lang/' . $_GET['lang'] . '.php', 'lang/' . $lang . '.php');
	}

	$i18n->languages[$lang]['name'] = $_POST['name'];
	$i18n->languages[$lang]['code'] = $_POST['code'];
	$i18n->languages[$lang]['locale'] = $_POST['locale'];
	$i18n->languages[$lang]['charset'] = $_POST['charset'];
	$i18n->languages[$lang]['fallback'] = $_POST['fallback'];
	$i18n->languages[$lang]['date_format'] = $_POST['date_format'];
	$i18n->languages[$lang]['short_format'] = $_POST['short_format'];
	$i18n->languages[$lang]['time_format'] = $_POST['time_format'];

	uasort ($i18n->languages, 'translator_sort_languages');

	if (! Ini::write ($i18n->languages, 'lang/languages.php')) {
		return false;
	}

	$form->controller->add_notification (__ ('Language updated.'));
	$form->controller->redirect ('/translator/index');
});

?>