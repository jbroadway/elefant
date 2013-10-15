/**
 * Provides a Dynamic Objects menu for the Redactor editor,
 * which integrates with Elefant's dynamic handler embedding
 * capability.
 */

if (typeof RedactorPlugins === 'undefined') var RedactorPlugins = {};

RedactorPlugins.dynamic = {
	// Initialize the plugin
	init: function () {
		$('.redactor_editor').on ('click', '.embedded', {plugin: this}, $.proxy (this.edit_handler, this));
		this.buttonAdd ('dynamic', $.i18n ('Dynamic Objects'), $.proxy (this.add_handler, this));
	},

	// Open the dialog when the button is clicked
	add_handler: function (button, el, self, evt, current) {
		this._current = current ? this._current : null;
		this.selectionSave ();
		$.dynamicobjects ({
			callback: $.proxy (this.insert_object, this),
			current: current ? current : null
		});
	},

	// Reopen the dialog to edit an existing embed
	edit_handler: function (evt) {
		this._current = evt.target;
		this.add_handler ('dynamic', evt.target, this, evt, $(evt.target).data ('embed'));
		return false;
	},

	// Insert/replace an embed code in the editor
	insert_object: function (embed_code, handler, params, label) {
		this.selectionRestore ();
		if (this._current) {
			// update existing embed
			$(this._current).replaceWith (
				'<span class="embedded" data-embed="' + embed_code + '" data-label="' + label + '" title="Click to edit."></span>'
			);
			this.sync();
		} else {
			// enter a new embed
			this.insertHtml (
			//this.exec.command.call (
			//	this,
			//	'inserthtml',
				'<span class="embedded" data-embed="' + embed_code + '" data-label="' + label + '" title="Click to edit."></span>'
			);
		}
	}
};