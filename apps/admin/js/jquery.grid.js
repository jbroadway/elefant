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
			edit:			'#tpl-grid-edit',
			icons:			'#tpl-grid-icons',
			add_button:		'#tpl-grid-add-button'
		}
		i18n = {
			choose_bg: 'Choose a background image'
		},
		base_row = {
			css_class: '',
			bg_image: '',
			variable: true,
			fixed: false,
			inset: false,
			equal_height: false,
			height: '',
			units: '100',
			id: '',
			row: 0
		},
		base_col = {
			col: 0,
			unit: '100',
			content: ''
		},
		rows = [];

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

	// Cancel edit row form and restore previous content.
	// Attach via $.proxy (cancel_edit_row, $this);
	function cancel_edit_row (e) {
		e.preventDefault ();
		
		var $this = this,
			$cancel = $(e.target),
			$row = $cancel.closest ('.e-grid-row');
		
		$row.html ($row.data ('_prev'));
	}

	// Remove a row from the grid.
	// Attach via $.proxy (remove_row, $this);
	function remove_row (e) {
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
			$choice = $(e.target),
			$row = $choice.closest ('.e-grid-row'),
			row = $row.data ('_row');
		
		row.units = $choice.data ('units');
		$row.data ('_row', row);
		
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
			row = $row.data ('_row'),
			css_style = $select.find ('option:selected').val ();

		row.css_class = css_style;
		$row.data ('_row', row);

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
		
		var $row = $(e.target).closest ('.e-grid-row'),
			row = $row.data ('_row');
		
		$.filebrowser ({
			allowed: ['jpg', 'jpeg', 'png', 'gif'],
			title: i18n.choose_bg,
			thumbs: true,
			callback: function (file) {
				row.bg_image = file;
				$row.data ('_row', row)
					.css ({
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
		
		var $row = $(e.target).closest ('.e-grid-row'),
			row = $row.data ('_row');

		row.bg_image = '';
		$row.data ('_row', row);
		
		$row.removeAttr ('style');
	}
	
	// Change the height of the row
	// Attach via $.proxy (background_chooser, $this)
	function set_height (e) {
		e.preventDefault ();
		
		var $target = $(e.target),
			height = $target.val (),
			$row = $(e.target).closest ('.e-grid-row'),
			row = $row.data ('_row');

		if (height !== '') {
			row.height = height;
			$row.data ('_row', row)
				.css ({height: height + 'px', 'min-height': height + 'px'});
		} else {
			row.height = '';
			$row.data ('_row', row)
				.css ({height: '', 'min-height': ''});
		}
	}
	
	// Set column heights to be equal
	// Attach via $.proxy (toggle_equal_heights, $this)
	function toggle_equal_heights (e) {
		e.preventDefault ();
		
		var $target = $(e.target),
			checked = $target.is (':checked'),
			$row = $(e.target).closest ('.e-grid-row'),
			row = $row.data ('_row');

		if (checked) {
			row.equal_heights = true;
			$row.data ('_row', row).find ('.e-row*').addClass ('e-row-equal');
		} else {
			row.equal_heights = false;
			$row.data ('_row', row).find ('.e-row*').removeClass ('e-row-equal');
		}
	}
	
	// Change the background attachment
	// Attach via $.proxy (toggle_fixed, $this)
	function toggle_fixed (e) {
		e.preventDefault ();
		
		var $target = $(e.target),
			checked = $target.is (':checked'),
			$row = $(e.target).closest ('.e-grid-row'),
			row = $row.data ('_row');

		if (checked) {
			row.fixed = true;
			$row.data ('_row', row)
				.removeClass ('e-no-fixed').addClass ('e-fixed');
		} else {
			row.fixed = false;
			$row.data ('_row', row)
				.removeClass ('e-fixed').addClass ('e-no-fixed');
		}
	}
	
	// Change the drop shadow
	// Attach via $.proxy (toggle_inset, $this)
	function toggle_inset (e) {
		e.preventDefault ();
		
		var $target = $(e.target),
			checked = $target.is (':checked'),
			$row = $(e.target).closest ('.e-grid-row'),
			row = $row.data ('_row');

		if (checked) {
			row.inset = true;
			$row.data ('_row', row)
				.removeClass ('e-no-inset').addClass ('e-inset');
		} else {
			row.inset = false;
			$row.data ('_row', row)
				.removeClass ('e-inset').addClass ('e-no-inset');
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
			row = $.extend (base_row, {
				id: $this.data ('id'),
				row: $this.rows ().length,
				css_class: '',
				variable: $this.opts.variable,
				fixed: false,
				inset: false,
				height: '',
				styles: $this.opts.styles,
				units: '100'
			}),
			$row = $(tpl.add (row));

		// show/hide unit options
		$row.find ('.e-grid-icon').css ({display: 'none'});
		for (var u in $this.opts.units) {
			$row.find ('.e-grid-icon-' + $this.opts.units[u].replace (/,/g, '-')).css ({display: 'inline-block'});
		}
		
		$row.insertBefore ($add.closest ('.e-grid-add-button'))
			.velocity ('slideDown', 500)
			.data ('_row', row);

		$('html,body').animate ({
			scrollTop: $row.offset ().top
		});
		
		$row.find ('.e-grid-toggle a')
			.click (toggle_active_tab);
		
		$row.find ('.e-grid-icon')
			.click ($.proxy (select_grid, $this));
		$row.find ('.e-grid-cancel-link')
			.click ($.proxy (cancel_row, $this));
		$row.find ('.e-grid-add-row-button')
			.click ($.proxy (add_row, $this));
		$row.find ('.e-grid-select-style')
			.change ($.proxy (select_style, $this));
		$row.find ('button.e-grid-set-bg-button')
			.click ($.proxy (background_chooser, $this));
		$row.find ('.e-grid-clear-bg-link')
			.click ($.proxy (background_clear, $this));
		$row.find ('.e-grid-set-height')
			.change ($.proxy (set_height, $this));
		$row.find ('.e-grid-toggle-equal-heights')
			.change ($.proxy (toggle_equal_heights, $this));
		$row.find ('.e-grid-toggle-fixed')
			.change ($.proxy (toggle_fixed, $this));
		$row.find ('.e-grid-toggle-inset')
			.change ($.proxy (toggle_inset, $this));
	}

	// Add row from add row form.
	// Attach via $.proxy (add_row, $this);
	function add_row (e) {
		e.preventDefault ();
		
		var $this = this,
			$row = $(e.target).closest ('.e-grid-row'),
			row = $row.data ('_row'),
			units = row.units.split (',');
		
		row.cols = [];
		for (var i = 0; i < units.length; i++) {
			row.cols.push ({
				unit: units[i],
				content: tpl.icons ({}),
				empty: true
			});
		}
		
		$row.removeClass ('e-grid-edit')
			.html (tpl.row (row));
	}
	
	// Create edit row form.
	// Attach via $.proxy (edit_row, $this)
	function edit_row_form (e) {
		e.preventDefault ();

		var $this = this,
			$add = $(e.target),
			row = $.extend (base_row, {
				id: $this.data ('id'),
				row: $this.data ('row'),
				css_class: '',
				variable: $this.opts.variable,
				fixed: $this.hasClass ('e-fixed'),
				inset: $this.hasClass ('e-inset'),
				height: $this.css ('height'),
				styles: $this.opts.styles,
				units: get_units ($this)
			}),
			$row = $add.closest ('.e-grid-row');
			$row.data ('_row', row); // stores our data model
			$row.data ('_prev', $row.html ()); // cached for cancel

		// show/hide unit options
		$row.find ('.e-grid-icon').css ({display: 'none'});
		for (var u in $this.opts.units) {
			$row.find ('.e-grid-icon-' + $this.opts.units[u].replace (/,/g, '-')).css ({display: 'inline-block'});
		}
		
		$row.find ('.e-grid-toggle a')
			.click (toggle_active_tab);
		
		$row.find ('.e-grid-icon')
			.click ($.proxy (select_grid, $this));
		$row.find ('.e-grid-cancel-link')
			.click ($.proxy (cancel_edit_row, $this));
		$row.find ('.e-grid-edit-row-button')
			.click ($.proxy (edit_row, $this));
		$row.find ('.e-grid-select-style')
			.change ($.proxy (select_style, $this));
		$row.find ('button.e-grid-set-bg-button')
			.click ($.proxy (background_chooser, $this));
		$row.find ('.e-grid-clear-bg-link')
			.click ($.proxy (background_clear, $this));
		$row.find ('.e-grid-set-height')
			.change ($.proxy (set_height, $this));
		$row.find ('.e-grid-toggle-equal-heights')
			.change ($.proxy (toggle_equal_heights, $this));
		$row.find ('.e-grid-toggle-fixed')
			.change ($.proxy (toggle_fixed, $this));
		$row.find ('.e-grid-toggle-inset')
			.change ($.proxy (toggle_inset, $this));
	}

	// Update row from edit row form.
	// Attach via $.proxy (add_row, $this);
	function edit_row (e) {
		e.preventDefault ();
		
		var $this = this,
			$row = $(e.target).closest ('.e-grid-row'),
			row = $row.data ('_row');
		
		$row.removeClass ('e-grid-edit')
			.html (tpl.row (row));
	}

	// Edit the contents of a cell.
	// Attach via $.proxy (edit_col, $this);	
	function edit_col (e) {
		e.preventDefault ();
		console.log ('edit_col');
		
		var $this = this,
			$col = $(e.target),
			col = $col.data ('col'),
			$row = $col.closest ('.e-grid-row'),
			row = $row.data ('_row');

		console.log (col);
		console.log (row);
	}
	
	// Add a photo to the cell.
	// Attach via $.proxy (edit_photo, $this);
	function edit_photo (e) {
		e.preventDefault ();
		e.stopPropagation ();
		console.log ('edit_photo');

		var $this = this,
			$col = $(e.target).hasClass ('e-grid-col')
				? $(e.target)
				: $(e.target).closest ('.e-grid-col'),
			col = $col.data ('col'),
			$row = $col.closest ('.e-grid-row'),
			row = $row.data ('_row');

		$.filebrowser ({
			allowed: ['jpg', 'jpeg', 'png', 'gif'],
			title: i18n.choose_bg,
			thumbs: true,
			callback: function (file) {
				// TODO: Replace with filemanager/photo embed
				$col.html ('<img src="' + file + '" />');
			}
		});
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
	
	// Fetch all columns from a row.
	function get_cols ($row) {
		return $row.find ('.e-col-*');
	}
	
	// Build the units string from columns.
	function get_units ($row) {
		var $cols = get_cols ($row),
			units = '',
			sep = '';
		
		for (var i in $cols) {
			var unit = $cols[i].attr ('class').match (/e-col-([0-9]+)/)[1];
			units += sep + unit;
			sep = ',';
		}
		
		return units;
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
				$this.opts.variable = ! $this.hasClass ('e-grid-fixed');
				$this.rows = $.proxy (get_rows, $this);
				
				$this.find ('.e-grid-col-empty').html (tpl.icons ({}));

				$this.on ('click', '.e-grid-col', $.proxy (edit_col, $this));
				$this.on ('click', '.e-col-edit-photo', $.proxy (edit_photo, $this));

				// Create and connect 'Add row' button
				$this.append (tpl.add_button ({ id: $this.opts.id }));
				$this.find ('.e-grid-add-button button')
					.click ($.proxy (add_row_form, $this));
			});
		}
	});
})(jQuery, Handlebars);

