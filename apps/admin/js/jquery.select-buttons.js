/**
 * Turns a select into a series of buttons.
 *
 * Usage:
 *
 *     <select name="some-options" class="buttons">
 *         <option value="one">One</option>
 *         <option value="two" selected>Two</option>
 *     </select>
 *
 *     <script> $(function () { $('select.buttons').select_buttons (); }); </script>
 */

(function ($) {
	$.fn.extend ({
		select_buttons: function (options) {
			var defaults = {
			};
			
			var options = $.extend (defaults, options);
			
			return this.each (function () {
				var opts = options,
					sel = $(this),
					group = $('<div class="select-buttons" data-select="' + sel.attr ('name') + '"></div>');
				
				sel.children ().each (function () {
					var btn = $('<a href="#" class="select-button" data-value="' + $(this).val () + '">' + $(this).text () + '</a>');
					
					if ($(this).is (':checked')) {
						btn.addClass ('select-button-active');
					}
					
					group.append (btn);
				});
				
				group.insertAfter (sel);
				
				$(document).on ('click', '.select-button', function (e) {
					e.preventDefault ();

					$(this).siblings ().removeClass ('select-button-active');
					$(this).addClass ('select-button-active');
					
					var selname = $(this).parent ().data ('select'),
						sel = $('[name=' + selname + ']');
					
					sel.val ($(this).data ('value'));
					sel.trigger ('change');
				});
				
				sel.hide ();
			});
		}
	});
})(jQuery);
