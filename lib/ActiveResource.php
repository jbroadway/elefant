<?php

/**
 * Basic implementation of the Ruby on Rails ActiveResource REST client.
 * Intended to work with RoR-based REST servers, which all share similar
 * API patterns.
 *
 * Usage:
 *
 * <?php
 *
 * require_once ('ActiveResource.php');
 *
 * class Song extends ActiveResource {
 *     var $site = 'http://localhost:3000/';
 *     var $element_name = 'songs';
 * }
 *
 * // create new item
 * $song = new Song (array ('artist' => 'Joe Cocker', 'title' => 'A Little Help From My Friends'));
 * $song->save ();
 *
 * // fetch and update an item
 * $song->find (44)->set ('title', 'The River')->save ();
 *
 * // line by line
 * $song->find (44);
 * $song->title = 'The River';
 * $song->save ();
 *
 * // get all songs
 * $songs = $song->find ('all');
 *
 * // delete a song
 * $song->find (44);
 * $song->destroy ();
 *
 * // custom method
 * $songs = $song->get ('by_year', array ('year' => 1999));
 *
 * ?>
 *
 * @author John Luxford <lux@companymachine.com>
 * @version 0.14 beta
 * @license http://opensource.org/licenses/lgpl-2.1.php
 */
class ActiveResource {
	/**
	 * The REST site address, e.g., http://user:pass@domain:port/
	 */
	var $site = false;

	/**
	 * Add any extra params to the end of the url eg: API key
	 */
	var $extra_params = false;

	/**
	 * HTTP Basic Authentication user
	 */
	var $user = null;

	/**
	 * HTTP Basic Authentication password
	 */	
	var $password = null;
	
	/**
	 * The remote collection, e.g., person or things
	 */
	var $element_name = false;

	/**
	 * The data of the current object, accessed via the anonymous get/set methods.
	 */
	var $_data = array ();

	/**
	 * An error message if an error occurred.
	 */
	var $error = false;

	/**
	 * The error number if an error occurred.
	 */
	var $errno = false;

	/**
	 * The request that was sent to the server.
	 */
	var $request_body = '';

	/**
	 * The complete URL that the request was sent to.
	 */
	var $request_uri = '';

	/**
	 * The request method sent to the server.
	 */
	var $request_method = '';

	/**
	 * The response code returned from the server.
	 */
	var $response_code = false;

	/**
	 * The raw response headers sent from the server.
	 */
	var $response_headers = '';

	/**
	 * The response body sent from the server.
	 */
	var $response_body = '';

	/**
	 * The format requests should use to send data (url or xml).
	 */
	var $request_format = 'url';

	/**
	 * Corrections to improper pleuralizations.
	 */
	var $pleural_corrections = array (
		'persons' => 'people',
		'peoples' => 'people',
		'mans' => 'men',
		'mens' => 'men',
		'womans' => 'women',
		'womens' => 'women',
		'childs' => 'children',
		'childrens' => 'children',
		'sheeps' => 'sheep',
		'octopuses' => 'octopi',
		'quizs' => 'quizzes',
		'axises' => 'axes',
		'buffalos' => 'buffaloes',
		'tomatos' => 'tomatoes',
		'potatos' => 'potatoes',
		'oxes' => 'oxen',
		'mouses' => 'mice',
		'matrixes' => 'matrices',
		'vertexes' => 'vertices',
		'indexes' => 'indices',
	);

	/**
	 * Constructor method.
	 */
	function __construct ($data = array ()) {
		$this->_data = $data;
		// Allow class-defined element name or use class name if not defined
		$this->element_name = ($this->element_name ? $this->pluralize ($this->element_name) : $this->pluralize (strtolower (get_class ($this))));

		// Detect for namespaces, and take just the class name
		if (stripos($this->element_name, '\\'))
		{
			$classItems = explode('\\', $this->element_name);
			$this->element_name = end($classItems);
		}

		// if configuration file (config.ini.php) exists use it (overwrite class properties/attribute values).
		$config_file_path = dirname (__FILE__) . '/' . 'config.ini.php';
		if (file_exists ($config_file_path)) {
			$properties = parse_ini_file ($config_file_path);
			foreach ($properties as $property => $value )
				$this->{$property} = $value;
		}
	}

