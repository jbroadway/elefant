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

	// Shorten a file name if it's too long
	self.shorten = function (name) {
		return (name.length < 30)
			? name
			: name.substr (0, 17) + '...' + name.slice (-9);
	};

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

		// Initialize columns for file lists
		if (! self.opts.thumbs) {
			self.list.append ('<ul id="filebrowser-col-a"></ul><ul id="filebrowser-col-b"></ul>');
			var list = $('#filebrowser-col-a');

			if (res.data.files.length > 8) {
				col_b_after = Math.ceil (res.data.files.length / 2);
			} else {
				col_b_after = res.data.files.length;
			}
		}

		for (var i in res.data.files) {
			if (self.extensions && ! res.data.files[i].path.match (self.extensions)) {
				continue;
			}

			if (! self.opts.thumbs) {
				// Create list items
				if (i >= col_b_after) {
					list = $('#filebrowser-col-b');
				}

				list.append (
					$('<li></li>')
						.append (
							$('<img />')
								.attr ('src', '/apps/admin/css/admin/file.png')
								.css ({
									'padding-right': '5px',
									'margin-top': '-2px'
								})
						)
						.append (
							$('<a></a>')
								.attr ('href', '#')
								.attr ('class', 'filebrowser-file')
								.attr ('title', res.data.files[i].name)
								.data ('file', res.data.files[i].path)
								.text (self.shorten (res.data.files[i].name))
								.click (self.select_file)
						)
				);

			} else {
				// Create thumbnails
				self.list.append (
					$('<a></a>')
						.attr ('href', '#')
						.attr ('class', 'filebrowser-thumb')
						.attr ('title', res.data.files[i].name)
						.data ('file', res.data.files[i].path)
						.click (self.select_file)
						.append ('<img src="' + self.prefix + res.data.files[i].path + '" />')
				);
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
		return false;
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

		if (self.opts.thumbs) {
			self.opts.allowed = ['jpg', 'jpeg', 'png', 'gif'];
		}

		self.extensions = self.opts.allowed.length
			? new RegExp ('\.(' + self.opts.allowed.join ('|') + ')$', 'i')
			: null;

		$.open_dialog (
			self.opts.title,
			'<select id="filebrowser-dirs"><option value="">files</option></select><div id="filebrowser-list"></div>'
		);

		self.dirs = $('#filebrowser-dirs');
		self.list = $('#filebrowser-list');

		self.dirs.change (self.fetch_list);

		filemanager.dirs (self.update_dirs);
		filemanager.ls ({path: self.opts.path}, self.update_list);
	};
})(jQuery);