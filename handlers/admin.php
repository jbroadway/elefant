<?php

auth_basic ();

$page->layout = 'admin';
$page->template = 'admin/index';

require_once ('models/Webpage.php');

$wp = new Webpage ();

$page->pages = $wp->query ()->order ('title asc')->fetch_assoc ('id', 'title');

?>