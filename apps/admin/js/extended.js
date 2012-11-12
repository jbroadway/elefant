/**
 * This is the extended fields object that contains the front-end logic
 * for the extended fields management form (`/admin/extended`). It is
 * initialized in the `apps/admin/views/extended.html` view template.
 */
var extended = (function ($) {
	var e = {};

	/**
	 * Debug mode.
	 */
	e.debug = true;

	/**
	 * The fields themselves.
	 */
	e.fields = {};

	/**
	 * The class that the fields are extending.
	 */
	e.extends = '';

	/**
	 * Whether a save request has been sent.
	 */
	e.saving = false;

	/**
	 * A list of templates.
	 */
	e.render = {};
	
	/**
	 * The fields div.
	 */
	e.div = null;

	/**
	 * Strings for i18n.
	 */
	e.strings = {
		confirm_delete: 'Are you sure you want to delete this field?',
		label_empty: 'You must enter a field name.',
		options_empty: 'You must enter options for a select field.'
	};

	/**
	 * Parse the ID from an ID attribute, taking the form
	 * `{{id}}-extra`.
	 */
	var _parse_id = function (value) {
		return parseInt (value.split ('-').shift ());
	};

	/**
	 * Generate a field name for ExtendedModel from the label.
	 */
	var _name = function (label) {
		return label
			.replace (/^\s+|\s+$/g, '')
			.replace (/[^a-zA-Z0-9_]+/g, '_')
			.replace (/^_+|_+$/g, '')
			.toLowerCase ();
	};

	/**
	 * Does the object have the specified property.
	 */
	var _has = function (obj, prop) {
		return obj.hasOwnProperty (prop);
	};

	/**
	 * Calls `console.log()` if debug mode is on.
	 */
	var _log = function (obj) {
		if (e.debug) {
			console.log (obj);
		}
	};

	/**
	 * Initialize the extended fields UI.
	 */
	e.init = function (data) {
		e.extends = data.extends;
		e.fields = data.fields;
		e.strings = data.strings;

		e.div = $('#fields');
		e.render.field = Handlebars.compile ($('#field-tpl').html ());

		for (i = 0; i < e.fields.length; i++) {
			e.init_field (e.fields[i]);
		}

		$('#add-field').on ('click', e.show_add_field_form);
		$('#new-field-type').on ('change', e.toggle_add_field_options);
		$('#new-field-cancel').on ('click', e.show_add_field_button);
		$('#new-field').on ('submit', e.add_field);
	};

	/**
	 * Initializes a field's edit form.
	 */
	e.init_field = function (field) {
		field.required = parseInt (field.required);
		field.text = (field.type === 'text') ? true : false;
		field.textarea = (field.type === 'textarea') ? true : false;
		field.select = (field.type === 'select') ? true : false;
		field.file = (field.type === 'file') ? true : false;
		e.div.append (e.render.field (e.fields[i]));

		$('#' + field.id + '-type').on ('change', e.toggle_field_options);
		$('#' + field.id + '-delete').on ('click', e.delete_field);
	};

	/**
	 * Shows the field form.
	 */
	e.show_add_field_form = function (evt) {
		$('#new-field').show ();
		$('#add-field').hide ();
		return false;
	};
	
	/**
	 * Adds a new field.
	 */
	e.show_add_field_button = function (evt) {
		$('#new-field').hide ();
		var form = $('#new-field-form')[0];
		form.elements.label.value = '';
		form.elements.type.selectedIndex = 0;
		form.elements.required.checked = false;
		form.elements.options.value = '';
		$('#add-field').show ();
		return false;
	};

	/**
	 * Toggle the options field visibility, show only for select fields.
	 */
	e.toggle_add_field_options = function (evt) {
		if ($(evt.target).val () === 'select') {
			$('#new-field-options').show ();
		} else {
			$('#new-field-options').hide ();
		}
	};

	/**
	 * Toggle the options field visibility, show only for select fields.
	 */
	e.toggle_field_options = function (evt) {
		var id = _parse_id ($(evt.target).attr ('id'));
		if ($(evt.target).val () === 'select') {
			$('#' + id + '-options').show ();
		} else {
			$('#' + id + '-options').hide ();
		}
	};
	
	/**
	 * Adds a new field.
	 */
	e.add_field = function (evt) {
		var form = $('#new-field-form')[0],
			data = {
				label: form.elements.label.value,
				type: form.elements.type[form.elements.type.selectedIndex].value,
				required: form.elements.required.checked,
				options: form.elements.options.value,
				name: _name (form.elements.label.value)
			};

		_log (data);
		if (data.label.length === 0) {
			return e.error (e.strings.label_empty);
		}

		if (data.type === 'select' && data.options.length === 0) {
			return e.error (e.strings.options_empty);
		}

		e.show_add_field_button ();
		return false;
	};

	/**
	 * Deletes a field from the list.
	 */
	e.delete_field = function (evt) {
		if (confirm (e.strings.confirm_delete)) {
			var id = _parse_id ($(evt.target).attr ('id'));
			console.log (id);
		};
		return false;
	};
	
	/**
	 * Show input validation error message.
	 */
	e.error = function (msg) {
		$.add_notification (msg);
		return false;
	};
	
	return e;
})(jQuery);