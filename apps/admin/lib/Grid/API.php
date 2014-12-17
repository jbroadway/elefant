<?php

namespace admin\Grid;

use \admin\Grid;
use \Restful;

class API extends Restful {
	/**
	 * Saves specified column to the database. Also returns rendered
	 * version of the HTML for embeds.
	 *
	 * @param id
	 * @param row
	 * @param col
	 * @param content
	 */
	public function post_update_column () {
		// TODO: Verify values
		// TODO: Save changes

		// render and return
		$this->controller->add_notification (__ ('Changes saved.'));
		return array (
			'html' => $this->controller->template ()->run_includes ($_POST['content']),
			'scripts' => $this->controller->page ()->scripts
		);
	}
	
	/**
	 * Updates all columns to the database at once. Does not return
	 * a rendered version, simply true or false. The `grid` parameter
	 * is an object matching the structure stored by `admin\Grid`.
	 *
	 * @param id
	 * @param grid
	 */
	public function post_update () {
		// TODO: Verify changes
		// TODO: Save changes
	}
}
