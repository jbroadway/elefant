<?php

/**
 * Renders the RSS feed for the blog.
 */

$res = $cache->get ('blog_rss');
if (! $res) {
	$p = new blog\Post;
	$page->posts = $p->latest (10, 0);
	$page->title = $appconf['Blog']['title'];
	$page->date = gmdate ('Y-m-d\TH:i:s');

	if (Appconf::blog ('Blog', 'post_format') === 'markdown') {
		require_once ('apps/blog/lib/markdown.php');
	}

	$preview_chars = (int) Appconf::blog('Blog', 'preview_chars') ? (int) Appconf::blog('Blog', 'preview_chars') : false;
	if ($preview_chars) {
		require_once ('apps/blog/lib/Filters.php');
	}

	foreach ($page->posts as $k => $post) {
		$page->posts[$k]->url = '/blog/post/' . $post->id . '/' . URLify::filter ($post->title);
		if (Appconf::blog ('Blog', 'post_format') === 'html') {
			$page->posts[$k]->body = $tpl->run_includes ($page->posts[$k]->body);
		} else {
			$page->posts[$k]->body = $tpl->run_includes (Markdown ($page->posts[$k]->body));
		}
		
		if ($post->thumbnail !== '') {
			$page->posts[$k]->image = ($this->is_https () ? 'https' : 'http') . '://'. Appconf::admin ('Site Settings', 'site_domain') . str_replace (' ', '%20', $post->thumbnail);
		} else {
			$page->posts[$k]->image = '';
		}
		
		// Strip script, iframe, link, and video tags
		$html = preg_replace ('#<script(.*?)>(.*?)</script>#is', '', $page->posts[$k]->body);
		$html = preg_replace ('#<style(.*?)>(.*?)</style>#is', '', $page->posts[$k]->body);
		$html = preg_replace ('#<iframe(.*?)>(.*?)</iframe>#is', '', $html);
		$html = preg_replace ('#<link(.*?)>#is', '', $html);
		$html = preg_replace ('#<video(.*?)>(.*?)</video>#is', '', $html);
		
		// Make sure all URLs are absolutized
		$html = preg_replace ('/(src|href)="\//i', '\1="' . $this->absolutize ('/'), $html);
		
		$page->posts[$k]->body = $html;
		
		if ($preview_chars) {
			$page->posts[$k]->body = blog_filter_truncate ($page->posts[$k]->body, $preview_chars);
		}
	}

	$res = $tpl->render ('blog/rss', $page);
	$cache->set ('blog_rss', $res, 1800); // half an hour
}
$page->layout = FALSE;
header ('Content-Type: text/xml; charset=UTF-8');
echo $res;
