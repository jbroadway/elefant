/**
 * Integrates Redactor with Elefant's file browser dialog.
 * Requires Elefant's filemanager/util/browser handler.
 */

if (typeof RedactorPlugins === 'undefined') var RedactorPlugins = {};

RedactorPlugins.filebrowser = {
	// Initialize the plugin
	init: function () {
		this.addBtnAfter ('imagebrowser', 'filebrowser', $.i18n ('Insert File'), $.proxy (this.open_dialog, this));
		//this.button.addAfter.call (this, 'imagebrowser', 'filebrowser', $.i18n ('Insert File'), $.proxy (this.open_dialog, this));
	},
	
	open_dialog: function (self, evt, button) {
		$.filebrowser ({
			callback: $.proxy (this.insert_file, this)
		});
	},
	
	insert_file: function (file) {
		if (file.match (/\.(jpg|png|gif)$/i)) {
			this.insertHtml ('<img src="' + file + '" alt="" style="" />');
			//this.exec.command.call (this, 'inserthtml', '<img src="' + file + '" alt="" style="" />');
		} else if (file.match (/\.swf$/i)) {
			this.insertHtml ('<span class="embedded" data-embed="filemanager/swf?file=' + file + '" data-label="Embedded Flash (SWF)" title="Click to edit."></span>');
			//this.exec.command.call (this, 'inserthtml', '<p><span class="embedded" data-embed="filemanager/swf?file=' + file + '" data-label="{"Embedded Flash (SWF)"}" title="{"Click to edit."}"></span></p>');
		} else if (file.match (/\.(mp4|m4v|flv|f4v)$/i)) {
			this.insertHtml ('<span class="embedded" data-embed="filemanager/video?file=' + file + '" data-label="Embedded Video (MP4)" title="Click to edit."></span>');
			//this.exec.command.call (this, 'inserthtml', '<p><span class="embedded" data-embed="filemanager/video?file=' + file + '" data-label="{"Embedded Video (MP4)"}" title="{"Click to edit."}"></span></p>');
		} else if (file.match (/\.mp3$/i)) {
			this.insertHtml ('<span class="embedded" data-embed="filemanager/audio?file=' + file + '" data-label="Embedded Audio (MP3)" title="Click to edit."></span>');
			//this.exec.command.call (this, 'inserthtml', '<p><span class="embedded" data-embed="filemanager/audio?file=' + file + '" data-label="{"Embedded Audio (MP3)"}" title="{"Click to edit."}"></span></p>');
		} else {
			var basename = file.match (/([^\/]+)$/)[1];
			this.insertHtml ('<a href="' + file + '">' + basename + '</a>');
			//this.exec.command.call (this, 'inserthtml', '<a href="' + file + '">' + basename + '</a>');
		}
	}
};