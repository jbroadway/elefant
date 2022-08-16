<?php

/**
 * Embeds a Twitter Tweet This button into the current page.
 *
 * In PHP code, call it like this:
 *
 *     echo $this->run ('social/twitter/tweet');
 *
 * In a template, call it like this:
 *
 *     {! social/twitter/tweet !}
 *
 * Parameters:
 *
 * - `twitter_id` - The Twitter ID to mention (default = Twitter ID setting).
 *
 * Also available in the dynamic objects menu as "Twitter: Share".
 */

if (! isset (self::$called['social/twitter/init'])) {
	echo $this->run ('social/twitter/init');
}

if (! isset ($data['via']) || empty ($data['via'])) {
	$id = Appconf::user ('Twitter', 'twitter_id');
	$data['via'] = (! empty ($id)) ? $id : Appconf::social ('Twitter', 'id');
}

if (isset ($data['url']) && strpos ($data['url'], '/') === 0) {
	$data['url'] = '//' . conf ('General', 'site_domain') . $data['url'];
}
echo $tpl->render ('social/twitter/tweet', $data);
