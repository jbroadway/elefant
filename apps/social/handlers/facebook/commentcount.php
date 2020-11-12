<?php

/**
 * Embeds a facebook comment count into the current page.
 *
 * In PHP code, call it like this:
 *
 *     echo $this->run ('social/facebook/commentcount');
 *
 * In a template, call it like this:
 *
 *     {! social/facebook/commentcount !}
 *
 * Parameters:
 *
 * - `url` - The URL to request a comment count for (optional).
 *
 * Also available in the dynamic objects menu as "Facebook: Comment Count".
 */

if (! isset (self::$called['social/facebook/init'])) {
	echo $this->run ('social/facebook/init');
}

if (strpos ($data['url'], '/') === 0) {
	$data['url'] = '//' . conf ('General', 'site_domain') . $data['url'];
}
echo $tpl->render ('social/facebook/commentcount', $data);
