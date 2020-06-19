/**
 * Used by the filemanager app to implement ajax functions.
 */
(function ($) {
	var filemanager = {
		path: '',
		aviary: null,
		aviary_key: false,
		aviary_current: false,
		text_file: /\.(txt|html?|xml|md|csv|css|js|json|cfg|yml|yaml)$/i,
		img_file: /\.(gif|png|jpe?g)$/i,
		zip_file: /\.zip$/i,
		max_filesize: 2,
		token: '',
		strings: {
			
		},
		dirs: [],
		files: []
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
					var name = prompt ($.i18n ('New folder name:'), '');
					if (name != null) {
						name = downcode (name, dir_length);
						$.post (options.root + cmd, {file: options.file + '/' + name}, function (res) {
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
					var name = prompt ($.i18n ('Rename:'), options.name);
					if (name != null) {
						name = downcode (name, dir_length);
						$.post (options.root + cmd, {file: options.file, rename: name}, function (res) {
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
					$.post (options.root + cmd, {file: options.file, folder: options.folder}, function (res) {
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
						$.post (options.root + cmd, {file: options.file}, function (res) {
							if (res.success) {
								$.add_notification (res.data.msg);
								$.filemanager ('ls', {file: filemanager.path});
							} else {
								$.add_notification (res.error);
							}
						});
					}
					break;
				case 'rmdir':
					if (confirm ($.i18n ('Are you sure you want to delete this folder and all of its contents?'))) {
						$.post (options.root + cmd, {file: options.file}, function (res) {
							if (res.success) {
								$.add_notification (res.data.msg);
								$.filemanager ('ls', {file: filemanager.path});
							} else {
								$.add_notification (res.error);
							}
						});
					}
					break;
				case 'search':
					$.template ('tpl_dir', $('#tpl_dir'));
					$.template ('tpl_file', $('#tpl_file'));
					$.template ('tpl_no_results', $('#tpl_no_results'));
					$.get (options.root + cmd + '?query=' + encodeURIComponent (options.query), function (res) {
						if (res.success && res.data) {
							tbody = $('#file-list').html ('');
							
							if (res.data.dirs.length == 0 && res.data.files.length == 0) {
								$.tmpl ('tpl_no_results', {}).appendTo (tbody);
							}
							
							if (res.data.dirs) {
								for (var i = 0; i < res.data.dirs.length; i++) {
									res.data.dirs[i]._name = res.data.dirs[i].name.replace (/'/g, '\\\'');
									res.data.dirs[i]._path = res.data.dirs[i].path.replace (/'/g, '\\\'');
									$.tmpl ('tpl_dir', res.data.dirs[i]).appendTo (tbody);
								}
								
								dirs = res.data.dirs
							}
							if (res.data.files) {
								for (var i = 0; i < res.data.files.length; i++) {
									res.data.files[i].is_img = res.data.files[i].name.match (filemanager.img_file);
									res.data.files[i].image_file = (filemanager.aviary !== null)
										? res.data.files[i].name.match (filemanager.img_file)
										: false;
									res.data.files[i].zip_file = res.data.files[i].name.match (filemanager.zip_file);
									res.data.files[i].text_file = res.data.files[i].name.match (filemanager.text_file);
									res.data.files[i]._name = res.data.files[i].name.replace (/'/g, '\\\'');
									res.data.files[i]._path = res.data.files[i].path.replace (/'/g, '\\\'');
                                    res.data.files[i].conf_root = conf_root + '/';
									$.tmpl ('tpl_file', res.data.files[i]).appendTo (tbody);
								}
								
								files = res.data.files
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
									var type = ui.draggable[0].nodeName.toLowerCase (),
										src = ui.draggable,
										folder = $(this).data ('folder');

									if (type === 'a' || type === 'img') {
										src = src.parent ();
									}

									var file = src.data ('file');
									file = file ? file : src.data ('folder');
								
									$.filemanager ('drop', {file: file, folder: folder});
								}
							});
						} else {
							$.add_notification (res.error);
						}
					});
					break;
				case 'ls':
					$.template ('tpl_dir', $('#tpl_dir'));
					$.template ('tpl_file', $('#tpl_file'));
					$.get (options.root + cmd + '?file=' + encodeURIComponent (options.file), function (res) {
						tbody = $('#file-list').html ('');
						if (res.success && res.data) {
							if (res.data.dirs) {
								for (var i = 0; i < res.data.dirs.length; i++) {
									res.data.dirs[i]._name = res.data.dirs[i].name.replace (/'/g, '\\\'');
									res.data.dirs[i]._path = res.data.dirs[i].path.replace (/'/g, '\\\'');
									$.tmpl ('tpl_dir', res.data.dirs[i]).appendTo (tbody);
								}
								
								dirs = res.data.dirs
							}
							if (res.data.files) {
								for (var i = 0; i < res.data.files.length; i++) {
									res.data.files[i].is_img = res.data.files[i].name.match (filemanager.img_file);
									res.data.files[i].image_file = (filemanager.aviary !== null)
										? res.data.files[i].name.match (filemanager.img_file)
										: false;
									res.data.files[i].zip_file = res.data.files[i].name.match (filemanager.zip_file);
									res.data.files[i].text_file = res.data.files[i].name.match (filemanager.text_file);
									res.data.files[i]._name = res.data.files[i].name.replace (/'/g, '\\\'');
									res.data.files[i]._path = res.data.files[i].path.replace (/'/g, '\\\'');
                                    res.data.files[i].conf_root = conf_root + '/';
									$.tmpl ('tpl_file', res.data.files[i]).appendTo (tbody);
								}
								
								files = res.data.files
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
									var type = ui.draggable[0].nodeName.toLowerCase (),
										src = ui.draggable,
										folder = $(this).data ('folder');

									if (type === 'a' || type === 'img') {
										src = src.parent ();
									}

									var file = src.data ('file');
									file = file ? file : src.data ('folder');
								
									$.filemanager ('drop', {file: file, folder: folder});
								}
							});
						}
					});
					break;
				case 'bitly':
					$.get (options.root + cmd + '?file=' + encodeURIComponent (options.file), function (res) {
						var num = $.open_dialog (
							$.i18n ('Bitly Link'),
							'<p class="copy-me" data-clipboard-text="' + res.data + '">' + res.data + ' (' + $.i18n ('copy to clipboard') + ')</p>' +
							'<p class="copied">' + $.i18n ('Copied!') + '</p>' +
							'<p class="ctrlc">' + $.i18n ('Press Ctrl+C to copy') + '</p>'
						);
						
						var clippy = new ClipboardJS ('.copy-me');

						clippy.on ('success', function () {
							$('.copied').show ();
							
							setTimeout (function () {
								$('.copied').fadeOut (300);
							}, 3000);
						});
						
						clippy.on ('error', function (e) {
							$('.ctrlc').show ();
							
							setTimeout (function () {
								$('.ctrlc').fadeOut (300);
							}, 3000);
						});
					
						console.log (clippy);
					});
					break;
				case 'prop':
					// display properties dialogue
					$.post ('/filemanager/properties', {file: options.file}, function (res) {
						console.log (res);
						$.open_dialog (
							res.title,
							res.body
						);
					});
					break;
				case 'img':
					// edit an image
					var url = window.location.href.split ('/filemanager')[0] + '/' + conf_root + '/' + options.file;
					filemanager.aviary_current = options.file;
					$('#aviary-tmp').attr ('src', url);

					filemanager.aviary.launch ({
						image: 'aviary-tmp',
						url: url
					});
					break;
				case 'unzip':
					// unzip an archive
					$.post (options.root + cmd, {file: options.file}, function (res) {
						if (! res.success) {
							$.add_notification (res.error);
							return;
						}
						$.add_notification (res.data.msg);
						$.filemanager ('ls', {file: filemanager.path});
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
		
		$('#search-input').on ('keyup', function (e) {
			if (e.key === 'Enter') {
				e.preventDefault ();

				var query = $('#search-input').val ();
				
				if (query == '') return;
				
				$('#search-clear').removeClass ('hidden');

				$.filemanager ('search', {query: query});
			}
		})
		
		$('#search-button').on ('click', function (e) {
			e.preventDefault ();
			
			var query = $('#search-input').val ();
			
			if (query == '') return;
			
			$('#search-clear').removeClass ('hidden');

			$.filemanager ('search', {query: query});
		});
		
		$('#search-clear').on ('click', function (e) {
			e.preventDefault ();
			
			$('#search-input').val ('');
			$('#search-clear').addClass ('hidden');
			
			$.filemanager ('ls', {file: filemanager.path});
		});

		$('#filemanager-dropzone').filedrop ({
			fallback_id: 'file-upload',
			url: '/filemanager/upload/drop',
			paramname: 'file',
			withCredentials: (navigator.userAgent.indexOf('MSIE') === -1) ? true : false,
			fallback_dropzoneClick: false,
			data: {
				path: function () {
					return filemanager.path
				},
				token: function () {
					return filemanager.token
				}
			},
			error: function (err, file) {
				$('#filemanager-dropzone').removeClass ('filemanager-over');

				// Reset the upload progress bar
				hide_progress_bar ();

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
			drop: function (e) {
				$('#filemanager-dropzone').removeClass ('filemanager-over');
				
				// Confirm if overwriting
				if (!e.originalEvent.dataTransfer) {
					return false;
				}
				
				var uploads = e.originalEvent.dataTransfer.files;
				
				if (uploads === null || uploads === undefined || uploads.length === 0) {
					return false;
				}
				
				for (i = 0; i < uploads.length; i++) {
					for (j = 0; j < files.length; j++) {
						if (files[j].name == uploads[i].name) {
							if (! confirm ($.i18n ('Overwrite existing file? ' + uploads[i].name))) {
								return false;
							}
						}
					}
				}
			},
			uploadStarted: function (i, file, len) {
				// Save the total so we only notify at the end
				filemanager._upload_total = len;

				// Replace the upload field with a progress bar
				show_progress_bar ();
			},
			uploadFinished: function (i, file, res, time) {
				if (! res.success) {
					alert (res.error);

					// Reset the upload progress bar
					hide_progress_bar ();
				
				} else {
					if (i === filemanager._upload_total - 1) {
						// This is the last file, add notification
						$.add_notification (res.data);
						
						// Update the file list
						$.filemanager ('ls', {file: filemanager.path});
						
						// Reset the upload progress bar
						hide_progress_bar ();
					}
				}
			},
			progressUpdated: function (i, file, progress) {
				// Update the progress bar
				$('#filemanager-upload-progress-bar').css ('width', progress + '%');
			}
		});
			
		// filedrop hides the input, show it again
		$('#file-upload').css ({
			display: 'inline',
			width: 'auto',
			height: 'auto'
		});
	};

	function show_progress_bar () {
		$('#filemanager-upload-form').css ({display: 'none'});
		$('#filemanager-upload-progress-bar').css ({display: 'inline-block'});
		$('#filemanager-upload-progress-text').css ({display: 'inline-block'});
		$('#filemanager-upload-progress').css ({display: 'inline-block'});
	}

	function hide_progress_bar () {
		$('#filemanager-upload-progress-bar').css ('width', '5%');
		$('#filemanager-upload-progress-bar').css ({display: 'none'});
		$('#filemanager-upload-progress-text').css ({display: 'none'});
		$('#filemanager-upload-progress').css ({display: 'none'});
		$('#filemanager-upload-form').css ({display: 'inline-block'});
	}

	$.filemanager_prop = function (form) {
		var file = form.elements.file.value,
			desc = form.elements.desc.value,
			link = form.elements.link.value,
			data = {
				file: file,
				props: {
					desc: desc,
					link: link
				}
			};

		$.post ('/filemanager/api/prop', data, function (res) {
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
