<?php

/**
 * Embeds a facebook like-box into the current page.
 *
 * In PHP code, call it like this:
 *
 *     echo $this->run ('social/facebook/like-box');
 *
 * In a template, call it like this:
 *
 *     {! social/facebook/like-box !}
 *
 * Parameters:
 *
 * - `url` - The URL to request a comment count for (optional).
 *
 * Also available in the dynamic objects menu as "Facebook: Like-Box".
 */

if (! isset (self::$called['social/facebook/init'])) {
	echo $this->run ('social/facebook/init');
}

if (strpos ($data['url'], '/') === 0) {
	$data['url'] = '//' . Appconf::admin ('Site Settings', 'site_domain') . $data['url'];
}
echo $tpl->render ('social/facebook/like-box', $data);
