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
			
			switch (cmd) {
				case 'mkdir':
					var name = prompt ('New folder name:', '');
					if (name) {
						$.get (options.root + cmd + '/' + options.file + '/' + name, function (res) {
							if (res.success) {
								window.location = '/filemanager?path=' + res.data;
							} else {
								alert (res.error);
							}
						});
					}
					break;
				case 'mv':
					var name = prompt ('Rename:', options.name);
					if (name) {
						$.get (options.root + cmd + '/' + options.file + '?rename=' + name, function (res) {
							if (res.success) {
								$.filemanager ('ls', {file: filemanager_path});
							} else {
								alert (res.error);
							}
						});
					}
					break;
				case 'rm':
					if (confirm ('Are you sure you want to delete this file?')) {
						$.get (options.root + cmd + '/' + options.file, function (res) {
							if (res.success) {
								$.filemanager ('ls', {file: filemanager_path});
							} else {
								alert (res.error);
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
						}
					});
					break;
			}
			return false;
		}
	});
})(jQuery);
