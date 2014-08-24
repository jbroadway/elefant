/**
 * Used by the user/util/userchooser handler to provide a
 * user add form for adding users directly from the user
 * chooser.
 */
;(function () {
	var self = {};

	// Current list of options
	self.opts = {};

	// The HTML for the add form
	self.html = '';

	// Initialized
	self.initialized = false;

	self.init = function () {
		$.get (
			'/user/chooser/addform',
			function (res) {
				self.html = res;
				self.initialized = true;
			}
		);
	};

	self.add_user = function () {
		$.post (
			'/user/chooser/adduser',
			$(this).serializeArray (),
			function (res) {
				if (! res.success) {
					$('#adduser-error').html (res.error).show ();
					$('#adduser-form').parent ('div').scrollTop (0);
					return;
				}

				if (self.opts.callback) {
					self.opts.callback (res.data.id, res.data.name, res.data.email);
				}
				$.close_dialog ();
			}
		);
		return false;
	};

	$.add_user = function (opts) {
		var defaults = {
			callback: null,
			title: $.i18n ('Add Member')
		}

		self.opts = $.extend (defaults, opts);

		if (! self.initialized) {
			alert ($.i18n ('Unable to load the add member form. Please try again in a few seconds.'));
			return;
		}

		$.open_dialog (self.opts.title, self.html, {height: 325});

		$('#adduser-form').submit (self.add_user);
		$('#adduser-cancel').click (function () {
			$.close_dialog ();
			return false;
		});
	};

	self.init ();
})(jQuery);