$.Redactor.prototype.superscript = function () {
	return {
		init: function () {
			var sup = this.button.add ('superscript', $.i18n ('Superscript'));
			this.button.setIcon (sup, '<i class="re-icon-sup"></i>');
			this.button.addCallback (sup, this.superscript.formatSup);
		},
		formatSup: function () {
			this.inline.format ('sup');
		}
	};
};
