/**
 * Makes elements with an .editable-* class name editable
 * directly in the page (inline editing). Valid classes
 * include:
 *
 * - .editable-text
 * - .editable-textarea
 * - .editable-select
 *
 * Usage:
 *
 *     <h2 class="editable-text" id="2"
 *         data-property="title"
 *         data-url="/myapp/save_editable">Edit This Header</h2>
 *
 *     <p class="editable-textarea" id="{{id}}">{{description}}</h2>
 *
 *     <p class="editable-select" id="{{id}}"
 *         data-name="category"
 *         data-options='{{categories|json_encode}}'
 *         >{{categories[$data->category]}}</p>
 */
$(function () {
	var opts = {
		indicator: $.i18n ('Saving...'),
		tooltip: $.i18n ('Click to edit'),
		cancel: $.i18n ('Cancel'),
		submit: $.i18n ('OK'),
		type: 'text',
		style: 'inherit',
		cssclass: 'editable',
		onerror: function (settings, original, xhr) {
			original.reset ();
		}
	};

	$('.editable-text').each (function () {
		var $this = $(this),
			url = $this.data ('url')
				? $this.data ('url')
				: editable_default_url,
			
			text_opts = $.extend (opts, {type: 'text', submitdata: {type: 'text'}});
		
		$this.editable (url, text_opts);
	});

	$('.editable-textarea').each (function () {
		var $this = $(this),

			url = $this.data ('url')
				? $this.data ('url')
				: editable_default_url,
			
			textarea_opts = $.extend (opts, {type: 'autogrow', submitdata: {type: 'textarea'}});

		$this.editable (url, textarea_opts);
	});

	$('.editable-select').each (function () {
		var $this = $(this),

			url = $this.data ('url')
				? $this.data ('url')
				: editable_default_url,

			select_opts = $.extend (
				opts,
				{
					type: 'select',
					data: $this.data ('options'),
					submitdata: function (value, settings) {
						var sel = $this.find ('select');

						return {
							type: 'select',
							label: sel[0].options[sel[0].selectedIndex].text
						};
					}
				}
			);

		$this.editable (url, select_opts);
	});
});
