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
		options_empty: 'You must enter options for a select field.',
		field_added: 'Field added.',
		field_updated: 'Field updated.',
		field_deleted: 'Field deleted.',
		order_updated: 'Field order has been updated.'
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
	 * Server-side API methods.
	 */
	e.api = {};

	/**
	 * API URL prefix.
	 */
	e.api.prefix = '/admin/extended/api/';

	/**
	 * Add a new field to the list.
	 */
	e.api.add_field = function (data, callback) {
		$.post (e.api.prefix + 'add', data, callback);
	};

	/**
	 * Update an existing field.
	 */
	e.api.update_field = function (data, callback) {
		$.post (e.api.prefix + 'edit', data, callback);
	};

	/**
	 * Delete a field from the list.
	 */
	e.api.delete_field = function (id, callback) {
		$.post (e.api.prefix + 'delete', {id: id}, callback);
	};

	/**
	 * Update sorting order of the fields.
	 */
	e.api.update_order = function (fields, callback) {
		$.post (e.api.prefix + 'sort', {fields: fields}, callback);
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

		$('#fields').sortable ({
			items: '> div',
			handle: '.field-handle',
			containment: 'parent',
			axis: 'y',
			placeholder: 'placeholder',
			opacity: 0.9,
			update: e.update_sorting_order
			
		});
		$('#fields').disableSelection ();
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
		e.div.append (e.render.field (field));

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
				required: form.elements.required.checked ? 1 : 0,
				options: form.elements.options.value,
				name: _name (form.elements.label.value),
				class: e.extends
			};

		if (data.label.length === 0) {
			return e.error (e.strings.label_empty);
		}

		if (data.type === 'select' && data.options.length === 0) {
			return e.error (e.strings.options_empty);
		}

		$('#new-field-saving').show ();

		e.api.add_field (data, function (res) {
			$('#new-field-saving').hide (200);

			if (! res.success) {
				$.add_notification (res.error);
				return;
			}

			$.add_notification (e.strings.field_added);
			e.init_field (res.data);
			e.show_add_field_button ();
		});

		return false;
	};

	/**
	 * Deletes a field from the list.
	 */
	e.delete_field = function (evt) {
		if (confirm (e.strings.confirm_delete)) {
			var id = _parse_id ($(evt.target).attr ('id'));
			e.api.delete_field ({id: id}, function (res) {
				if (! res.success) {
					$.add_notification (res.error);
					return;
				}

				$.add_notification (e.strings.field_deleted);
				$('#' + id + '-wrapper').remove ();
			});
		};
		return false;
	};
	
	/**
	 * Update the sorting order of the fields.
	 */
	e.update_sorting_order = function (evt, ui) {
		var new_order = [],
			fields = $('#fields .field');

		for (var i = 0; i < fields.length; i++) {
			new_order.push (_parse_id ($(fields[i]).attr ('id')));
		}

		e.api.update_order (new_order, function (res) {
			if (! res.success) {
				$.add_notification (res.error);
				return;
			}

			$.add_notification (e.strings.order_updated);
		});
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