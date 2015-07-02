<?php

/**
 * Embeds a Google +1 button.
 */

if (! isset (self::$called['social/google/init'])) {
	echo $this->run ('social/google/init');
}

if (strpos ($data['url'], '/') === 0) {
	$data['url'] = '//' . $_SERVER['HTTP_HOST'] . $data['url'];
}
echo $tpl->render ('social/google/plusone', $data);
