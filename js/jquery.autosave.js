/**
 * Use this jQuery plugin to auto-save and restore form data via
 * a cookie to protect against browser crashes and other accidental
 * interruptions.
 *
 * Usage:
 *
 * 1. Include the script and prerequisites:
 *
 *   <script src="http://code.jquery.com/jquery-1.5.2.min.js"></script>
 *   <script src="/js/json2.js"></script>
 *   <script src="/js/jquery.cookie.js"></script>
 *   <script src="/js/jquery.autosave.js"></script>
 *
 * 2. Initialize the form:
 *
 *   <script>
 *     $(function () {
 *       $('form').autosave ();
 *     });
 *   </script>
 *
 * 3. Add a restore option if the cookie is detected on page load
 * (using an Elefant template as an example):
 *
 *   {% if isset ($_COOKIE['autosave-' . preg_replace ('/[^a-z0-9-]/', '', $_SERVER['REQUEST_URI'])]) %}
 *   <p class="autosave-notice">Auto-saved data found for this form. <a href="#" class="autosave-restore">Click here to restore.</a></p>
 *   {% end %}
 *
 * 4. Add class="autosave-clear" to the submit button so saving the form
 * clears the cookie.
 *
 * The cookie name is determined as follows:
 *
 *   "autosave-" + (pathname + parameters).replace (/[^a-z0-9-]/g, '')
 *
 * So for example if the request is:
 *
 *   /admin/edit?page=index
 *
 * The cookie name will be:
 *
 *   autosave-admineditpageindex
 *
 */
var autosave_interval = null,
	autosave_focused = false;

(function ($) {
	$.fn.extend ({
		autosave: function (options) {
			var defaults = {
				cookie_name: 'autosave',
				interval: 10000,
				form: null,
			};
			
			var options = $.extend (defaults, options);

			// Get the form object
			options.form = this[0];

			// Set the cookie name based on the current request uri
			options.cookie_name = 'autosave-' + (window.location.pathname + window.location.search).replace (/[^a-zA-Z0-9-]+/g, '');
			
			// Handler to clear the cookie (used on submit)
			$('.autosave-clear').click (function () {
				var opts = options;
				$.cookie (opts.cookie_name, null);
			});

			// Handler to restore data from the cookie
			$('.autosave-restore').click (function () {
				var i = 0,
					opts = options,
					vals = $.parseJSON ($.cookie (opts.cookie_name));

				for (i = 0; i < vals.length; i++) {
					try {
						// Set the field value
						if ($(opts.form.elements[vals[i].name]).is (':radio') || $(opts.form.elements[vals[i].name]).is (':checkbox')) {
							if (vals[i].value) {
								$(opts.form.elements[vals[i].name]).attr ('checked', 'checked');
							} else {
								$(opts.form.elements[vals[i].name]).attr ('checked', null);
							}
						} else {
							opts.form.elements[vals[i].name].value = vals[i].value;
						}
						if (opts.form.elements[vals[i].name].getAttribute ('id') == 'webpage-body') {
							// Set the contents of wysiwyg editor
							$('#webpage-body').wysiwyg ('setContent', vals[i].value);
						} else if (opts.form.elements[vals[i].name].getAttribute ('id') == 'code-body') {
							// Set the contents of codemirror editor
							_codemirror.setValue (vals[i].value);
						}
					} catch (e) {}
				}

				// Hide the restore notice
				$('.autosave-notice').slideUp ('slow');
				return false;
			});

			// Add a focus handler that triggers saving only once the form is active
			for (var i = 0; i < options.form.elements.length; i++) {
				$(options.form.elements[i]).focus (function () {
					autosave_focused = true;
				});
			}
			
			// Don't set the interval more than once
			if (autosave_interval != null) {
				return;
			}

			// Set an interval to save the form data to the cookie
			autosave_interval = setInterval (function () {
				if (autosave_focused === false) {
					return;
				}

				var i = 0,
					opts = options,
					vals = [];

				for (i = 0; i < opts.form.elements.length; i++) {
					if (! opts.form.elements[i].name) {
						// Unnamed fields can be ignored (submit buttons)
						continue;
					}
					if (opts.form.elements[i].getAttribute ('id') == 'code-body') {
						// Get the contents from codemirror editor
						vals[i] = {
							name: opts.form.elements[i].name,
							value: _codemirror.getValue ()
						};
					} else if ($(opts.form.elements[i]).is (':radio') || $(opts.form.elements[i]).is (':checkbox')) {
						if (opts.form.elements[i].checked) {
							vals[i] = {
								name: opts.form.elements[i].name,
								value: true
							};
						} else {
							vals[i] = {
								name: opts.form.elements[i].name,
								value: false
							};
						}
					} else {
						// Get the contents from the field itself
						vals[i] = {
							name: opts.form.elements[i].name,
							value: opts.form.elements[i].value
						};
					}
				}
				
				$.cookie (opts.cookie_name, JSON.stringify (vals), { expires: 1, path: '/' });
			}, options.interval);
		}
	});
})(jQuery);
