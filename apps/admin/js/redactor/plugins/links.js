/**
 * Provides a link menu for the Redactor editor, which integrates
 * with Elefant's list of internal pages so a user doesn't have
 * to copy and paste or manually type a link to a page within
 * their own site.
 */

if (! RedactorPlugins) var RedactorPlugins = {};

RedactorPlugins.links = function () {
	return {
		// Initialize the plugin
		init: function () {
			$.getJSON (
				'/admin/wysiwyg/links',
				$.proxy (
					function (res) {
						this.links._links = res;
					},
					this
				)
			);
			
			var dropdown = {};
			
			dropdown.point1 = { title: $.i18n ('Insert Link'), func: this.links.insert };
			dropdown.point2 = { title: $.i18n ('Unlink'), func: this.link.unlink };
			
			var button = this.button.addBefore ('html', 'links', $.i18n ('Links'));
			this.button.setAwesome ('links', 'fa-link');
			this.button.addDropdown (button, dropdown);
		},
	
		// Initialize the plugin when the button is clicked
		insert: function (self, evt, button) {
			this.links.insert_link_node = false;
			this.links.has_selected_text = false;
			var sel = this.selection.getCurrent (),
				url = '', text = '', target = '',
				page_matched = false,
				base = window.location.origin
					? window.location.origin
					: window.location.protocol + '//' + window.location.host;

			if (sel && sel.anchorNode && sel.anchorNode.parentNode.tagName === 'A') {
				this.links.insert_link_node = sel.anchorNode.parentNode;
				url = sel.anchorNode.parentNode.href;
				text = sel.anchorNode.parentNode.text;
				target = sel.anchorNode.parentNode.target;
			} else if (sel.tagName && sel.tagName === 'A') {
				this.links.insert_link_node = sel;
				url = sel.href;
				text = sel.text;
				target = sel.target;
			} else {
				var parent = this.selection.getParent ();
				if (parent.nodeName === 'A') {
					this.links.insert_link_node = $(parent);
					text = this.links.insert_link_node.text ();
					url = this.links.insert_link_node.attr ('href');
					target = this.links.insert_link_node.attr ('target');
				} else {
					text = this.selection.getText ();
				}
			}
		
			if (text.length !== 0) {
				this.links.has_selected_text = true;
			}
		
			if (url.search (base) === 0) {
				url = url.replace (base, '');
			}

			this.selection.save ();

			$.open_dialog (
				$.i18n ('Link'),
				'<div class="links-content">' +
					'<p>' +
						'<span class="links-btn" id="links-url-btn">' + $.i18n ('URL') + '</span>' +
						'<span class="links-btn" id="links-page-btn">' + $.i18n ('Page') + '</span>' +
						'<span class="links-btn" id="links-email-btn">' + $.i18n ('Email') + '</span>' +
						'<br />' +
						'<input type="text" id="links-url" size="65" placeholder="http://" />' +
						'<select id="links-page">' +
						'</select>' +
						'<input type="email" id="links-email" size="65" placeholder="you@example.com" />' +
					'</p>' +
					'<p>' +
						$.i18n ('Text') + '<br />' +
						'<input type="text" id="links-text" size="65" />' +
					'</p>' +
					'<p>' +
						'<input type="checkbox" id="links-tab"> ' +
						'<label for="links-tab">' +
							$.i18n ('Open link in new tab') +
						'</label>' +
					'</p>' +
					'<p>' +
						'<input type="submit" id="links-submit" value="' + $.i18n ('Insert') + '" />' +
						' &nbsp; ' +
						'<a href="#" id="links-cancel">' + $.i18n ('Cancel') + '</a>' +
					'</p>' +
				'</div>'
			);

			if (this.links._links) {
				var sel = $('#links-page');
				sel.append (
					$('<option>')
						.attr ('value', '')
						.text ($.i18n ('- select -'))
				);
				for (var i in this.links._links) {
					if (url === this.links._links[i].url) {
						sel.append (
							$('<option>')
								.attr ('value', this.links._links[i].url)
								.text (this.links._links[i].title)
								.attr ('selected', 'selected')
						);
						page_matched = true;
					} else {
						sel.append (
							$('<option>')
								.attr ('value', this.links._links[i].url)
								.text (this.links._links[i].title)
						);
					}
				}
			}

			if (page_matched) {
				this.links.show_pages ();
			} else if (url.search ('mailto:') === 0) {
				$('#links-email').val (url.replace ('mailto:', ''));
				this.links.show_email ();
			} else if (url.length !== 0) {
				$('#links-url').val (url);
				this.links.show_url ();
			} else {
				this.links.show_url ();
			}

			$('#links-text').val (text);

			if (target && target.length !== 0) {
				$('#links-tab').attr ('checked', 'checked');
			}

			$('#links-page-btn').click ($.proxy (this.links.show_pages, this));
			$('#links-url-btn').click ($.proxy (this.links.show_url, this));
			$('#links-email-btn').click ($.proxy (this.links.show_email, this));
			$('#links-cancel').click (function () { $.close_dialog (); });
			$('#links-submit').click ($.proxy (this.links.handle, this));
		},

		// Insert or update the link
		handle: function () {
			this.selection.restore ();
			this.buffer.set ();
		
			var active = this.links.links_active,
				page = $('#links-page').find (':selected').val (),
				url = $('#links-url').val (),
				email = $('#links-email').val (),
				text = $('#links-text').val (),
				target = $('#links-tab').is (':checked'),
				href = '';

			if (active === 'page') {
				href = page;
			} else if (active === 'url') {
				href = url;
			} else if (active === 'email') {
				href = 'mailto:' + email;
			}

			if (this.links.insert_link_node) {
				$(this.links.insert_link_node).text (text);
				$(this.links.insert_link_node).attr ('href', href);
				if (target) {
					$(this.links.insert_link_node).attr ('target', '_blank');
				} else {
					$(this.links.insert_link_node).removeAttr ('target');
				}
				this.code.sync ();
			} else {
				var html = '<a href="' + href + '"';
				if (target) {
					html += ' target="_blank"';
				}
				html += '>' + text + '</a>';
				this.insert.html (html);
			}

			$.close_dialog ();
		},
	
		// Show the pages list
		show_pages: function () {
			this.links.links_active = 'page';
			$('#links-page-btn').addClass ('active');
			$('#links-url-btn').removeClass ('active');
			$('#links-email-btn').removeClass ('active');
			$('#links-page').show ();
			$('#links-url').hide ();
			$('#links-email').hide ();
		},
	
		// Show the URL input
		show_url: function () {
			this.links.links_active = 'url';
			$('#links-page-btn').removeClass ('active');
			$('#links-url-btn').addClass ('active');
			$('#links-email-btn').removeClass ('active');
			$('#links-page').hide ();
			$('#links-url').show ();
			$('#links-email').hide ();
		},
	
		// Show the email input
		show_email: function () {
			this.links.links_active = 'email';
			$('#links-page-btn').removeClass ('active');
			$('#links-url-btn').removeClass ('active');
			$('#links-email-btn').addClass ('active');
			$('#links-page').hide ();
			$('#links-url').hide ();
			$('#links-email').show ();
		}
	};
};
