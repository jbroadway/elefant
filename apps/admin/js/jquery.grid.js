$.grid = (function ($) {
	var self = {
		opts: {
			styles: {},
			api: '/admin/grid/api'
		},
	};

	self.make_editable = function () {
		console.log ($(this).data ('id'));
	};
	
	self.init = function (options) {
		self.opts = $.extend (self.opts, options);
		
		console.log (self.opts.styles);
		console.log (self.opts.api);
		
		$('#e-grid').each (self.make_editable);
	};
	
	return self;
})(jQuery);
