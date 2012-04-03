<?php

/**
 * Helps convert dates to show in the current timezone.
 * Loads and initializes the jQuery localize plugin.
 *
 * Usage:
 *
 * 1. Load this handler either in your handler:
 *
 *     $this->run ('admin/dates');
 * 
 * Or anywhere in your view:
 *
 *     {! admin/dates !}
 *
 * 2. Filter your dates via:
 *
 *     {{ date_value|I18n::date }}
 *     {{ date_value|I18n::time }}
 *     {{ date_value|I18n::date_time }}
 *
 * These will display dates in the following forms:
 *
 *     January 3, 2012
 *     5:30PM
 *     April 16, 2012 - 11:13AM
 */

$page->add_script ('/js/jquery.localize.min.js');
$page->add_script ('<script>
$(function () {
	$.localize_dates = function () {
		$(\'time.datetime\').localize(\'mmmm d, yyyy - h:MMa\');
		$(\'time.date\').localize(\'mmmm d, yyyy\');
		$(\'time.time\').localize(\'h:MMa\');
	};
	$.localize_dates ();
});
</script>');

?>