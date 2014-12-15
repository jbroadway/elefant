<?php

/**
 * Creates a responsive grid of rows and columns.
 *
 *     echo $this->run ('admin/grid', array (
 *         'id' => $webpage->id,
 *         'rows' => $webpage->body (),
 *         'api' => '/admin/grid/api'
 *     ));
 *
 * The `api` parameter is used as a prefix for the REST API to call
 * for admin usage for inline editing. This can be changed so you
 * can use the grid for other things than simply the page body,
 * while still using the same editing facilities.
 *
 * To output the page body with a variable width grid, use:
 *
 *     {{ body|none }}
 * 
 * To output the page body with a fixed width grid, use:
 * 
 *     {{ body|fixed }}
 *
 * This is a filter that simply search and replaces `e-row-variable`
 * with `e-row` in the output.
 *
 * Output has the following structure and classes:
 *
 *     <div class="e-grid" data-id="{id}">
 *         <div class="e-grid-row {css_class}" data-id="{id}" data-row="{row}">
 *             <div class="e-row-variable {equal_height}">
 *                 <div class="e-col-50 e-grid-col" data-id="{id}" data-row="{row}" data-col="{col}">
 *                     {content}
 *                 </div>
 *             </div>
 *         </div>
 *     </div>
 *
 * The template values in the above are:
 *
 * - id           - The page ID
 * - css_class    - An optional CSS class to apply to rows
 * - equal_height - Whether to add `e-row-equal` for equal height columns
 * - row          - The row number starting from zero
 * - col          - The column number starting from zero
 * - content      - The HTML contents of a column
 * - inset        - Whether to add `e-row-inset` class
 */

$id = (count ($this->params) > 0) ? $this->params[0] : (isset ($data['id']) ? $data['id'] : $page->id);
$grid = (isset ($data['grid']) && is_object ($data['grid'])) ? $data['grid'] : new admin\Grid ();
$api = isset ($data['api']) ? $data['api'] : '/admin/grid/api';

echo $this->run ('admin/util/minimal-grid');

if (User::require_acl ('admin', 'admin/edit')) {
	echo $this->run ('admin/util/fontawesome');
	echo $this->run ('admin/util/dynamicobjects');
	echo $this->run ('admin/util/wysiwyg');
	echo $this->run ('filemanager/util/browser');

	$page->add_script ('apps/admin/js/handlebars-v2.0.0.js');
	$page->add_script ('apps/admin/js/velocity.min.js');
	$page->add_script ('apps/admin/js/velocity.ui.js');
	$page->add_script ('apps/admin/js/jquery.grid.js');
	$page->add_style ('apps/admin/css/jquery.grid.css');
	$page->add_script (
		$tpl->render (
			'admin/grid',
			array (
				'styles' => admin\Layout::styles ($page->layout),
				'api' => $api
			)
		)
	);
}

// open grid
echo "<div class=\"e-grid\" id=\"e-grid-{$id}\" data-id=\"{$id}\">\n";

foreach ($grid as $r => $row) {
	// open row wrapper
	echo '<div class="e-grid-row';
	if ($row->css_class !== '') {
		echo ' ' . $row->css_class;
	}
	if ($row->fixed) {
		echo ' e-fixed';
	}
	if ($row->equal_heights) {
		echo ' e-row-equal';
	}
	if ($row->inset) {
		echo ' e-inset';
	}
	if ($row->height !== '') {
		echo '" style="height: ' . $row->height . 'px';
	}
	echo '" id="e-row-' . $id . '-' . $r . '" data-id="' . $id . '" data-row="' . $r . '"';
	if ($row->bg_image !== '') {
		echo ' style="background: url(\'' . Template::sanitize ($row->bg_image) . '\');'
			. ' background-repeat: no-repeat;'
			. ' background-position: center top;'
			. ' -webkit-background-size: cover;'
			. ' -moz-background-size: cover;'
			. ' -o-background-size: cover;'
			. ' background-size: cover;"';
	}
	echo ">\n";
	
	// open row
	echo '<div class="e-row-variable';
	if ($row->equal_height) {
		echo ' e-row-equal';
	}
	echo "\">\n";

	$units = explode (',', $row->units);
	
	foreach ($row->cols as $c => $col) {
		$col_id = 'e-grid-' . $id . '-' . $r . '-' . $c;
		$empty = (strlen ($col) === 0) ? true : false;
		$empty_class = $empty ? ' e-grid-col-empty' : '';
		echo "<div class=\"e-col-{$units[$c]} e-grid-col{$empty_class}\" id=\"{$col_id}\" data-id=\"{$id}\" data-row=\"{$r}\" data-col=\"{$c}\">\n";
		if (! $empty) {
			echo $tpl->run_includes ($col);
		}
		echo "</div>\n";
	}
	
	// close row and wrapper
	echo "</div>\n</div>\n";
}

// close grid
echo "</div>\n";
