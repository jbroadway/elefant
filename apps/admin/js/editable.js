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
 *     
 *     <!-- add a delete button -->
 *     <h2 class="editable-text" id="2"
 *         data-property="title"
 *         data-url="/myapp/category/edit"
 *         data-delete="/myapp/category/delete">Category Title</h2>
 */
$(function () {
	var opts = {
		indicator: $.i18n ('Saving...'),
		tooltip: $.i18n ('Click to edit'),
		cancel: $.i18n ('Cancel'),
		submit: $.i18n ('Save'),
		type: 'text',
		style: 'inherit',
		cssclass: 'editable',
		submitdata: {},
		onerror: function (settings, original, xhr) {
			original.reset ();
		}
	};

	var opts_by_type = {
		text: {
			type: 'text',
			submitdata: {type: 'text'}
		},
		textarea: {
			type: 'autogrow',
			submitdata: {type: 'textarea'}
		},
		select: {
			type: 'select',
			submitdata: {type: 'select'}
		}
	};
	
	function parse_type_from_class (el) {
		for (var i in opts_by_type) {
			if (el.hasClass ('editable-' + i)) {
				return i;
			}
		}
		return 'text';
	}

	$('.editable-text,.editable-textarea,.editable-select').each (function () {
		var $this = $(this),

			type = parse_type_from_class ($this),

			del = $this.data ('delete'),
			
			prop = $this.data ('property'),

			url = $this.data ('url')
				? $this.data ('url')
				: editable_default_url,

			id = $this.attr ('id'),

			custom_opts = opts_by_type[type],
			
			final_opts = $.extend (true, {}, opts, custom_opts);

		if (typeof del !== 'undefined') {
			final_opts.del = $.i18n ('Delete');
			final_opts._delete_url = del;
		}

		if (typeof prop !== 'undefined') {
			final_opts.submitdata.property = prop;
		} else {
			delete final_opts.submitdata.property;
		}

		final_opts._url = url;
		final_opts._id = id;
		
		if (type === 'select') {
			final_opts.data = $this.data ('options');
			final_opts.submitdata = function (value, settings) {
				var sel = $this.find ('select');

				var submitdata = {
					type: 'select',
					label: sel[0].options[sel[0].selectedIndex].text
				};
				
				if (typeof prop !== 'undefined') {
					submitdata.property = prop;
				}
				
				return submitdata;
			};
		}
		
		$this.editable (url, final_opts);
	});
});
