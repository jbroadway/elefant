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
							uiHtml += '<li><a href="javascript:void(0)" class="wysiwyg-embed-object" data-handler="' + i + '">' + _embed_list[i].label + '</a></li>';
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
							
							// generate a form screen
							uiHtml = '<form><input type="hidden" name="handler" value="' + obj.handler + '" />';
							uiHtml += '<p><strong>' + obj.label + '</strong></p>';

							for (var i in obj.fields) {
								if (! obj.fields[i].initial) {
									obj.fields[i].initial = '';
								}
								if (obj.fields[i].type == 'select') {
									uiHtml += '<p>' + obj.fields[i].label + ':<br /><select name="' + obj.fields[i].name + '">';
									for (var o in obj.fields[i].values) {
										if (obj.fields[i].initial == obj.fields[i].values[o]) {
											uiHtml += '<option value="' + obj.fields[i].values[o] + '" selected>' + obj.fields[i].values[o] + '</option>';
										} else {
											uiHtml += '<option value="' + obj.fields[i].values[o] + '">' + obj.fields[i].values[o] + '</option>';
										}
									}
									if (obj.fields[i].message) {
										uiHtml += '</select><span id="' + obj.fields[i].name + '-msg" class="notice" style="display: none"><br />' + obj.fields[i].message + '</span></p>';
									} else {
										uiHtml += '</select></p>';
									}
								} else {
									uiHtml += '<p>' + obj.fields[i].label + ':<br /><input type="text" name="' + obj.fields[i].name + '" value="' + obj.fields[i].initial + '" size="30" />';
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
							$('.wysiwyg-embed-object-form-submit').click (function (evt) {
								evt.preventDefault ();
								var i = 0,
									fields = obj.fields,
									form = $(this)[0].form,
									out = form.elements.handler.value,
									key_list = ['name', 'label', 'type', 'initial', 'message', 'require', 'callback', 'values'],
									sep = '?';

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

								for (i = 0; i < form.elements.length; i++) {
									if (form.elements[i].name == 'handler' || ! form.elements[i].name) {
										continue;
									}
									out += sep + form.elements[i].name + '=' + escape (form.elements[i].value);
									sep = '&';
								}

								callback (self.embed_element, out);
								embedUI.close ();
								return false;
							});
						});
					},
					modal: false
				});
				
				embedUI.open ();
			}
		};
	}
})(jQuery);
