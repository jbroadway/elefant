<?php

/**
 * Embeds a Google +1 button.
 *
 * In PHP code, call it like this:
 *
 *     echo $this->run ('social/google/plusone');
 *
 * In a template, call it like this:
 *
 *     {! social/google/plusone !}
 *
 * Parameters:
 *
 * - `url` - The URL to request a comment count for (optional).
 *
 * Also available in the dynamic objects menu as "Google: +1 Button".
 */

if (! isset (self::$called['social/google/init'])) {
	echo $this->run ('social/google/init');
}

if (strpos ($data['url'], '/') === 0) {
	$data['url'] = '//' . conf ('General', 'site_domain') . $data['url'];
}
echo $tpl->render ('social/google/plusone', $data);