	/**
	 * Pluralize the element name.
	 */
	function pluralize ($word) {
		$word .= 's';
		$word = preg_replace ('/(x|ch|sh|ss])s$/', '\1es', $word);
		$word = preg_replace ('/ss$/', 'ses', $word);
		$word = preg_replace ('/([ti])ums$/', '\1a', $word);
		$word = preg_replace ('/sises$/', 'ses', $word);
		$word = preg_replace ('/([^aeiouy]|qu)ys$/', '\1ies', $word);
		$word = preg_replace ('/(?:([^f])fe|([lr])f)s$/', '\1\2ves', $word);
		if (isset ($this->pleural_corrections[$word])) {
			return $this->pleural_corrections[$word];
		}
		return $word;
	}

	/**
	 * For backwards-compatibility.
	 */
	function pleuralize ($word) {
		return $this->pluralize ($word);
	}

	/**
	 * Saves a new record or updates an existing one via:
	 *
	 * POST /collection.xml
	 * PUT  /collection/id.xml
	 */
	function save () {
		if (isset ($this->_data['id'])) {
			return $this->_send_and_receive ($this->site . $this->element_name . '/' . $this->_data['id'] . '.xml', 'PUT', $this->_data); // update
		}
		return $this->_send_and_receive ($this->site . $this->element_name . '.xml', 'POST', $this->_data); // create
	}

	/**
	 * Deletes a record via:
	 *
	 * DELETE /collection/id.xml
	 */
	function destroy () {
		return $this->_send_and_receive ($this->site . $this->element_name . '/' . $this->_data['id'] . '.xml', 'DELETE');
	}

	/**
	 * Finds a record or records via:
	 *
	 * GET /collection/id.xml
	 * GET /collection.xml
	 */
	function find ($id = false, $options = array ()) {
		if (! $id) {
			$id = $this->_data['id'];
		}
		if ($id == 'all') {
			$url = $this->site . $this->element_name . '.xml';
			if (count ($options) > 0) {
				$url .= '?' . http_build_query ($options);
			}
			return $this->_send_and_receive ($url, 'GET');
		}
		return $this->_send_and_receive ($this->site . $this->element_name . '/' . $id . '.xml', 'GET');
	}

	/**
	 * Gets a specified custom method on the current object via:
	 *
	 * GET /collection/id/method.xml
	 * GET /collection/id/method.xml?attr=value
	 */
	function get ($method, $options = array ()) {
		$req = $this->site . $this->element_name;
        if ($this->_data['id']) { 
          $req .= '/' . $this->_data['id'];
        }
        $req .= '/' . $method . '.xml';
		if (count ($options) > 0) {
			$req .= '?' . http_build_query ($options);
		}
		return $this->_send_and_receive ($req, 'GET');
	}

	/**
	 * Posts to a specified custom method on the current object via:
	 *
	 * POST /collection/id/method.xml
	 */
	function post ($method, $options = array ()) {
		$req = $this->site . $this->element_name;
        if ($this->_data['id']) {
          $req .= '/' . $this->_data['id'];
        }
        $req .= '/' . $method . '.xml';
		return $this->_send_and_receive ($req, 'POST', $options);
	}

	/**
	 * Puts to a specified custom method on the current object via:
	 *
	 * PUT /collection/id/method.xml
	 */
	function put ($method, $options = array ()) {
		$req = $this->site . $this->element_name;
        if ($this->_data['id']) { 
          $req .= '/' . $this->_data['id'];
        }
        $req .= '/' . $method . '.xml';
		if (count ($options) > 0) {
			$req .= '?' . http_build_query ($options);
		}
		return $this->_send_and_receive ($req, 'PUT');
	}

