$.Redactor.prototype.undo = function () {
	return {
		init: function () {
			var dropdown = {};
			
			dropdown.point1 = { title: $.i18n ('Undo'), func: this.buffer.undo };
			dropdown.point2 = { title: $.i18n ('Redo'), func: this.buffer.redo };
			
			var button = this.button.add ('undo', $.i18n ('Undo'));
			this.button.setIcon (button, '<i class="re-icon-undo"></i>');
			this.button.addDropdown (button, dropdown);
		}
	};
};
