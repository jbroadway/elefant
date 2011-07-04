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

			options.form = this[0];
			options.cookie_name = 'autosave-' + (window.location.pathname + window.location.search).replace (/[^a-zA-Z0-9-]+/g, '');
			
			$('.autosave-clear').click (function () {
				var opts = options;
				$.cookie (opts.cookie_name, null);
			});

			$('.autosave-restore').click (function () {
				var i = 0,
					opts = options,
					vals = $.parseJSON ($.cookie (opts.cookie_name));

				for (i = 0; i < vals.length; i++) {
					opts.form.elements[vals[i].name].value = vals[i].value;
					if (opts.form.elements[vals[i].name].getAttribute ('id') == 'webpage-body') {
						$('#webpage-body').wysiwyg ('setContent', vals[i].value);
					}
				}

				$('.autosave-notice').slideUp ('slow');
				return false;
			});

			for (var i = 0; i < options.form.elements.length; i++) {
				$(options.form.elements[i]).focus (function () {
					autosave_focused = true;
				});
			}
			
			if (autosave_interval != null) {
				return;
			}

			autosave_interval = setInterval (function () {
				if (autosave_focused === false) {
					return;
				}

				var i = 0,
					opts = options,
					vals = [];

				for (i = 0; i < opts.form.elements.length; i++) {
					if (! opts.form.elements[i].name) {
						continue;
					}
					vals[i] = {
						name: opts.form.elements[i].name,
						value: opts.form.elements[i].value
					};
				}
				
				$.cookie (opts.cookie_name, JSON.stringify (vals), { expires: 1, path: '/' });
			}, options.interval);
		}
	});
})(jQuery);
