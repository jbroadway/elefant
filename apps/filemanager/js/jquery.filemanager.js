/**
 * Used by the filemanager app to implement ajax functions.
 */
(function ($) {
	$.extend ({
		filemanager: function (cmd, options) {
			var defaults = {
				root: '/filemanager/api/'
			};
			
			var options = $.extend (defaults, options);
			var dir_lenght= 120;
			
			switch (cmd) {
				case 'mkdir':
					var name = downcode ( prompt ('New folder name:', ''), dir_lenght );
					if (name) {
						$.get (options.root + cmd + '/' + options.file + '/' + , function (res) {
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
					var name = downcode ( prompt ('Rename:', options.name), dir_lenght);
					if (name) {
						$.get (options.root + cmd + '/' + options.file + '?rename=' + name, function (res) {
							if (res.success) {
								$.add_notification (res.data.msg);
								$.filemanager ('ls', {file: filemanager_path});
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
								$.filemanager ('ls', {file: filemanager_path});
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
			}
			return false;
		}
	});

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
