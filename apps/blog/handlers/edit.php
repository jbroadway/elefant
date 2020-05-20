<?php

/**
 * Blog post edit form.
 */

$page->layout = 'admin';

$this->require_acl ('admin', 'blog');

$lock = new Lock ('Blog', $_GET['id']);
if ($lock->exists ()) {
	$page->title = __ ('Editing Locked');
	echo $tpl->render ('admin/locked', $lock->info ());
	return;
} else {
	$lock->add ();
}

$page->add_script ('/js/urlify.js');

$p = new blog\Post ($_GET['id']);

if ($p->slug == '') {
	$p->slug = URLify::filter ($p->title);
}

$f = new Form ('post', 'blog/edit');

if ($f->submit ()) {
	$p->title = $_POST['title'];
	$p->author = $_POST['author'];
	$p->ts = $_POST['ts'];
	$p->published = $_POST['published'];
	$p->body = $_POST['body'];
	$p->tags = $_POST['tags'];
	$p->thumbnail = $_POST['thumbnail'];
	$p->slug = $_POST['slug'];
	$p->description = $_POST['description'];
	$p->keywords = $_POST['keywords'];

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

		// reset blog rss cache
		$cache->delete ('blog_rss');

		$_POST['page'] = 'blog/post/' . $p->id . '/' . $p->slug;
		$lock->remove ();
		$this->hook ('blog/edit', $_POST);
		
		if (isset ($_GET['redirect']) && $_GET['redirect'] == 'post') {
			$this->redirect ('/blog/post/' . $p->id . '/' . $p->slug);
		} else {
			$this->redirect ('/blog/admin');
		}
	}
	$page->title = __ ('An Error Occurred');
	echo __ ('Error Message') . ': ' . $p->error;
} else {
	$p->tag_list = explode (',', $p->tags);

	$p->failed = $f->failed;
	$p = $f->merge_values ($p);
	if ($p->title === '') {
		$page->window_title = __ ('Add Blog Post');
	} else {
		$page->window_title = __ ('Edit Blog Post') . ': ' . Template::sanitize ($p->title);
	}
	$page->add_script ('/apps/blog/css/related.css');
	if (Appconf::blog ('Blog', 'post_format') === 'html') {
		$this->run ('admin/util/wysiwyg');
	} else {
		$this->run ('admin/util/wysiwyg', array ('field_id' => false));
		$this->run (
			'admin/util/codemirror',
			array (
				'field_id' => 'webpage-body',
				'mode' => 'markdown',
				'lineWrapping' => true
			)
		);
	}
	echo $tpl->render ('blog/edit/head', $p);
	echo $tpl->render ('blog/edit', $p);
}
