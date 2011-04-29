<?php

auth_basic ();

$page->layout = 'admin';
$page->template = 'admin/index';

$wp = new Webpage ();

$page->pages = $wp->query ()->order ('title asc')->fetch_assoc ('id', 'title');

?>