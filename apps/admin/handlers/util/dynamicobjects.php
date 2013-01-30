<?php

/**
 * Provides a modal dialog to browse for dynamic objects to embed.
 *
 * Usage:
 *
 * 1. Load this handler either in your handler:
 *
 *     $this->run ('admin/util/dynamicobjects');
 *
 * Or anywhere in your view:
 *
 *      {! admin/util/dynamicobjects !}
 *
 * 2. Use the $.dynamicobjects() function to open the dialog window:
 *
 *      $.dynamicobjects ({
 *          set_value: '#field-id',
 *          callback: function (embed_code, handler, params) {
 *              console.log (embed_code);
 *              console.log (handler);
 *              console.log (params);
 *          }
 *     });
 *
 * Options:
 *
 * - callback:  A function to call with the resulting embed code.
 * - set_value: The selector of an input field to update with the
 *   resulting embed code.
 */

$this->run ('admin/util/modal');

$page->add_script ('/apps/admin/js/jquery.dynamicobjects.js');
$page->add_script (
	I18n::export (
		array (
			'Dynamic Objects'
		)
	)
);

?>