<?php

namespace admin\Grid;

use \admin\Grid;
use \Restful;
use \FileManager;
use \Webpage;
use \Versions;

class API extends Restful {
	/**
	 * API endpoint:
	 *
	 *     /admin/grid/api/update_column
	 *
	 * Saves specified column to the database. Also returns rendered
	 * version of the HTML for embeds.
	 *
	 * @param id
	 * @param row
	 * @param col
	 * @param content
	 */
	public function post_update_column () {
		// verify values
		if (! isset ($_POST['id'])) return $this->error (__ ('Error: Missing page ID.'));
		if (! isset ($_POST['row'])) return $this->error (__ ('Error: Missing row.'));
		if (! isset ($_POST['col'])) return $this->error (__ ('Error: Missing column.'));
		if (! isset ($_POST['content'])) return $this->error (__ ('Error: Missing content.'));
		if (! is_numeric ($_POST['row']) || ! is_numeric ($_POST['col'])) return $this->error (__ ('Error: Invalid data.'));

		// save changes
		$p = new Webpage ($_POST['id']);
		if ($p->error) return $this->error (__ ('Error: Page not found.'));

		$grid = $p->body ();
		if (! $grid->update ($_POST['row'], $_POST['col'], $_POST['content'])) {
			return $this->error (__ ('Error: Column not found.'));
		}
		$g = $grid->all ();
		$p->ext ('grid', $g);
		if (! $p->put ()) {
			return $this->error (__ ('Unexpected error.'));
		}
		
		Versions::add ($p);

		// render and return
		$this->controller->add_notification (__ ('Changes saved.'));
		return array (
			'html' => $this->controller->template ()->run_includes ($_POST['content']),
			'scripts' => $this->controller->page ()->scripts
		);
	}
	
	/**
	 * API endpoint:
	 *
	 *     /admin/grid/api/update_properties
	 *
	 * Updates one or more properties of a row at once.
	 *
	 * @param id
	 * @param row
	 * @param properties
	 */
	public function post_update_properties () {
		// verify values
		if (! isset ($_POST['id'])) return $this->error (__ ('Error: Missing page ID.'));
		if (! isset ($_POST['row'])) return $this->error (__ ('Error: Missing row.'));
		if (! isset ($_POST['properties'])) return $this->error (__ ('Error: No properties.'));
		if (! is_numeric ($_POST['row']) || ! is_array ($_POST['properties'])) return $this->error (__ ('Error: Invalid data.'));
		
		// save changes
		$p = new Webpage ($_POST['id']);
		if ($p->error) return $this->error (__ ('Error: Page not found.'));

		$grid = $p->body ();
		foreach ($_POST['properties'] as $property => $value) {
			if ($grid->property ($_POST['row'], $property, $value) === null) {
				return $this->error (__ ('Error: Row not found.'));
			}
		}
		$g = $grid->all ();
		$p->ext ('grid', $g);
		if (! $p->put ()) {
			return $this->error (__ ('Unexpected error.'));
		}
		
		Versions::add ($p);

		// render and return
		$this->controller->add_notification (__ ('Changes saved.'));
		return $_POST;
	}
	
	/**
	 * API endpoint:
	 *
	 *     /admin/grid/api/add_row
	 *
	 * Adds a row to the grid.
	 *
	 * @param id
	 * @param units
	 * @param cols
	 * @param css_class
	 * @param bg_image
	 * @param inset
	 * @param fixed
	 * @param heights
	 * @param equal_heights
	 */
	public function post_add_row () {// verify values
		if (! isset ($_POST['id'])) return $this->error (__ ('Error: Missing page ID.'));
		
		// save changes
		$p = new Webpage ($_POST['id']);
		if ($p->error) return $this->error (__ ('Error: Page not found.'));

		$grid = $p->body ();

		if (! isset ($_POST['units'])) return $this->error (__ ('Error: Missing units.'));
		if (! $grid->valid_units ($_POST['units'])) return $this->error (__ ('Error: Invalid units.'));
		$units = $_POST['units'];

		$css_class = (isset ($_POST['css_class']) && preg_match ('/^[a-zA-Z0-9_-]+$/', $_POST['css_class']))
			? $_POST['css_class']
			: '';
		
		$bg_image = isset ($_POST['bg_image'])
			? $_POST['bg_image']
			: '';
		
		$fixed = (isset ($_POST['fixed']) && $_POST['fixed'] == 1)
			? true
			: false;

		$inset = (isset ($_POST['inset']) && $_POST['inset'] == 1)
			? true
			: false;

		$equal_heights = (isset ($_POST['equal_heights']) && $_POST['equal_heights'] == 1)
			? true
			: false;
		
		$height = (isset ($_POST['height']) && is_numeric ($_POST['height']))
			? $_POST['height']
			: '';
		
		$cols = array ();
		if (! isset ($_POST['cols']) || ! is_array ($_POST['cols'])) {
			return $this->error (__ ('Error: Invalid columns.'));
		}
		foreach ($_POST['cols'] as $col) {
			$cols[] = $col;
		}

		$grid->add_row (
			$units,
			$css_class,
			$equal_heights,
			$bg_image,
			$fixed,
			$inset,
			$height,
			$cols
		);
		$g = $grid->all ();
		$p->ext ('grid', $g);
		if (! $p->put ()) {
			return $this->error (__ ('Unexpected error.'));
		}
		
		Versions::add ($p);

		// render and return
		$this->controller->add_notification (__ ('Changes saved.'));
		return array (
			'units' => $units,
			'css_class' => $css_class,
			'equal_heights' => $equal_heights,
			'bg_image' => $bg_image,
			'fixed' => $fixed,
			'inset' => $inset,
			'height' => $height,
			'cols' => $cols
		);
	}
	
	/**
	 * API endpoint:
	 *
	 *     /admin/grid/api/delete_row
	 *
	 * Deletes a row to the grid.
	 *
	 * @param id
	 * @param row
	 */
	public function post_delete_row () {
		// verify values
		if (! isset ($_POST['id'])) return $this->error (__ ('Error: Missing page ID.'));
		if (! isset ($_POST['row'])) return $this->error (__ ('Error: Missing row.'));
		if (! is_numeric ($_POST['row'])) return $this->error (__ ('Error: Invalid row.'));
		
		// save changes
		$p = new Webpage ($_POST['id']);
		if ($p->error) return $this->error (__ ('Error: Page not found.'));

		$grid = $p->body ();
		$grid->delete_row ($_POST['row']);
		$g = $grid->all ();
		$p->ext ('grid', $g);
		if (! $p->put ()) {
			return $this->error (__ ('Unexpected error.'));
		}
		
		Versions::add ($p);

		// render and return
		$this->controller->add_notification (__ ('Changes saved.'));
		return true;
	}
}
