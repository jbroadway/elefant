/**
 * Manages block group reordering.
 */
(function ($) {
	var get_outer = function ($button) {
		return $button.closest ('.block-outer');
	};
	
	var get_wrapper = function ($el) {
		return $el.closest ('.block-group-wrapper');
	}
	
	var get_updated_order = function ($outer) {
		$children = $outer.closest ('.block-group-wrapper').children ('.block-outer');
		
		var ids = [];
		
		$children.each (function () {
			ids.push ($(this).data ('block-id'));
		});
		
		return ids;
	}
	
	var save_updated_order = function (order_id, block_ids) {
		var params = {
			order_id: order_id,
			block_ids: block_ids
		};
		
		$.post ('/blocks/api/update_order', params);
	}
	
	$('.block-move-up').on ('click', function (e) {
		e.preventDefault ();

		$outer = get_outer ($(e.target));
		$outer.insertBefore ($outer.prev ());
		
		var order_id = $outer.data ('order-id'),
			ids = get_updated_order ($outer);
		
		save_updated_order (order_id, ids);
	});
	
	$('.block-move-down').on ('click', function (e) {
		e.preventDefault ();

		$outer = get_outer ($(e.target));
		$outer.insertAfter ($outer.next ());
		
		var order_id = $outer.data ('order-id'),
			ids = get_updated_order ($outer);
		
		save_updated_order (order_id, ids);
	});
})(jQuery);
