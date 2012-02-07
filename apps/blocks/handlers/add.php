<?php

/**
 * Block add form.
 */

$page->layout = 'admin';

if (! User::require_admin ()) {
	$this->redirect ('/admin');
}

$f = new Form ('post', 'blocks/add');
$f->verify_csrf = false;
if ($f->submit ()) {
	$b = new Block ($_POST);
	$b->put ();
	Versions::add ($b);
	if (! $b->error) {
		$this->add_notification ('Block added.');
		$this->hook ('blocks/add', $_POST);
		if (isset ($_GET['return'])) {
			$this->redirect ($_GET['return']);
		}
		$this->redirect ('/blocks/admin');
	}
	$page->title = i18n_get ('An Error Occurred');
	echo i18n_get ('Error Message') . ': ' . $b->error;
} else {
	$b = new Block;
	$b->id = $_GET['id'];
	$b->access = 'public';
	$b->yes_no = array ('yes' => i18n_get ('Yes'), 'no' => i18n_get ('No'));

	$b->failed = $f->failed;
	$b = $f->merge_values ($b);
	$page->title = i18n_get ('Add Block');
	$page->head = $tpl->render ('blocks/add/head', $b)
				. $tpl->render ('admin/wysiwyg');
	echo $tpl->render ('blocks/add', $b);
}

?>