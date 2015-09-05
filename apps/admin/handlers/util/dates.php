<?php

/**
 * Helps convert dates to show in the current timezone.
 * Loads and initializes the jQuery localize plugin.
 *
 * Usage:
 *
 * ### 1. Load this handler either in your handler:
 *
 *     $this->run ('admin/util/dates');
 * 
 * Or anywhere in your view:
 *
 *     {! admin/util/dates !}
 *
 * ### 2. Filter your dates via:
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
 *
 * See the [[I18n]] class for a list of available filter methods.
 */

$abbr_months = explode (
	' ',
	__ ('Jan Feb Mar Apr May Jun Jul Aug Sep Oct Nov Dec')
);

$full_months = explode (
	' ',
	__ ('January February March April May June July August September October November December')
);

$abbr_days = explode (
	' ',
	__ ('Sun Mon Tue Wed Thu Fri Sat')
);

$full_days = explode (
	' ',
	__ ('Sunday Monday Tuesday Wednesday Thursday Friday Saturday')
);

$page->add_script ('/js/jquery.localize.min.js');
$page->add_script ('<script>
$(function () {
	$.localize_dates = function () {
		$.localize.fullMonths = ' . json_encode ($full_months) . ';
		$.localize.abbrMonths = ' . json_encode ($abbr_months) . ';
		$.localize.abbrDays = ' . json_encode ($abbr_days) . ';
		$.localize.fullDays = ' . json_encode ($full_days) . ';
		$(\'time.datetime\').localize(\'' . $i18n->date_format . ' - ' . $i18n->time_format . '\');
		$(\'time.shortdatetime\').localize(\'' . $i18n->short_format . ' - ' . $i18n->time_format . '\');
		$(\'time.date\').localize(\'' . $i18n->date_format . '\');
		$(\'time.shortdate\').localize(\'' . $i18n->short_format . '\');
		$(\'time.shortdate\').localize(\'' . $i18n->short_format . ', ' . $i18n->year_format . '\');
		$(\'time.time\').localize(\'' . $i18n->time_format . '\');
		$(\'time.shortdaydate\').localize(\'' . $i18n->short_day_date_format . '\');
		$(\'time.shortdaydatetime\').localize(\'' . $i18n->short_day_date_format . ' - ' . $i18n->time_format . '\');
		$(\'time.daydate\').localize(\'' . $i18n->day_date_format . '\');
		$(\'time.daydatetime\').localize(\'' . $i18n->day_date_format . ' - ' . $i18n->time_format . '\');
	};
	$.localize_dates ();
});
</script>');
