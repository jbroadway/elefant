<?php

/**
 * Embeds a Twitter Follow button into the current page.
 *
 * In PHP code, call it like this:
 *
 *     echo $this->run ('social/twitter/follow');
 *
 * In a template, call it like this:
 *
 *     {! social/twitter/follow !}
 *
 * Parameters:
 *
 * - `twitter_id` - The Twitter ID to follow (default = Twitter ID setting).
 *
 * Also available in the dynamic objects menu as "Twitter: Follow".
 */

if (! isset (self::$called['social/twitter/init'])) {
	echo $this->run ('social/twitter/init');
}

if (! isset ($data['twitter_id'])) {
	$id = Appconf::user ('Twitter', 'twitter_id');
	$data['twitter_id'] = (! empty ($id)) ? $id : $appconf['Twitter']['id'];
}

echo $tpl->render ('social/twitter/follow', $data);
