/**
 * Use this jQuery plugin to auto-save and restore form data via
 * jStorage to protect against browser crashes and other accidental
 * interruptions.
 *
 * Usage:
 *
 * 1. Include the script and prerequisites:
 *
 *   <script src="http://code.jquery.com/jquery-1.5.2.min.js"></script>
 *   <script src="/js/json2.js"></script>
 *   <script src="/js/jstorage.js"></script>
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
 * 3. Add a restore option if the saved data is detected on page load
 * (using an Elefant template as an example):
 *
 *   <p class="autosave-notice">Auto-saved data found for this form. <a href="#" class="autosave-restore">Click here to restore.</a></p>
 *
 * 4. Add class="autosave-clear" to the submit button so saving the form
 * clears the data.
 *
 * 5. To add a cancel link, add the following onclick handler:
 *
 *   onclick="return $.cancel_autosave ('Are you sure you want to cancel?')"
 *
 * The stored key name is determined as follows:
 *
 *   "autosave-" + (pathname + parameters).replace (/[^a-z0-9-]/g, '')
 *
 * So for example if the request is:
 *
 *   /admin/edit?page=index
 *
 * The key name will be:
 *
 *   autosave-admineditpageindex
 *
 */
var autosave_interval = null,
	autosave_focused = false,
	autosave_key_name = 'autosave-' + (window.location.pathname + window.location.search).replace (/[^a-zA-Z0-9-]+/g, '');

(function ($) {
	if ($.jStorage.get (autosave_key_name)) {
		setTimeout (function () {
			var vals = $.parseJSON (lzw_decode ($.jStorage.get (autosave_key_name)));
			$('.autosave-notice').show ();
		}, 100);
	}

	$.cancel_autosave = function (msg) {
		if (confirm (msg)) {
			$.jStorage.deleteKey (autosave_key_name);
			return true;
		}
		return false;
	}

	$.fn.extend ({
		autosave: function (options) {
			var defaults = {
				interval: 10000,
				form: null,
			};
			
			var options = $.extend (defaults, options);

			// Get the form object
			options.form = this[0];

			// Handler to clear the key (used on submit)
			$('.autosave-clear').click (function () {
				$.jStorage.deleteKey (autosave_key_name);
			});

			// Handler to restore data from the key
			$('.autosave-restore').click (function () {
				var i = 0,
					opts = options,
					vals = $.parseJSON (lzw_decode ($.jStorage.get (autosave_key_name)));

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
							//$('#webpage-body').redactor ('code.set', vals[i].value);
							$('#webpage-body').setCode (vals[i].value);
						} else if (opts.form.elements[vals[i].name].getAttribute ('id') == 'code-body') {
							// Set the contents of codemirror editor
							_codemirror.setValue (vals[i].value);
						} else if (opts.form.elements[vals[i].name].getAttribute ('id') == 'tags') {
							// Set the contents of tag-it widget
							$('#tagit').tagit ('removeAll');
							var tags = vals[i].value.split (',');
							for (var t = 0; t < tags.length; t++) {
								$('#tagit').tagit ('createTag', tags[t]);
							}
						}
					} catch (e) {}
				}

				// Clear the key
				$.jStorage.deleteKey (autosave_key_name);

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

			// Set an interval to save the form data
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
					if (opts.form.elements[i].getAttribute ('id') == 'webpage-body') {
						// Get the contents from Redactor editor
						vals[i] = {
							name: opts.form.elements[i].name,
							//value: $('#webpage-body').redactor ('code.get')
							value: $('#webpage-body').getCode ()
						};
					} else if (opts.form.elements[i].getAttribute ('id') == 'code-body') {
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
				
				var _json = JSON.stringify (vals);
				$.jStorage.set (autosave_key_name, lzw_encode (_json));
			}, options.interval);
		}
	});
})(jQuery);

// The following are from http://jsolait.net/

// LZW-compress a string
function lzw_encode(s) {
    var dict = {};
    var data = (s + "").split("");
    var out = [];
    var currChar;
    var phrase = data[0];
    var code = 256;
    for (var i=1; i<data.length; i++) {
        currChar=data[i];
        if (dict[phrase + currChar] != null) {
            phrase += currChar;
        }
        else {
            out.push(phrase.length > 1 ? dict[phrase] : phrase.charCodeAt(0));
            dict[phrase + currChar] = code;
            code++;
            phrase=currChar;
        }
    }
    out.push(phrase.length > 1 ? dict[phrase] : phrase.charCodeAt(0));
    for (var i=0; i<out.length; i++) {
        out[i] = String.fromCharCode(out[i]);
    }
    return out.join("");
}

// Decompress an LZW-encoded string
function lzw_decode(s) {
    var dict = {};
    var data = (s + "").split("");
    var currChar = data[0];
    var oldPhrase = currChar;
    var out = [currChar];
    var code = 256;
    var phrase;
    for (var i=1; i<data.length; i++) {
        var currCode = data[i].charCodeAt(0);
        if (currCode < 256) {
            phrase = data[i];
        }
        else {
           phrase = dict[currCode] ? dict[currCode] : (oldPhrase + currChar);
        }
        out.push(phrase);
        currChar = phrase.charAt(0);
        dict[code] = oldPhrase + currChar;
        code++;
        oldPhrase = phrase;
    }
    return out.join("");
}
