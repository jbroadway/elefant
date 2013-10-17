/**
 * Finds all tags with `data-trigger="k"` and makes them accessible
 * by the specified key press (Ctrl+Shift+KEY). You can also include
 * a `title="Label"` or `data-trigger-label="Label"` attribute so
 * they might be indexed in a help info dialog, as well as a
 * `data-trigger-event="touchdown"` to specify an alternate event type
 * to trigger.
 *
 * Usage:
 *
 *     <a href="#" onclick="return app.do_stuff()" data-trigger="d">Do Stuff</a>
 *
 *     <script src="jquery.js"></script>
 *     <script src="jquery.triggers.js"></script>
 *     <script>
 *     $(function () {
 *         $.triggers ();
 *     });
 *     </script>
 */
(function ($) {
	/**
	 * The list of elements that can be triggered.
	 */
	$.trigger_list = {};

	/**
	 * The dialog number if help is open.
	 */
	var help_open = false;

	/**
	 * Simulate a click on an element, with ctrl, alt, shift, and cmd disabled.
	 */
	$.simulate_click = function (target, type) {
		var event = target.ownerDocument.createEvent ('MouseEvents');
		
		var opts = {
			type: type,
			canBubble: true,
			cancelable: true,
			view: target.ownerDocument.defaultView,
			detail: 1,
			screenX: 0,
			screenY: 0,
			clientX: 0,
			clientY: 0,
			ctrlKey: false,
			altKey: false,
			shiftKey: false,
			metaKey: false,
			button: 0,
			relatedTarget: null
		};
		
		event.initMouseEvent (
			opts.type,
			opts.canBubble,
			opts.cancelable,
			opts.view, 
			opts.detail,
			opts.screenX,
			opts.screenY,
			opts.clientX,
			opts.clientY,
			opts.ctrlKey,
			opts.altKey,
			opts.shiftKey,
			opts.metaKey,
			opts.button,
			opts.relatedTarget
		);

		target.dispatchEvent(event);
	};

	/**
	 * Initialize the plugin.
	 */
	$.triggers = function () {
		$('body').append (
			'<span id="triggers-help" style="width:0;height:0;border:0;margin:0;padding:0;background-color:inherit"' +
			' data-trigger="h"' +
			' data-trigger-label="' + $.i18n ('List keyboard shortcuts') + '"></span>'
		);

		$('#triggers-help').click ($.triggers_help);

		$('[data-trigger]').each (function () {
			var el = $(this),
				key = el.data ('trigger'),
				title = el.data ('trigger-label') || el.attr ('title') || el.text (),
				type = el.data ('trigger-event') || 'click';

			$.trigger_list[key] = {
				key: key,
				element: this,
				title: title,
				type: type
			};
		});
	};

	/**
	 * Display a list of keyboard shortcuts.
	 */
	$.triggers_help = function () {
		if (help_open) {
			$.close_dialog (help_open);
			help_open = false;
			return false;
		}

		var html = '<ul>',
			keys = [];

		for (var key in $.trigger_list) {
			if ($.trigger_list.hasOwnProperty (key)) {
				keys.push (key);
			}
		}

		keys.sort ();

		for (var i in keys) {
			var trigger = $.trigger_list[keys[i]];
			html += '<li><tt>Ctrl + Shift + ' + trigger.key + '</tt> &nbsp; &nbsp; ' + trigger.title + '</li>';
		}
		html += '</ul>';

		help_open = $.open_dialog ($.i18n ('Keyboard shortcuts'), html);

		return false;
	};

	/**
	 * Handle the key presses.
	 */
	$(document).keydown (function (e) {
		e = e || window.event;

		var meta = e.ctrlKey || e.metaKey,
			shift = e.shiftKey,
			chr = String.fromCharCode (e.which || e.keyCode).toLowerCase ();

		if (meta && shift && $.trigger_list[chr]) {
			$.simulate_click ($.trigger_list[chr].element, $.trigger_list[chr].type);
		}
	});
})(jQuery);
