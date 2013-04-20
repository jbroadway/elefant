<?php

/**
 * Blog post add form.
 */

$page->layout = 'admin';

$this->require_acl ('admin', 'admin/edit', 'blog');

$f = new Form ('post', 'blog/add');
$f->verify_csrf = false;
if ($f->submit ()) {
	$autopost_pom = ($_POST['autopost_pom'] == 'yes') ? true : false;
	unset ($_POST['autopost_pom']);

	$p = new blog\Post ($_POST);
	$p->put ();
	Versions::add ($p);
	if (! $p->error) {
		$this->add_notification (__ ('Blog post added.'));

		// add tags
		if ($_POST['published'] == 'yes' && ! empty ($_POST['tags'])) {
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

		// autopost
		if ($_POST['published'] == 'yes') {
			require_once ('apps/blog/lib/Filters.php');

			if ($autopost_pom) {
				$pom = new Pingomatic;
				$pom->post ($appconf['Blog']['title'], 'http://' . $_SERVER['HTTP_HOST'] . '/blog');
			}
		}

		// reset blog rss cache
		$cache->delete ('blog_rss');

		$_POST['page'] = 'blog/post/' . $p->id . '/' . URLify::filter ($p->title);
		$this->hook ('blog/add', $_POST);
		$this->redirect ('/blog/admin');
	}
	$page->title = __ ('An Error Occurred');
	echo __ ('Error Message') . ': ' . $p->error;
} else {
	$p = new blog\Post;
	$p->author = User::val ('name');
	$p->ts = gmdate ('Y-m-d H:i:s');
	$p->yes_no = array ('yes' => __ ('Yes'), 'no' => __ ('No'));
	$p->autopost_pom = 'yes';

	$p->failed = $f->failed;
	$p = $f->merge_values ($p);
	$p->tag_list = explode (',', $p->tags);
	$page->title = __ ('Add Blog Post');
	$this->run ('admin/util/wysiwyg');
	echo $tpl->render ('blog/add/head', $p);
	echo $tpl->render ('blog/add', $p);
}

?>
