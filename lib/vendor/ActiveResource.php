<?php

/**
 * Basic implementation of the Ruby on Rails ActiveResource REST client.
 * Intended to work with RoR-based REST servers, which all share similar
 * API patterns.
 *
 * Usage:
 *
 *     <?php
 *     
 *     require_once ('ActiveResource.php');
 *     
 *     class Song extends ActiveResource {
 *         var $site = 'http://localhost:3000/';
 *         var $element_name = 'songs';
 *     }
 *     
 *     // create new item
 *     $song = new Song (array ('artist' => 'Joe Cocker', 'title' => 'A Little Help From My Friends'));
 *     $song->save ();
 *     
 *     // fetch and update an item
 *     $song->find (44)->set ('title', 'The River')->save ();
 *     
 *     // line by line
 *     $song->find (44);
 *     $song->title = 'The River';
 *     $song->save ();
 *     
 *     // get all songs
 *     $songs = $song->find ('all');
 *     
 *     // delete a song
 *     $song->find (44);
 *     $song->destroy ();
 *     
 *     // custom method
 *     $songs = $song->get ('by_year', array ('year' => 1999));
 *     
 *     ?>
 *
 * @author John Luxford <lux@companymachine.com>
 * @version 0.14 beta
 * @license http://opensource.org/licenses/lgpl-2.1.php
 */
class ActiveResource {
	/**
	 * The REST site address, e.g., `http://user:pass@domain:port/`
	 */
	public $site = false;

	/**
	 * Add any extra params to the end of the url eg: API key
	 */
	public $extra_params = false;

	/**
	 * HTTP Basic Authentication user
	 */
	public $user = null;

	/**
	 * HTTP Basic Authentication password
	 */	
	public $password = null;
	
	/**
	 * The remote collection, e.g., person or thing
	 */
	public $element_name = false;

	/**
	 * Pleural form of the element name, e.g., people or things
	 */
	public $element_name_plural = '';

	/**
	 * The data of the current object, accessed via the anonymous get/set methods.
	 */
	private $_data = array ();

	/**
	 * An error message if an error occurred.
	 */
	public $error = false;

	/**
	 * The error number if an error occurred.
	 */
	public $errno = false;

	/**
	 * The request that was sent to the server.
	 */
	public $request_body = '';

	/**
	 * The complete URL that the request was sent to.
	 */
	public $request_uri = '';

	/**
	 * The request method sent to the server.
	 */
	public $request_method = '';

	/**
	 * The response code returned from the server.
	 */
	public $response_code = false;

	/**
	 * The raw response headers sent from the server.
	 */
	public $response_headers = '';

	/**
	 * The response body sent from the server.
	 */
	public $response_body = '';

	/**
	 * The format requests should use to send data (url or xml).
	 */
	public $request_format = 'url';

