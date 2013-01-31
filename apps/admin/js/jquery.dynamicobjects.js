/**
 * Used by the admin/util/dynamicobjects handler to provide an
 * object chooser for app developers.
 */
;(function ($) {
	var self = {};

	// Current list of options
	self.opts = {};

	// List of embeddable handlers
	self.list = [];

	// Initialized
	self.initialized = false;

	self.init = function () {
		$.get (
			'/admin/embed',
			function (res) {
				self.list = res;
				self.initialized = true;
			}
		);
	};

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
	self.return_object = function (handler, data) {
		var handler = handler ? handler : $(this).data ('handler'),
			data = data ? data : $(this).data,
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
			current: null,
			title: $.i18n ('Dynamic Objects'),
			embed_button: $.i18n ('Embed'),
			back_button: $.i18n ('Back')
		};
		
		self.opts = $.extend (defaults, opts);
		
		if (! self.initialized) {
			alert ($.i18n ('Unable to load the dynamic object list.'));
			return;
		}

		// the base html
		var html = '<div id="dynamicobjects-wrapper">' +
			'<div class="dynamicobjects-content clearfix">' +
				'<ul class="dynamicobjects-list clearfix"></ul>' +
			'</div>' +
			'<div class="dynamicobjects-form"></div>' +
			'<br clear="both" />' +
		'</div>';

		// current is an existing object choice
		var current = (self.opts.current !== null)
			? self.parse_embed_string (self.opts.current)
			: false;

		$.open_dialog (self.opts.title, html);

		// build the 
		var ui = ''
			list = $('.dynamicobjects-list');

		for (var i = 0; i < self.list.length; i++) {
			var item = self.list[i],
				icon = '';

			if (item.icon) {
				if (item.icon.indexOf ('/') === -1) {
					icon = 'class="icon-' + item.icon + '"';
				} else {
					icon = 'style="background: url(' + item.icon + ') no-repeat"';
				}
			} else {
				icon = 'style="background: url(/apps/admin/css/admin/dynamic-icon.png) no-repeat"';
			}

			ui += '<li>' +
				'<a href="javascript:void(0)" class="dynamicobjects-object" id="dynamicobjects-object-' + i + '" data-handler="' + i + '">' +
					'<i ' + icon + '></i>' + item.label +
				'</a>' +
			'</li>';
		}

		// page the list of handlers
		list.html (ui).quickPager ();

		// handle choosing a handler from the list
		$('.dynamicobjects-object').click (function () {
			var i = 0,
				num = $(this).data ('handler'),
				obj = self.list[num],
				f = $('.dynamicobjects-form'),
				html = '';

				if (obj.fields.length === 0) {
					// no parameters, return handler
					self.return_object (obj.handler, {});
				}

				// generate the form screen
				$('.dynamicobjects-object').removeClass ('current');
				$(this).addClass ('current');
				$('.dynamicobjects-content').hide ();
				$('.dynamicobjects-form').show ();

				html = '<form id="dynamicobjects-form">' +
					'<input type="hidden" name="handler" value="' + obj.handler + '" />' +
					'<h2>' + obj.label + '</h2>' +
					'<div class="clearfix">';

				for (var i in obj.fields) {
				}

				html += '</div><div>' +
					'<input type="submit" class="dynamicobjects-submit" value="' + self.opts.embed_button + '" />' +
					'<input type="button" class="dynamicobjects-back clearfix" value="' + self.opts.back_button + '" />' +
				'</div><br clear="both" /></form>';

				f.html (html);

				if (obj.columns == '2') {
					$('.dynamicobjects-form').addClass ('columns-2');
				} else {
					$('.dynamicobjects-form').removeClass ('columns-2');
				}

				// back button handler
				$('.dynamicobjects-back', '#dynamicobjects-form')
					.unbind ('click')
					.click (function () {
						$('.dynamicobjects-form').hide ();
						$('.dynamicobjects-content').show ();
					});

				// selecting a file

				// submit button handler
		});
	};

	self.init ();
})(jQuery);