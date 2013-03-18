/**
 * Integrates Redactor with Elefant's thumbnail image browser.
 * Requires Elefant's filemanager/util/browser handler.
 */

if (typeof RedactorPlugins === 'undefined') var RedactorPlugins = {};

RedactorPlugins.imagebrowser = {
	// Initialize the plugin
	init: function () {
		this.addBtnAfter ('link_unlink', 'imagebrowser', $.i18n ('Insert Image'), $.proxy (this.open_dialog, this));
		//this.button.addAfter.call (this, 'link_unlink', 'imagebrowser', $.i18n ('Insert Image'), $.proxy (this.open_dialog, this));
	},
	
	open_dialog: function (self, evt, button) {
		self.saveSelection ();
		$.filebrowser ({
			thumbs: true,
			callback: $.proxy (this.insert_image, this)
		});
	},
	
	insert_image: function (file) {
		this.restoreSelection ();
		this.insertHtml ('<img src="' + file + '" alt="" style="" />');
		//this.exec.command.call (this, 'inserthtml', '<img src="' + file + '" alt="" style="" />');
	}
};