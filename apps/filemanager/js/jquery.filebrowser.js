/**
 * Used by the filemanager/util/browser handler to provide a file
 * browser for app developers.
 */
;(function ($) {
	var self = {};

	// Current list of options
	self.opts = {};

	// The folder prefix for file paths
	self.prefix = '/' + filemanager_path + '/';

	// jQuery reference to #filebrowser-dirs
	self.dirs = null;

	// jQuery reference to #filebrowser-list
	self.list = null;
	
	// jQuery reference to #filebrowser-upload
	self.upload = null;

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
			var option = $('<option></option>')
				.attr ('value', res.data[i])
				.text ('files/' + res.data[i]);

			if (self.opts.path === res.data[i]) {
				option.attr ('selected', 'selected');
			}

			self.dirs.append (option);
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
			self.opts.path = $(this).val ();
			filemanager.ls ({path: self.opts.path}, self.update_list);
		});
	};

	// Select a file; return it if not multiple
	self.select_file = function () {
		var file = $(this).data ('file');

		if (self.opts.multiple) {
			if (self.opts.files.indexOf (file) === -1) {
				self.opts.files.push (file);
				$(this).addClass ('filebrowser-selected');
			} else {
				var index = self.opts.files.indexOf(file);
				self.opts.files.splice(index, 1);
				$(this).removeClass ('filebrowser-selected');
			}
		} else {
			if (self.opts.set_value) {
				$(self.opts.set_value).val (self.prefix + file);
			}

			if (self.opts.callback) {
				self.opts.callback (self.prefix + file);
			}

			$.close_dialog ();
		}
		return false;
	};

	// Select and return multiple files
	self.select_files = function () {
		if (self.opts.files.length) {
			for (var i in self.opts.files) {
				self.opts.files[i] = self.prefix + self.opts.files[i];
			}

			if (self.opts.set_value) {
				$(self.opts.set_value).val (self.opts.files);
			}

			if (self.opts.callback) {
				self.opts.callback (self.opts.files);
			}

			$.close_dialog ();
		} else {
			alert ("No files selected.");
		}
		return false;
	};

	// Return an array of allowed mime types
	self.allowed_mimes = function () {
		if (self.opts.allowed.length === 0) {
			return [];
		}

		var mimes = [];
		for (var i = 0; i < self.opts.allowed.length; i++) {
			if (self.opts.mimes[self.opts.allowed[i]]) {
				mimes.push (self.opts.mimes[self.opts.allowed[i]]);
			}
		}
		return mimes;
	};

	$.filebrowser = function (opts) {
		var defaults = {
			allowed: [],
			callback: null,
			set_value: null,
			title: $.i18n ('Choose a file'),
			new_file: $.i18n ('New file'),
			uploading_text: $.i18n ('Uploading...'),
			thumbs: false,
			multiple: false,
			files: [],
			path: '',
			uploading: 0,
			mimes: {
				jpg: 'image/jpeg',
				jpeg: 'image/jpeg',
				png: 'image/png',
				gif: 'image/gif',
				mp4: 'video/mp4',
				m4v: 'video/x-m4v',
				flv: 'video/x-flv',
				f4v: 'video/mp4',
				mp3: 'audio/mp3',
				pdf: 'application/pdf',
				doc: 'application/msword',
				docx: 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
				xls: 'application/vnd.ms-excel',
				xlsx: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
				ppt: 'application/vnd.ms-powerpoint',
				pptx: 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
				txt: 'text/plain',
				html: 'text/html',
				js: 'text/javascript',
				css: 'text/css'
			}
		};

		self.opts = $.extend (defaults, opts);

		if (self.opts.thumbs) {
			self.opts.allowed = ['jpg', 'jpeg', 'png', 'gif'];
		}

		self.extensions = self.opts.allowed.length
			? new RegExp ('\.(' + self.opts.allowed.join ('|') + ')$', 'i')
			: null;

		var form =
			'<div id="filebrowser-dropzone">' +
				'<form method="post" enctype="multipart/form-data">' +
					'<div id="filebrowser-upload">' +
						'<div id="filebrowser-upload-form">' +
							self.opts.new_file + ': ' +
							'<input type="file" name="file[]" id="filebrowser-file" multiple="multiple" />' +
						'</div>' +
						'<div id="filebrowser-upload-progress">' +
							'<div id="filebrowser-upload-progress-bar"></div>' +
							'<div id="filebrowser-upload-progress-text">' + self.opts.uploading_text + '</div>' +
						'</div>' +
					'</div>' +
					'<select id="filebrowser-dirs"><option value="">files</option></select>' +
					'<div id="filebrowser-list"></div>';
		if (self.opts.multiple) {
			form += '<input type="submit" id="filebrowser-select" value="' + $.i18n ('Select') + '">';
		}
		form +=
				'</form>' +
			'</div>';
		$.open_dialog (
			self.opts.title,
			form
		);

		self.dirs = $('#filebrowser-dirs');
		self.list = $('#filebrowser-list');
		self.upload = $('#filebrowser-upload');

		self.dirs.change (self.fetch_list);

		filemanager.dirs (self.update_dirs);
		filemanager.ls ({path: self.opts.path}, self.update_list);

		$('#filebrowser-select').one ('click', self.select_files);

		// Implements drag and drop file upload support,
		// for browsers that support it, with fallback
		// for those that don't.
		$('#filebrowser-dropzone').filedrop ({
			fallback_id: 'filebrowser-file',
			url: '/filemanager/upload/drop',
			paramname: 'file',
			withCredentials: true,
			data: {
				path: function () {
					return self.opts.path;
				}
			},
			error: function (err, file) {
				$('#filebrowser-dropzone').removeClass ('filebrowser-over');

				// Reset the upload progress bar
				$('#filebrowser-upload-progress-bar').css ('width', '5%');
				$('#filebrowser-upload-progress').hide ();
				$('#filebrowser-upload-form').show ();

				switch (err) {
					case 'FileTypeNotAllowed':
						alert (
							$.i18n ('Please upload one of the following file types')
							+ ': ' +
							self.opts.allowed.join (', ')
						);
						break;
					case 'BrowserNotSupported':
						alert ($.i18n ('Your browser does not support drag and drop file uploads.'));
						break;
					case 'TooManyFiles':
						alert ($.i18n ('Please upload fewer files at a time.'));
						break;
					case 'FileTooLarge':
						alert (
							$.i18n ('The following file is too large to upload')
							+ ': ' +
							file.name
						);
						break;
				}
			},
			allowedfiletypes: self.allowed_mimes (),
			maxfiles: 12,
			maxfilesize: filebrowser_max_filesize ? filebrowser_max_filesize : 2,
			queuefiles: 2,
			dragOver: function () {
				$('#filebrowser-dropzone').addClass ('filebrowser-over');
			},
			dragLeave: function () {
				$('#filebrowser-dropzone').removeClass ('filebrowser-over');
			},
			docLeave: function () {
				$('#filebrowser-dropzone').removeClass ('filebrowser-over');
			},
			drop: function () {
				$('#filebrowser-dropzone').removeClass ('filebrowser-over');
			},
			uploadStarted: function (i, file, len) {
				// Save the total so we only notify at the end
				self.opts.uploading = len;
				
				// Replace the upload field with a progress bar
				$('#filebrowser-upload-form').hide ();
				$('#filebrowser-upload-progress').show ();
			},
			uploadFinished: function (i, file, res, time) {
				if (! res.success) {
					alert (res.error);

					// Reset the upload progress bar
					$('#filebrowser-upload-progress-bar').css ('width', '5%');
					$('#filebrowser-upload-progress').hide ();
					$('#filebrowser-upload-form').show ();

				} else {
					self.opts.files.push (self.opts.path + '/' + file.name)
					if (i === self.opts.uploading - 1) {
						// This is the last file, add notification
						$.add_notification (res.data);

						// Reset the upload progress bar
						$('#filebrowser-upload-progress-bar').css ('width', '5%');
						$('#filebrowser-upload-progress').hide ();
						$('#filebrowser-upload-form').show ();

						if (i === 0) {
							// Only one file, auto-select it
							$('<a></a>')
								.data ('file', self.opts.path + '/' + file.name)
								.click (self.select_file)
								.click ();
							return;
						} else {
							self.select_files();
							return;
						}

						// Update the file list
						filemanager.ls ({path: self.opts.path}, self.update_list);
					}
				}
			},
			progressUpdated: function (i, file, progress) {
				// Update the progress bar
				$('#filebrowser-upload-progress-bar').css ('width', progress + '%');
			}
		});
	};
})(jQuery);
