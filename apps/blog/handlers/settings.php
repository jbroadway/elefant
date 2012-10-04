<?php

/**
 * This is the settings form for the blog app.
 */

$this->require_admin ();

require_once ('apps/admin/lib/Functions.php');

$page->layout = 'admin';
$page->title = __ ('Blog Settings');

$form = new Form ('post', $this);

$form->data = array (
	'title' => $appconf['Blog']['title'],
	'layouts' => admin_get_layouts (),
	'layout' => $appconf['Blog']['layout'],
	'post_layout' => $appconf['Blog']['post_layout'],
	'comments' => $appconf['Blog']['comments'],
	'disqus_shortname' => $appconf['Blog']['disqus_shortname']
);

echo $form->handle (function ($form) {
	if (! Ini::write (
		array (
			'Blog' => array (
				'title' => $_POST['title'],
				'layout' => $_POST['layout'],
				'post_layout' => $_POST['post_layout'],
				'comments' => ($_POST['comments'] === 'none') ? false : $_POST['comments'],
				'disqus_shortname' => $_POST['disqus_shortname']
			)
		),
		'conf/app.blog.' . ELEFANT_ENV . '.php'
	)) {
		printf ('<p>%s</p>', __ ('Unable to save changes. Check your folder permissions and try again.'));
		return;
	}

	$form->controller->add_notification (__ ('Settings saved.'));
	$form->controller->redirect ('/blog/admin');
});

?>