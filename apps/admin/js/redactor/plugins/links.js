/**
 * Provides a link menu for the Redactor editor, which integrates
 * with Elefant's list of internal pages so a user doesn't have
 * to copy and paste or manually type a link to a page within
 * their own site.
 */

if (typeof RedactorPlugins === 'undefined') var RedactorPlugins = {};

RedactorPlugins.links = {
	// 
	// Initialize the plugin
	init: function () {
		$.getJSON (
			'/admin/wysiwyg/links',
			$.proxy (
				function (res) {
					this._links = res;
				},
				this
			)
		);
	},
	
	// Initialize the plugin when the button is clicked
	insert: function (self, evt, button) {
		self.insert_link_node = false;
		self.has_selected_text = false;
		var sel = self.selection.get.call (self)
			url = '', text = '', target = '',
			page_matched = false,
			base = window.location.origin
				? window.location.origin
				: window.location.protocol + '//' + window.location.host;

		// Based on Redactor core's link dialog
		if (self.utils.browser.call (self, 'msie')) {
			var parent = self.selection.getElement.call (self);
			if (parent.nodeName === 'A') {
				self.insert_link_node = $(parent);
				text = self.insert_link_node.text ();
				url = self.insert_link_node.attr ('href');
				target = self.insert_link_node.attr ('target');
			} else {
				if (self.utils.oldIE.call (self)) {
					text = sel.text;
				} else {
					text = sel.toString ();
				}
			}
		} else {
			if (sel && sel.anchorNode && sel.anchorNode.parentNode.tagName === 'A') {
				self.insert_link_node = sel.anchorNode.parentNode;
				url = sel.anchorNode.parentNode.href;
				text = sel.anchorNode.parentNode.text;
				target = sel.anchorNode.parentNode.target;
				
				if (sel.toString () === '') {
					self.insert_link_node = sel.anchorNode.parentNode;
				}
			} else {
				text = sel.toString ();
			}
		}
		
		if (text.length !== 0) {
			self.has_selected_text = true;
		}
		
		if (url.search (base) === 0) {
			url = url.replace (base, '');
		}

		self.selection.save.call (self);

		$.open_dialog (
			$.i18n ('Link'),
			'<div class="links-content">' +
				'<p>' +
					'<span class="links-btn" id="links-page-btn">' + $.i18n ('Page') + '</span>' +
					'<span class="links-btn" id="links-url-btn">' + $.i18n ('URL') + '</span>' +
					'<span class="links-btn" id="links-email-btn">' + $.i18n ('Email') + '</span>' +
					'<br />' +
					'<select id="links-page">' +
					'</select>' +
					'<input type="text" id="links-url" size="70" placeholder="http://" />' +
					'<input type="email" id="links-email" size="70" placeholder="you@example.com" />' +
				'</p>' +
				'<p>' +
					$.i18n ('Text') + '<br />' +
					'<input type="text" id="links-text" size="70" />' +
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

		if (self._links) {
			var sel = $('#links-page');
			sel.append (
				$('<option>')
					.attr ('value', '')
					.text ($.i18n ('- select -'))
			);
			for (var i in self._links) {
				if (url === self._links[i].url) {
					sel.append (
						$('<option>')
							.attr ('value', self._links[i].url)
							.text (self._links[i].title)
							.attr ('selected', 'selected')
					);
					page_matched = true;
				} else {
					sel.append (
						$('<option>')
							.attr ('value', self._links[i].url)
							.text (self._links[i].title)
					);
				}
			}
		}

		if (page_matched) {
			self.show_pages ();
		} else if (url.search ('mailto:') === 0) {
			$('#links-email').val (url.replace ('mailto:', ''));
			self.show_email ();
		} else if (url.length !== 0) {
			$('#links-url').val (url);
			self.show_url ();
		} else {
			self.show_pages ();
		}

		$('#links-text').val (text);

		if (target.length !== 0) {
			$('#links-tab').attr ('checked', 'checked');
		}

		$('#links-page-btn').click ($.proxy (self.show_pages, self));
		$('#links-url-btn').click ($.proxy (self.show_url, self));
		$('#links-email-btn').click ($.proxy (self.show_email, self));
		$('#links-cancel').click (function () { $.close_dialog (); });
		$('#links-submit').click ($.proxy (self.handle, self));
	},

	// Insert or update the link
	handle: function () {
		console.log ('handle()');
		console.log (this);
		$(this.editor_id).redactor ('selection.restore');
		
		var active = this.links_active,
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

		if (this.insert_link_node) {
			$(this.insert_link_node).text (text);
			$(this.insert_link_node).attr ('href', href);
			if (target) {
				$(this.insert_link_node).attr ('target', '_blank');
			} else {
				$(this.insert_link_node).removeAttr ('target');
			}
		} else {
			var html = '<a href="' + href + '"';
			if (target) {
				html += ' target="_blank"';
			}
			html += '>' + text + '</a>';
			console.log ('inserting ' + html);
			if (! this.has_selected_text) {
				console.log ('nodeAtCaret');
				$(this.editor_id).redactor ('insert.nodeAtCaret', $(html));
			} else {
				console.log ('inserthtml');
				$(this.editor_id).redactor ('exec.command', 'inserthtml', html);
			}
		}
		this.sync ();

		$.close_dialog ();
	},
	
	// Show the pages list
	show_pages: function () {
		this.links_active = 'page';
		$('#links-page-btn').addClass ('active');
		$('#links-url-btn').removeClass ('active');
		$('#links-email-btn').removeClass ('active');
		$('#links-page').show ();
		$('#links-url').hide ();
		$('#links-email').hide ();
	},
	
	// Show the URL input
	show_url: function () {
		this.links_active = 'url';
		$('#links-page-btn').removeClass ('active');
		$('#links-url-btn').addClass ('active');
		$('#links-email-btn').removeClass ('active');
		$('#links-page').hide ();
		$('#links-url').show ();
		$('#links-email').hide ();
	},
	
	// Show the email input
	show_email: function () {
		this.links_active = 'email';
		$('#links-page-btn').removeClass ('active');
		$('#links-url-btn').removeClass ('active');
		$('#links-email-btn').addClass ('active');
		$('#links-page').hide ();
		$('#links-url').hide ();
		$('#links-email').show ();
	}
};