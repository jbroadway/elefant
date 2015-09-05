<?php

/**
 * If a Google Analytics ID is set in the site settings, this will
 * return the Google Analytics code for your site. You can use it in
 * your layout templates just before the `</body>` tag like this:
 *
 *     {! admin/util/analytics !}
 *
 * To set your Google Analytics ID, visit the Site Settings
 * link in the admin toolbar.
 */

$analytics_id = Appconf::admin ('Site Settings', 'google_analytics_id');
if ($analytics_id) {
	echo $tpl->render (
		'admin/util/analytics',
		array (
			'analytics_id' => $analytics_id
		)
	);
}
