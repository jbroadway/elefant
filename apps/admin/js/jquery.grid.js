$.grid = (function ($) {
	var self = {
			opts: {
				styles: {},
				api: '/admin/grid/api'
			}
		};

	self.add_row = function (e) {
		e.preventDefault ();
		
		console.log ('add row!');
		$(this).before (
			$('<div class="e-grid-row">'
				+ '<div class="e-row-variable">'
					+ '<div class="e-col-50 e-grid-col">...</div>'
					+ '<div class="e-col-50 e-grid-col">...</div>'
				+ '</div>'
			+ '</div>')
		);
	};

	self.make_editable = function () {
		var $this = $(this),
			id = $this.data ('id'),
			rows = $this.children ('.e-grid-row');

		console.log (id);
		console.log (rows);

		$(this).append (
			$('<button class="e-grid-button"><i class="fa fa-plus"></i> Add row</button>')
				.click (self.add_row)
		);
	};
	
	self.init = function (options) {
		self.opts = $.extend (self.opts, options);
		
		$('.e-grid').each (self.make_editable);
	};
	
	return self;
})(jQuery);

