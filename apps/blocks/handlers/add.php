<?php

$page->layout = 'admin';
$page->template = 'admin/base';

if (! User::require_admin ()) {
	$this->redirect ('/admin');
}

$f = new Form ('post', 'blocks/add');
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
	$page->title = 'An Error Occurred';
	echo 'Error Message: ' . $wp->error;
} else {
	$b = new Block;
	$b->id = $_GET['id'];
	$b->access = 'public';
	$b->yes_no = array ('yes', 'no');

	$b->failed = $f->failed;
	$b = $f->merge_values ($b);
	$page->title = 'Add Block';
	$page->head = $tpl->render ('blocks/add/head', $b)
				. $tpl->render ('admin/wysiwyg');
	echo $tpl->render ('blocks/add', $b);
}

?>