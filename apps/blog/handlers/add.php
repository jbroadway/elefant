<?php

/**
 * Blog post add form.
 */

$page->layout = 'admin';

if (! User::require_admin ()) {
	$this->redirect ('/admin');
}

$f = new Form ('post', 'blog/add');
$f->verify_csrf = false;
if ($f->submit ()) {
	$autopost_pom = ($_POST['autopost_pom'] == 'yes') ? true : false;
	$autopost_tw = ($_POST['autopost_tw'] == 'yes') ? true : false;
	unset ($_POST['autopost_pom']);
	unset ($_POST['autopost_tw']);

	$p = new blog\Post ($_POST);
	$p->put ();
	Versions::add ($p);
	if (! $p->error) {
		$this->add_notification (i18n_get ('Blog post added.'));

		// add tags
		if ($_POST['published'] == 'yes' && ! empty ($_POST['tags'])) {
			$tags = explode (',', $_POST['tags']);
			foreach ($tags as $tag) {
				$tr = trim ($tag);
				db_execute ('insert into blog_tag (id) values (?)', $tr);
				db_execute (
					'insert into blog_post_tag (tag_id, post_id) values (?, ?)',
					$tr,
					$p->id
				);
			}
		}
		
		// autopost
		if ($_POST['published'] == 'yes') {
			require_once ('apps/blog/lib/Filters.php');

			if ($autopost_pom) {
				$pom = new Pingomatic;
				$pom->post ($appconf['Blog']['title'], 'http://' . $_SERVER['HTTP_HOST'] . '/blog');
			}

			if ($autopost_tw && ! empty ($appconf['Twitter']['username']) && ! empty ($appconf['Twitter']['password'])) {
				$b = new Bitly;
				$short = $b->shorten ('http://' . $_SERVER['HTTP_HOST'] . '/blog/post/' . $p->id . '/' . blog_filter_title ($p->title));
				$t = new twitter;
				$t->username = $appconf['Twitter']['username'];
				$t->password = $appconf['Twitter']['password'];
				$t->update ($p->title . ' ' . $short);
			}
		}

		$_POST['page'] = 'blog/post/' . $p->id . '/' . blog_filter_title ($p->title);
		$this->hook ('blog/add', $_POST);
		$this->redirect ('/blog/admin');
	}
	$page->title = 'An Error Occurred';
	echo 'Error Message: ' . $u->error;
} else {
	$p = new blog\Post;
	$p->author = $GLOBALS['user']->name;
	$p->ts = gmdate ('Y-m-d H:i:s');
	$p->yes_no = array ('yes', 'no');
	$p->autopost_pom = 'yes';
	$p->autopost_tw = 'yes';

	$p->failed = $f->failed;
	$p = $f->merge_values ($p);
	$p->tag_list = explode (',', $p->tags);
	$page->title = i18n_get ('Add Blog Post');
	$page->head = $tpl->render ('admin/wysiwyg')
				. $tpl->render ('blog/add/head', $p);
	echo $tpl->render ('blog/add', $p);
}

?>