/**
 * Translates strings of text in .js files.
 * Usage:
 *
 *     <script>
 *     $(function () {
 *         // This will make the text available to $.i18n()
 *         $.i18n_append ({
 *             'Some text': '{"Some text"}',
 *             'Another string': '{"Another string"}'
 *         });
 *         
 *         // Now fetch a translated string
 *         console.log ($.i18n ('Some text'));
 *     });
 *     </script>
 */
(function ($) {
	var strings = {};

	$.i18n_append = function (obj) {
		strings = $.extend (strings, obj);
	};

	$.i18n = function (text) {
		if (strings[text]) {
			return strings[text];
		}
		return text;
	};
})(jQuery);
