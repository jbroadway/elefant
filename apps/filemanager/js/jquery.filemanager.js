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
		img_file: /\.(gif|png|jpe?g)$/
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
					var name = downcode ( prompt ('New folder name:', ''), dir_length );
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
					var name = downcode ( prompt ('Rename:', options.name), dir_length);
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
				case 'rm':
					if (confirm ('Are you sure you want to delete this file?')) {
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
									$.tmpl ('tpl_dir', res.data.dirs[i]).appendTo (tbody);
								}
							}
							if (res.data.files) {
								for (var i = 0; i < res.data.files.length; i++) {
									res.data.files[i].image_file = (filemanager.aviary !== null)
										? res.data.files[i].name.match (filemanager.img_file)
										: false;
									res.data.files[i].text_file = res.data.files[i].name.match (filemanager.text_file);
									$.tmpl ('tpl_file', res.data.files[i]).appendTo (tbody);
								}
							}
							$.localize_dates ();
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

		if (filemanager.aviary_key) {
			filemanager.aviary = new Aviary.Feather ({
				apiKey: filemanager.aviary_key,
				apiVersion: 2,
				tools: 'all',
				appendTo: '',
				onSave: function (img, url) {
					// send update to server
					console.log (img);
					console.log (url);
					console.log (filemanager.aviary_current);
				}
			});
		}

		$.filemanager ('ls', {file: filemanager.path});
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
})(jQuery);
