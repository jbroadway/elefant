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
	
	var cols_html = `<table class="editable-layout-options">
			<tr>
				<td><a href="#" class="editable-layout-option" data-layout="100"><img src="/apps/blocks/pix/layout/100.png" alt="100" /></a></td>
				<td><a href="#" class="editable-layout-option" data-layout="50-50"><img src="/apps/blocks/pix/layout/50-50.png" alt="50-50" /></a></td>
				<td><a href="#" class="editable-layout-option" data-layout="60-40"><img src="/apps/blocks/pix/layout/60-40.png" alt="60-40" /></a></td>
			</tr>
			<tr>
				<td><a href="#" class="editable-layout-option" data-layout="40-60"><img src="/apps/blocks/pix/layout/40-60.png" alt="40-60" /></a></td>
				<td><a href="#" class="editable-layout-option" data-layout="66-33"><img src="/apps/blocks/pix/layout/66-33.png" alt="66-33" /></a></td>
				<td><a href="#" class="editable-layout-option" data-layout="33-66"><img src="/apps/blocks/pix/layout/33-66.png" alt="33-66" /></a></td>
			</tr>
			<tr>
				<td><a href="#" class="editable-layout-option" data-layout="70-30"><img src="/apps/blocks/pix/layout/70-30.png" alt="70-30" /></a></td>
				<td><a href="#" class="editable-layout-option" data-layout="30-70"><img src="/apps/blocks/pix/layout/30-70.png" alt="30-70" /></a></td>
				<td><a href="#" class="editable-layout-option" data-layout="75-25"><img src="/apps/blocks/pix/layout/75-25.png" alt="75-25" /></a></td>
			</tr>
			<tr>
				<td><a href="#" class="editable-layout-option" data-layout="25-75"><img src="/apps/blocks/pix/layout/25-75.png" alt="25-75" /></a></td>
				<td><a href="#" class="editable-layout-option" data-layout="80-20"><img src="/apps/blocks/pix/layout/80-20.png" alt="80-20" /></a></td>
				<td><a href="#" class="editable-layout-option" data-layout="20-80"><img src="/apps/blocks/pix/layout/20-80.png" alt="20-80" /></a></td>
			</tr>
			<tr>
				<td><a href="#" class="editable-layout-option" data-layout="33-33-33"><img src="/apps/blocks/pix/layout/33-33-33.png" alt="33-33-33" /></a></td>
				<td><a href="#" class="editable-layout-option" data-layout="50-25-25"><img src="/apps/blocks/pix/layout/50-25-25.png" alt="50-25-25" /></a></td>
				<td><a href="#" class="editable-layout-option" data-layout="25-50-25"><img src="/apps/blocks/pix/layout/25-50-25.png" alt="25-50-25" /></a></td>
			</tr>
			<tr>
				<td><a href="#" class="editable-layout-option" data-layout="25-25-50"><img src="/apps/blocks/pix/layout/25-25-50.png" alt="25-25-50" /></a></td>
				<td><a href="#" class="editable-layout-option" data-layout="25-25-25-25"><img src="/apps/blocks/pix/layout/25-25-25-25.png" alt="25-25-25-25" /></a></td>
				<td><a href="#" class="editable-layout-option" data-layout="20-20-20-20-20"><img src="/apps/blocks/pix/layout/20-20-20-20-20.png" alt="20-20-20-20-20" /></a></td>
			</tr>
		</table>`;
	
	var modal_opts = {width: 640, height: 585};
	
	$('.block-move-up').on ('click', function (e) {
		e.preventDefault ();

		var $outer = get_outer ($(e.target));
		$outer.insertBefore ($outer.prev ());
		
		var order_id = $outer.data ('order-id'),
			ids = get_updated_order ($outer);
		
		save_updated_order (order_id, ids);
	});
	
	$('.block-move-down').on ('click', function (e) {
		e.preventDefault ();

		var $outer = get_outer ($(e.target));
		$outer.insertAfter ($outer.next ());
		
		var order_id = $outer.data ('order-id'),
			ids = get_updated_order ($outer);
		
		save_updated_order (order_id, ids);
	});
	
	$('.editable-add').on ('click', function (e) {
		e.preventDefault ();
		
		var $a = (e.target.nodeName == 'A') ? $(e.target) : $(e.target.parentNode),
			href = $a.attr ('href');
		
		$.open_dialog (window._i18n_.add_layout, cols_html, modal_opts);
		
		$('.editable-layout-option').on ('click', function (e) {
			// Append the chosen layout
			window.location.href = href + '&column_layout=' + $(this).data ('layout');
		})
	});
	
	$('.editable-layout').on ('click', function (e) {
		e.preventDefault ();
		
		var $outer = get_outer ($(e.target)),
			block_id = $outer.data ('block-id');
		
		$.open_dialog (window._i18n_.column_layout, cols_html, modal_opts);
		
		$('.editable-layout-option').on ('click', function (e) {
			e.preventDefault ();
			
			var params = {
				block_id: block_id,
				column_layout: $(this).data ('layout')
			};
		
			$.post ('/blocks/api/update_column_layout', params)
				.done (function (res) {
					// Render the other columns by reloading the page
					location.reload ();
				})
				.fail (function (res) {
					alert (res.error);
				});
		});
	});
})(jQuery);
