/**
 * Responsive grid-based inline editing. See:
 *
 *     apps/admin/handlers/grid.php
 *
 * Usage:
 *
 *     $('.e-grid').grid ({
 *         styles: {{admin\Layout::styles ()|json_encode}},
 *         api: '/admin/grid/api',
 *         units: ['100','50,50','33,33,33','25,25,25,25']
 *     });
 */
(function ($, H) {
	var initialized = false,
		tpl = {
			row:			'#tpl-grid-row',
			add:			'#tpl-grid-add',
			add_button:		'#tpl-grid-add-button'
		}
		i18n = {
			choose_bg: 'Choose a background image'
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
	
	// Select a grid choice
	// Attach via $.proxy (select_grid, $this);
	function select_grid (e) {
		e.preventDefault ();
		
		var $this = this,
			$choice = $(e.target);
		
		$choice.siblings ('.e-grid-icon').removeClass ('e-grid-icon-highlighted');		
		$choice.addClass ('e-grid-icon-highlighted');
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
	
	// Change the background image fo the row
	// Attach via $.proxy (background_chooser, $this)
	function background_chooser (e) {
		e.preventDefault ();
		
		var $row = $(e.target).closest ('.e-grid-row');
		
		$.filebrowser ({
			allowed: ['jpg', 'jpeg', 'png', 'gif'],
			title: i18n.choose_bg,
			callback: function (file) {
				$row.css ({
					'background-image': 'url(\'' + file + '\')',
					'background-repeat': 'no-repeat',
					'background-position': 'center top',
					'-webkit-background-size': 'cover',
					'-moz-background-size': 'cover',
					'-o-background-size': 'cover',
					'background-size': 'cover'
				});
			}
		});
	}
	
	// Clear background image
	// Attach via $.proxy (background_clear, $this)
	function background_clear (e) {
		e.preventDefault ();
		
		var $row = $(e.target).closest ('.e-grid-row');
		
		$row.removeAttr ('style');
	}
	
	// Change the height of the row
	// Attach via $.proxy (background_chooser, $this)
	function set_height (e) {
		e.preventDefault ();
		
		var $target = $(e.target),
			$row = $(e.target).closest ('.e-grid-row');
		
		$row.css ({height: $target.val () + 'px', 'min-height': $target.val () + 'px'});
	}
	
	// Change the height of the row
	// Attach via $.proxy (background_chooser, $this)
	function toggle_inset (e) {
		e.preventDefault ();
		
		var $target = $(e.target),
			checked = $target.is (':checked'),
			$row = $(e.target).closest ('.e-grid-row');

		if (checked) {
			$row.removeClass ('e-no-inset').addClass ('e-inset');
		} else {
			$row.removeClass ('e-inset').addClass ('e-no-inset');
		}
	}
	
	// Toggle tabs on click
	function toggle_active_tab (e) {
		e.preventDefault ();

		var $target = $(e.target),
			$toggle = $target.closest ('.e-grid-toggle'),
			$row = $target.closest ('.e-grid-row'),
			$tabs = $row.find ('.e-grid-tabs .e-grid-tab');
		
		// change active tab
		$toggle.find ('a').removeClass ('e-toggle-active');
		$target.addClass ('e-toggle-active');

		// change visible section
		$tabs.hide ();
		$row.find ('.e-tab-' + $target.data ('tab')).show ();
	}
	
	// Create add row form.
	// Attach via $.proxy (add_row, $this)
	function add_row_form (e) {
		e.preventDefault ();

		var $this = this,
			$add = $(e.target),
			$row = $(
				tpl.add ({
					id: $this.data ('id'),
					row: $this.rows ().length,
					css_class: '',
					variable: $this.opts.variable,
					fixed: false,
					styles: $this.opts.styles
				})
			);
		
		// show/hide unit options
		$row.find ('.e-grid-icon').css ({display: 'none'});
		for (var u in $this.opts.units) {
			$row.find ('.e-grid-icon-' + $this.opts.units[u].replace (/,/g, '-')).css ({display: 'inline-block'});
		}
		
		$row.insertBefore ($add.closest ('.e-grid-add-button')).velocity ('slideDown', 500);
		
		// open columns tab first
		if (window.history.pushState) {
			window.history.pushState (null, null, '#add-columns');
		} else {
			window.location.hash = '#add-columns';
		}
		
		$row.find ('.e-grid-toggle a')
			.click (toggle_active_tab);
		
		$row.find ('.e-grid-icon')
			.click ($.proxy (select_grid, $this));
		$row.find ('.e-grid-cancel-link')
			.click ($.proxy (cancel_row, $this));
		$row.find ('.e-grid-select-style')
			.change ($.proxy (select_style, $this));
		$row.find ('button.e-grid-set-bg-button')
			.click ($.proxy (background_chooser, $this));
		$row.find ('.e-grid-clear-bg-link')
			.click ($.proxy (background_clear, $this));
		$row.find ('.e-grid-set-height')
			.change ($.proxy (set_height, $this));
		$row.find ('.e-grid-toggle-inset')
			.change ($.proxy (toggle_inset, $this));
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
				variable: true,
				styles: {},
				api: '/admin/grid/api',
				units: [
					'100', '50,50', '66,33', '33,66', '75,25', '25,75',
					'80,20', '20,80', '33,33,33', '25,50,25', '25,25,25,25',
					'20,20,20,20,20'
				]
			};
			
			if (! initialized) {
				// compile templates
				for (var k in tpl) {
					tpl[k] = H.compile ($(tpl[k]).html ());
				}
				initialized = true;
			}
			
			options = $.extend (defaults, options);
			i18n = options.i18n;
			
			// convert styles to objects
			for (var s in options.styles) {
				options.styles[s] = {css_class: s, name: options.styles[s]};
			}
			
			return this.each (function () {
				var $this = $(this);
				
				$this.opts = options;
				$this.opts.id = $this.data ('id');
				$this.opts.variable = $this.hasClass ('e-grid-fixed');
				$this.rows = $.proxy (get_rows, $this);

				// Create and connect 'Add row' button
				$this.append (tpl.add_button ({ id: $this.opts.id }));
				$this.find ('.e-grid-add-button button')
					.click ($.proxy (add_row_form, $this));
			});
		}
	});
})(jQuery, Handlebars);

