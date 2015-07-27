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
			embed += sep + i + '=' + encodeURIComponent (data[i]);
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
	self.return_object = function (handler, data, label) {
		var handler = handler ? handler : $(this).data ('handler'),
			data = data ? data : $(this).data,
			embed_code = self.build_embed_string (handler, data),
			label = label ? label : $(this).data ('label');
		
		if (self.opts.set_value) {
			$(self.opts.set_value).val (embed_code);
		}

		if (self.opts.callback) {
			self.opts.callback (embed_code, handler, data, label);
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
			back_button: $.i18n ('Back'),
			browse_button: $.i18n ('Browse')
		};
		
		self.opts = $.extend (defaults, opts);
		
		if (! self.initialized) {
			alert ($.i18n ('Unable to load the dynamic object list. Please try again in a few seconds.'));
			return;
		}

		// the base html
		var html = '<div id="dynamicobjects-wrapper">' +
			'<div class="dynamicobjects-content clearfix">' +
				'<ul class="dynamicobjects-list clearfix"></ul>' +
			'</div>' +
			'<div class="dynamicobjects-form">' +
				'<form id="dynamicobjects-form"></form>' +
			'</div>' +
			'<br clear="both" />' +
		'</div>';

		// current is an existing object choice
		var current = (self.opts.current !== null)
			? self.parse_embed_string (self.opts.current)
			: false;

		$.open_dialog (self.opts.title, html);

		// build the list of handlers
		var ui = '',
			list = $('.dynamicobjects-list');

		for (var i = 0; i < self.list.length; i++) {
			var item = self.list[i],
				icon = '';

			if (item.icon) {
				if (item.icon.indexOf ('/') === -1) {
					icon = 'class="fa fa-' + item.icon + '"';
				} else {
					icon = 'style="background: url(' + item.icon + ') no-repeat"';
				}
			} else {
				icon = 'class="fa fa-cog"';
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
				f = $('#dynamicobjects-form'),
				html = '';

				if (obj.fields.length === 0) {
					// no parameters, return handler
					self.return_object (obj.handler, {}, obj.label);
				}

				// generate the form screen
				$('.dynamicobjects-object').removeClass ('current');
				$(this).addClass ('current');
				$('.dynamicobjects-content').hide ();
				$('.dynamicobjects-form').show ();

				html = '' +
					'<input type="hidden" name="handler" value="' + obj.handler + '" />' +
					'<input type="hidden" name="label" value="' + obj.label + '" />' +
					'<h2>' + obj.label + '</h2>' +
					'<div class="clearfix">';

				for (var i in obj.fields) {
					if (! obj.fields[i].initial) {
						obj.fields[i].initial = '';
					}
					
					if (obj.fields[i].type == 'select') {
						html += '<p><label for="' + obj.fields[i].name + '">' + obj.fields[i].label + '</label><select name="' + obj.fields[i].name + '">';
						for (var o in obj.fields[i].values) {
							if (obj.fields[i].values[o].hasOwnProperty ('key') && obj.fields[i].values[o].hasOwnProperty ('value')) {
								if (obj.fields[i].initial == obj.fields[i].values[o].key) {
									html += '<option value="' + obj.fields[i].values[o].key + '" selected>' + obj.fields[i].values[o].value + '</option>';
								} else {
									html += '<option value="' + obj.fields[i].values[o].key + '">' + obj.fields[i].values[o].value + '</option>';
								}
							} else {
								if (obj.fields[i].initial == obj.fields[i].values[o]) {
									html += '<option value="' + obj.fields[i].values[o] + '" selected>' + obj.fields[i].values[o] + '</option>';
								} else {
									html += '<option value="' + obj.fields[i].values[o] + '">' + obj.fields[i].values[o] + '</option>';
								}
							}
						}
						if (obj.fields[i].message) {
							html += '</select><span id="' + obj.fields[i].name + '-msg" class="notice" style="display: none"><br />' + obj.fields[i].message + '</span></p>';
						} else {
							html += '</select></p>';
						}

					} else if (obj.fields[i].type == 'textarea') {
						html += '<p><label for="' + obj.fields[i].name + '">' + obj.fields[i].label + '</label><textarea name="' + obj.fields[i].name + '" rows="6">' + obj.fields[i].initial + '</textarea>';
						if (obj.fields[i].message) {
							html += '<span id="' + obj.fields[i].name + '-msg" class="notice" style="display: none"><br />' + obj.fields[i].message + '</span></p>';
						} else {
							html += '</p>';
						}

					} else if (obj.fields[i].type == 'hidden') {
						html += '<input type="hidden" name="' + obj.fields[i].name + '" value="' + obj.fields[i].initial + '" />';

					} else if (obj.fields[i].type == 'file') {
						html += '<p><label for="' + obj.fields[i].name + '" >' + obj.fields[i].label + '</label>';
						html += '<input type="text" class="wysiwyg-file-input" name="' + obj.fields[i].name + '" id="' + obj.fields[i].name + '" value="' + obj.fields[i].initial + '" />';

						// add the File Manager icon:
						html += '&nbsp;<button class="wysiwyg-fileManager" id="' + obj.fields[i].name + '-fileManager" data-name="' + obj.fields[i].name + '">' + self.opts.browse_button + '</button>';
						html += "<br clear:both />"

						if (obj.fields[i].message) {
							html += '<span id="' + obj.fields[i].name + '-msg" class="notice" style="display: none"><br />' + obj.fields[i].message + '</span>';
						} 
							
						html += '</p>';
						
					} else if (obj.fields[i].type == 'image') {
						html += '<p><label for="' + obj.fields[i].name + '" >' + obj.fields[i].label + '</label>';
						html += '<input type="text" class="wysiwyg-file-input" name="' + obj.fields[i].name + '" id="' + obj.fields[i].name + '" value="' + obj.fields[i].initial + '" />';

						// add the File Manager icon:
						html += '&nbsp;<button class="wysiwyg-imageManager" id="' + obj.fields[i].name + '-imageManager" data-name="' + obj.fields[i].name + '">' + self.opts.browse_button + '</button>';
						html += "<br clear:both />"

						if (obj.fields[i].message) {
							html += '<span id="' + obj.fields[i].name + '-msg" class="notice" style="display: none"><br />' + obj.fields[i].message + '</span>';
						} 
							
						html += '</p>';
						
					} else if (obj.fields[i].type == 'color') {
						html += '<p><label for="' + obj.fields[i].name + '">' + obj.fields[i].label + '</label><input type="color" name="' + obj.fields[i].name +'" value="' + obj.fields[i].initial + '" />';
						if (obj.fields[i].message)
							html += '<span id="' + obj.fields[i].name + '-msg" class="notice" style="display: none"><br />' + obj.fields[i].message + '</span>';
						html += '</p>';
						
					} else {
						html += '<p><label for="' + obj.fields[i].name + '">' + obj.fields[i].label + '</label><input type="text" name="' + obj.fields[i].name + '" value="' + obj.fields[i].initial + '" />';
						if (obj.fields[i].message) {
							html += '<span id="' + obj.fields[i].name + '-msg" class="notice" style="display: none"><br />' + obj.fields[i].message + '</span></p>';
							
						} else {
							html += '</p>';
						}
					}
				}

				html += '</div><div>' +
					'<input type="submit" class="dynamicobjects-submit" value="' + self.opts.embed_button + '" />' +
					'<input type="submit" class="dynamicobjects-back clearfix" value="' + self.opts.back_button + '" />' +
				'</div><br clear="both" />';

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
						return false;
					});

				// selecting a file
				for (var i in obj.fields) {
					if (obj.fields[i].type == 'file') {
						$('#' + obj.fields[i].name + '-fileManager').bind ('click', function () {
							var input_field = $(this).data ('name');
							$.filebrowser ({
								set_value: '#' + input_field
							});
							return false;
						});
					} else if (obj.fields[i].type == 'image') {
						$('#' + obj.fields[i].name + '-imageManager').bind ('click', function () {
							var input_field = $(this).data ('name');
							$.filebrowser ({
								set_value: '#' + input_field,
								thumbs: true,
								allowed: ['jpg', 'jpeg', 'png', 'gif']
							});
							return false;
						});
					}
					// TODO: Limit by additional file types (audio, video)
				}

				// submit button handler
				$('.dynamicobjects-submit').click (function (evt) {
					evt.preventDefault ();

					var i = 0,
						fields = obj.fields,
						label = obj.label,
						form = $(this)[0].form,
						out = form.elements.handler.value,
						label = form.elements.label.value,
						key_list = ['name', 'label', 'type', 'initial', 'message', 'require', 'callback', 'values', 'filter'],
						filters = false;

					// validate form
					for (var i in fields) {
						var rules = [];
						for (var r in fields[i]) {
							var in_key_list = false;
							for (var k = 0; k < key_list.length; k++) {
								if (r == key_list[k]) {
									in_key_list = true;
									break;
								}
							}
							if (! in_key_list) {
								// it's a rule!
								if (! $(form.elements[i]).verify_value ({form:form, type:r, validator: fields[i][r]})) {
									$('#' + i + '-msg').show ();
									return false;
								}
							}
						}
					}

					// are there any filters?
					for (var i in fields) {
						if (fields[i].hasOwnProperty ('filter')) {
							filters = true;
						}
					}

					// build an array of valid data from the fields
					var unfiltered = {};
					for (var i = 0; i < form.elements.length; i++) {
						if (form.elements[i].name == 'handler' || form.elements[i].name == 'label' || ! form.elements[i].name) {
							continue;
						}
						unfiltered[form.elements[i].name] = form.elements[i].value;
					}

					if (filters) {
						// apply filters server-side then submit the form with the returned values
						$.post ('/admin/embed/filters', {handler: out, data: unfiltered}, function (res) {
							self.return_object (out, res.data, label);
						});
					} else {
						// no filters, submit the form now
						self.return_object (out, unfiltered, label);
					}

					return false;
				});
		});

		if (current) {
			for (var i = 0; i < self.list.length; i++) {
				if (self.list[i].handler === current.handler) {
					$('#dynamicobjects-object-' + i).addClass ('current');

					if (! $.isEmptyObject (current.data)) {
						var c = 0;
						for (k in current.data) {
							if (current.data.hasOwnProperty (k)) {
								c++;
							}
						}

						if (c > 0) {
							// simulate selection
							$('#dynamicobjects-object-' + i).click ();

							// fill with original values
							var f = $('#dynamicobjects-form').get (0);
							for (var k in current.data) {
								if (f.elements[k]) {
									if (self.list[i].fields[k] && self.list[i].fields[k].hasOwnProperty ('filter')) {
										var data = {};
										data[k] = current.data[k];
										$.post ('/admin/embed/filters', {handler: current.handler, data: data, reverse: 'yes'}, function (res) {
											for (var _k in res.data) {
												$(f.elements[_k]).val (res.data[_k]);
											}
										});
									} else {
										$(f.elements[k]).val (current.data[k]);
									}
								}
							}
						}
					}

					$('.simplePageNav' + Math.ceil (i / 10) + ' a').click ();
					break;
				}
			}
		}
	};

	self.init ();
})(jQuery);
