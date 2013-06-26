<?php

/**
 * Call this to turn a textarea into an in-browser code editor
 * using the CodeMirror (http://codemirror.net/) editor.
 *
 * In PHP code, call it like this:
 *
 *     $this->run (
 *         'admin/util/codemirror',
 *         array (
 *             'field_id' => 'body'
 *         )
 *     );
 *
 * In a view template, call it like this:
 *
 *     {! admin/util/codemirror?field_id=my_field !}
 *
 * Alternately, you can disable the auto-initialization and call
 * it manually on an element like this:
 *
 *     {! admin/util/codemirror?field_id=0 !}
 *     
 *     <textarea id="edit-me"></textarea>
 *     
 *     <script>
 *     $(function () {
 *         var codemirror = CodeMirror.fromTextArea (
 *             document.getElementById ('my_field'),
 *             {
 *                 mode: 'text/html',
 *                 theme: 'default',
 *                 lineNumbers: true
 *             }
 *         );
 *     });
 *     </script>
 *
 * Additional parameters:
 *
 * - mode - The editor mode for syntax highlighting (default = htmlmixed)
 * - theme - The color theme to use (default = elegant)
 * - lineWrapping - Whether to wrap lines (default = false)
 */

$modes = array (
	'css' => 'text/css',
	'htmlmixed' => 'text/html',
	'http' => 'message/http',
	'javascript' => 'text/javascript',
	'json' => 'application/json',
	'php' => 'application/x-httpd-php',
	'xml' => 'application/xml'
);

$data['theme'] = isset ($data['theme']) ? $data['theme'] : 'elegant';
$data['lineWrapping'] = isset ($data['lineWrapping']) ? $data['lineWrapping'] : false;


$page->add_style ('/apps/designer/js/codemirror/lib/codemirror.css');
$page->add_style ('/apps/designer/js/codemirror/theme/' . $data['theme'] . '.css');
$page->add_script ('/apps/designer/js/codemirror/lib/codemirror.js');

if (isset ($data['mode'])) {
	$page->add_script ('/apps/designer/js/codemirror/mode/' . $data['mode'] . '/' . $data['mode'] . '.js');
	$data['mime'] = isset ($mimes[$data['mode']]) ? $mimes[$data['mode']] : 'text/x-' . $data['mode'];
} else {
	$page->add_script ('/apps/designer/js/codemirror/mode/xml/xml.js');
	$page->add_script ('/apps/designer/js/codemirror/mode/css/css.js');
	$page->add_script ('/apps/designer/js/codemirror/mode/javascript/javascript.js');
	$page->add_script ('/apps/designer/js/codemirror/mode/htmlmixed/htmlmixed.js');
	$data['mime'] = 'text/html';
}

$data['field_id'] = isset ($data['field_id'])
	? (($data['field_id'] === '0' || empty ($data['field_id'])) ? false : $data['field_id'])
	: 'code-body';

if ($data['field_id']) {
	$page->add_script ($tpl->render ('admin/util/codemirror', $data));
}

?>