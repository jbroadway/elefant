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
 */

$id = (count ($this->params) > 0) ? $this->params[0] : (isset ($data['id']) ? $data['id'] : $page->id);
$grid = (isset ($data['grid']) && is_object ($data['grid'])) ? $data['grid'] : new admin\Grid ();
$api = isset ($data['api']) ? $data['api'] : '/admin/grid/api';

if (User::require_acl ('admin', 'admin/edit')) {
	$page->add_script ('apps/admin/js/jquery.grid.js');
	$page->add_script (
		$tpl->render (
			'admin/grid',
			array (
				'styles' => admin\Layout::styles (),
				'api' => $api
			)
		)
	);
}

echo $this->run ('admin/util/minimal-grid');

// open grid
echo "<div id=\"e-grid\" data-id=\"{$id}\">\n";

foreach ($grid as $r => $row) {
	// open row wrapper
	echo '<div class="e-grid-row';
	if ($row->css_class !== '') {
		echo ' ' . $row->css_class;
	}
	echo '" id="e-row-' . $id . '-' . $r . '"';
	if ($row->bg_image !== '') {
		echo ' style="background: url(\'' . Template::sanitize ($row->bg_image) . '\') no-repeat center center fixed;'
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
		echo "<div class=\"e-col-{$units[$c]}\" id=\"{$col_id}\">\n";
		echo $tpl->run_includes ($col);
		echo "</div>\n";
	}
	
	// close row and wrapper
	echo "</div>\n</div>\n";
}

// close grid
echo "</div>\n";
