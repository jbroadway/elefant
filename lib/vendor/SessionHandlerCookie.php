<?php

/**
 * Session Handling Cookie Class
 *
 * Modified version of original class by Steve Corona to accept constructor of HASH information
 *
 * In addition to Vijay Mahrra's changes, this version also reads the additional cookie
 * parameters from session_get_cookie_params()
 *
 * <example>
 * $handler = new SessionHandlerCookie('SECRET');
 * session_set_save_handler($handler, true);
 * session_start();
 * </example>
 *
 * @author Steve Corona
 * @author Vijay Mahrra <vijay@yoyo.org>
 * @copyright (c) 2012, Steve Corona
 * @see https://www.scalingphpbook.com/
 * @see https://github.com/stevencorona/SessionHandlerCookie
 * @see https://github.com/vijinho/SessionHandlerCookie
 */
class SessionHandlerCookie implements SessionHandlerInterface {

    private $data = array();
    private $save_path = null;
    public $HASH_LEN;
    public $HASH_ALGO;
    public $HASH_SECRET;

    public function __construct($HASH_SECRET = 'YOUR_SECRET_STRING', $HASH_ALGO = 'sha512', $HASH_LEN = 128) {
        $this->HASH_SECRET = $HASH_SECRET;
        $this->HASH_ALGO = $HASH_ALGO;
        $this->HASH_LEN = $HASH_LEN;
    }

    public function getHashLength() {
        return $this->HASH_LEN;
    }

    public function getHashAlgorithm() {
        return $this->HASH_ALGO;
    }

    public function getSecret() {
        return $this->HASH_SECRET;
    }

    public function open($save_path, $name) {
        $this->save_path = $save_path;
        return true;
    }

    public function read($id) {

        // Check for the existance of a cookie with the name of the session id
        // Make sure that the cookie is atleast the size of our hash, otherwise it's invalid
        // Return an empty string if it's invalid.
        if (!isset($_COOKIE[$id]))
            return '';

        // We expect the cookie to be base64 encoded, so let's decode it and make sure
        // that the cookie, at a minimum, is longer than our expact hash length.
        $raw = gzuncompress(base64_decode($_COOKIE[$id]));
        if ($raw === false || strlen($raw) < $this->HASH_LEN)
            return '';

        // The cookie data contains the actual data w/ the hash concatonated to the end,
        // since the hash is a fixed length, we can extract the last HMAC_LENGTH chars
        // to get the hash.
        $hash = substr($raw, strlen($raw) - $this->HASH_LEN, $this->HASH_LEN);
        $data = substr($raw, 0, -($this->HASH_LEN));

        // Calculate what the hash should be, based on the data. If the data has not been
        // tampered with, $hash and $hash_calculated will be the same
        $hash_calculated = hash_hmac($this->HASH_ALGO, $data, $this->HASH_SECRET);

        // If we calculate a different hash, we can't trust the data. Return an empty string.
        if ($hash_calculated !== $hash)
            return '';

        // Return the data, now that it's been verified.
        return (string) $data;
    }

    public function write($id, $data) {

        // Calculate a hash for the data and append it to the end of the data string
        $hash = hash_hmac($this->HASH_ALGO, $data, $this->HASH_SECRET);
        $data .= $hash;

        // Set a cookie with the data
        $this->_setcookie($id, base64_encode(gzcompress($data, 9)));
    }

    public function destroy($id) {
        $this->_setcookie($id, '', time());
    }

    // In the context of cookies, these two methods are unneccessary, but must
    // be implemented as part of the SessionHandlerInterface.
    public function gc($maxlifetime) {

    }

    public function close() {

    }

	private function _setcookie($id, $data, $duration = null) {
		extract (session_get_cookie_params ());
		$duration = $duration ? $duration : time () + $lifetime;
		setcookie ($id, $data, $duration, $path, $domain, $secure, $httponly);
	}
}