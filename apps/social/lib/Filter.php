<?php

namespace social;

/**
 * Filters for the social app.
 */
class Filter {
	/**
	 * Filter a tweet for links, @names and #hashtags.
	 */
	public static function tweet ($tweet) {
		$tweet = preg_replace (
			'/(https?:\/\/[a-zA-Z0-9\.\/_-]+)/',
			'<a href="\1">\1</a>',
			$tweet
		);

		$tweet = preg_replace (
			'/@([a-zA-Z0-9_]+)/',
			'<a href="https://twitter.com/\1">@\1</a>',
			$tweet
		);

		$tweet = preg_replace (
			'/#([a-zA-Z0-9_]+)/',
			'<a href="https://twitter.com/search?q=%23\1&src=hash">#\1</a>',
			$tweet
		);

		return $tweet;
	}
}

?>