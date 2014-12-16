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
	public function post_update () {
		// TODO: Save changes

		// render and return
		$this->controller->add_notification (__ ('Changes saved.'));
		return array (
			'html' => $this->controller->template ()->run_includes ($_POST['content']),
			'scripts' => $this->controller->page ()->scripts
		);
	}
}
