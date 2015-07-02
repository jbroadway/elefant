/**
 * Provides client-side equivalent to `Controller::add_notification()`.
 * Usage:
 *
 *     $.add_notification('My notification.');
 */
jQuery.add_notification = function (msg) {
	var notices = $.cookie ('elefant_notification');
	if (notices !== null) {
		msg = notices + '|' + msg;
	}
	$.cookie ('elefant_notification', msg, {path: '/'});
};

/**
 * Adds a confirmation to a link and turns its `data-*` properties
 * into a `POST` request. Usage:
 *
 *     <a href="/post/here"
 *        data-id="{{id}}"
 *        onclick="return $.confirm_and_post (this, 'Are you sure?')"
 *     >Delete</a>
 */
jQuery.confirm_and_post = function (el, msg) {
	if (window.event && window.event.preventDefault) {
		window.event.preventDefault ();
	}

	if (! confirm (msg)) {
		return false;
	}

	var $el = $(el),
		params = $el.data (),
		url = $el.attr ('href'),
		$form = $('<form>')
			.attr ('method', 'post')
			.attr ('action', url);

	$.each (params, function (name, value) {
		$('<input type="hidden">')
			.attr ('name', name)
			.attr ('value', value)
			.appendTo ($form);
	});

	$form.appendTo ('body');
	$form.submit ();
	return false;
};

/**
 * Turns a section of a form into an expandable/collapsible section.
 * Usage:
 *
 *     <h4 id="extras-header">{"Extra options"}</h4>
 *     <div id="extras-section">
 *         <!-- Extra content here -->
 *     </div>
 *     
 *     <script>
 *     $(function () {
 *         $.expanded_section ({
 *             handle: '#extras-header',
 *             section: '#extras-section',
 *             visible: false
 *         });
 *     });
 *     </script>
 */
jQuery.expanded_section = function (options) {
	var defaults = {
		header: '#expanded-header',
		section: '#expanded-section',
		arrowClass: 'arrow',
		arrowOffClass: 'arrow-off',
		visible: false,
		modal: false
	};

	var options = $.extend (defaults, options),
		header = $(options.header),
		section = $(options.section);

	if (options.visible) {
		header.prepend ('<span class="' + options.arrowClass + '"></span>');
		section.css ('display', 'block');
	} else {
		header.prepend ('<span class="' + options.arrowClass + ' ' + options.arrowOffClass + '"></span>');
		section.css ('display', 'none');
	}

	header
		.hover (
			function () {
				$(this).css ('cursor', 'pointer');
			},
			function () {
				$(this).css ('cursor', 'default');
			}
		)
		.click (
			function (evt) {
				if (section.css ('display') === 'none') {
					section.slideDown ('fast', function () {
						if (! options.modal) {
							window.scrollTo (0, 1000);
						}
					});
					header.children ('span.' + options.arrowClass).removeClass (options.arrowOffClass);
				} else {
					section.slideUp ('fast', function () {
						section.css ('display', 'none');
					});
					header.children ('span.' + options.arrowClass).addClass (options.arrowOffClass);
				}
			}
		);
};

/**
 * Displays the update notice if one is available. This is a JSONP
 * response handler.
 */
function elefant_update_response (res) {
	var _old = $.elefant_version.split ('.'),
		_new = res.latest.split ('.'),
		is_new = false;

	for (var i = 0; i < _old.length; i++) {
		var _o = parseInt (_old[i]),
			_n = parseInt (_new[i]);

		if (_o === _n) {
			continue;
		} else if (_o < _n) {
			is_new = true;
			break;
		}
		break;
	}

	if (is_new) {
		$('#admin-bar>a').append ('<a href="http://www.elefantcms.com/download" target="_blank" id="admin-update-available">Update Available: ' + res.latest + '</a>');
	}
}

