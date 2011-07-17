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
				'<br /><br /><label>{url}: <input type="text" name="linkhref" value="" size="30" /></label>' +
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
				href: "http://"//,
				//title: "",
				//target: ""
			};

			if (a.self) {
				a.href = a.self.href ? a.self.href : a.href;
				//a.title = a.self.title ? a.self.title : "";
				//a.target = a.self.target ? a.self.target : "";
			}
			
			elements = $(formLinkHtml);
			elements.find("input[name=linkhref]").val(a.href);

			$.getJSON ('/admin/wysiwyg/links', function (res) {
				var link, s = $('#innerlink');
				for (var i = 0; i < res.length; i++) {
					link = res[i];
					s.append ('<option value="' + link.url + '">' + link.title + '</option>');
				}
			});

			dialog = new $.wysiwyg.dialog (Wysiwyg, {
				title: dialogReplacements.legend,
				content: formLinkHtml,
				open: function (ev, ui) {
					$("#link-dialog-submit").click(function (e) {
						e.preventDefault();

						var url = $('input[name="linkhref"]').val(),
							inner = $('select[name="innerlink"]').val (),
							//title = $('input[name="linktitle"]', dialog).val(),
							//target = $('input[name="linktarget"]', dialog).val(),
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
									$(a.self).attr("href", url).attr("title", title).attr("target", target);
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

								$(a.self).attr("href", url);//.attr("title", title);

								/**
								 * @url https://github.com/akzhan/jwysiwyg/issues/16
								 */
								//$(a.self).attr("target", target);
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

			/*} else {
				if (a.self) {
					url = window.prompt("URL", a.href);

					if (Wysiwyg.options.controlLink.forceRelativeUrls) {
						baseUrl = window.location.protocol + "//" + window.location.hostname;
						if (0 === url.indexOf(baseUrl)) {
							url = url.substr(baseUrl.length);
						}
					}

					if ("string" === typeof (url)) {
						if (url.length > 0) {
							$(a.self).attr("href", url);
						} else {
							$(a.self).replaceWith(a.self.innerHTML);
						}
					}
				} else {
					//Do new link element
					selection = Wysiwyg.getRangeText();
					img = Wysiwyg.dom.getElement("img");

					if ((selection && selection.length > 0) || img) {
						if ($.browser.msie) {
							Wysiwyg.ui.focus();
							Wysiwyg.editorDoc.execCommand("createLink", true, null);
						} else {
							url = window.prompt(dialogReplacements.url, a.href);

							if (Wysiwyg.options.controlLink.forceRelativeUrls) {
								baseUrl = window.location.protocol + "//" + window.location.hostname;
								if (0 === url.indexOf(baseUrl)) {
									url = url.substr(baseUrl.length);
								}
							}

							if ("string" === typeof (url)) {
								if (url.length > 0) {
									Wysiwyg.editorDoc.execCommand("createLink", false, url);
								} else {
									Wysiwyg.editorDoc.execCommand("unlink", false, null);
								}
							}
						}
					} else if (Wysiwyg.options.messages.nonSelection) {
						window.alert(Wysiwyg.options.messages.nonSelection);
					}
				}

				Wysiwyg.saveContent();
			}*/

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
