<?php

/**
 * Outputs the admin toolbar if the user is an admin,
 * otherwise simply loads jQuery for other scripts that
 * may rely on it.
 */

if (User::require_admin ()) {
	$appconf['Scripts']['jquery_source'] = 'local';
}

if ($appconf['Scripts']['jquery_source'] === 'local') {
	$page->add_script ('/js/jquery-1.12.4.min.js');
} elseif ($appconf['Scripts']['jquery_source'] === 'google') {
	$page->add_script ('<script src="//ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js"></script>');
} elseif ($appconf['Scripts']['jquery_source'] != false) {
	$page->add_script ('<script src="' . $appconf['Scripts']['jquery_source'] . '"></script>');
}

if (User::require_admin () && $page->preview == false) {
	$page->add_style ('/apps/admin/css/jquery.jgrowl.css');
	$page->add_style ('/apps/admin/css/modal.css');
	$page->add_style (Product::toolbar_stylesheet ());
	$page->add_style ('/apps/admin/css/font-awesome/css/font-awesome.min.css');

	$page->add_script ("<script>\$(function(){\$.elefant_version='" . ELEFANT_VERSION . "';});</script>\n");
	$page->add_script ("<script>\$(function(){\$.elefant_updates=" . (int) conf ('General', 'check_for_updates') . ";});</script>\n");
	$page->add_script ('/apps/admin/js/modal.js');
	$page->add_script ('/apps/admin/js/jquery.jgrowl.min.js');
	$page->add_script ('/apps/admin/js/jquery.triggers.js');
	$page->add_script ('/js/jquery.cookie.js');
	$page->add_script ('/apps/admin/js/jquery.i18n.js');
	$page->add_script ('/apps/admin/js/top-bar.js?v=10');
	$page->add_script (I18n::export (
		'List keyboard shortcuts',
		'Keyboard shortcuts'
	));
}

$page->add_script ('<script>$(function(){$.htmlPrefilter=function(html){return html;};});</script>');