/**
 * Used by the filemanager app to implement ajax functions.
 */
(function ($) {
	var filemanager = {
		path: '',
		aviary: null,
		aviary_key: false,
		aviary_current: false,
		text_file: /\.(txt|html?|xml|md|csv|css|js|json)$/,
		img_file: /\.(gif|png|jpe?g)$/,
		max_filesize: 2,
		strings: {
			
		}
	};

	$.extend ({
		filemanager: function (cmd, options) {
			var defaults = {
				root: '/filemanager/api/'
			};
			
			var options = $.extend (defaults, options);
			var dir_length= 120;
			
			switch (cmd) {
				case 'mkdir':
					var name = downcode ( prompt ($.i18n ('New folder name:'), ''), dir_length );
					if (name) {
						$.get (options.root + cmd + '/' + options.file + '/' + name, function (res) {
							if (res.success) {
								$.add_notification (res.data.msg);
								window.location = '/filemanager?path=' + res.data.data;
							} else {
								$.add_notification (res.error);
							}
						});
					}
					break;
				case 'mv':
					var name = downcode ( prompt ($.i18n ('Rename:'), options.name), dir_length);
					if (name) {
						$.get (options.root + cmd + '/' + options.file + '?rename=' + name, function (res) {
							if (res.success) {
								$.add_notification (res.data.msg);
								$.filemanager ('ls', {file: filemanager.path});
							} else {
								$.add_notification (res.error);
							}
						});
					}
					break;
				case 'drop':
					$.get (options.root + cmd + '/' + options.file + '?folder=' + options.folder, function (res) {
						if (res.success) {
							$.add_notification (res.data.msg);
							$.filemanager ('ls', {file: filemanager.path});
						} else {
							$.add_notification (res.error);
						}
					});
					break;
				case 'rm':
					if (confirm ($.i18n ('Are you sure you want to delete this file?'))) {
						$.get (options.root + cmd + '/' + options.file, function (res) {
							if (res.success) {
								$.add_notification (res.data.msg);
								$.filemanager ('ls', {file: filemanager.path});
							} else {
								$.add_notification (res.error);
							}
						});
					}
					break;
				case 'ls':
					$.template ('tpl_dir', $('#tpl_dir'));
					$.template ('tpl_file', $('#tpl_file'));
					$.get (options.root + cmd + '/' + options.file, function (res) {
						tbody = $('#file-list').html ('');
						if (res.success && res.data) {
							if (res.data.dirs) {
								for (var i = 0; i < res.data.dirs.length; i++) {
									res.data.dirs[i]._name = res.data.dirs[i].name.replace (/'/g, '\\\'');
									res.data.dirs[i]._path = res.data.dirs[i].path.replace (/'/g, '\\\'');
									$.tmpl ('tpl_dir', res.data.dirs[i]).appendTo (tbody);
								}
							}
							if (res.data.files) {
								for (var i = 0; i < res.data.files.length; i++) {
									res.data.files[i].image_file = (filemanager.aviary !== null)
										? res.data.files[i].name.match (filemanager.img_file)
										: false;
									res.data.files[i].text_file = res.data.files[i].name.match (filemanager.text_file);
									res.data.files[i]._name = res.data.files[i].name.replace (/'/g, '\\\'');
									res.data.files[i]._path = res.data.files[i].path.replace (/'/g, '\\\'');
									$.tmpl ('tpl_file', res.data.files[i]).appendTo (tbody);
								}
							}

							$.localize_dates ();

							$('.draggable').draggable ({
								cursor: 'move',
								revert: 'invalid'
							});

							$('.dropzone').droppable ({
								accept: '.draggable',
								tolerance: 'pointer',
								drop: function (event, ui) {
									var type = event.srcElement.nodeName.toLowerCase (),
										src = $(event.srcElement),
										folder = $(event.target).data ('folder');

									if (type === 'a' || type === 'img') {
										src = src.parent ();
									}

									var file = src.data ('file');
									file = file ? file : src.data ('folder');

									//console.log (file + ' -> ' + folder);									
									$.filemanager ('drop', {file: file, folder: folder});
								}
							});
						}
					});
					break;
				case 'prop':
					// display properties dialogue
					$.get ('/filemanager/properties/' + options.file, function (res) {
						$.open_dialog (
							res.title,
							res.body
						);
					});
					break;
				case 'img':
					// edit an image
					var url = window.location.href.split ('/filemanager')[0] + '/files/' + options.file;
					filemanager.aviary_current = options.file;
					$('#aviary-tmp').attr ('src', url);

					filemanager.aviary.launch ({
						image: 'aviary-tmp',
						url: url
					});
					break;
			}
			return false;
		}
	});

	$.filemanager_init = function (options) {
		filemanager = $.extend (filemanager, options);

		$.filemanager ('ls', {file: filemanager.path});

		if (filemanager.aviary_key) {
			filemanager.aviary = new Aviary.Feather ({
				apiKey: filemanager.aviary_key,
				apiVersion: 2,
				tools: 'all',
				appendTo: '',
				onSave: function (img, url) {
					// send update to server
					$.get ('/filemanager/edit/img?file=' + encodeURIComponent (filemanager.aviary_current) + '&url=' + encodeURIComponent (url), function (res) {
						if (! res.success) {
							$.add_notification (res.error);
						} else {
							$.add_notification (res.data);
						}
					});
				}
			});
		}

		$('#filemanager-dropzone').filedrop ({
			fallback_id: 'file-upload',
			url: '/filemanager/upload/drop',
			paramname: 'file',
			withCredentials: true,
			data: {
				path: function () {
					return filemanager.path
				}
			},
			error: function (err, file) {
				$('#filemanager-dropzone').removeClass ('filemanager-over');

				// Reset the upload progress bar
				$('#filemanager-upload-progress-bar').css ('width', '5%');
				$('#filemanager-upload-progress').css ({display: 'none'});
				$('#filemanager-upload-form').show ();

				switch (err) {
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
			maxfiles: 12,
			maxfilesize: filemanager.max_filesize,
			queuefiles: 2,
			dragOver: function () {
				$('#filemanager-dropzone').addClass ('filemanager-over');
			},
			dragLeave: function () {
				$('#filemanager-dropzone').removeClass ('filemanager-over');
			},
			dropLeave: function () {
				$('#filemanager-dropzone').removeClass ('filemanager-over');
			},
			drop: function () {
				$('#filemanager-dropzone').removeClass ('filemanager-over');
			},
			uploadStarted: function (i, file, len) {
				// Save the total so we only notify at the end
				filemanager._upload_total = len;

				// Replace the upload field with a progress bar
				$('#filemanager-upload-form').css ({display: 'none'});
				$('#filemanager-upload-progress').css ({display: 'inline-block'});
			},
			uploadFinished: function (i, file, res, time) {
				if (! res.success) {
					alert (res.error);

					// Reset the upload progress bar
					$('#filemanager-upload-progress-bar').css ('width', '5%');
					$('#filemanager-upload-progress').css ({display: 'none'});
					$('#filemanager-upload-form').css ({display: 'inline-block'});
				
				} else {
					if (i === filemanager._upload_total - 1) {
						// This is the last file, add notification
						$.add_notification (res.data);
						
						// Update the file list
						$.filemanager ('ls', {file: filemanager.path});
						
						// Reset the upload progress bar
						$('#filemanager-upload-progress-bar').css ('width', '5%');
						$('#filemanager-upload-progress').css ({display: 'none'});
						$('#filemanager-upload-form').css ({display: 'inline-block'});
					}
				}
			},
			progressUpdated: function (i, file, progress) {
				// Update the progress bar
				$('#filemanager-upload-progress-bar').css ('width', progress + '%');
			}
		});
	};

	$.filemanager_prop = function (form) {
		var file = form.elements.file.value,
			desc = form.elements.desc.value;

		$.get ('/filemanager/api/prop/' + file + '?prop=desc&value=' + encodeURIComponent (desc), function (res) {
			$.close_dialog ();
			$.add_notification (res.data.msg);
		});
		return false;
	};

	$.filemanager_verify_files = function (files) {
		if (files.length === 0) {
			return 'no_files';
		}
		for (var i = 0; i < files.length; i++) {
			if (files[i].name === '') {
				return 'invalid_name';
			}
			if (files[i].name.indexOf ('..') !== -1) {
				return 'invalid_name';
			}
		}
		return false;
	};
})(jQuery);
