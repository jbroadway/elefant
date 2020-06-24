<?php

namespace blocks;

class API extends \Restful {
	/**
	 * Update the sorting order of a block group.
	 */
	public function update_order () {
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
}
