<?php

/**
 * Provides a multi-image selector based on the image browser
 * from filemanager/util/browser.
 *
 * Usage:
 *
 * 1. Load this handler either in your handler:
 *
 *     $this->run ('filemanager/util/multi-image');
 *
 * Or anywhere in your view:
 *
 *     {! filemanager/util/multi-image !}
 *
 * 2. Create the HTML elements for the input field and the
 * preview area:
 *
 *     <p>
 *         {"Attach images"}:
 *         <div id="preview"></div>
 *         <input type="hidden" name="images" id="images" />
 *     </p>
 *
 * 3. Use the $.multi_image() function to initialize the plugin:
 *
 *     $.multi_image ({
 *         field: '#images',
 *         preview: '#preview'
 *     });
 *
 * This helper stores the list of images in the following format
 * in the input field, so you can easily split them into an array:
 *
 *     /files/file1.jpg|/files/file2.jpg|/files/file3.png
 *
 * Options:
 *
 * - field:   The selector of an input field to update with the list.
 * - preview: The selector of an element to use to contain the list preview.
 */

$this->run ('filemanager/util/browser');

$page->add_style ('/apps/filemanager/css/multi-image.css');
$page->add_script ('/apps/filemanager/js/jquery.multi-image.js');
$page->add_script (I18n::export (
	'Click to remove',
	'Browse images'
));

?>