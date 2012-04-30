<?php

/**
 * Provides modal dialog capabilities to admin screens.
 * Loads and initializes the jQuery SimpleModal plugin,
 * and wraps it in a $.open_dialog(title, html) function.
 *
 * Usage:
 *
 * 1. Load this handler either in your handler:
 *
 *     $this->run ('admin/util/modal');
 *
 * Or anywhere in your view:
 *
 *     {! admin/util/modal !}
 *
 * 2. Use the $.open_dialog() function to create your
 * dialog windows:
 *
 *     $.open_dialog ('Title', 'HTML goes here');
 *
 * 3. To close the dialog programmatically, you can
 * use the $.close_dialog() function:
 *
 *     $.close_dialog ();
 */

$page->add_script ('/apps/admin/js/jquery.simplemodal.1.4.2.min.js');
$page->add_script ('/apps/admin/js/modal.js');
$page->add_style ('/apps/admin/css/modal.css');

?>