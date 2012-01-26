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
 * ExtendedModel extends Model to include the ability to add arbitrary values
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
 *   <?php
 *   
 *   class Foo extends ExtendedModel {
 *     // store extended data in a field named extradata
 *     public $_extended_field = 'extradata';
 *   }
 *   
 *   // fetch an item
 *   $foo = new Foo (1);
 *   
 *   // get its extradata field
 *   $extra = $foo->extradata;
 *
 *   // or
 *   $extra = $foo->ext ();
 *
 *   // add some fields to it
 *   $extra['favorite_food'] = 'pizza';
 *   $extra['favorite_color'] = 'green';
 *
 *   // or
 *   $foo->ext ('favorite_food', 'pizza');
 *   $foo->ext ('favorite_color', 'green');
 *
 *   // set it and save it
 *   $foo->extradata = $extra;
 *   $foo->put ();
 *
 *   // next time we retrieve the item
 *   // our data will be there
 *   $foo = new Foo (1);
 *   $extra = $foo->extra;
 *   echo $extra['favorite_food'];
 *
 *   // or
 *   $foo = new Foo (1);
 *   echo $foo->ext ('favorite_food');
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
	 * Dynamic getter for user properties. If you get the field specified
	 * in the child class's `$_extended_field` property, it will automatically
	 * unserialize it into an array for you.
	 */
	public function __get ($key) {
		if ($key == $this->_extended_field) {
			if ($this->_extended === false) {
				$this->_extended = (array) json_decode ($this->data[$this->_extended_field]);
			}
			return $this->_extended;
		}
		return parent::__get ($key);
	}

	/**
	 * Dynamic setter for extended properties field. If you set the field
	 * specified in the child class's `$_extended_field` property, it will
	 * automatically serialize it into JSON for storage.
	 */
	public function __set ($key, $val) {
		if ($key === $this->_extended_field) {
			$this->_extended = $val;
			$this->data[$key] = json_encode ($val);
			return;
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

?>