<?php

/**
 * Elefant CMS - http://www.elefantcms.com/
 *
 * Copyright (c) 2011 Johnny Broadway
 * 
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 * 
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 * 
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */

namespace blocks;

/**
 * Model for managing the sorting order of content block groups.
 *
 * Usage:
 *
 * To fetch a match for a wildcard block group:
 *
 *     $order_id = $wildcard_string;
 *     $order = blocks\GroupOrder::for ($order_id);
 *
 * To fetch a match for a set of block IDs:
 *
 *     $order_id = join (',', $block_ids);
 *     $order = blocks\GroupOrder::for ($order_id);
 *
 * To save an updated order for a block group:
 *
 *     $group_order = new blocks\GroupOrder ($order_id);
 *     $group_order->set_order ($block_ids);
 *     $group_order->put ();
 */
class GroupOrder extends \Model {
	public $table = '#prefix#block_group_order';
	
	public $key = 'order_id';
	
	/**
	 * Fetch the sorting_order field for a given order ID.
	 */
	public static function for ($order_id) {
		return self::field ($order_id, 'sorting_order');
	}
	
	/**
	 * Set the order as a set of block IDs.
	 */
	public function set_order ($block_ids) {
		$this->sorting_order = is_array ($block_ids)
			? join (',', $block_ids)
			: $block_ids;
	}
	
	/**
	 * Apply an order to a set of block IDs.
	 */
	public static function apply_order ($ids, $order) {
		$order = is_array ($order)
			? $order
			: explode (',', $order);
		
		$sorted = [];
		
		// Add known sorted blocks to the top
		foreach ($order as $id) {
			if (in_array ($id, $ids)) {
				$sorted[] = $id;
			}
		}
		
		// Add unknown blocks below
		foreach ($ids as $id) {
			if (! in_array ($id, $order)) {
				$sorted[] = $id;
			}
		}
		
		return $sorted;
	}
}
