/**
 * Integrates Redactor with Elefant's file browser dialog.
 * Requires Elefant's filemanager/util/browser handler.
 */

if (! RedactorPlugins) var RedactorPlugins = {};

RedactorPlugins.filebrowser = function () {
	return {
		// Initialize the plugin
		init: function () {
			var button = this.button.addAfter ('imagebrowser', 'filebrowser', $.i18n ('Insert File'));
			this.button.setAwesome ('filebrowser', 'fa-paperclip');
			this.button.addCallback (button, this.filebrowser.open_dialog);
		},
	
		open_dialog: function (button, el, self, evt) {
			this.selection.save ();
			$.filebrowser ({
				callback: $.proxy (this.filebrowser.insert_file, this)
			});
		},
	
		insert_file: function (file) {
			this.selection.restore ();
			this.buffer.set ();

			if (file.match (/\.(jpg|png|gif)$/i)) {
				this.insert.html ('<img src="' + file + '" alt="" style="" />');
			} else if (file.match (/\.swf$/i)) {
				this.insert.html ('<span class="embedded" data-embed="filemanager/swf?file=' + file + '" data-label="Embedded Flash (SWF)" title="Click to edit."></span>');
			} else if (file.match (/\.(mp4|m4v|flv|f4v)$/i)) {
				this.insert.html ('<span class="embedded" data-embed="filemanager/video?file=' + file + '" data-label="Embedded Video (MP4)" title="Click to edit."></span>');
			} else if (file.match (/\.mp3$/i)) {
				this.insert.html ('<span class="embedded" data-embed="filemanager/audio?file=' + file + '" data-label="Embedded Audio (MP3)" title="Click to edit."></span>');
			} else {
				var basename = file.match (/([^\/]+)$/)[1];
				this.insert.html ('<a href="' + file + '">' + basename + '</a>');
			}
		}
	};
};