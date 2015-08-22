<?php

/**
 * Displays a single blog post.
 */

// Check for a custom handler override
$res = $this->override ('blog/post');
if ($res) { echo $res; return; }

$page->id = 'blog';
$page->layout = Appconf::blog ('Blog', 'post_layout');

require_once ('apps/blog/lib/Filters.php');

$p = new blog\Post ($this->params[0]);

// post not found
if ($p->error) {
	return $this->error (404, __ ('Post not found'), '<p>' . __ ('Hmm, we can\'t seem to find the post you wanted at the moment.') . '</p>');
}

if ($p->published === 'no' && ! User::require_acl ('admin', 'blog')) {
	return $this->error (404, __ ('Post not found'), '<p>' . __ ('Hmm, we can\'t seem to find the post you wanted at the moment.') . '</p>');
}

// published if it was scheduled and it's time
if ($p->published === 'que') {
	if ($p->ts <= gmdate ('Y-m-d H:i:s')) {
		$p->published = 'yes';
		$p->put ();
		Versions::add ($p);
	} else {
	    return $this->error (404, __ ('Post not found'), '<p>' . __ ('Hmm, we can\'t seem to find the post you wanted at the moment.') . '</p>');
	}
}

$page->title = $p->title;

$post = $p->orig ();
$post->full = true;
$post->url = '/blog/post/' . $post->id . '/';
$post->fullurl = $post->url . URLify::filter ($post->title);
$post->tag_list = (strlen ($post->tags) > 0) ? explode (',', $post->tags) : array ();
if (Appconf::blog ('Blog', 'post_format') === 'html') {
	$post->body = $tpl->run_includes ($post->body);
} else {
	require_once ('apps/blog/lib/markdown.php');
	$post->body = $tpl->run_includes (Markdown ($post->body));
}
$post->social_buttons = Appconf::blog ('Social Buttons');
$post->related = Appconf::blog ('Blog', 'show_related_posts');

$footer = Appconf::blog ('Blog', 'post_footer');
$footer_stripped = strip_tags ($footer);
$post->footer = ($footer && ! empty ($footer_stripped))
	? $tpl->run_includes ($footer)
	: false;

echo $tpl->render ('blog/post', $post);

switch (Appconf::blog ('Blog', 'comments')) {
	case 'disqus':
		echo $this->run ('blog/disqus/comments', $post);
		break;
	case 'facebook':
		echo $this->run ('social/facebook/comments', $post);
		break;
	default:
		if (Appconf::blog ('Blog', 'comments') != false) {
			echo $this->run (
				Appconf::blog ('Blog', 'comments'),
				array (
					'identifier' => $post->url
				)
			);
		}
		break;
}

// add rss discovery
$page->add_script (sprintf (
	'<link rel="alternate" type="application/rss+xml" href="http://%s/blog/rss" />',
	$_SERVER['HTTP_HOST']
));

// add opengraph/twitter card meta tags
$url = ($this->is_https () ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . $post->fullurl;
$desc = blog_filter_truncate (strip_tags ($post->body), 300);

$page->add_meta ('og:type', 'article', 'property');
$page->add_meta ('og:site_name', conf ('General', 'site_name'), 'property');
$page->add_meta ('og:title', $post->title, 'property');
$page->add_meta ('og:description', $desc, 'property');
$page->add_meta ('og:url', $url, 'property');

if ($post->thumbnail !== '') {
	$page->add_meta (
		'og:image',
		($this->is_https () ? 'https' : 'http') . '://'. $_SERVER['HTTP_HOST'] . $post->thumbnail,
		'property'
	);
}

$page->add_meta ('twitter:card', 'summary_large_image');
$page->add_meta ('twitter:title', $post->title);
$page->add_meta ('twitter:description', $desc);
$twitter_id = Appconf::user ('Twitter', 'twitter_id');
if (is_string ($twitter_id) && $twitter_id !== '') {
	$page->add_meta ('twitter:site', '@' . $twitter_id);
}

if ($post->thumbnail !== '') {
	$page->add_meta (
		'twitter:image',
		($this->is_https () ? 'https' : 'http') . '://'. $_SERVER['HTTP_HOST'] . $post->thumbnail
	);
}