$(function () {
	// is the tools menu sliding up right now?
	var sliding_up = false;

	// fetch data for admin toolbar
	$('body').append ('<div id="admin-bar"><div id="admin-links"></div><a href="/"><img id="admin-logo" src="/apps/admin/css/admin/spacer.png" alt="" /></a></div><div id="preview-bar"><a href="#" class="admin-tools-hide-preview" data-title="'+$.i18n("Back to Edit Mode")+'"><i class="fa fa-lg fa-pencil"></i></a></div>');
	$.get ('/admin/head/links', function (res) {
		$('#admin-logo').attr ('src', res.logo).attr ('alt', res.name);
		$('#admin-links').append (res.links);
	
		// show/hide tools menu
		if (!res.no_apps) {
			// custom tools menu
			var admin_tools = $('#admin-bar'),
				admin_tools_list = $('#admin-tools-list');
			
			$('#admin-bar>a').after ('<span id="admin-tools-arrow"></span>');

			function toggle_custom_tools_open () {
				admin_tools_list.stop ().css ('height', 'auto').slideDown ('fast', function(){admin_tools_list.css ('overflow-y', 'auto');});
			}

			function toggle_custom_tools_close () {
				admin_tools_list.stop ().css ('overflow-y', 'hidden').slideUp ('slow');
			}

			function toggle_custom_tools (e) {
				e.preventDefault ();

				// fix for clicks on tools menu links
				if ($(e.target).attr ('id') !== 'admin-bar' && $(e.target).attr ('id') !== 'admin-tools-arrow') {
					window.location.href = $(e.target).attr ('href');
					return false;
				}

				if (admin_tools_list.is (':visible')) {
					admin_tools_list.stop ().css ('overflow-y', 'hidden').slideUp ('fast');
				} else {
					admin_tools_list.stop ().css ('height', 'auto').slideDown ('fast', function(){admin_tools_list.css ('overflow-y', 'auto');});
					$('#admin-tools-list a')[0].focus ();
				}
			
				return false;
			}

			if (navigator.pointerEnabled) {
				$('#admin-tools-arrow').on ('pointerdown', toggle_custom_tools);
			} else if (navigator.msPointerEnabled) {
				$('#admin-tools-arrow').on ('MSPointerDown', toggle_custom_tools);
			} else {
				admin_tools.hover (
					function(){if(admin_tools_list.is(':visible')){toggle_custom_tools_open();}},
					toggle_custom_tools_close
				);

				$('#admin-tools-arrow').hover (
					toggle_custom_tools_open,
					function () {}
				).on ('touchdown click', toggle_custom_tools);
			}

		}

		// website link should open to the last non-admin page visited
		var last_page = $('body').data ('admin-last-page');
		if (last_page) {
			$('#admin-tools-website').attr ('href', last_page);
		} else {
			$.cookie ('elefant_last_page', window.location.pathname, { path: '/' });
			$('#admin-tools-website').attr ('href', window.location.pathname);
		}

		// toggle preview mode, shows/hides edit buttons and admin toolbar
		var toggle_preview = function () {
			var b = $('body');
		
			if (b.hasClass ('is-preview')) {
				b.removeClass ('is-preview');
			} else {
				b.addClass ('is-preview');
			}
			return false;
		};

		// attach preview toggle events
		$('a.admin-tools-show-preview', '#admin-bar' ).click (toggle_preview);
		$('a.admin-tools-hide-preview', '#preview-bar').click (toggle_preview);

		// add keyboard shortcuts
		$.triggers ();
	});

    $('.admin-options a').hover (
		function () {
			this.tip = this.title;
			$(this).append (
				'<div class="admin-tooltip"><div class="admin-tooltip-top"></div>' +
				'<div class="admin-tooltip-body">' + this.tip + '</div></div>'
			);
			this.title = '';
			$('.admin-tooltip').fadeIn (100);
		},
		function () {
			$('.admin-tooltip').fadeOut (100);
			$('.admin-tooltip').remove ();
			this.title = this.tip;
		}
	);

	// check for and display elefant updates if available
	if ($.elefant_updates && ! $.cookie ('elefant_update_checked')) {
		var major_minor = $.elefant_version.replace (/\.[0-9]+$/, '');

		$.ajax ({
			url: '/admin/head/updates/' + major_minor + '?callback=elefant_update_response',
			type: 'GET',
			dataType: 'jsonp',
			callback: elefant_update_response
		});

		$.cookie ('elefant_update_checked', 1, { expires: 1, path: '/' });
	}

	var jgrowl_interval = function () {
		var notice = $.cookie ('elefant_notification'),
			msglist = [],
			i = 0;

		$.cookie ('elefant_notification', null, {path: '/'});

		if (notice !== null) {
			msglist = notice.split ('|');
			for (i = 0; i < msglist.length; i++) {
				if (msglist[i].length > 0) {
					$.jGrowl (msglist[i].replace (/\+/g, ' '));
				}
			}
		}
		// clear notices
		setTimeout (jgrowl_interval, 1000);
	}

	jgrowl_interval ();

	// Set left column headers for responsive tables
	if ($(window).width () <= 480) {
		var th = [];

		$('th').each (function () {
			th.push ($(this).text ());
		});

		var n = 0;
		$('td').each (function () {
			var orig = $(this).html ();
			$(this).html (
				'<div class="before">' + th[n] + '</div>' +
				'<div class="after">' + orig + '</div>' +
				'<div class="clear"></div>'
			);
			n++;
			if (n >= th.length) {
				n = 0;
			}
		});
	}

	// focus on the first form input on load
	if (typeof elefant_focus_input !== 'undefined') {
		try {
			$('input, textarea').eq (0).focus ();
		} catch (e) {}
	}
});
