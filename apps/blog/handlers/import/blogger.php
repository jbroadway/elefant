<?php

/**
 * Implements a blog post importer from a Blogger.com export file.
 */

$this->require_admin ();

$page->layout = 'admin';
$page->title = __ ('Blogger importer');

if (! class_exists ('SimpleXMLElement')) {
	echo '<p>' . __ ('Please install the php-xml extension to use the Blogger importer.') . '</p>';
	echo '<p><a href="/blog/admin">' . __ ('Back') . '</a></p>';
	return;
}

$f = new Form ('post');

if ($f->submit ()) {
	set_time_limit (0);
	if (! is_dir ('files/imported')) {
		mkdir ('files/imported', 0777);
	}
	
	// download files from external site into files/imported
	function blog_import_blogger_fix_links ($matches) {
		$url = $matches[1];
		$file = 'files/imported/' . trim (str_replace ('/', '-', $matches[2] . $matches[3]), '-');
		$file = urldecode ($file);
		$file = str_replace ('+', '-', $file);
		if (! file_exists ($file)) {
			file_put_contents ($file, fetch_url ($url));
			chmod ($file, 0666);
		}
		return '"/' . $file . '"';
	}

	if (move_uploaded_file ($_FILES['import_file']['tmp_name'], 'cache/blog_' . $_FILES['import_file']['name'])) {
		$file = 'cache/blog_' . $_FILES['import_file']['name'];
		
		$imported = 0;
	
		try {
			$posts = new SimpleXMLElement (file_get_contents ($file));
			
			foreach ($posts->entry as $entry) {
				if (strpos ($entry->id, '.settings.BLOG_') !== false) {
					continue;
				}
				if (strpos ($entry->id, '.layout') !== false) {
					continue;
				}
				if (count ($entry->category) > 0) {
					$pos = strpos ($entry->category[0]->attributes ()->term, '#comment');
					if ($pos !== false) {
						continue;
					}
				}

				$body = $entry->content;
				$body = preg_replace_callback (
					'/"(https?:\/\/.*?\.blogspot\.com\/[^\s"<>]+?\/([^\s"<>\/]+?\.)(jpg|png|gif))"/i',
					'blog_import_blogger_fix_links',
					$body
				);
				
				$ts = explode ('.', $entry->published);
				$ts = array_shift ($ts);
				$ts = str_replace ('T', ' ', $ts);

				$post = array (
					'title' => (string) $entry->title,
					'author' => (string) $entry->author->name,
					'ts' => $ts,
					'published' => $_POST['published'],
					'body' => $body,
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
			
			echo '<p>' . __ ('Imported %d posts.', $imported) . '</p>';
			echo '<p><a href="/blog/admin">' . __ ('Continue') . '</a></p>';
		} catch (Exception $e) {
			echo '<p><strong>' . __ ('Error importing file') . ': ' . $e->getMessage () . '</strong></p>';
			echo '<p><a href="/blog/admin">' . __ ('Back') . '</a></p>';
		}
	
		if (file_exists ($file)) {
			unlink ($file);
		}
		
		return;
	} else {
		echo '<p><strong>' . __ ('Error uploading file.') . '</strong></p>';
	}
}

$o = new StdClass;

echo $tpl->render ('blog/import/blogger', $o);
