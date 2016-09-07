<?php

/**
 * Embeds facebook comments into the current page.
 *
 * In PHP code, call it like this:
 *
 *     echo $this->run ('social/facebook/comments');
 *
 * In a template, call it like this:
 *
 *     {! social/facebook/comments !}
 *
 * Parameters:
 *
 * - `url` - The URL to pull comments for (optional).
 *
 * Also available in the dynamic objects menu as "Facebook: Comments".
 */

if (! isset (self::$called['social/facebook/init'])) {
	echo $this->run ('social/facebook/init');
}

$data['url'] = isset ($data['url'])
	? $data['url']
	: '//' . Appconf::admin ('Site Settings', 'site_domain') . filter_var ($_SERVER['REQUEST_URI'], FILTER_SANITIZE_URL);

echo $tpl->render ('social/facebook/comments', $data);
