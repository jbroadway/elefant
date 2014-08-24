$.editable.types.defaults.buttons = function (settings, original) {
	var $this = $(this);

	if (settings.submit) {
		var submit = jQuery('<button type="submit">');
		submit.addClass('editable-btn-save');
		submit.text(settings.submit);
		$this.append(submit);
	}

	if (settings.del) {
		var del = jQuery('<button>');
		del.addClass('editable-btn-delete');
		del.text(settings.del);
		del.data ('id', settings._id);
		del.attr ('href', settings._delete_url);
		$(this).append(del);

		$(del).click(function(e) {
			e.preventDefault ();
			$.confirm_and_post (this, $.i18n ('Are you sure you want to delete this item?'));
		});
	}

	if (settings.cancel) {
		var cancel = jQuery('<button type="button">');
		cancel.addClass('editable-btn-cancel');
		cancel.text(settings.cancel);
		$(this).append(cancel);

		$(cancel).click(function() {
			$(original).html(original.revert);
			original.editing = false;
		});
	}
};