<?php

namespace user;

/**
 * Template filters for the user app.
 */
class Filter {
	public static function resource_id ($resource) {
		return str_replace ('/', '-', $resource);
	}
}
