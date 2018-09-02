<?php

/**
 * Provides modal dialog capabilities to app developers.
 * Supports nested dialogs too.
 *
 * Usage:
 *
 * Load this handler either in your handler:
 *
 *     $this->run ('admin/util/modal');
 *
 * Or anywhere in your view:
 *
 *     {! admin/util/modal !}
 *
 * Use the `$.open_dialog()` function to create your
 * dialog windows:
 *
 *     $.open_dialog ('Title', 'HTML goes here', options);
 *
 * To close the dialog programmatically, you can
 * use the `$.close_dialog()` function:
 *
 *     $.close_dialog ();
 *
 * Valid options are width and height. Note that `$.open_dialog()`
 * returns the current dialog number. A more top level dialog
 * number can be passed to `$.close_dialog()` and it will cascade
 * the close action to all child dialogs too.
 */

$page->add_script ('/apps/admin/js/modal.js?v=2');
$page->add_style ('/apps/admin/css/modal.css?v=2');
