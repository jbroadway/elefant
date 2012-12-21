/**
 * Provides a Dynamic Objects menu for the Redactor editor,
 * which integrates with Elefant's dynamic handler embedding
 * capability.
 */

if (typeof ReactorPlugins === 'undefined') var RedactorPlugins = {};

RedactorPlugins.dynamic = {
	// The URI to fetch the dynamic object list
	ajax_handler: '/admin/embed',

	// Has the dynamic object list been loaded
	is_initialized: false,

	// Dynamic object list from fetch_objects()
	object_list: [],

	// Initialize the plugin
	init: function () {
		this.fetch_objects ();

		$('.redactor_editor').on ('click', '.embedded', {plugin: this}, this.edit_handler);

		this.addBtn ('dynamic', 'Dynamic Objects', this.addbtn_handler);
	},

	// Fetch and save the list of dynamic objects
	fetch_objects: function () {
		$.get (this.ajax_handler, $.proxy (function (res) {
			this.object_list = res;
			this.is_initialized = true;
		}, this));
	},

	// Initialize the plugin button handler
	addbtn_handler: function (self) {
		if (! self.is_initialized) {
			return false;
		}

		// Build the paged list of dynamic objects
		var html = '';
		for (var i = 0; i < self.object_list.length; i++) {
			var icon = '';

			if (self.object_list[i].icon) {
				if (self.object_list[i].icon.indexOf ('/') === -1) {
					icon = 'class="icon-' + self.object_list[i].icon + '"';
				} else {
					icon = 'style="background: url(' + self.object_list[i].icon + ') no-repeat"';
				}
			} else {
				icon = 'style="background: url(/apps/admin/css/admin/dynamic-icon.png) no-repeat"';
			}

			html += '<li><a href="javascript:void(0)" class="dynamic-embed-object" id="dynamic-embed-object-' + i + '" data-handler="' + i + '"><i ' + icon + ' ></i>' + self.object_list[i].label + '</a></li>';
		}

		self.modalInit (
			'Dynamic Objects',
			'<div id="redactor_modal_content"><div class="dynamic-embed-objects clearfix"><ul class="dynamic-embed-object-list clearfix">' + html + '</ul></div><div class="dynamic-embed-object-form"></div><br clear="both" /></div><div id="redactor_modal_footer"></div>',
			500,
			$.proxy (this.init_callback, self)
		);

		$('.dynamic-embed-object-list').quickPager ();
		$('#redactor_modal_footer').html ('').hide ();
		$('.dynamic-embed-object').unbind ('click').click (function () {
			self.select_object (this, self);
		});
	},

	// Reopen the dialog to edit an existing embed
	edit_handler: function (evt) {
		var self = evt.data.plugin;

		console.log (this);
		self.setSelection (this, 0, this, 0);
		console.log (self.getSelection ());
		self.saveSelection ();

		var emb = self.parse_embed_string ($(this).data ('embed'));

		self.addbtn_handler (self);

		for (var i = 0; i < self.object_list.length; i++) {
			if (self.object_list[i].handler === emb.handler) {
				$('#dynamic-embed-object-' + i).addClass ('current');

				if (! $.isEmptyObject (emb.data)) {
					var c = 0;

					for (var k in emb.data) {
						if (emb.data.hasOwnProperty (k)) {
							c++;
						}
					}

					if (c > 0) {
						// Has property, simulate selection
						$('#dynamic-embed-object-' + i).click ();

						// Fill with original values
						// TODO
					}
				}
				
				$('.simplePageNav' + Math.ceil (i / 10) + ' a').click ();
				break;
			}
		}
	},

	// The callback sent to modalInit()
	init_callback: function () {
		this.saveSelection ();
	},

	// Selects an object from the list and shows its form
	select_object: function (el, self) {
		var i = 0,
			num = $(el).data ('handler'),
			obj = self.object_list[num],
			html = '';

		if (obj.fields.length === 0) {
			self.insert_object (obj.handler, obj.label, obj.fields);
		}

		$('.dynamic-embed-object').removeClass ('current');
		$(el).addClass ('current');
		$('.dynamic-embed-objects').hide ();
		$('.dynamic-embed-object-form').show ();

		html = '<form id="dynamic-embed-form"><input type="hidden" name="handler" value="' + obj.handler + '" />';
		html += '<p><strong>' + obj.label + '</strong></p>';
		html += '<div class="clearfix">';

		for (var i in obj.fields) {
			if (! obj.fields[i].initial) {
				obj.fields[i].initial = '';
			}
		}

		html += '</div></form>';
		$('.dynamic-embed-object-form').html (html);
		$('#redactor_modal_footer').html (
			'<a href="#" class="redactor_modal_btn dynamic-embed-object-back">Back</a>' +
			'<a href="#" class="redactor_modal_btn dynamic-embed-object-form-submit">Embed</a>'
		).show ();

		if (obj.columns == '2') { 
			$('.dynamic-embed-object-form').addClass ('columns-2');
		} else { 
			$('.dynamic-embed-object-form').removeClass ('columns-2');
		};

		$('.dynamic-embed-object-back', '#redactor_modal_footer').unbind ('click').click (function () {
			$('#redactor_modal_footer').html ('').hide ();
			$('.dynamic-embed-object-form').hide ();
			$('.dynamic-embed-objects').show ();
		});

		//...
	},

	// Insert a dynamic object into the editor
	insert_object: function (handler, label, fields) {
		var i, sep = '?', out = handler;
		for (i in fields) {
			out += sep + i + '=' + escape (fields[i]);
			sep = '&';
		}
		this.restoreSelection ();
		console.log (this.getCurrentNode ());
		this.execCommand ('inserthtml', '<span class="embedded" data-embed="' + out + '" data-label="' + label + '" title="Click to edit."></span>');
		this.modalClose ();
		this.syncCode ();
	},

	// Parse an embed string and return its handler and a list of
	// field names and values.
	parse_embed_string: function (str) {
		str = str.replace ('{!', '').replace ('!}', '').trim ();
		if (str.indexOf ('?') !== -1) {
			var split = str.split ('?'),
				handler = split[0],
				params = (split[1].indexOf ('&') !== -1) ? split[1].split ('&') : [split[1]],
				data = {};

			for (var i = 0; i < params.length; i++) {
				split = params[i].split ('=');
				data[split[0]] = decodeURIComponent (split[1]);
			}
		} else {
			var handler = str,
				data = {};
		}
		return {
			handler: handler,
			data: data
		};
	}
};