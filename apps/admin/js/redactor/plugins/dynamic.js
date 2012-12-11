if (typeof ReactorPlugins === 'undefined') var RedactorPlugins = {};

RedactorPlugins.dynamic = {
	init: function () {
		var callback = $.proxy (function () {
			this.saveSelection ();
			$('#redactor_modal #dialog-link').click ($.proxy (function () {
				this.insert_object ();
				return false;
			}, this));
		}, this);

		this.addBtn ('dynamic', 'Dynamic Objects', function (obj) {
			obj.modalInit ('Dynamic Objects', '#dynamic', 500, callback);
		});
	},

	insert_object: function (html) {
		this.restoreSelection ();
		this.execCommand ('inserthtml', '<b>html here</b>');
		this.modalClose ();
	}
};