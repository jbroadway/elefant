<?php
/**
 * Script includes for converting a standard website into a single-page app.
 * Also adds any script helpers as defined from a comma separated list.
 * This is NOT an advanced framework for single-page apps. This utility is
 * simply for catching links and fetching any normal $page->body data via ajax,
 * then replacing the existing body content on the webpage. This utility
 * does NOT handle forms.
 *
 * Usage:
 *
 * This utility should be called to in the HEAD of the
 * desired layout AFTER {! admin/head !} has been called.
 *
 * {! admin/util/async !}
 *
 * To deal with the differences between how the setup of scripts can differ,
 * there are two parts to assist in managing these scripts.
 *
 * First is triggers. These are jQuery.trigger() events to manage the DOM and
 * ajax data along different points of the ajax fetch. They are triggered on
 * the $(document) node.
 *
 * Called before the ajax request is made:
 * 		$(document).trigger('async_start');
 *
 * Called upon successful RESTful data return 
 * and before DOM insertion happens:
 *		$(document).trigger('async_pre');
 *
 * Called after the DOM insertion happens:
 *		$(document).trigger('async_post');
 *
 * Called if all data operations have succeeded:
 *		$(document).trigger('async_success');
 *
 * Called if a RESTful error is returned:
 *		$(document).trigger('async_error');
 *
 * Called if the ajax request fails:
 *		$(document).trigger('async_fail');
 *
 * Called after the ajax request has finished:
 *		$(document).trigger('async_end');
 *
 * Second is helpers. These are pre-made scripts that make use of
 * the above triggers to manage and assist scripts that are loaded
 * in via the ajax request.
 *
 * Helpers can be requested via comma separated 'helpers' parameter.
 * The script naming convention is "helper.app.handler.js".
 *
 * {! admin/util/async?helpers=polls,events.calendar,myapp.handler !}
 *
 */

$page->add_script('/apps/admin/js/async/core.js','head');

$helpers = ($data['helpers'])?$data['helpers']:($_GET['helpers'])?$_GET['helpers']:false;
if ($helpers) {
	$helpers = array_map(trim,explode(',',$helpers));
	foreach ($helpers as $helper) {
		$page->add_script('/apps/admin/js/async/helper.'. $helper .'.js','head');
	}
}