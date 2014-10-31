<?php

/**
 * Initializes the google API for the other google handlers.
 */

if (self::$called['social/google/init'] > 1) {
	return;
}

$page->tail .= $tpl->render ('social/google/init');
