/**
 * Used by the filemanager/util/multi-image handler to provide
 * a multi-image selector for app developers.
 */
;(function ($) {
	var self = {};

	self.opts = {};

	self.last_path = null;

	// Get the image list as an array from the field
	self.get_images = function () {
		var images = $(self.opts.field).val ();
		
		if (images.length === 0) {
			return [];
		}

		if (images.match ('|')) {
			return images.split ('|');
		}

		return [images];
	};

	// Store the image list back into the field
	self.set_images = function (images) {
		$(self.opts.field).val (images.join ('|'));
	};

	// Add an image from the chooser
	self.add_image = function (files) {
		var images = self.get_images ();

		for (var i in files) {
			file = files[i];
			self.last_path = self.dirname (file).replace (RegExp('^\/' + filemanager_path + '\/?'), '');

			// avoid duplicates
			if ($.inArray (file, images) === -1) {
				images.push (file);
			}

		}

		self.set_images (images);
		self.update_preview (images);
	};

	// Remove an image when it's been clicked
	self.remove_image = function () {
		var file = $(this).attr ('src'),
			images = self.get_images ();

		while (images.indexOf (file) !== -1) {
			images.splice (images.indexOf (file), 1);
		}
		
		self.set_images (images);
		self.update_preview (images);
	};

	// Update the preview of the images
	self.update_preview = function (images) {
		var prev = $('#multi-image-list');
		prev.html ('');
		for (var i in images) {
			prev.append (
				$('<div></div>').append (
					$('<img>')
						.attr ('src', images[i])
						.attr ('title', $.i18n ('Click to remove, drag to re-order'))
						.addClass ('sortable')
						.click (self.remove_image)
				)
			);
		}

		// Allow images to be sorted via drag and drop
		$('#multi-image-list').sortable ({
			placeholder: 'multi-image-placeholder',
			distance: 5,
			helper: 'clone',
			tolerance: 'pointer',
			update: function (evt, ui) {
				var images = [];
				$('#multi-image-list div img').each (function () {
					images.push ($(this).attr ('src'));
				});
				self.set_images (images);
			}
		});
	};

	// From http://phpjs.org/functions/dirname/
	self.dirname = function (path) {
		return path.replace(/\\/g, '/').replace(/\/[^\/]*\/?$/, '');
	};

	$.multi_image = function (opts) {
		var defaults = {
			field: '#images',
			preview: '#images-preview',
			path: null
		};

		self.opts = $.extend (defaults, opts);
		self.last_path = self.opts.path;

		$(self.opts.preview)
			.addClass ('multi-image-preview')
			.append (
				$('<div></div>')
					.attr ('id', 'multi-image-list')
			)
			.append (
				$('<button>' + $.i18n ('Browse images') + '</button>')
					.attr ('id', 'multi-image-button')
			);

		var images = self.get_images ();
		self.update_preview (images);

		$('#multi-image-button').click (function () {
			var fb_opts = {
				multiple: true,
				thumbs: true,
				callback: self.add_image,
				new_file: $.i18n ('New images'),
				title: $.i18n ('Choose images')
			};

			if (self.last_path !== null) {
				fb_opts.path = self.last_path;
			}

			$.filebrowser (fb_opts);
			return false;
		});
	};
})(jQuery);
