/**
 * Used by the filemanager/util/multi-file handler to provide
 * a multi-file selector for app developers.
 */
;(function ($) {
	var self = {};

	self.opts = {};

	// Get the file list as an array from the field
	self.get_files = function () {
		var files = $(self.opts.field).val ();
		
		if (files.length === 0) {
			return [];
		}

		if (files.match ('|')) {
			return files.split ('|');
		}

		return [files];
	};

	// Store the file list back into the field
	self.set_files = function (files) {
		$(self.opts.field).val (files.join ('|'));
	};

	// Add a file from the chooser
	self.add_file = function (file) {
		var files = self.get_files ();

		// avoid duplicates
		if ($.inArray (file, files) !== -1) {
			return;
		}

		files.push (file);

		self.set_files (files);
		self.update_preview (files);
	};

	// Remove a file when it's been clicked
	self.remove_file = function (e) {
		e.preventDefault ();

		var file = $(this).attr ('href'),
			files = self.get_files ();

		while (files.indexOf (file) !== -1) {
			files.splice (files.indexOf (file), 1);
		}
		
		self.set_files (files);
		self.update_preview (files);
	};

	// Update the preview of the files
	self.update_preview = function (files) {
		var prev = $('#multi-file-list');
		prev.html ('');
		for (var i in files) {
			prev.append (
				$('<li></li>')
					.append (
						$('<a></a>')
							.attr ('href', files[i])
							.attr ('title', $.i18n ('Click to remove'))
							.html ('<span class="icon-remove"></span>')
							.click (self.remove_file)
					)
					.append (
						$('<span></span>')
							.attr ('title', files[i])
							.text (files[i].replace (/^.*[\/\\]/g, ''))
					)
			);
		}
	};

	$.multi_file = function (opts) {
		var defaults = {
			field: '#files',
			preview: '#files-preview',
			allowed: []
		};

		self.opts = $.extend (defaults, opts);

		$(self.opts.preview)
			.addClass ('multi-file-preview')
			.append (
				$('<ul></ul>')
					.attr ('id', 'multi-file-list')
			)
			.append (
				$('<button>' + $.i18n ('Browse files') + '</button>')
					.attr ('id', 'multi-file-button')
			);

		var files = self.get_files ();
		self.update_preview (files);

		$('#multi-file-button').click (function () {
			$.filebrowser ({
				callback: self.add_file,
				allowed: self.opts.allowed
			});
			return false;
		});
	};
})(jQuery);