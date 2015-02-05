<?php

/**
 * Elefant CMS - http://www.elefantcms.com/
 *
 * Copyright (c) 2011 Johnny Broadway
 * 
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 * 
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 * 
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */

/**
 * ExtendedModel extends [[Model]] to include the ability to add arbitrary values
 * to a single field that will be JSON encoded in storage and transparently
 * decoded in use. This allows you to extend your Model data with any number
 * of additional values without needing to change your schema.
 *
 * Note that these values are not indexable directly, but for additional data
 * that you don't need to search on, this can be useful. You can also build
 * simple additional tables for indexing associated data that does need to
 * be searched on, but that is outside of the scope of this class.
 *
 * Usage:
 *
 * 1\. By specifying allowed extended fields in `$verify`
 *
 *     <?php
 *     
 *     class Foo extends ExtendedModel {
 *         // store extended data in a field named extradata
 *         public $_extended_field = 'extradata';
 *     
 *         public $verify = array (
 *             // regular field validations, followed by extended fields:
 *             'favorite_food' => array (
 *                 'extended' => 1 // mark as extended
 *             ),
 *             'favorite_color' => array (
 *                 'extended' => 1, // can also have validation rules:
 *                 'regex' => '/^(red|green|blue|yellow|orange|purple|pink|brown)$/i'
 *             )
 *         );
 *     }
 *     
 *     // fetch an item
 *     $foo = new Foo (1);
 *     
 *     // since we've defined the extended fields, we can access them directly
 *     $foo->favorite_food = 'pizza';
 *    
 *     // this will fail to save because the validation fails
 *     $foo->favorite_color = 'black';
 *     if (! $foo->put ()) {
 *         echo $foo->error;
 *     }
 *     
 *     ?>
 *
 * 2\. By accessing the extradata field directly (will automatically serialize
 * and unserialize for you), or through the `ext()` method:
 *
 *     <?php
 *     
 *     class Foo extends ExtendedModel {
 *         // store extended data in a field named extradata
 *         public $_extended_field = 'extradata';
 *     }
 *     
 *     // fetch an item
 *     $foo = new Foo (1);
 *     
 *     // get its extradata field
 *     $extra = $foo->extradata;
 *     
 *     // or
 *     $extra = $foo->ext ();
 *     
 *     // add some fields to it
 *     $extra['favorite_food'] = 'pizza';
 *     $extra['favorite_color'] = 'green';
 *     
 *     // or
 *     $foo->ext ('favorite_food', 'pizza');
 *     $foo->ext ('favorite_color', 'green');
 *     
 *     // set it and save it
 *     $foo->extradata = $extra;
 *     $foo->put ();
 *     
 *     // next time we retrieve the item
 *     // our data will be there
 *     $foo = new Foo (1);
 *     $extra = $foo->extra;
 *     echo $extra['favorite_food'];
 *     
 *     // or
 *     $foo = new Foo (1);
 *     echo $foo->ext ('favorite_food');
 *      
 *     ?>
 */
class ExtendedModel extends Model {
	/**
	 * Override this in your child class to tell the ExtendedModel which field
	 * contains your extended properties.
	 */
	public $_extended_field;

	/**
	 * This is the unserialized array of extended data from the extended field.
	 */
	public $_extended = false;

	/**
	 * Verification rules for extended attributes.
	 */
	private $_extended_verify = array ();

