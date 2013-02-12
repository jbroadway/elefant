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
		this.button.add.call (this, 'dynamic', $.i18n ('Dynamic Objects'), $.proxy (this.add_handler, this));
	},

	// Open the dialog when the button is clicked
	add_handler: function (self, evt, button, current) {
		console.log ('add_handler()');
		this._current = current ? this._current : null;
		this.selection.save.call (this);
		$.dynamicobjects ({
			callback: $.proxy (this.insert_object, this),
			current: current ? current : null
		});
	},

	// Reopen the dialog to edit an existing embed
	edit_handler: function (evt) {
		console.log ('edit_handler()');
		this._current = evt.currentTarget;
		var current = $(evt.currentTarget).data ('embed');

		console.log (this._current);								// <span class="embedded"...

		// I've got the node in evt.currentTarget, but how
		// do I turn it into a selection in the editor?

		//this.selection.element.call (this, evt.currentTarget);	// Uncaught Error: TYPE_MISMATCH_ERR: DOM Exception 17
		//this.selection.caret.call (this, evt.currentTarget, 0);	// TypeError: Object #<Object> has no method 'getTextNodesIn'
		//this.selection.start.call (this, evt.currentTarget);		// Uncaught Error: TYPE_MISMATCH_ERR: DOM Exception 17

		console.log (this.selection.html.call (this));				// "", hoping for <span class="embedded"...
		console.log (this.selection.getElement.call (this));		// false, hoping for <span class="embedded"...

		this.add_handler (this, evt, 'dynamic', current);
		return false;
	},

	// Insert/replace an embed code in the editor
	insert_object: function (embed_code, handler, params, label) {
		console.log ('insert_object()');
		console.log (embed_code);									// "myapp/myhandler?foo=bar"
		console.log (handler);										// "myapp/myhandler"
		console.log (params);										// {"foo": "bar"}
		console.log (label);										// "MyApp: My Handler"

		if (this._current) {
			// update existing embed
			this.selection.restore.call (this);

			console.log (this._current);							// <span class="embedded"...
			console.log (this.selection.html.call (this));			// "", hoping for <span class="embedded"...
			console.log (this.selection.getElement.call (this));	// false, hoping for <span class="embedded"...
		} else {
			// enter a new embed
			//this.insert.html.call (this, '<span class="embedded" data-embed="' + embed_code + '" data-label="' + label + '" title="Click to edit."></span>'); // Uncaught Error: INDEX_SIZE_ERR: DOM Exception 1
			//this.insert.force.call (this, '<span class="embedded" data-embed="' + embed_code + '" data-label="' + label + '" title="Click to edit."></span>'); // Uncaught Error: INDEX_SIZE_ERR: DOM Exception 1

			// this one works:
			this.exec.command.call (this, 'inserthtml', '<span class="embedded" data-embed="' + embed_code + '" data-label="' + label + '" title="Click to edit."></span>');
		}
	}
};