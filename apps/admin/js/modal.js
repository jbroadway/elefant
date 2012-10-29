$(function () {
	$('body').append ('<div id="modal-dialog">' +
			'<div id="modal-titlebar">' +
				'<div id="modal-close-wrapper">' +
					'<a id="modal-close-button" href="#" class="simplemodal-close">X</a>' +
				'</div>' +
				'<div id="modal-title"></div>' +
			'</div>' +
			'<div id="modal-content"></div>' +
		'</div>');

	$.open_dialog = function (title, html, opts) {
		opts = opts || {};
		$('#modal-title').html (title);
		$('#modal-content').html (html);
		$('#modal-dialog').modal (opts);
	};

	$.close_dialog = function () {
		$.modal.close ();
		return false;
	}
});
