<?php

/**
 * Blog post edit form.
 */

$page->layout = 'admin';

if (! User::require_admin ()) {
	$this->redirect ('/admin');
}

$lock = new Lock ('Blog', $_GET['id']);
if ($lock->exists ()) {
	$page->title = __ ('Editing Locked');
	echo $tpl->render ('admin/locked', $lock->info ());
	return;
} else {
	$lock->add ();
}

$p = new blog\Post ($_GET['id']);

$f = new Form ('post', 'blog/edit');
$f->verify_csrf = false;
if ($f->submit ()) {
	$autopost_pom = ($_POST['autopost_pom'] == 'yes') ? true : false;
	unset ($_POST['autopost_pom']);

	if ($p->published == 'no' && $_POST['published'] == 'yes') {
		$autopost = true;
	} else {
		$autopost = false;
	}

	$p->title = $_POST['title'];
	$p->author = $_POST['author'];
	$p->ts = $_POST['ts'];
	$p->published = $_POST['published'];
	$p->body = $_POST['body'];
	$p->tags = $_POST['tags'];

	$p->update_extended ();

	$p->put ();
	Versions::add ($p);
	if (! $p->error) {
		$this->add_notification (__ ('Blog post saved.'));

		// update tags
		if ($_POST['published'] == 'yes') {
			DB::execute ('delete from #prefix#blog_post_tag where post_id = ?', $p->id);
			$tags = explode (',', $_POST['tags']);
			foreach ($tags as $tag) {
				$tr = trim ($tag);
				DB::execute ('insert into #prefix#blog_tag (id) values (?)', $tr);
				DB::execute (
					'insert into #prefix#blog_post_tag (tag_id, post_id) values (?, ?)',
					$tr,
					$p->id
				);
			}
		}

		require_once ('apps/blog/lib/Filters.php');

		// autopost
		if ($autopost) {
			if ($autopost_pom) {
				$pom = new Pingomatic;
				$pom->post ($appconf['Blog']['title'], 'http://' . $_SERVER['HTTP_HOST'] . '/blog');
			}
		}

		// reset blog rss cache
		$cache->delete ('blog_rss');

		$_POST['page'] = 'blog/post/' . $p->id . '/' . URLify::filter ($p->title);
		$lock->remove ();
		$this->hook ('blog/edit', $_POST);
		$this->redirect ('/blog/admin');
	}
	$page->title = __ ('An Error Occurred');
	echo __ ('Error Message') . ': ' . $p->error;
} else {
	$p->yes_no = array ('yes' => __ ('Yes'), 'no' => __ ('No'));
	$p->autopost_pom = 'yes';
	$p->tag_list = explode (',', $p->tags);

	$p->failed = $f->failed;
	$p = $f->merge_values ($p);
	$page->title = __ ('Edit Blog Post') . ': ' . $p->title;
	$this->run ('admin/util/wysiwyg');
	echo $tpl->render ('blog/edit/head', $p);
	echo $tpl->render ('blog/edit', $p);
}

?>
