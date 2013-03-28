/**
 * Use this jQuery plugin to save a form in the background using an
 * AJAX request to the server.
 *
 * Usage:
 *
 * 1. Include the script and prerequisites:
 *
 *   <script src="http://code.jquery.com/jquery-1.5.2.min.js"></script>
 *   <script src="/js/jquery.bgsave.js"></script>
 *
 * 2. Initialize the form and give it the server-side handler url and
 * unique ID for the item being saved:
 *
 *   <script>
 *     $(function () {
 *       $('form').bgsave ({url: '/myapp/bgsave?id=', id: 5});
 *     });
 *   </script>
 *
 * 3. Link it to elements in the page:
 *
 *   <input type="submit" id="bgsave" value="Save and keep editing" />
 *   <span id="bgsave-status"></span>
 */
(function ($) {
	$.fn.extend ({
		bgsave: function (options) {
			var defaults = {
				url: '/appname/bgsave?id=',
				id: null,
				form: null,
				button: 'bgsave',
				status: 'bgsave-status',
				msg_saving: 'Saving...',
				msg_saved: 'Saved!',
				msg_failed: 'Failed to save...'
			};
			
			var options = $.extend (defaults, options);
			options.form = this[0];
			
			$('#' + options.button).click (function (evt) {
				evt.preventDefault ();

				var i = 0,
					opts = options,
					params = {};
				
				for (i = 0; i < opts.form.elements.length; i++) {
					if (! opts.form.elements[i].name) {
						// Unnamed fields can be ignored (submit buttons)
						continue;
					}
					if (opts.form.elements[i].getAttribute ('id') == 'code-body') {
						// Get the contents from codemirror editor
						params[opts.form.elements[i].name] = _codemirror.getValue ();
					} else if ($(opts.form.elements[i]).is (':radio') || $(opts.form.elements[i]).is (':checkbox')) {
						if (opts.form.elements[i].checked) {
							params[opts.form.elements[i].name] = true;
						} else {
							params[opts.form.elements[i].name] = false;
						}
					} else {
						// Get the contents from the field itself
						params[opts.form.elements[i].name] = opts.form.elements[i].value;
					}
				}
				
				$('#' + opts.status).html (opts.msg_saving).show ();

				$.post (opts.url + opts.id, params, function (res) {
					if (res && res.success) {
						$('#' + opts.status).html (opts.msg_saved).delay (2000).fadeOut ();
						return;
					}

					$('#' + opts.status).html (opts.msg_failed).delay (2000).fadeOut ();
				});
			});
		}
	});
})(jQuery);
