<?php

/**
 * Initializes the facebook API for the other facebook handlers.
 */

if (self::$called['social/facebook/init'] > 1) {
	return;
}

echo $tpl->render ('social/facebook/init');
