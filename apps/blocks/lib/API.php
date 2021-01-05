<?php

namespace blocks;

use Block;

class API extends \Restful {
	/**
	 * Update the sorting order of a block group.
	 */
	public function post_update_order () {
		if (! isset ($_POST['order_id'])) return $this->error ('Missing parameter: order_id');
		if (! isset ($_POST['block_ids'])) return $this->error ('Missing parameter: block_ids');
		
		$order = new GroupOrder ($_POST['order_id']);
		
		if ($order->error) {
			// Doesn't exist yet
			$order = new GroupOrder (['order_id' => $_POST['order_id']]);
		}
		
		$order->set_order ($_POST['block_ids']);
		
		if (! $order->put ()) {
			error_log ($order->error);
			return $this->error ('Internal server error.');
		}
		
		return $order->orig ();
	}
	
	/**
	 * Update the column layout for a row in a block group.
	 */
	public function post_update_column_layout () {
		if (! isset ($_POST['block_id'])) return $this->error ('Missing parameter: block_id');
		if (! isset ($_POST['column_layout'])) return $this->error ('Missing parameter: column_layout');

		$block = new Block ($_POST['block_id']);
		
		if ($block->error) {
			return $this->error ('Block not found.');
		}
		
		if (! in_array ($_POST['column_layout'], Block::$column_layouts)) {
			return $this->error ('Invalid column layout');
		}
		
		$block->column_layout = $_POST['column_layout'];
		
		if (! $block->put ()) {
			error_log ($block->error);
			return $this->error ('Internal server error.');
		}
		
		return true;
	}
}
