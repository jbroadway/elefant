/**
 * Responsive grid-based inline editing. See:
 *
 *     apps/admin/handlers/grid.php
 *
 * Usage:
 *
 *     $('.e-grid').grid ({
 *         id: '{{id}}',
 *         styles: {{admin\Layout::styles ()|json_encode}},
 *         api: '/admin/grid/api'
 *     });
 */
(function ($, H) {
	var initialized = false,
		tpl = {
			row:			'#tpl-grid-row',
			add:			'#tpl-grid-add',
			add_button:		'#tpl-grid-add-button'
		};
	
	// attach via $.proxy (add_row, $this)
	function add_row (e) {
		e.preventDefault ();

		var $this = this,
			$add = $(e.target),
			$row = $(tpl.add ({
			id: $this.data ('id'),
			row: $this.rows ().length,
			css_class: '',
			variable: false,
			fixed: false,
			styles: $this.opts.styles
		})).insertBefore ($add).velocity ('transition.slideDownIn', 200);
		
		$row.find ('.e-grid-cancel-link')
			.click ($.proxy (cancel_row, $this));
	}

	// attach via $.proxy (cancel_row, $this);
	function cancel_row (e) {
		e.preventDefault ();
		
		var $this = this,
			$cancel = $(e.target);
		
		$cancel.parent ('.e-grid-row').remove ();
	}
	
	// attach via $.proxy (get_rows, $this)
	function get_rows () {
		var $this = this;

		return $this.children ('.e-grid-row');
	}

	$.fn.extend ({
		grid: function (options) {
			var defaults = {
				id: '',
				styles: {},
				api: '/admin/grid/api'
			};
			
			if (! initialized) {
				// compile templates
				for (var k in tpl) {
					tpl[k] = H.compile ($(tpl[k]).html ());
				}
				initialized = true;
			}
			
			options = $.extend (defaults, options);
			
			return this.each (function () {
				var $this = $(this);
				
				$this.opts = options;
				$this.rows = $.proxy (get_rows, $this);

				// Create and connect 'Add row' button
				$this.append (tpl.add_button ({ id: $this.opts.id }));
				$this.find ('.e-grid-add-button button')
					.click ($.proxy (add_row, $this));
			});
		}
	});
})(jQuery, Handlebars);

