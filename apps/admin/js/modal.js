$(function () {
	// n is the topmost dialog
	var n = 0;
	
	// take the default overflow at the outset, in case another
	// script modifies it dynamically
	var default_overflow = $('html').css ('overflow');

	// create the html a new modal dialog
	function new_modal () {
		n++;
		$('body').append ('<div id="modal-overlay-' + n + '" class="modal-overlay"></div>' + 
			'<div id="modal-dialog-' + n + '" class="modal-dialog">' +
				'<div id="modal-titlebar-' + n + '" class="modal-titlebar">' +
					'<div class="modal-close-wrapper">' +
						'<a href="#" id="modal-close-' + n + '" data-modal="' + n + '" class="modal-close-button">X</a>' +
					'</div>' +
					'<div id="modal-title-' + n + '" class="modal-title"></div>' +
				'</div>' +
				'<div id="modal-content-' + n + '" class="modal-content"></div>' +
			'</div>');
		return n;
	}

	// removes the html from the specified dialog
	function close_modal (num) {
		$('#modal-dialog-' + num).remove ();
		$('#modal-overlay-' + num).remove ();
	}

	// centers the specified dialog
	function center_modal (num) {
		var modal = $('#modal-dialog-' + num),
			top = Math.max ($(window).height () - modal.outerHeight (), 0) / 2,
			left = Math.max ($(window).width () - modal.outerWidth (), 0) / 2;

		modal.css ({
			top: top + $(window).scrollTop () + ((num - 1) * 10),
			left: left + $(window).scrollLeft () + ((num - 1) * 10)
		});
	}

	// disable scrolling
	function disable_scrolling () {
		var html = $('html'),
			scrollpos = [
				self.pageXOffset || document.documentElement.scrollLeft || document.body.scrollLeft,
				self.pageYOffset || document.documentElement.scrollTop || document.body.scrollTop
			];

		html.data ('scroll-pos', scrollpos).css ('overflow', 'hidden');

		window.scrollTo (scrollpos[0], scrollpos[1]);
	}

	// re-enable scrolling
	function enable_scrolling () {
		var html = $('html'),
			scrollpos = html.data ('scroll-pos');

		html.css ('overflow', default_overflow);
		window.scrollTo (scrollpos[0], scrollpos[1]);
	}

	// open a new modal dialog
	$.open_dialog = function (title, html, opts) {
		var defaults = {
			width: 550,
			height: 300
		};

		opts = opts || {};
		opts = $.extend (defaults, opts);

		var num = new_modal (),
			modal = $('#modal-dialog-' + n);

		$('#modal-title-' + num).html (title);
		$('#modal-content-' + num).html (html);
		$('#modal-overlay-' + num).show ().css ({'z-index': num * 100000});
		modal.show ().css ({'z-index': (num * 100000) + 1});

		if (opts.width) {
			modal.css ({width: opts.width + 'px'});
			modal.children ('.modal-close-wrapper').css ({width: (opts.width - 22) + 'px'});
		}

		if (opts.height) {
			modal.css ({height: opts.height + 'px'});
			modal.children ('.modal-content').css ({height: (opts.height - 67) + 'px'});
		}

		center_modal (num);

		$('#modal-close-' + num).click ($.close_dialog);

		if (num === 1) {
			disable_scrolling ();
		}

		return num;
	};

	// close the top or the specified dialog
	$.close_dialog = function (num) {
		if (typeof num === 'object') {
			num = $(num.target).data ('modal');
		} else {
			num = num ? num : n;
		}

		// cascade if outer dialog is closed
		for (var i = n; i >= num; i--) {
			close_modal (i);
		}

		// adjust active number
		n = num - 1;

		if (n === 0) {
			enable_scrolling ();
		}

		return false;
	}
});
