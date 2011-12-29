<?php

/**
 * Implements a blog post importer from a Blogger.com export file.
 */

$this->require_admin ();

$page->layout = 'admin';
$page->title = i18n_get ('Blogger importer');

$f = new Form ('post');

if ($f->submit ()) {
	if (move_uploaded_file ($_FILES['import_file']['tmp_name'], 'cache/blog_' . $_FILES['import_file']['name'])) {
		$file = 'cache/blog_' . $_FILES['import_file']['name'];
	
		$posts = new SimpleXMLElement (file_get_contents ($file));
		
		$imported = 0;
		
		foreach ($posts->entry as $entry) {
			if (strpos ($entry->id, '.settings.BLOG_') !== false) {
				continue;
			}
			if (strpos ($entry->id, '.layout') !== false) {
				continue;
			}
			$post = array (
				'title' => (string) $entry->title,
				'author' => (string) $entry->author->name,
				'ts' => str_replace ('T', ' ', array_shift (explode ('.', $entry->published))),
				'published' => 'yes',
				'body' => $entry->content,
				'tags' => ''
			);
			if (count ($entry->category) > 1) {
				$sep = '';
				for ($i = 1; $i < count ($entry->category); $i++) {
					$post['tags'] .= $sep . $entry->category[$i]->attributes ()->term;
					$sep = ', ';
				}
			}
			$p = new blog\Post ($post);
			if ($p->put ()) {
				Versions::add ($p);
				$imported++;
			}
		}
		
		echo '<p>' . i18n_getf ('Imported %d posts.', $imported) . '</p>';
		echo '<p><a href="/blog/admin">' . i18n_get ('Continue') . '</a></p>';
		return;
	} else {
		echo '<p><strong>' . i18n_get ('Error uploading file.') . '</strong></p>';
	}
}

$o = new StdClass;

echo $tpl->render ('blog/import/blogger', $o);

?>