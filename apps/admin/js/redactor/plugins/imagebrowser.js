/**
 * Integrates Redactor with Elefant's thumbnail image browser.
 * Requires Elefant's filemanager/util/browser handler.
 */

if (typeof RedactorPlugins === 'undefined') var RedactorPlugins = {};

RedactorPlugins.imagebrowser = {
	// Initialize the plugin
	init: function () {
		this.buttonAddAfter ('link_unlink', 'imagebrowser', $.i18n ('Insert Image'), $.proxy (this.open_image_dialog, this));
	},
	
	open_image_dialog: function (button, el, self, evt) {
		this.selectionSave ();
		$.filebrowser ({
			thumbs: true,
			callback: $.proxy (this.insert_image, this)
		});
	},
	
	insert_image: function (file) {
		this.selectionRestore ();
		this.bufferSet ();

		this.insertHtml ('<img src="' + file + '" alt="" style="" />');
	}
};