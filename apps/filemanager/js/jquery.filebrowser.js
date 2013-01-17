/**
 * Used by the filemanager/browse handler to provide a file
 * browser for app developers.
 */
;(function ($) {
	var self = {};

	// Current list of options
	self.opts = {};

	// The folder prefix for file paths
	self.prefix = '/files/';

	// jQuery reference to #filemanager-dirs
	self.dirs = null;

	// jQuery reference to #filemanager-list
	self.list = null;

	// A regular expression matching any of the allowed file extensions
	self.extensions = null;

	// Callback to update list of folders
	self.update_dirs = function (res) {
		if (! res.success) {
			return;
		}

		for (var i in res.data) {
			self.dirs.append (
				$('<option></option>')
					.attr ('value', res.data[i])
					.text ('files/' + res.data[i])
			);
		}
	};

	// Callback to update list of files
	self.update_list = function (res) {
		if (! res.success) {
			return;
		}

		self.list.empty ();

		for (var i in res.data.files) {
			if (self.extensions && ! res.data.files[i].path.match (self.extensions)) {
				continue;
			}

			if (! self.opts.thumbs) {
				self.list.append (
					$('<li></li>')
						.append (
							$('<a></a>')
								.attr ('href', '#')
								.attr ('class', 'filebrowser-file')
								.data ('file', res.data.files[i].path)
								.text (res.data.files[i].name)
								.click (self.select_file)
						)
				);
			} else {
				// TODO: show as thumbnails
			}
		}
	};

	// Fetch folder of files
	self.fetch_list = function () {
		$('#filebrowser-dirs option:selected').each (function () {
			filemanager.ls ({path: $(this).val ()}, self.update_list);
		});
	};

	// Select and return a file
	self.select_file = function () {
		var file = $(this).data ('file');

		if (self.opts.set_value) {
			$(self.opts.set_value).val (self.prefix + file);
		}

		if (self.opts.callback) {
			self.opts.callback (self.prefix + file);
		}

		$.close_dialog ();
	};

	$.filebrowser = function (opts) {
		var defaults = {
			allowed: [],
			callback: null,
			set_value: null,
			title: 'Choose a file',
			thumbs: false,
			path: ''
		};

		self.opts = $.extend (defaults, opts);

		self.extensions = self.opts.allowed.length
			? new RegExp ('\.(' + self.opts.allowed.join ('|') + ')$')
			: null;

		$.open_dialog (
			self.opts.title,
			'<p><select id="filebrowser-dirs"><option value="">files</option></select></p><ul id="filebrowser-list"></ul>'
		);

		self.dirs = $('#filebrowser-dirs');
		self.list = $('#filebrowser-list');

		self.dirs.change (self.fetch_list);

		filemanager.dirs (self.update_dirs);
		filemanager.ls ({path: self.opts.path}, self.update_list);
	};
})(jQuery);