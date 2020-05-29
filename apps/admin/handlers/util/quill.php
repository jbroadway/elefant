<?php

/**
 * Call this to load the required libraries for the Quill editor.
 * Note: For a full editor with the Elefant integrations, see
 * `admin/util/wysiwyg` instead.
 *
 * In PHP code, call it like this:
 *
 *     $this->run ('admin/util/quill');
 *
 * In a view template, call it like this:
 *
 *     {! admin/util/quill !}
 *
 * From here, you can initialize Redactor like this:
 *
 *     var editor = new Quill ('#my-textarea');
 */

$version = '1.3.6';

$page->add_style ('/apps/admin/js/quill/quill.snow.css?v=' . $version);
//$page->add_style ('/apps/admin/js/redactor/custom.css?v=' . $version);

$page->add_script ('/apps/admin/js/quill/quill.min.js?v=' . $version);