	/**
	 * Need to separate verify list for regular and extended attributes,
	 * so we override the constructor to do so.
	 */
	public function __construct ($vals = false, $is_new = true) {
		$defaults = array ();

		foreach ($this->verify as $k => $v) {
			if (isset ($v['extended'])) {
				if (isset ($v['default'])) {
					$defaults[$k] = $v['default'];
					unset ($v['default']);
				}
				unset ($v['extended']);
				$this->_extended_verify[$k] = $v;
				unset ($this->verify[$k]);
			}
		}

		// `_extended` is a special field to auto-populate extended attributes
		if (is_array ($vals) && isset ($vals['_extended'])) {
			$extended = $vals['_extended'];
			unset ($vals['_extended']);
		}

		parent::__construct ($vals, $is_new);

		// Pre-populate extended attributes with given default values
		if (! empty ($defaults)) {
			$ext = (is_string($this->data[$this->_extended_field])) ? 
				(array) json_decode($this->data[$this->_extended_field]) : 
				(array) $this->data[$this->_extended_field];
			foreach ($defaults as $k => $v) {
				if (!isset($ext[$k])) 
					$ext[$k] = $v;
			}
			$this->data[$this->_extended_field] = json_encode($ext);
		}

		// Populate extended attributes after `parent::__construct()`
		if (isset ($extended)) {
			foreach ($extended as $k => $v) {
				$this->ext ($k, $v);
			}
		}
	}

	/**
	 * Need to verify extended fields, so we override the `put()` method.
	 * Note: On update forms, call `update_extended()` if the fields were
	 * set by the `admin/util/extended` handler.
	 */
	public function put () {
		$failed = Validator::validate_list ($this->ext (), $this->_extended_verify);
		if (! empty ($failed)) {
			$this->error = 'Validation failed for extended fields: ' . join (', ', $failed);
			return false;
		}

		return parent::put ();
	}

	/**
	 * Look for `_extended` field and auto-populate extended attributes.
	 * Will unset `$_POST['_extended']` as a side-effect. Call this before
	 * calling `put()` on update forms that use the `admin/util/extended`
	 * handler.
	 */
	public function update_extended () {
		if (isset ($_POST['_extended'])) {
			foreach ($_POST['_extended'] as $k => $v) {
				$this->ext ($k, $v);
			}
			unset ($_POST['_extended']);
		}
	}

	/**
	 * Return the original data as an object, including extended fields.
	 */
	public function orig () {
		return (object) array_merge ($this->data, $this->ext ());
	}

	/**
	 * Dynamic getter for user properties. If you get the field specified
	 * in the child class's `$_extended_field` property, it will automatically
	 * unserialize it into an array for you.
	 *
	 * If an extended property has been defined in the `$verify` list, you can
	 * also get it directly using the usual `$model->property` syntax.
	 */
	public function __get ($key) {
		if ($key == $this->_extended_field) {
			if ($this->_extended === false) {
				if (isset ($this->data[$this->_extended_field])) {
					$this->_extended = (array) json_decode ($this->data[$this->_extended_field]);
				} else {
					$this->data[$this->_extended_field] = json_encode (array ());
					$this->_extended = array ();
				}
			}
			return $this->_extended;
		} elseif (isset ($this->_extended_verify[$key])) {
			return $this->ext ($key);
		}
		return parent::__get ($key);
	}

	/**
	 * Dynamic setter for extended properties field. If you set the field
	 * specified in the child class's `$_extended_field` property, it will
	 * automatically serialize it into JSON for storage.
	 *
	 * If an extended property has been defined in the `$verify` list, you can
	 * also set it directly using the usual `$model->property = '...'` syntax.
	 */
	public function __set ($key, $val) {
		if ($key === $this->_extended_field) {
			$this->_extended = $val;
			$this->data[$key] = json_encode ($val);
			return;
		} elseif (isset ($this->_extended_verify[$key])) {
			return $this->ext ($key, $val);
		}
		return parent::__set ($key, $val);
	}

	/**
	 * This method provides an easy getter/setter for the extended field values.
	 * It works around the fact that accessing the array elements directly from the
	 * extended field won't trigger the __get() and __set() magic methods.
	 */
	public function ext ($key = null, $val = null) {
		if ($key === null) {
			return $this->{$this->_extended_field};
		}
		if ($val !== null) {
			$ext = $this->{$this->_extended_field};
			$ext[$key] = $val;
			$this->{$this->_extended_field} = $ext;
		}
		$ext = $this->{$this->_extended_field};
		return $ext[$key];
	}
}
