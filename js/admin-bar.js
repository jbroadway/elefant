$(function () {
	$('body').append ('<div id="admin-bar"><div id="admin-links"></div><span><em>Elefant</em>Admin</span></div>');
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
});
