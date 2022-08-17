<?php

/**
 * Block add form.
 */

$page->layout = 'admin';

$this->require_acl ('admin', 'blocks', 'admin/add');

$f = new Form ('post', 'blocks/add');

if ($f->submit ()) {
	unset ($_POST['_token_']);
	if (isset ($_POST['col1'])) {
		$_POST['body'] = $_POST['col1'];
		unset ($_POST['col1']);
	}
	$b = new Block ($_POST);
	$b->put ();
	Versions::add ($b);
	if (! $b->error) {
		$this->add_notification (__ ('Block added.'));
		$this->hook ('blocks/add', $_POST);
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
	$b = new Block;
	$b->id = $_GET['id'] ?? '';
	$b->access = 'public';
	$b->show_title = 'yes';
	$b->background = '';
	$b->yes_no = array ('yes' => __ ('Yes'), 'no' => __ ('No'));
	$b->rows = isset ($_GET['column_layout']);
	$b->column_layout = $b->rows ? $_GET['column_layout'] : '100';
	$b->columns = explode ('-', $b->column_layout);
	$b->column = 1;
	$b->layout_options = Block::$column_layouts;

	$b->failed = $f->failed;
	$b = $f->merge_values ($b);
	$page->window_title = __ ('Add Block');
	$page->add_script ('/js/jquery-migrate-1.2.1.js');
	$page->add_script ('/js/jquery-ui/jquery-ui.min.js');
	$page->add_script ('/apps/user/js/jquery.tools.min.js');
	$this->run ('admin/util/wysiwyg');
	echo $tpl->render ('blocks/add/head', $b);
	echo $tpl->render ('blocks/add', $b);
}
