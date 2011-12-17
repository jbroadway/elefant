/**
 * Controls: Link plugin
 *
 * Depends on jWYSIWYG
 *
 * By: Esteban Beltran (academo) <sergies@gmail.com>
 */
(function ($) {
	"use strict";

	if (undefined === $.wysiwyg) {
		throw "wysiwyg.link.js depends on $.wysiwyg";
	}

	if (!$.wysiwyg.controls) {
		$.wysiwyg.controls = {};
	}

	/*
	* Wysiwyg namespace: public properties and methods
	*/
	$.wysiwyg.controls.link = {
		init: function (Wysiwyg) {
			var self = this, elements, dialog, url, a, selection,
				formLinkHtml, dialogReplacements, key, translation, regexp,
				baseUrl, img;

			dialogReplacements = {
				legend: "Insert Link",
				select: "Link to page",
				url   : "Or paste link",
				title : "Link Title",
				target: "Link Target",
				submit: "Insert",
				reset: "Cancel"
			};

			formLinkHtml = '<form class="wysiwyg"><!-- <fieldset>legend>{legend}</legend -->' +
				'<label>{select}: <select name="innerlink" id="innerlink"><option value="">- SELECT -</option></select></label>' +
				'<br /><br /><label>{url}: <input type="text" name="linkhref" id="linkhref" value="" size="30" style="width: 270px" /></label>' +
				'<div class="wysiwyg-fileManager" title="Browse..." style="float: left; margin-top: -20px; margin-left: 380px" />' +
				//'<label>{title}: <input type="text" name="linktitle" value=""/></label>' +
				//'<label>{target}: <input type="text" name="linktarget" value=""/></label>' +
				'<br /><br /><input type="submit" class="button" value="{submit}" id="link-dialog-submit" /> ' +
				'<!-- <input type="reset" value="{reset}"/></fieldset> --></form>';

			for (key in dialogReplacements) {
				if ($.wysiwyg.i18n) {
					translation = $.wysiwyg.i18n.t(dialogReplacements[key], "dialogs.link");

					if (translation === dialogReplacements[key]) { // if not translated search in dialogs 
						translation = $.wysiwyg.i18n.t(dialogReplacements[key], "dialogs");
					}

					dialogReplacements[key] = translation;
				}

				regexp = new RegExp("{" + key + "}", "g");
				formLinkHtml = formLinkHtml.replace(regexp, dialogReplacements[key]);
			}

			a = {
				self: Wysiwyg.dom.getElement("a"), // link to element node
				href: "http://"
			};

			if (a.self) {
				a.href = a.self.href ? a.self.href : a.href;
				baseUrl = window.location.protocol + "//" + window.location.hostname;
				if (0 === a.href.indexOf(baseUrl)) {
					a.href = a.href.substr(baseUrl.length);
				}
			}
			
			formLinkHtml = formLinkHtml.replace ('id="linkhref" value=""', 'id="linkhref" value="' + a.href + '"');

			$.getJSON ('/admin/wysiwyg/links', function (res) {
				var link, s = $('#innerlink');

				s.change (function () {
					var val = $(this).val (), l = $('#linkhref');
					if (val.length != '' && l.val () == '') {
						l.val (val);
					}
				});

				for (var i = 0; i < res.length; i++) {
					link = res[i];
					s.append ('<option value="' + link.url + '">' + link.title + '</option>');
				}
			});

			dialog = new $.wysiwyg.dialog (Wysiwyg, {
				title: dialogReplacements.legend,
				content: formLinkHtml,
				open: function (ev, ui) {
					$('div.wysiwyg-fileManager').bind('click', function () {
						$.wysiwyg.fileManager.init(function (selected) {
							$('#linkhref').val(selected);
							$('#linkhref').trigger('change');
						});
					});

					$("#link-dialog-submit").click(function (e) {
						e.preventDefault();

						var url = $('input[name="linkhref"]').val(),
							inner = $('select[name="innerlink"]').val (),
							baseUrl,
							img;
						
						url = (inner.length > 0) ? inner : url;

						if (Wysiwyg.options.controlLink.forceRelativeUrls) {
							baseUrl = window.location.protocol + "//" + window.location.hostname;
							if (0 === url.indexOf(baseUrl)) {
								url = url.substr(baseUrl.length);
							}
						}

						if (a.self) {
							if ("string" === typeof (url)) {
								if (url.length > 0) {
									// to preserve all link attributes
									$(a.self).attr("href", url);
								} else {
									$(a.self).replaceWith(a.self.innerHTML);
								}
							}
						} else {
							if ($.browser.msie) {
								Wysiwyg.ui.returnRange();
							}

							//Do new link element
							selection = Wysiwyg.getRangeText();
							img = Wysiwyg.dom.getElement("img");

							if ((selection && selection.length > 0) || img) {
								if ($.browser.msie) {
									Wysiwyg.ui.focus();
								}

								if ("string" === typeof (url)) {
									if (url.length > 0) {
										Wysiwyg.editorDoc.execCommand("createLink", false, url);
									} else {
										Wysiwyg.editorDoc.execCommand("unlink", false, null);
									}
								}

								a.self = Wysiwyg.dom.getElement("a");

								$(a.self).attr("href", url);
							} else if (Wysiwyg.options.messages.nonSelection) {
								window.alert(Wysiwyg.options.messages.nonSelection);
							}
						}

						Wysiwyg.saveContent();

						dialog.close ();
					});
					$("input:reset", dialog).click(function (e) {
						e.preventDefault();
						dialog.close ();
					});
				},
				close: function (ev, ui) {
					dialog = null;
				}
			});
			dialog.open ();

			$(Wysiwyg.editorDoc).trigger("editorRefresh.wysiwyg");
		}
	};

	$.wysiwyg.createLink = function (object, url) {
		return object.each(function () {
			var oWysiwyg = $(this).data("wysiwyg"),
				selection;

			if (!oWysiwyg) {
				return this;
			}

			if (!url || url.length === 0) {
				return this;
			}

			selection = oWysiwyg.getRangeText();

			if (selection && selection.length > 0) {
				if ($.browser.msie) {
					oWysiwyg.ui.focus();
				}
				oWysiwyg.editorDoc.execCommand("unlink", false, null);
				oWysiwyg.editorDoc.execCommand("createLink", false, url);
			} else if (oWysiwyg.options.messages.nonSelection) {
				window.alert(oWysiwyg.options.messages.nonSelection);
			}
			return this;
		});
	};
})(jQuery);
