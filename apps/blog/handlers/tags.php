<?php

if (! $this->internal) {
	$page->layout = $appconf['Blog']['layout'];
	$page->title = i18n_get ('Tags');
}

$pg->limit = 10;
$pg->tags = blog\Post::tags ();
foreach ($pg->tags as $k => $v) {
	$pg->tags[$k] = ($v / 10 < 2) ? $v / 10 + .9 : (($v / 10 >= 2) ? 3 : $v / 10);
}

echo $tpl->render ('blog/tags', $pg);

$page->add_script (sprintf (
	'<link rel="alternate" type="application/rss+xml" href="http://%s/blog/rss" />',
	$_SERVER['HTTP_HOST']
));

?>