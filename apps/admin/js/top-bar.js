$(function () {
	$('body').append ('<div id="admin-bar"><div id="admin-links"></div><a href="/"><span><em>Elefant</em>Admin</span></a></div>');
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
});
