/**
 * Use this jQuery plugin to validate form data on the client side using
 * the same validation rules defined on the server-side.
 *
 * Usage:
 *
 * 1. Include jQuery and this script:
 *
 *   <script src="http://code.jquery.com/jquery-1.5.2.min.js"></script>
 *   <script src="/js/jquery.verify_values.js"></script>
 *
 * 2. Initialize the form:
 *
 *   <script>
 *     $(function () {
 *       $('form').verify_values ({
 *         handler: 'app/form',
 *         callback: function (failed) {
 *           // highlight the failed elements
 *         },
 *         reset: function (fields) {
 *           // unhighlight all elements
 *         }
 *       });
 *     });
 *   </script>
 */
(function ($) {
	$.fn.extend ({
		verify_values: function (options) {
			var defaults = {
				handler: '',
				prefix: '/admin/validator/',
				rules: [],
				callback: function (failed) {
					alert ('Please correct the following fields: ' + failed.join (', '));
				},
				reset: function (fields) {}
			};

			var options = $.extend (defaults, options);

			return this.each (function () {
				var opts = options,
					obj = $(this),
					url = opts.prefix + opts.handler;
				
				$.get (url, function (res) {
					opts.rules = res;
				});

				obj.bind ('submit', function (evt) {
					var failed = [],
						fields = [];
					
					for (var n in opts.rules) {
						fields.push (n);

						// switch for fields with name[] format
						var field = (typeof evt.target.elements[n + '[]'] !== 'undefined')
								? evt.target.elements[n + '[]']
								: evt.target.elements[n],
							skip_if_empty = false;

						for (var t in opts.rules[n]) {
							if (t == 'skip_if_empty') {
								skip_if_empty = true;
							}

							var opt_list = {
								form: evt.target,
								type: t,
								validator: opts.rules[n][t],
								skip_if_empty: skip_if_empty
							};
							if (! $(field).verify_value (opt_list)) {
								failed.push (n);
								break;
							}
						}
					}
					
					opts.reset (fields);
					
					if (failed.length > 0) {
						opts.callback (failed);
						return false;
					}
				});
			});
		},

		verify_value: function (options) {
			var value = $(this).attr ('value'),
				type = options.type,
				validator = options.validator;

			if (options.skip_if_empty == true && value == '') {
				return true;
			}

			// handle radio and checkbox buttons
			if ($(this).attr ('type') == 'radio') {
				value = '';
				for (var i = 0; i < this.length; i++) {
					var attr = $(this[i]).attr ('checked');
					if (typeof attr !== 'undefined' && attr !== false) {
						value = $(this[i]).attr ('value');
						break;
					}
				}
			} else if ($(this).attr ('type') == 'checkbox') {
				value = '';
				var sep = '';
				for (var i = 0; i < this.length; i++) {
					var attr = $(this[i]).attr ('checked');
					if (typeof attr !== 'undefined' && attr !== false) {
						value += sep + $(this[i]).attr ('value');
						sep = ', ';
					}
				}
			}
			
			if (type === 'default' || type.match (/^(not )?(type|callback|header|unique|exists)$/)) {
				return true;
			}
			
			if (type.match (/^each /)) {
				type = type.replace (/^each /, '');
				for (var i = 0; i < this.length; i++) {
					var opt_list = {
						form: options.form,
						type: type,
						validator: options.validator,
						skip_if_empty: options.skip_if_empty
					};
					if (! $(this[i]).verify_value (opt_list)) {
						return false;
					}
				}
				return true;
			} else if (type.match (/^not /)) {
				type = type.replace (/^not /, '');
				_false = true;
				_true = false;
			} else {
				_false = false;
				_true = true;
			}

			switch (type) {
				case 'empty':
					if (value != '') {
						return _false;
					}
					break;
				case 'matches':
					if (value != $(options.form.elements[validator]).attr ('value')) {
						return _false;
					}
					break;
				case 'regex':
					try {
						var mod = validator.replace (/^.+\/([igm]?)$/, '$1'),
							str = validator.substring (1).replace (/\/[igm]*$/, ''),
							re = new RegExp (str, mod);

						if (! value.match (re)) {
							return _false;
						}
					} catch (e) {
						if (window.console && console.log) {
							console.log (e.message);
						}
						return _false;
					}
					break;
				case 'contains':
					if (! value.toLowerCase ().match (validator.toLowerCase ())) {
						return _false;
					}
					break;
				case 'equals':
					if (value != validator) {
						return _false;
					}
					break;
				case 'gt':
					if (value <= validator) {
						return _false;
					}
					break;
				case 'gte':
					if (value < validator) {
						return _false;
					}
					break;
				case 'lt':
					if (value >= validator) {
						return _false;
					}
					break;
				case 'lte':
					if (value > validator) {
						return _false;
					}
					break;
				case 'range':
					range = validator.split ('-');
					range[0] -= 0;
					range[1] -= 0;
					if (range[0] > value || range[1] < value) {
						return _false;
					}
					break;
				case 'length':
					var re = /^([0-9]+)([+-]?)([0-9]*)$/;
					if (re.test (validator)) {
						if (RegExp.$3.length > 0) {
							if (value.length < RegExp.$1 || value.length > RegExp.$3) {
								return _false;
							}
						} else if (RegExp.$2 == '+' && value.length < RegExp.$1) {
							return _false;
						} else if (RegExp.$2 == '-' && value.length > RegExp.$1) {
							return _false;
						} else if (RegExp.$2.length == 0 && value.length != RegExp.$1) {
							return _false;
						}
					}
					break;
				case 'date':
					if (! value.match (/^[0-9]{4}-[0-9]{2}-[0-9]{2}$/)) {
						return _false;
					}
					break;
				case 'time':
					if (! value.match (/^[0-9]{2}:[0-9]{2}:[0-9]{2}$/)) {
						return _false;
					}
					break;
				case 'datetime':
					if (! value.match (/^[0-9]{4}-[0-9]{2}-[0-9]{2} [0-9]{2}:[0-9]{2}:[0-9]{2}$/)) {
						return _false;
					}
					break;
				case 'email':
					if (value.match (/\.@/)) {
						return _false;
					} else if (value.match (/\.$/)) {
						return _false;
					} else if (! value.match (/^([a-zA-Z0-9])+([a-zA-Z0-9\+\._-])*@([a-zA-Z0-9_-])+\.([a-zA-Z0-9\._-]+)+$/)) {
						return _false;
					}
					break;
				case 'url':
					if (! value.match (/^(ftp|http|https):\/\/(\w+:{0,1}\w*@)?(\S+)(:[0-9]+)?(\/|\/([\w#!:.?+=&%@!\-\/]))?$/)) {
						return _false;
					}
					break;
				// not implemented: type, callback, header, unique, exists
			}
			
			// on the client side, pass by default to avoid forms
			// becoming impossible to submit.
			return _true;
		}
	});
})(jQuery);
