/**
 * Provides a Dynamic Objects menu for the Redactor editor,
 * which integrates with Elefant's dynamic handler embedding
 * capability.
 */

if (! RedactorPlugins) var RedactorPlugins = {};

RedactorPlugins.dynamic = function () {
	return {
		// Initialize the plugin
		init: function () {
			var button = this.button.add ('dynamic', $.i18n ('Dynamic Objects'));
			this.button.setAwesome ('dynamic', 'fa-cog');
			this.button.addCallback (button, this.dynamic.add_handler);
			$('.redactor-editor').on ('click', '.embedded', $.proxy (this.dynamic.edit_handler, this));
		},

		// Open the dialog when the button is clicked
		add_handler: function (button, el, self, evt, current) {
			this.dynamic._current = current ? this.dynamic._current : null;
			this.selection.save ();
			$.dynamicobjects ({
				callback: $.proxy (this.dynamic.insert_object, this),
				current: current ? current : null
			});
		},

		// Reopen the dialog to edit an existing embed
		edit_handler: function (evt) {
			this.dynamic._current = evt.target;
			this.dynamic.add_handler ('dynamic', evt.target, this, evt, $(evt.target).attr ('data-embed'));
			return false;
		},

		// Insert/replace an embed code in the editor
		insert_object: function (embed_code, handler, params, label) {
			this.selection.restore ();
			this.buffer.set ();

			if (this.dynamic._current) {
				// update existing embed
				$(this.dynamic._current)
					.attr ('data-embed', embed_code)
					.attr ('data-label', label);

				this.code.sync();
			} else {
				// enter a new embed
				this.insert.htmlWithoutClean (
					'<span class="embedded" data-embed="' + embed_code + '" data-label="' + label + '" title="Click to edit."></span>'
				);
			}
		}
	};
};
