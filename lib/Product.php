<?php

/**
 * This class parses and returns values from `conf/product.php` which makes it easy
 * to rebrand the CMS interface with your own name, logo, website link,
 * and stylesheet.
 *
 * Usage:
 *
 *     {{ Product::name() }}
 */
class Product {
    /**
     * Returns the parsed INI data from `conf/product.php`.
     */
	static function get_info () {
		if (! isset ($GLOBALS['elefant_product_info'])) {
			$GLOBALS['elefant_product_info'] = parse_ini_file ('conf/product.php');
		}
		return $GLOBALS['elefant_product_info'];
	}

	/**
	 * Returns the `name` value.
	 */
	static function name () {
		$info = Product::get_info ();
		return $info['name'];
	}

	/**
	 * Returns the `website` value.
	 */
	static function website () {
		$info = Product::get_info ();
		return $info['website'];
	}

	/**
	 * Returns the `logo_login` value.
	 */
	static function logo_login () {
		$info = Product::get_info ();
		return $info['logo_login'];
	}

	/**
	 * Returns the `logo_toolbar` value.
	 */
	static function logo_toolbar () {
		$info = Product::get_info ();
		return $info['logo_toolbar'];
	}

	/**
	 * Returns the `stylesheet` value.
	 */
	static function stylesheet () {
		$info = Product::get_info ();
		return $info['stylesheet'];
	}
}

?>