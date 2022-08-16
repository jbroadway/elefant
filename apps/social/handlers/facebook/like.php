<?php

/**
 * Embeds a facebook Like button into the current page.
 *
 * In PHP code, call it like this:
 *
 *     echo $this->run ('social/facebook/like');
 *
 * In a template, call it like this:
 *
 *     {! social/facebook/like !}
 *
 * Parameters:
 *
 * - `url` - The URL to request a comment count for (optional).
 *
 * Also available in the dynamic objects menu as "Facebook: Like Button".
 */

if (! isset (self::$called['social/facebook/init'])) {
	echo $this->run ('social/facebook/init');
}

if (isset ($data['url']) && strpos ($data['url'], '/') === 0) {
	$data['url'] = '//' . conf ('General', 'site_domain') . $data['url'];
}
echo $tpl->render ('social/facebook/like', $data);
