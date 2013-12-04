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
	'preview_chars' => $appconf['Blog']['preview_chars'],
	'post_format' => $appconf['Blog']['post_format'],
	'disqus_shortname' => $appconf['Blog']['disqus_shortname'],
	'social_twitter' => $appconf['Social Buttons']['twitter'],
	'social_facebook' => $appconf['Social Buttons']['facebook'],
	'social_google' => $appconf['Social Buttons']['google'],
);

echo $form->handle (function ($form) {
	$merged = Appconf::merge ('blog', array (
		'Blog' => array (
			'title' => $_POST['title'],
			'layout' => $_POST['layout'],
			'post_layout' => $_POST['post_layout'],
			'preview_chars' => (! empty ($_POST['preview_chars'])) ? (int) $_POST['preview_chars'] : false,
			'post_format' => $_POST['post_format'],
			'comments' => ($_POST['comments'] === 'none') ? false : $_POST['comments'],
			'disqus_shortname' => $_POST['disqus_shortname']
		),
		'Social Buttons' => array (
			'twitter' => ($_POST['social_twitter'] === 'yes') ? true : false,
			'facebook' => ($_POST['social_facebook'] === 'yes') ? true : false,
			'google' => ($_POST['social_google'] === 'yes') ? true : false
		)
	));

	if (! Ini::write ($merged, 'conf/app.blog.' . ELEFANT_ENV . '.php')) {
		printf ('<p>%s</p>', __ ('Unable to save changes. Check your folder permissions and try again.'));
		return;
	}

	$form->controller->run (
		'navigation/hook/edit',
		array (
			'page' => 'blog',
			'id' => 'blog',
			'title' => $_POST['title']
		)
	);

	$form->controller->add_notification (__ ('Settings saved.'));
	$form->controller->redirect ('/blog/admin');
});

?>