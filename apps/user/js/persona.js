$(function () {
	$('#persona-login').click (function (e) {
		e.preventDefault ();
		navigator.id.request ();
	});

	$('#persona-logout').click (function (e) {
		if (persona_active) {
			e.preventDefault ();
			navigator.id.logout ();
		}
	});
	
	navigator.id.watch ({
		loggedInUser: null,
		onlogin: function (assertion) {
			$.post (
				'/user/login/persona?redirect=' + persona_redirect,
				{assertion: assertion},
				function (msg) {
					persona_active = 1;
					if (! persona_status && msg.redirect && window.location.pathname !== '/user/login/newpersona') {
						window.location.replace (msg.redirect);
					}
				}
			);
		},
		onlogout: function () {
			window.location.replace ($('#persona-logout').attr ('href'));
		}
	});
});