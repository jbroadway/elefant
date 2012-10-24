/**
 * Interfaces with /admin/embed to list embeddable objects.
 */
(function ($) {
	"use strict";
	
	if (undefined === $.wysiwyg) {
		throw "wysiwyg.embed.js depends on $.wysiwyg";
	}
	
	var embed = {
		name: "embed",
		version: "0.98",
		ajaxHandler: "/admin/embed",
		embedList: [],
		selected: null,
		ready: true,
		init: function (Wysiwyg, callback) {
			$.get (this.ajaxHandler, function (res) {
				this.embedList = res;
				var embedder = new embedObj(this.embedList);
				embedder.load (Wysiwyg, callback);
			});
		}
	};
	
	$.wysiwyg.plugin.register (embed);
	
	function embedObj (_embed_list) {
		this.loaded = false;
		this.embed_list = _embed_list;
		this.dialog = null;
		
		this.load = function (Wysiwyg, callback) {
			var self = this;
			self.loaded = true;
			
			var uiHtml = '<div id="wysiwyg-embed-content"><div class="wysiwyg-embed-objects"><ul class="wysiwyg-embed-object-list"></ul></div><div class="wysiwyg-embed-object-form"></div><br clear="both" /></div>';

			self.embed_element = Wysiwyg.dom.getElement ('span');
			if (self.embed_element && ! $(self.embed_element).hasClass ('embedded')) {
				self.embed_element = null;
			}
			
			if ($.wysiwyg.dialog) {
				var embedUI = new $.wysiwyg.dialog(_embed_list, {
					title: 'Dynamic Objects',
					content: uiHtml,
					width: 550,
					close: function (e, dialog) {
						self.dialog = null;
					},
					open: function (e, dialog) {
						self.dialog = dialog;
						
						var uiHtml = '';
						for (var i = 0; i < _embed_list.length; i++) {
							uiHtml += '<li><a href="javascript:void(0)" class="wysiwyg-embed-object" id="wysiwyg-embed-object-' + i + '" data-handler="' + i + '">' + _embed_list[i].label + '</a></li>';
						}
						$('.wysiwyg-embed-object-list').html (uiHtml);
						// enable pager for list
						$('.wysiwyg-embed-object-list').quickPager ();
						$('.wysiwyg-embed-object-form').html ('Select an object on the left.');
						$('.wysiwyg-embed-object').click (function () {
							var i = 0,
								dia = self.dialog,
								num = $(this).data ('handler'),
								obj = _embed_list[num],
								f = $('.wysiwyg-embed-object-form'),
								uiHtml = '';

							if (obj.fields.length === 0) {
								callback (self.embed_element, obj.handler);
								embedUI.close ();
								return false;
							}

							//Added to allow easy two columning in forms
							if(obj.columns==2){
								var columnwidth = 150;
								var inputsizer = 15;
								var columner = 'left';
							}
							else
							{
								var columnwidth = 300;
								var inputsizer = 30;
								var columner = 'left';
							}
							
							// generate a form screen
							uiHtml = '<form id="wysiwyg-embed-form"><input type="hidden" name="handler" value="' + obj.handler + '" />';
							uiHtml += '<p><strong>' + obj.label + '</strong></p>';

							for (var i in obj.fields) {
								if (! obj.fields[i].initial) {
									obj.fields[i].initial = '';
								}

								//Flip flops for easy column tracking
								if (obj.columns==2 && columner=='left') {
									columner = 'right';
								}
								else
								{
									columner = 'left';
								}
								

								if (obj.fields[i].type == 'select') {
									uiHtml += '<p style="width:' + columnwidth + 'px;float:' + columner + '">' + obj.fields[i].label + ':<br /><select name="' + obj.fields[i].name + '">';
									for (var o in obj.fields[i].values) {
										if (obj.fields[i].values[o].hasOwnProperty ('key') && obj.fields[i].values[o].hasOwnProperty ('value')) {
											if (obj.fields[i].initial == obj.fields[i].values[o].key) {
												uiHtml += '<option value="' + obj.fields[i].values[o].key + '" selected>' + obj.fields[i].values[o].value + '</option>';
											} else {
												uiHtml += '<option value="' + obj.fields[i].values[o].key + '">' + obj.fields[i].values[o].value + '</option>';
											}
										} else {
											if (obj.fields[i].initial == obj.fields[i].values[o]) {
												uiHtml += '<option value="' + obj.fields[i].values[o] + '" selected>' + obj.fields[i].values[o] + '</option>';
											} else {
												uiHtml += '<option value="' + obj.fields[i].values[o] + '">' + obj.fields[i].values[o] + '</option>';
											}
										}
									}
									if (obj.fields[i].message) {
										uiHtml += '</select><span id="' + obj.fields[i].name + '-msg" class="notice" style="display: none"><br />' + obj.fields[i].message + '</span></p>';
									} else {
										uiHtml += '</select></p>';
									}
								} else if (obj.fields[i].type == 'textarea') {
									uiHtml += '<p style="width:' + columnwidth + 'px;float:' + columner + '">' + obj.fields[i].label + ':<br /><textarea name="' + obj.fields[i].name + '" cols="' + inputsizer +'" rows="6">' + obj.fields[i].initial + '</textarea>';
									if (obj.fields[i].message) {
										uiHtml += '<span id="' + obj.fields[i].name + '-msg" class="notice" style="display: none"><br />' + obj.fields[i].message + '</span></p>';
									} else {
										uiHtml += '</p>';
									}
								} else if (obj.fields[i].type == 'file') {
									uiHtml += '<p style="width:' + columnwidth + 'px;float:' + columner + '">' + obj.fields[i].label + ':<br /><input type="text" name="' + obj.fields[i].name + '" value="' + obj.fields[i].initial + '" size="' + inputsizer +'" />';
									if ($.wysiwyg.fileManager && $.wysiwyg.fileManager.ready) {
										// Add the File Manager icon:
										uiHtml += ' <div class="wysiwyg-fileManager" id="' + obj.fields[i].name + '-fileManager" title="Browse..." style="float: left; margin-top: -40px; margin-left: 200px" />';
									}
									if (obj.fields[i].message) {
										uiHtml += '<span id="' + obj.fields[i].name + '-msg" class="notice" style="display: none"><br />' + obj.fields[i].message + '</span></p>';
									} else {
										uiHtml += '</p>';
									}
								} else {
									uiHtml += '<p style="width:' + columnwidth + 'px;float:' + columner + '">' + obj.fields[i].label + ':<br /><input type="text" name="' + obj.fields[i].name + '" value="' + obj.fields[i].initial + '" size="' + inputsizer +'" />';
									if (obj.fields[i].message) {
										uiHtml += '<span id="' + obj.fields[i].name + '-msg" class="notice" style="display: none"><br />' + obj.fields[i].message + '</span></p>';
										
									} else {
										uiHtml += '</p>';
									}
								}
							}

							uiHtml += '<p><input type="submit" class="wysiwyg-embed-object-form-submit" value="Embed" /></p>';
							uiHtml += '</form>';
							f.html (uiHtml);

							// File Manager (select file):
							if ($.wysiwyg.fileManager) {
								for (var i in obj.fields) {
									if (obj.fields[i].type == 'file') {
										$('#' + obj.fields[i].name + '-fileManager').bind('click', function () {
											$.wysiwyg.fileManager.init(function (selected) {
												dialog.find('input[name=' + obj.fields[i].name + ']').val(selected);
												dialog.find('input[name=' + obj.fields[i].name + ']').trigger('change');
											});
										});
									}
								}
							}

							$('.wysiwyg-embed-object-form-submit').click (function (evt) {
								evt.preventDefault ();
								var i = 0,
									fields = obj.fields,
									label = obj.label,
									form = $(this)[0].form,
									out = form.elements.handler.value,
									key_list = ['name', 'label', 'type', 'initial', 'message', 'require', 'callback', 'values', 'filter'],
									filters = false;

								// validate form
								for (var i in fields) {
									var rules = [];
									for (var r in fields[i]) {
										var in_key_list = false;
										for (var k = 0; k < key_list.length; k++) {
											if (r == key_list[k]) {
												in_key_list = true;
												break;
											}
										}
										if (! in_key_list) {
											// it's a rule!
											if (! $(form.elements[i]).verify_value ({form:form, type:r, validator: fields[i][r]})) {
												$('#' + i + '-msg').show ();
												return false;
											}
										}
									}
								}

								// are there any filters?
								for (var i in fields) {
									if (fields[i].hasOwnProperty ('filter')) {
										filters = true;
										break;
									}
								}

								// build an array of valid data from the fields
								var unfiltered = {};
								for (var i = 0; i < form.elements.length; i++) {
									if (form.elements[i].name == 'handler' || ! form.elements[i].name) {
										continue;
									}
									unfiltered[form.elements[i].name] = form.elements[i].value;
								}

								if (filters) {
									// apply filters server-side then submit the form with the returned values
									$.post ('/admin/embed/filters', {handler: out, data: unfiltered}, function (res) {
										submit_form (embedUI, self.embed_element, callback, out, label, res.data);
									});
								} else {
									// no filters, submit the form now
									submit_form (embedUI, self.embed_element, callback, out, label, unfiltered);
								}

								return false;
							});
						});
					},
					modal: false
				});
				
				embedUI.open ();
				
				// If we're editing an existing element, start the dynamic objects dialog
				// with that element selected and its form pre-filled with the existing
				// embedded data.
				if (self.embed_element !== null) {
					var emb = parse_embed_string ($(self.embed_element).data ('embed'));
					for (var i = 0; i < _embed_list.length; i++) {
						if (_embed_list[i].handler === emb.handler) {
							$('#wysiwyg-embed-object-' + i).click ();
							// Fill with original values
							var f = $('#wysiwyg-embed-form')[0];
							for (var k in emb.data) {
								if (f.elements[k]) {
									if (_embed_list[i].fields[k].hasOwnProperty ('filter')) {
										var data = {};
										data[k] = emb.data[k];
										$.post ('/admin/embed/filters', {handler: emb.handler, data: data, reverse: 'yes'}, function (res) {
											f.elements[k].value = res.data[k];
										});
									} else {
										f.elements[k].value = emb.data[k];
									}
								}
							}
							break;
						}
					}
				}
			}
		};
	};

	/**
	 * Parse an embed string and return its handler name and a list of
	 * field names and values.
	 */
	function parse_embed_string (str) {
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

	/**
	 * Inserts the data into the content and closes the dialog window.
	 *
	 * Parameters:
	 *
	 * - The UI object to be closed when finished
	 * - The span element to embed into
	 * - The callback function to call
	 * - The initial string containing the handler name
	 * - The array of field names and values
	 */
	function submit_form (ui, embed_element, callback, out, label, data) {
		var i, sep = '?';
		for (i in data) {
			out += sep + i + '=' + escape (data[i]);
			sep = '&';
		}
		callback (embed_element, out, label);
		ui.close ();
		return false;
	}
})(jQuery);
