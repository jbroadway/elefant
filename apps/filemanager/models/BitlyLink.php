<?php

namespace filemanager;

use Appconf;
use Bitly;

/**
 * For caching Bitly links to minimize API usage.
 */
class BitlyLink extends \Model {
	public $table = '#prefix#filemanager_bitly_link';
	public $key = 'link';
	
	public static function lookup ($link) {
		$cached = self::cached ($link);
		
		if ($cached) {
			return $cached;
		}
		
		$bitly = new Bitly ();
		$bitly_link = $bitly->shorten ($link);

		if ($bitly_link) {
		$obj = new BitlyLink ([
				'link' => $link,
				'bitly_link' => $bitly_link
			]);
			$obj->put ();
		}
		
		return $bitly_link;
	}
	
	public static function cached ($link) {
		return self::field ($link, 'bitly_link');
	}
}