	/**
	 * Simple recursive function to build an XML response.
	 */
	function _build_xml ($k, $v) {
		if (is_object ($v) && strtolower (get_class ($v)) == 'simplexmlelement') {
			return preg_replace ('/<\?xml(.*?)\?>\n*/', '', $v->asXML ());
		}
		$res = '';
		$attrs = '';
		if (! is_numeric ($k)) {
			$res = '<' . $k . '{{attributes}}>';
		}
		if (is_array ($v)) {
			foreach ($v as $key => $value) {
				if (strpos ($key, '@') === 0) {
					$attrs .= ' ' . substr ($key, 1) . '="' . $this->_xml_entities ($value) . '"';
					continue;
				}
				$res .= $this->_build_xml ($key, $value);
				$keys = array_keys ($v);
				if (is_numeric ($key) && $key != array_pop ($keys)) {
					$res .= '</' . $k . ">\n<" . $k . '>';
				}
			}
		} else {
			$res .= $this->_xml_entities ($v);
		}
		if (! is_numeric ($k)) {
			$res .= '</' . $k . ">\n";
		}
		$res = str_replace ('<' . $k . '{{attributes}}>', '<' . $k . $attrs . '>', $res);
		return $res;
	}

	/**
	 * Converts entities to unicode entities (ie. < becomes &#60;).
	 * From php.net/htmlentities comments, user "webwurst at web dot de"
	 */
	function _xml_entities ($string) {
		$trans = get_html_translation_table (HTML_ENTITIES);
	
		foreach ($trans as $key => $value) {
			$trans[$key] = '&#' . ord ($key) . ';';
		}
	
		return strtr ($string, $trans);
	}

	/**
	 * Build the request, call _fetch() and parse the results.
	 */
	function _send_and_receive ($url, $method, $data = array ()) {
		$params = '';
		$el = substr ($this->element_name, 0, -1);
		if ($this->request_format == 'url') {
			foreach ($data as $k => $v) {
				if ($k != 'id' && $k != 'created-at' && $k != 'updated-at') {
					$params .= '&' . $el . '[' . str_replace ('-', '_', $k) . ']=' . rawurlencode ($v);
				}
			}
			$params = substr ($params, 1);
		} elseif ($this->request_format == 'xml') {
			$params = '<?xml version="1.0" encoding="UTF-8"?><' . $el . ">\n";
			foreach ($data as $k => $v) {
				if ($k != 'id' && $k != 'created-at' && $k != 'updated-at') {
					$params .= $this->_build_xml ($k, $v);
				}
			}
			$params .= '</' . $el . '>';
		}

		if ($this->extra_params !== false)
		{
			$url = $url . $this->extra_params;
		}

		$this->request_body = $params;
		$this->request_uri = $url;
		$this->request_method = $method;

		$res = $this->_fetch ($url, $method, $params);

		if ($res === false)
		{
			return $this;
		}

		// Keep splitting off any top headers until we get to the (XML) body:
		while (strpos($res, "HTTP/") === 0) {
			list ($headers, $res) = explode ("\r\n\r\n", $res, 2);
			$this->response_headers = $headers;
			$this->response_body = $res;
			if (preg_match ('/HTTP\/[0-9]\.[0-9] ([0-9]+)/', $headers, $regs)) {
				$this->response_code = $regs[1];
			} else {
				$this->response_code = false;
			}

			if (! $res) {
				return $this;
			} elseif ($res == ' ') {
				$this->error = 'Empty reply';
				return $this;
			}
		}

		// parse XML response
		$xml = new SimpleXMLElement ($res);

		if ($xml->getName () == $this->element_name) {
			// multiple
			$res = array ();
			$cls = get_class ($this);
			foreach ($xml->children () as $child) {
				$obj = new $cls;
				foreach ((array) $child as $k => $v) {
					$k = str_replace ('-', '_', $k);
					if (isset ($v['nil']) && $v['nil'] == 'true') {
						continue;
					} else {
						$obj->_data[$k] = $v;
					}
				}
				$res[] = $obj;
			}
			return $res;
		} elseif ($xml->getName () == 'errors') {
			// parse error message
			$this->error = $xml->error;
			$this->errno = $this->response_code;
			return false;
		}

		foreach ((array) $xml as $k => $v) {
			$k = str_replace ('-', '_', $k);
			if (isset ($v['nil']) && $v['nil'] == 'true') {
				continue;
			} else {
				$this->_data[$k] = $v;
			}
		}
		return $this;
	}

