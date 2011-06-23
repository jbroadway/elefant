<?php

$wp = new Webpage ($_POST);

$page->id = $wp->id;
$page->title = $wp->title;
$page->menu_title = (! empty ($wp->menu_title)) ? $wp->menu_title : $wp->title;
$page->window_title = (! empty ($wp->window_title)) ? $wp->window_title : $wp->title;
$page->description = $wp->description;
$page->keywords = $wp->keywords;
$page->template = 'admin/base';
$page->layout = $wp->layout;
$page->head = $wp->head;

echo $wp->body;

?>