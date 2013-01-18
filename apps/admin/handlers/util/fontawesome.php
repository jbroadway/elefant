<?php

/**
 * Loads the Font Awesome icon set so you can use it in your
 * view templates.
 *
 * In PHP, load it like this:
 *
 *     $this->run ('admin/util/fontawesome');
 *
 * Or in your view template, load it like this:
 *
 *     {! admin/util/fontawesome !}
 *
 * Now you can use any Font Awesome icon:
 *
 *     <i class="icon-cogs"></i>
 */

if (detect ('msie 7')) {
	$page->add_style ('/apps/admin/css/font-awesome/css/font-awesome-ie7.css');
}
$page->add_style ('/apps/admin/css/font-awesome/css/font-awesome.css');

?>