<?php

require_once ('apps/blog/lib/Filters.php');

$p = new blog\Post;
$page->posts = $p->headlines ();
$page->template = 'blog/headlines';

?>