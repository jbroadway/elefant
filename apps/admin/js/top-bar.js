jQuery.add_notification = function (msg) {
	var notices = $.cookie ('elefant_notification');
	if (notices !== null) {
		msg = notices + '|' + msg;
	}
	$.cookie ('elefant_notification', msg, {path: '/'});
};

$(function () {
	$('body').append ('<div id="admin-bar"><div id="admin-links"></div><a href="/"><img src="/apps/admin/css/admin/elefant_logo.png" alt="Elefant CMS" /></a></div>');
	$.get ('/admin/head/links', function (res) {
		$('#admin-links').append (res);
		$('#admin-tools').hover (function () {
			$('#admin-tools-list').slideDown ('fast').show ();
			$(this).parent ().hover (
				function () {},
				function () {
					$('#admin-tools-list').slideUp ('slow');
				}
			);
		})
	});
	$('.admin-options a').hover (
		function () {
			this.tip = this.title;
			$(this).append (
				'<div class="admin-tooltip"><div class="admin-tooltip-top"></div>' +
				'<div class="admin-tooltip-body">' + this.tip + '</div></div>'
			);
			this.title = '';
			$('.admin-tooltip').fadeIn (100);
		},
		function () {
			$('.admin-tooltip').fadeOut (100);
			$('.admin-tooltip').remove ();
			this.title = this.tip;
		}
	);

	var jgrowl_interval = function () {
		var notice = $.cookie ('elefant_notification'),
			msglist = [],
			i = 0;

		$.cookie ('elefant_notification', null, {path: '/'});

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
