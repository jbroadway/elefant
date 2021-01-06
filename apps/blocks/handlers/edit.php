<?php

/**
 * Block edit form.
 */

$page->layout = 'admin';

$this->require_acl ('admin', 'blocks');

$lock = new Lock ('Block', $_GET['id']);
if ($lock->exists ()) {
	$page->title = __ ('Editing Locked');
	echo $tpl->render ('admin/locked', $lock->info ());
	return;
} else {
	$lock->add ();
}

$b = new Block ($_GET['id']);

$f = new Form ('post', 'blocks/edit');

if ($f->submit ()) {
	$b->id = $_POST['id'];
	$b->title = $_POST['title'];
	$b->body = $_POST['body'];
	$b->access = $_POST['access'];
	$b->show_title = $_POST['show_title'];
	$b->background = $_POST['background'];
	$b->style = $_POST['style'];
	$b->column_layout = $_POST['column_layout'];
	$b->col2 = $_POST['col2'];
	$b->col3 = $_POST['col3'];
	$b->col4 = $_POST['col4'];
	$b->col5 = $_POST['col5'];
	$b->put ();
	Versions::add ($b);
	if (! $b->error) {
		$this->add_notification (__ ('Block saved.'));
		$_POST['id'] = $_GET['id'];
		$lock->remove ();
		$this->hook ('blocks/edit', $_POST);
		if (isset ($_GET['return'])) {
			$_GET['return'] = filter_var ($_GET['return'], FILTER_SANITIZE_URL);

			if (Validator::validate ($_GET['return'], 'localpath')) {
				$this->redirect ($_GET['return']);
			}
		}
		$this->redirect ('/blocks/admin');
	}
	$page->title = __ ('An Error Occurred');
	echo __ ('Error Message') . ': ' . $b->error;
} else {
	$b->yes_no = array ('yes' => __ ('Yes'), 'no' => __ ('No'));
	$b->columns = explode ('-', $b->column_layout);
	$b->rows = isset ($_GET['column']);
	$b->column = $b->rows ? $_GET['column'] : 1;
	$b->layout_options = Block::$column_layouts;

	$b->failed = $f->failed;
	$b = $f->merge_values ($b);
	$page->window_title = __ ('Edit Block') . ': ' . Template::sanitize ($b->title);
	$page->add_script ('/js/jquery-migrate-1.2.1.js');
	$page->add_script ('/js/jquery-ui/jquery-ui.min.js');
	$page->add_script ('/apps/user/js/jquery.tools.min.js');
	$this->run ('admin/util/wysiwyg');
	echo $tpl->render ('blocks/edit/head', $b);
	echo $tpl->render ('blocks/edit', $b);
}