	/**
	 * Corrections to improper pleuralizations.
	 */
	public $pleural_corrections = array (
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
	public function __construct ($data = array ()) {
		$this->_data = $data;
		// Allow class-defined element name or use class name if not defined
		$this->element_name = $this->element_name ? $this->element_name : strtolower (get_class ($this));

		// Detect for namespaces, and take just the class name
		if (stripos ($this->element_name, '\\')) {
			$classItems = explode ('\\', $this->element_name);
			$this->element_name = end ($classItems);
		}

		// Get the plural name after removing namespaces
		$this->element_name_plural = $this->pluralize ($this->element_name);

		// If configuration file (`config.ini.php`) exists use it (overwrite class properties/attribute values).
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
	public function pluralize ($word) {
		$word .= 's';
		$word = preg_replace ('/(x|ch|sh|ss])s$/', '\1es', $word);
		$word = preg_replace ('/ss$/', 'ses', $word);
		$word = preg_replace ('/([ti])ums$/', '\1a', $word);
		$word = preg_replace ('/sises$/', 'ses', $word);
		$word = preg_replace ('/([^aeiouy]|qu)ys$/', '\1ies', $word);
		$word = preg_replace ('/(?:([^f])fe|([lr])f)s$/', '\1\2ves', $word);
		$word = preg_replace ('/ieses$/', 'ies', $word);
		if (isset ($this->pleural_corrections[$word])) {
			return $this->pleural_corrections[$word];
		}
		return $word;
	}

	/**
	 * For backwards-compatibility.
	 */
	public function pleuralize ($word) {
		return $this->pluralize ($word);
	}

	/**
	 * Saves a new record or updates an existing one via:
	 *
	 *     POST /collection.xml
	 *     PUT  /collection/id.xml
	 */
	public function save () {
		if (isset ($this->_data['id'])) {
			return $this->_send_and_receive ($this->site . $this->element_name_plural . '/' . $this->_data['id'] . '.xml', 'PUT', $this->_data); // update
		}
		return $this->_send_and_receive ($this->site . $this->element_name_plural . '.xml', 'POST', $this->_data); // Create
	}

	/**
	 * Deletes a record via:
	 *
	 *     DELETE /collection/id.xml
	 */
	public function destroy () {
		return $this->_send_and_receive ($this->site . $this->element_name_plural . '/' . $this->_data['id'] . '.xml', 'DELETE');
	}

	/**
	 * Finds a record or records via:
	 *
	 *     GET /collection/id.xml
	 *     GET /collection.xml
	 */
	public function find ($id = false, $options = array ()) {
		if (! $id) {
			$id = $this->_data['id'];
		}
		if ($id === 'all') {
			$url = $this->site . $this->element_name_plural . '.xml';
			if (count ($options) > 0) {
				$url .= '?' . http_build_query ($options);
			}
			return $this->_send_and_receive ($url, 'GET');
		}
		return $this->_send_and_receive ($this->site . $this->element_name_plural . '/' . $id . '.xml', 'GET');
	}

	/**
	 * Gets a specified custom method on the current object via:
	 *
	 *     GET /collection/id/method.xml
	 *     GET /collection/id/method.xml?attr=value
	 */
	public function get ($method, $options = array ()) {
		$req = $this->site . $this->element_name_plural;
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
	 *     POST /collection/id/method.xml
	 */
	public function post ($method, $options = array ()) {
		$req = $this->site . $this->element_name_plural;
        if ($this->_data['id']) {
          $req .= '/' . $this->_data['id'];
        }
        $req .= '/' . $method . '.xml';
		return $this->_send_and_receive ($req, 'POST', $options);
	}

	/**
	 * Puts to a specified custom method on the current object via:
	 *
	 *     PUT /collection/id/method.xml
	 */
	public function put ($method, $options = array ()) {
		$req = $this->site . $this->element_name_plural;
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
	public function _build_xml ($k, $v) {
		if (is_object ($v) && strtolower (get_class ($v)) === 'simplexmlelement') {
			return preg_replace ('/<\?xml(.*?)\?>\n*/', '', $v->asXML ());
		}
		$res = '';
		$attrs = '';
		if (! is_numeric ($k)) {
			$res = '<' . $k . '{{attributes}}>';
		}
		if (is_object ($v)) {
			$v = (array) $v;
		}
		if (is_array ($v)) {
			foreach ($v as $key => $value) {
				// handle attributes of repeating tags
				if (is_numeric ($key) && is_array ($value)) {
					foreach ($value as $sub_key => $sub_value) {
						if (strpos ($sub_key, '@') === 0) {
							$attrs .= ' ' . substr ($sub_key, 1) . '="' . $this->_xml_entities ($sub_value) . '"';
							unset ($value[$sub_key]);
							continue;
						}
					}
				}

				if (strpos ($key, '@') === 0) {
					$attrs .= ' ' . substr ($key, 1) . '="' . $this->_xml_entities ($value) . '"';
					continue;
				}
				$res .= $this->_build_xml ($key, $value);
				$keys = array_keys ($v);
				if (is_numeric ($key) && $key !== array_pop ($keys)) {
					// reset attributes on repeating tags
					if (is_array ($value)) {
						$res = str_replace ('<' . $k . '{{attributes}}>', '<' . $k . $attrs . '>', $res);
						$attrs = '';
					}
					$res .= '</' . $k . ">\n<" . $k . '{{attributes}}>';
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
	 * Returns the unicode value of the string
	 *
	 * @param string $c The source string
	 * @param integer $i The index to get the char from (passed by reference for use in a loop)
	 * @return integer The value of the char at $c[$i]
	 * @author kerry at shetline dot com
	 * @author Dom Hastings - modified to suit my needs
	 * @see http://www.php.net/manual/en/function.ord.php#78032
	 */
	private function _unicode_ord(&$c, &$i = 0) {
		// get the character length
		$l = strlen($c);
		// copy the offset
		$index = $i;
		
		// check it's a valid offset
		if ($index >= $l) {
			return false;
		}
		
		// check the value
		$o = ord($c[$index]);
		
		// if it's ascii
		if ($o <= 0x7F) {
			return $o;
		
		// not sure what it is...
		} elseif ($o < 0xC2) {
			return false;
		
		// if it's a two-byte character	
		} elseif ($o <= 0xDF && $index < $l - 1) {
			$i += 1;
			return ($o & 0x1F) <<	6 | (ord($c[$index + 1]) & 0x3F);
		
		// three-byte
		} elseif ($o <= 0xEF && $index < $l - 2) {
			$i += 2;
			return ($o & 0x0F) << 12 | (ord($c[$index + 1]) & 0x3F) << 6 | (ord($c[$index + 2]) & 0x3F);
			
		// four-byte
		} elseif ($o <= 0xF4 && $index < $l - 3) {
			$i += 3;
			return ($o & 0x0F) << 18 | (ord($c[$index + 1]) & 0x3F) << 12 | (ord($c[$index + 2]) & 0x3F) << 6 | (ord($c[$index + 3]) & 0x3F);
			
		// not sure what it is...
		} else {
			return false;
		}
	}

	/**
	 * Makes the specified string XML-safe
	 *
	 * @param string $s
	 * @param boolean $hex Whether or not to make hexadecimal entities (as opposed to decimal)
	 * @return string The XML-safe result
	 * @author Dom Hastings
	 * @see http://www.w3.org/TR/REC-xml/#sec-predefined-ent
	 */
	public function _xml_entities ($s, $hex = true) {
		// if the string is empty
		if (empty($s)) {
			// just return it
			return $s;
		}
		
		// create the return string
		$r = '';
		// get the length
		$l = strlen($s);
		
		// iterate the string
		for ($i = 0; $i < $l; $i++) {
			// get the value of the character
			$o = $this->_unicode_ord($s, $i);
			
			// valid cahracters
			$v = (
				// \t \n <vertical tab> <form feed> \r
				($o >= 9 && $o <= 13) || 
				// <space> !
				($o === 32) || ($o === 33) || 
				// # $ %
				($o >= 35 && $o <= 37) || 
				// ( ) * + , - . /
				($o >= 40 && $o <= 47) || 
				// numbers
				($o >= 48 && $o <= 57) ||
				// : ;
				($o === 58) || ($o === 59) ||
				// = ?
				($o === 61) || ($o === 63) ||
				// @
				($o === 64) ||
				// uppercase
				($o >= 65 && $o <= 90) ||
				// [ \ ] ^ _ `
				($o >= 91 && $o <= 96) || 
				// lowercase
				($o >= 97 && $o <= 122) || 
				// { | } ~
				($o >= 123 && $o <= 126)
			);
			
			// if it's valid, just keep it
			if ($v) {
				$r .= $s[$i];
			
			// &
			} elseif ($o === 38) {
				$r .= '&amp;';
			
			// <
			} elseif ($o === 60) {
				$r .= '&lt;';
			
			// >
			} elseif ($o === 62) {
				$r .= '&gt;';
			
			// '
			} elseif ($o === 39) {
				$r .= '&apos;';
			
			// "
			} elseif ($o === 34) {
				$r .= '&quot;';
			
			// unknown, add it as a reference
			} elseif ($o > 0) {
				if ($hex) {
					$r .= '&#x'.strtoupper(dechex($o)).';';
					
				} else {
					$r .= '&#'.$o.';';
				}
			}
		}
		
		return $r;
	}

	/**
	 * Build the request, call `_fetch()` and parse the results.
	 */
	private function _send_and_receive ($url, $method, $data = array ()) {
		$params = '';
		$el = $this->element_name; // Singular this time
		if ($this->request_format === 'url') {
			foreach ($data as $k => $v) {
				if ($k !== 'id' && $k !== 'created-at' && $k !== 'updated-at') {
					$params .= '&' . $el . '[' . str_replace ('-', '_', $k) . ']=' . rawurlencode ($v);
				}
			}
			$params = substr ($params, 1);
		} elseif ($this->request_format === 'xml') {
			$params = '<?xml version="1.0" encoding="UTF-8"?><' . $el . ">\n";
			foreach ($data as $k => $v) {
				if ($k !== 'id' && $k !== 'created-at' && $k !== 'updated-at') {
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
			} elseif ($res === ' ') {
				$this->error = 'Empty reply';
				return $this;
			}
		}

		// Parse XML response
		$xml = new SimpleXMLElement ($res);

		// Normalize xml element name in case rails ressource contains an underscore
		if (str_replace ('-', '_', $xml->getName ()) === $this->element_name_plural) {
			// Multiple
			$res = array ();
			$cls = get_class ($this);
			foreach ($xml->children () as $child) {
				$obj = new $cls;
				foreach ((array) $child as $k => $v) {
					$k = str_replace ('-', '_', $k);
					if (isset ($v['nil']) && $v['nil'] === 'true') {
						continue;
					} else {
						$obj->_data[$k] = $v;
					}
				}
				$res[] = $obj;
			}
			return $res;
		} elseif ($xml->getName () === 'errors') {
			// Parse error message
			$this->error = $xml->error;
			$this->errno = $this->response_code;
			return false;
		}

		foreach ((array) $xml as $k => $v) {
			$k = str_replace ('-', '_', $k);
			if (isset ($v['nil']) && $v['nil'] === 'true') {
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
	private function _fetch ($url, $method, $params) {
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

		// HTTP Basic Authentication
		if ($this->user && $this->password) {
			curl_setopt ($ch, CURLOPT_USERPWD, $this->user . ":" . $this->password);	
		}

		if ($this->request_format === 'xml') {
			curl_setopt ($ch, CURLOPT_HTTPHEADER, array ("Expect:", "Content-Type: text/xml", "Length: " . strlen ($params)));
		}
		switch ($method) {
			case 'POST':
				curl_setopt ($ch, CURLOPT_POST, 1);
				curl_setopt ($ch, CURLOPT_POSTFIELDS, $params);
				break;
			case 'DELETE':
				curl_setopt ($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
				break;
			case 'PUT':
				curl_setopt ($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
				curl_setopt ($ch, CURLOPT_POSTFIELDS, $params);
				break;
			case 'GET':
			default:
				break;
		}
		$res = curl_exec ($ch);

		// Check HTTP status code for denied access
		$http_code = curl_getinfo ($ch, CURLINFO_HTTP_CODE);
		if ($http_code === 401) {
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
	public function __get ($k) {
		if (isset ($this->_data[$k])) {
			return $this->_data[$k];
		}
		return $this->{$k};
	}

	/**
	 * Setter for internal object data.
	 */
	public function __set ($k, $v) {
		if (isset ($this->_data[$k])) {
			$this->_data[$k] = $v;
			return;
		}
		$this->{$k} = $v;
	}

	/**
	 * Quick setter for chaining methods.
	 */
	public function set ($k, $v = false) {
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