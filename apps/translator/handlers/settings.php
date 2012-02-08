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

$this->require_admin ();

$page->layout = 'admin';

global $i18n;

$lang = $i18n->languages[$_GET['lang']];

$page->title = i18n_get ('Language settings') . ': ' . $lang['name'];

$form = new Form ('post', $this);

$form->data = $i18n->languages[$_GET['lang']];

require_once ('apps/translator/lib/Functions.php');

echo $form->handle (function ($form) {
	// Update lang/languages.php

	if (! empty ($_POST['locale'])) {
		$lang = $_POST['code'] . '_' . $_POST['locale'];
	} else {
		$lang = $_POST['code'];
	}

	global $i18n;

	if ($lang !== $_GET['lang']) {
		// Language has changed ids
		if (isset ($i18n->languages[$lang])) {
			// Language already exists
			$form->failed = array ('dupe');
			return false;
		}
		$i18n->languages[$lang] = $i18n->languages[$_GET['lang']];
		unset ($i18n->languages[$_GET['lang']]);
	}

	$i18n->languages[$lang]['name'] = $_POST['name'];
	$i18n->languages[$lang]['code'] = $_POST['code'];
	$i18n->languages[$lang]['locale'] = $_POST['locale'];
	$i18n->languages[$lang]['charset'] = $_POST['charset'];
	$i18n->languages[$lang]['fallback'] = $_POST['fallback'];

	uasort ($i18n->languages, 'translator_sort_languages');

	if (! file_put_contents ('lang/languages.php', translator_ini_write ($i18n->languages))) {
		return false;
	}

	$form->controller->add_notification (i18n_get ('Language updated.'));
	$form->controller->redirect ('/translator/index');
});

?>