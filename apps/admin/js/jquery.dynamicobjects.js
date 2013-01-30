/**
 * Used by the admin/util/dynamicobjects handler to provide an
 * object chooser for app developers.
 */
;(function ($) {
	var self = {};

	// Current list of options
	self.opts = {};

	// Build an embed string from a handler and data
	self.build_embed_string = function (handler, data) {
		var i, sep = '?', embed = handler;
		for (i in data) {
			embed += sep + i + '=' + escape (data[i]);
			sep = '&';
		}
		return embed;
	};

	// Parse an embed string into a handler and data
	self.parse_embed_string = function (str) {
		str = str.replace ('{!', '').replace ('!}', '').trim ();
		if (str.indexOf ('?') !== -1) {
			var split = str.split ('?'),
				handler = split[0],
				params = (split[1].indexOf ('&') !== -1) ? split[1].split ('&') : [split[1]],
				data = {};

			for (var i = 0; i < params.length; i++) {
				split = params[i].split ('=');
				data[split[0]] = decodeURIComponent (split[1]);
			}
		} else {
			var handler = str,
				data = {};
		}
		return {
			handler: handler,
			data: data
		};
	};

	// Submit the chosen handler and data
	self.return_object = function () {
		var handler = $(this).data ('handler'),
			data = $(this).data,
			embed_code = self.build_embed_string (handler, data);
		
		if (self.opts.set_value) {
			$(self.opts.set_value).val (embed_code);
		}

		if (self.opts.callback) {
			self.opts.callback (embed_code, handler, data);
		}

		$.close_dialog ();
		return false;
	};

	$.dynamicobjects = function (opts) {
		var defaults = {
			callback: null,
			set_value: null,
			title: $.i18n ('Dynamic Objects')
		};
		
		self.opts = $.extend (defaults, opts);
		
		$.open_dialog (
			self.opts.title,
			'<div></div>'
		);
	};
})(jQuery);