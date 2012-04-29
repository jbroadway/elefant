<?php

/**
 * Helps convert dates to show in the current timezone.
 * Loads and initializes the jQuery localize plugin.
 *
 * Usage:
 *
 * 1. Load this handler either in your handler:
 *
 *     $this->run ('admin/util/dates');
 * 
 * Or anywhere in your view:
 *
 *     {! admin/util/dates !}
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

$abbr_months = explode (
	' ',
	i18n_get ('Jan Feb Mar Apr May Jun Jul Aug Sep Oct Nov Dec')
);

$full_months = explode (
	' ',
	i18n_get ('January February March April May June July August September October November December')
);

$page->add_script ('/js/jquery.localize.min.js');
$page->add_script ('<script>
$(function () {
	$.localize_dates = function () {
		$.localize.fullMonths = ' . json_encode ($full_months) . ';
		$.localize.abbrMonths = ' . json_encode ($abbr_months) . ';
		$(\'time.datetime\').localize(\'mmmm d, yyyy - h:MMa\');
		$(\'time.shortdatetime\').localize(\'mmm d - h:MMa\');
		$(\'time.date\').localize(\'mmmm d, yyyy\');
		$(\'time.shortdate\').localize(\'mmm d\');
		$(\'time.time\').localize(\'h:MMa\');
	};
	$.localize_dates ();
});
</script>');

?>