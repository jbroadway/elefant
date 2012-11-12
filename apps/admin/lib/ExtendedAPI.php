<?php

/**
 * Provides the RESTful API for the extended fields form.
 */
class ExtendedAPI extends Restful {
	/**
	 * Add a new field. Parameters should be:
	 *
	 * - class
	 * - name
	 * - label
	 * - type
	 * - required
	 * - options
	 */
	public function post_add () {
		if (! isset ($_POST['class'])) {
			return $this->error (__ ('Missing parameter: class'));
		}
		
		if (! class_exists ($_POST['class'])) {
			return $this->error (__ ('Invalid class name.'));
		}

		if (! isset ($_POST['name']) || empty ($_POST['name'])) {
			return $this->error (__ ('Missing parameter: name'));
		}

		if (! isset ($_POST['label']) || empty ($_POST['label'])) {
			return $this->error (__ ('Missing parameter: label'));
		}

		if (! isset ($_POST['type']) || empty ($_POST['type'])) {
			return $this->error (__ ('Missing parameter: type'));
		}
		
		if (! isset ($_POST['required'])) {
			return $this->error (__ ('Missing parameter: required'));
		}
		
		if (! isset ($_POST['options'])) {
			return $this->error (__ ('Missing parameter: options'));
		}
		error_log (json_encode ($_POST));
		error_log ($_POST['class']);
		error_log ($_POST['type']);
		error_log ($_POST['name']);

		$_POST['sort'] = ExtendedFields::next_sort ($_POST['class']);
		error_log ($_POST['sort']);

		$obj = new ExtendedFields ($_POST);

		if (! $obj->put ()) {
			return $this->error (__ ('An unknown error occurred.'));
		}

		return $obj->orig ();
	}

	/**
	 * Update a field.
	 */
	public function post_edit () {
		if (! isset ($_POST['id'])) {
			return $this->error (__ ('Missing parameter: id'));
		}

		$obj = new ExtendedFields ($_POST['id']);
		if ($obj->error) {
			return $this->error (__ ('Field not found.'));
		}

		$obj->label		= isset ($_POST['label'])		? $_POST['label']		: $obj->label;
		$obj->type		= isset ($_POST['type'])		? $_POST['type']		: $obj->type;
		$obj->options	= isset ($_POST['options'])		? $_POST['options']		: $obj->options;
		$obj->required	= isset ($_POST['required'])	? $_POST['required']	: $obj->required;

		if (! $obj->put ()) {
			return $this->error (__ ('An unknown error occurred.'));
		}

		return $obj->orig ();
	}

	/**
	 * Delete a field.
	 */
	public function post_delete () {
		if (! isset ($_POST['id'])) {
			return $this->error (__ ('Missing parameter: id'));
		}

		$obj = new ExtendedFields ($_POST['id']);
		if ($obj->error) {
			return $this->error (__ ('Field not found.'));
		}

		$o = $obj->orig ();

		if (! $obj->remove ()) {
			return $this->error (__ ('An unknown error occurred.'));
		}

		return $_POST['id'];
	}

	/**
	 * Update the sorting order of the specified fields.
	 * Accepts an array of fields of the form:
	 *
	 *     fields[123]=2&fields[234]=0&field[345]=1
	 *
	 * The keys are the field IDs and the values are the sorting
	 * order (ascending).
	 */
	public function post_sort () {
		if (! isset ($_POST['fields']) || ! is_array ($_POST['fields'])) {
			return $this->error (__ ('Missing parameter: fields'));
		}

		return true;
	}
}

?>