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

	// Cancel add row form.
	// Attach via $.proxy (cancel_row, $this);
	function cancel_row (e) {
		e.preventDefault ();
		
		var $this = this,
			$cancel = $(e.target);
		
		$cancel
			.closest ('.e-grid-row')
			.velocity (
				'slideUp',
				{
					duration: 500,
					complete: function () {
						$(this).remove ();
					}
				}
			);
	}
	
	// Change the style of the row based on selected
	// Attach via $.proxy (select_style, $this)
	function select_style (e) {
		e.preventDefault ();
		
		var $this = this,
			$select = $(e.target),
			$row = $select.closest ('.e-grid-row'),
			css_style = $select.find ('option:selected').val ();

		// clear existing styles
		for (var s in $this.opts.styles) {
			if (s !== '') {
				$row.removeClass (s);
			}
		}
		
		// add selected style
		$row.addClass (css_style);
	}
	
	// Create add row form.
	// Attach via $.proxy (add_row, $this)
	function add_row (e) {
		e.preventDefault ();

		var $this = this,
			$add = $(e.target),
			$row = $(
				tpl.add ({
					id: $this.data ('id'),
					row: $this.rows ().length,
					css_class: '',
					variable: false,
					fixed: false,
					styles: $this.opts.styles
				})
			).insertBefore ($add.closest ('.e-grid-add-button')).velocity ('slideDown', 500);
		
		$row.find ('.e-grid-cancel-link')
			.click ($.proxy (cancel_row, $this));
		$row.find ('.e-grid-select-style')
			.change ($.proxy (select_style, $this));
	}
	
	// Fetch all rows.
	// Attach via $.proxy (get_rows, $this)
	function get_rows () {
		var $this = this;

		return $this.children ('.e-grid-row');
	}
	
	// Fetch row for an element within it.
	function find_row (el) {
		return $(el).closest ('.e-grid-row');
	}

	// Start of jQuery.grid plugin
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
			
			// convert styles to objects
			for (var s in options.styles) {
				options.styles[s] = {css_class: s, name: options.styles[s]};
			}
			
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

