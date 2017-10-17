<?php

/**
 * Embeds text with a "Tweet This" link to encourage social media sharing.
 *
 * In PHP code, call it like this:
 *
 *     echo $this->run ('social/twitter/tweetthis');
 *
 * In a template, call it like this:
 *
 *     {! social/twitter/tweetthis !}
 *
 * Parameters:
 *
 * - `display` - The text to display.
 * - `tweet` - The text to tweet.
 * - `url` - The URL to link to.
 *
 * Also available in the dynamic objects menu as "Twitter: Tweet This".
 */

$data['url'] = ($data['url'] !== '')
	? $data['url']
	: ($this->is_https () ? 'https' : 'http') . '://'. Appconf::admin ('Site Settings', 'site_domain') . $_SERVER['REQUEST_URI'];

echo $tpl->render ('social/twitter/tweetthis', $data);
