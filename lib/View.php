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
 * View provides a facility for calling functions or templates and returning
 * their data, for the purpose of separating presentational logic from
 * handlers.
 *
 * Usage:
 *
 *     <?php
 *     
 *     // render a view template
 *     echo View::render ('myapp/hello', array ('name' => 'Joe'));
 *     
 *     // set parameters first
 *     View::set ('name', 'Jack');
 *     echo View::render ('myapp/hello');
 *     
 *     // set a list of parameters first
 *     View::set (array (
 *         'name' => 'Jill',
 *         'age' => 28
 *     ));
 *     
 *     // render a view function
 *     echo View::render (function ($params) {
 *         return sprintf ('<p>%s</p>', join (', ', $params));
 *     });
 *     
 *     // render a template from within a view function
 *     echo View::render (function ($params) {
 *         return View::render ('myapp/hello', $params);
 *     });
 *     
 *     // pass $page to your view function
 *     echo View::render (function ($params) use ($page) {
 *         return sprintf ('<p>Layout: %s</p>', $page->layout);
 *     });
 *     
 *     ?>
 *
 * Assuming `myapp/hello` contains `<p>Hello {{name}}</p>`, then the above
 * will output:
 *
 *     <p>Hello Joe</p>
 *     <p>Hello Jack</p>
 *     <p>Jill, 28</p>
 *     <p>Hello Jill</p>
 *     <p>Layout: default</p>
 *
 * Note: To pass the controller to a view function, here's how you do it:
 *
 *     <?php
 *     
 *     // reassign the controller, since
 *     // we can't pass $this to use()
 *     $c = $this;
 *     
 *     echo View::render (function ($params) use ($c) {
 *         if (! $c->is_https ()) {
 *             $c->force_https ();
 *         }
 *         // continue with rendering...
 *     });
 *     
 *     ?>
 */
class View {
	/**
	 * The template renderer.
	 */
	public static $tpl;

	/**
	 * Parameters passed to the next `render()` that were assigned
	 * via `set()`.
	 */
	public static $params = array ();

	/**
	 * Sets the template renderer. The renderer can be any object
	 * that satisfies the following interface:
	 *
	 *     interface AbstractTemplateRenderer {
	 *         public function render ($template, $data = array ());
	 *     }
	 */
	public static function init ($tpl) {
		self::$tpl = $tpl;
	}

	/**
	 * Set a parameter or list of parameters. Accepts a key/value
	 * pair, or a single array or object.
	 */
	public static function set ($name, $value = null) {
		if ($value === null) {
			self::$params = $name;
		} else {
			self::$params[$name] = $value;
		}
	}

	/**
	 * Renders a view. Accepts either a function and its parameters, or a
	 * template path and the data to pass to it.
	 */
	public static function render ($view, $params = null) {
		// determine the parameters
		if (func_num_args () > 2) {
			$params = func_get_args ();
			array_shift ($params);
		} elseif ($params === null) {
			$params = self::$params;
		}

		// reset params for the next call
		self::$params = array ();

		// render and return the response
		if (is_callable ($view)) {
			return call_user_func ($view, $params);
		}
		return self::$tpl->render ($view, $params);
	}
}

?>