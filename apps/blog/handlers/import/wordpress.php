<?php

/**
 * Implements a blog post importer from a Wordpress export file.
 */

$this->require_admin ();

$page->layout = 'admin';
$page->title = __ ('Wordpress importer');

if (! class_exists ('SimpleXMLElement')) {
	echo '<p>' . __ ('Please install the php-xml extension to use the Wordpress importer.') . '</p>';
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
	function blog_import_wordpress_fix_links ($matches) {
		$url = trim ($matches[0], '"');
		$file = 'files/imported/' . str_replace ('/', '-', $matches[1]);
		if (! file_exists ($file)) {
			file_put_contents ($file, fetch_url ($url));
			chmod ($file, 0666);
		}
		return '"/' . $file . '"';
	}

	if (move_uploaded_file ($_FILES['import_file']['tmp_name'], 'cache/blog_' . $_FILES['import_file']['name'])) {
		$file = 'cache/blog_' . $_FILES['import_file']['name'];
		
		$imported_posts = $imported_pages = 0;
		
		$links = array ();

		try {
			$posts = new SimpleXMLElement (file_get_contents ($file));
			
			foreach ($posts->channel->item as $entry) {
				$dc = $entry->children ('http://purl.org/dc/elements/1.1/');
				$content = $entry->children ('http://purl.org/rss/1.0/modules/content/');
				$wp = $entry->children ('http://wordpress.org/export/1.2/');
				
				switch ($wp->post_type) {
					
					case 'post':
						
						$published = $wp->status == 'publish' ? 'yes' : 'no';

						$body = str_replace ("\n", "<br />\n", (string) $content->encoded);
						$body = preg_replace_callback (
							'|"https?://[^"]+?/wp-content/uploads/([^"]+)?"|i',
							'blog_import_wordpress_fix_links',
							$body
						);

						$post = array (
							'title' => (string) $entry->title,
							'author' => (string) $dc->creator,
							'ts' => gmdate ('Y-m-d H:i:s', strtotime ($entry->pubDate)),
							'published' => $published,
							'body' => $body,
							'tags' => ''
						);
						$sep = '';
						for ($i = 0; $i < count ($entry->category); $i++) {
							if ('uncategorized' != ($tag = urldecode ($entry->category[$i]->attributes ()->nicename))) {
								$post['tags'] .= $sep . $tag;
							}
							$sep = ', ';
						}
						$p = new blog\Post ($post);
						if ($p->put ()) {
							Versions::add ($p);

							// add tags
							if ($published == 'yes' && ! empty ($post['tags'])) {
								$tags = explode (',', $post['tags']);
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
							
							$links[url_get_fullpath ((string)$entry->link)] = '/blog/post/' . $p->id;

							$imported_posts ++;
						}						
						
						break;
					
					case 'page':
						
						if ($wp->status == 'publish') {
							
							$body = str_replace ("\n", "<br />\n", (string) $content->encoded);
							$body = preg_replace_callback (
								'|"https?://[^"]+?/wp-content/uploads/([^"]+)?"|i',
								'blog_import_wordpress_fix_links',
								$body
							);

							$page_data = array (
								'id' => $wp->post_name,
								'title' => (string) $entry->title,
								'body' => $body
							);
							
							$wp = new Webpage ($page_data);
							$wp->put ();
							Versions::add ($wp);
							if ($wp->error) {
								throw new Exception (__ ('Cannot create page') . ': ' . $wp->error);
							}
							
							$links[url_get_fullpath ((string)$entry->link)] = '/' . $page_data['id'];
							
							$imported_pages ++;
						}
						
						break;					
				}
				
			}
			
			echo $tpl->render ('blog/import/wordpress_done', array (
				'imported_posts' => $imported_posts,
				'imported_pages' => $imported_pages,
				'links' => $links
			));
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

echo $tpl->render ('blog/import/wordpress', $o);