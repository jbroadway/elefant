<?php

/**
 * Initializes the twitter API for the other twitter handlers.
 */

if (self::$called['social/twitter/init'] > 1) {
	return;
}

echo $tpl->render ('social/twitter/init');
