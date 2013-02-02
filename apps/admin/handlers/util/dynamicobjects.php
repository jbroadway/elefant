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
 * - current:   Current embed code, for updating existing values.
 */

$this->run ('admin/util/fontawesome');
$this->run ('admin/util/modal');
$this->run ('filemanager/util/browser');

$page->add_style ('/apps/admin/css/dynamicobjects.css');
$page->add_script ('/js/jquery.verify_values.js');
$page->add_script ('/js/jquery.quickpager.js');
$page->add_script ('/apps/admin/js/jquery.dynamicobjects.js');
$page->add_script (
	I18n::export (
		array (
			'Dynamic Objects',
			'Unable to load the dynamic object list.',
			'Embed',
			'Back'
		)
	)
);

?>