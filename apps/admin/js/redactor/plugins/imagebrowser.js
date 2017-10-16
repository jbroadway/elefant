/**
 * Integrates Redactor with Elefant's thumbnail image browser.
 * Requires Elefant's filemanager/util/browser handler.
 */

$.Redactor.prototype.imagebrowser = function () {
	return {
		// Initialize the plugin
		init: function () {
			var button = this.button.addAfter ('links', 'imagebrowser', $.i18n ('Insert Image'));
			this.button.setIcon (button, '<i class="re-icon-image"></i>');
			this.button.addCallback (button, this.imagebrowser.open_image_dialog);
		},
	
		open_image_dialog: function (button, el, self, evt) {
			this.selection.save ();
			$.filebrowser ({
				thumbs: true,
				callback: $.proxy (this.imagebrowser.insert_image, this)
			});
		},
	
		insert_image: function (file) {
			this.selection.restore ();
			this.buffer.set ();

			this.insert.html ('<img src="' + file + '" alt="" style="" />');
		}
	};
};
