<?php

/**
 * Provides a public-accessible notifier similar to Elefant's
 * `Controller::add_notification()` mechanism for admin users.
 *
 * Usage:
 *
 *     // Initialize in PHP
 *     $this->run ('admin/util/notifier');
 *     
 *     // Initialize in a view template
 *     {! admin/util/notifier !}
 *     
 *     // Add notice in PHP
 *     Notifier::add_notice ('My notification.');
 *
 *     // Add notice in JavaScript
 *     $.add_notice ('My notification.');
 */

$page->add_style ('/apps/admin/css/jquery.notifier.css');
$page->add_script ('/js/jquery.cookie.js');
$page->add_script ('/apps/admin/js/jquery.jgrowl.min.js');
$page->add_script ('/apps/admin/js/jquery.notifier.js');
