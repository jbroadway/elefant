/**
 * Used by the filemanager/photo handler to provide a file browser
 * for changing an image in place.
 */
(function ($) {
	/**
	 * Open the file browser to choose a new image.
	 */
	var open = function (opts, e) {
		e.preventDefault ();

		$.filebrowser ({
			allowed: ['jpg', 'jpeg', 'png', 'gif'],
			title: opts.title,
			thumbs: true,
			callback: $.proxy (update, this, opts)
		});
	};
	
	/**
	 * Send newly chosen image to server.
	 */
	var update = function (opts, file) {
		$.get (
			'/filemanager/photo?key=' + opts.key
				+ '&width=' + opts.width
				+ '&height=' + opts.height
				+ '&photo=' + file,
			$.proxy (replace, this, opts)
		);
	};
	
	/**
	 * Update page with newly chosen image.
	 */
	var replace = function (opts, res) {
		if (! res.success) {
			return;
		}
		
		opts.src = res.data.src;
		$(this).attr ('src', res.data.src);
	};
	
	$.fn.extend ({
		photoswitcher: function (options) {
			var defaults = {
				key: null,
				src: 'http://placehold.it/300x200',
				width: 300,
				height: 200,
				title: 'Choose an image'
			};

			var options = $.extend (defaults, options);

			return this.each (function () {
				var opts = options;
				$('#photo-' + opts.key + '-wrapper').on ('click', $.proxy (open, this, opts));
			});
		}
	});
})(jQuery);
