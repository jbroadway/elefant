/**
 * Provides a general-purpose user notifier based on the jQuery
 * cookie and jGrowl plugins. See `apps/admin/handlers/util/notifier.php`
 * for more information.
 */

jQuery.add_notice = function (msg) {
	var notices = $.cookie ('notifier_notices');
	if (notices !== null) {
		msg = notices + '|' + msg;
	}
	$.cookie ('notifier_notices', msg, {path: '/'});
};

$(function () {
	var jgrowl_interval = function () {
		var notice = $.cookie ('notifier_notices'),
			msglist = [],
			i = 0;

		$.cookie ('notifier_notices', null, {path: '/'});

		if (notice !== null) {
			msglist = notice.split ('|');
			for (i = 0; i < msglist.length; i++) {
				if (msglist[i].length > 0) {
					$.jGrowl (msglist[i].replace (/\+/g, ' '));
				}
			}
		}
		// clear notices
		setTimeout (jgrowl_interval, 1000);
	}

	jgrowl_interval ();
});
