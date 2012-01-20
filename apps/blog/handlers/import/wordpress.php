<?php

/**
 * Implements a blog post importer from a Blogger.com export file.
 */

$this->require_admin ();

$page->layout = 'admin';
$page->title = i18n_get ('Wordpress importer');

$f = new Form ('post');

if ($f->submit ()) {
	if (move_uploaded_file ($_FILES['import_file']['tmp_name'], 'cache/blog_' . $_FILES['import_file']['name'])) {
		$file = 'cache/blog_' . $_FILES['import_file']['name'];
	
		$posts = new SimpleXMLElement (file_get_contents ($file));
		
		$imported = 0;
		
		foreach ($posts->channel->item as $entry) {
			$dc = $entry->children ('http://purl.org/dc/elements/1.1/');
			$content = $entry->children ('http://purl.org/rss/1.0/modules/content/');
			$post = array (
				'title' => (string) $entry->title,
				'author' => (string) $dc->creator,
				'ts' => gmdate ('Y-m-d H:i:s', strtotime ($entry->pubDate)),
				'published' => 'yes',
				'body' => str_replace ("\n", "<br />\n", (string) $content->encoded),
				'tags' => ''
			);
			$sep = '';
			for ($i = 0; $i < count ($entry->category); $i++) {
				$post['tags'] .= $sep . $entry->category[$i]->attributes ()->nicename;
				$sep = ', ';
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

echo $tpl->render ('blog/import/wordpress', $o);

?>