	/**
	 * Fetch the specified request via cURL.
	 */
	function _fetch ($url, $method, $params) {
		if (! extension_loaded ('curl')) {
			$this->error = 'cURL extension not loaded.';
			return false;
		}

		$ch = curl_init ();
		curl_setopt ($ch, CURLOPT_URL, $url);
		curl_setopt ($ch, CURLOPT_MAXREDIRS, 3);
		curl_setopt ($ch, CURLOPT_FOLLOWLOCATION, 0);
		curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt ($ch, CURLOPT_VERBOSE, 0);
		curl_setopt ($ch, CURLOPT_HEADER, 1);
		curl_setopt ($ch, CURLOPT_CONNECTTIMEOUT, 10);
		curl_setopt ($ch, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt ($ch, CURLOPT_SSL_VERIFYHOST, 0);

		/* HTTP Basic Authentication */
		if ($this->user && $this->password) {
			curl_setopt ($ch, CURLOPT_USERPWD, $this->user . ":" . $this->password);	
		}

		if ($this->request_format == 'xml') {
			curl_setopt ($ch, CURLOPT_HTTPHEADER, array ("Content-Type: text/xml", "Length: " . strlen ($params)));
		}
		switch ($method) {
			case 'POST':
				curl_setopt ($ch, CURLOPT_POST, 1);
				curl_setopt ($ch, CURLOPT_POSTFIELDS, $params);
				//curl_setopt ($ch, CURLOPT_HTTPHEADER, array ("Content-Type: application/x-www-form-urlencoded\n"));
				break;
			case 'DELETE':
				curl_setopt ($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
				break;
			case 'PUT':
				curl_setopt ($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
				curl_setopt ($ch, CURLOPT_POSTFIELDS, $params);
				//curl_setopt ($ch, CURLOPT_HTTPHEADER, array ("Content-Type: application/x-www-form-urlencoded\n"));
				break;
			case 'GET':
			default:
				break;
		}
		$res = curl_exec ($ch);

		// Check HTTP status code for denied access
		$http_code = curl_getinfo ($ch, CURLINFO_HTTP_CODE);
		if ($http_code == 401) {
			$this->errno = $http_code;
			$this->error = "HTTP Basic: Access denied.";
			curl_close ($ch);
			return false;
		}

		if (! $res) {
			$this->errno = curl_errno ($ch);
			$this->error = curl_error ($ch);
			curl_close ($ch);
			return false;
		}
		curl_close ($ch);
		return $res;
	}

	/**
	 * Getter for internal object data.
	 */
	function __get ($k) {
		if (isset ($this->_data[$k])) {
			return $this->_data[$k];
		}
		return $this->{$k};
	}

	/**
	 * Setter for internal object data.
	 */
	function __set ($k, $v) {
		if (isset ($this->_data[$k])) {
			$this->_data[$k] = $v;
			return;
		}
		$this->{$k} = $v;
	}

	/**
	 * Quick setter for chaining methods.
	 */
	function set ($k, $v = false) {
		if (! $v && is_array ($k)) {
			foreach ($k as $key => $value) {
				$this->_data[$key] = $value;
			}
		} else {
			$this->_data[$k] = $v;
		}
		return $this;
	}
}

?>