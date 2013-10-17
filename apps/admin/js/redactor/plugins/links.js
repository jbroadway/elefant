/**
 * Provides a link menu for the Redactor editor, which integrates
 * with Elefant's list of internal pages so a user doesn't have
 * to copy and paste or manually type a link to a page within
 * their own site.
 */

if (typeof RedactorPlugins === 'undefined') var RedactorPlugins = {};

RedactorPlugins.links = {
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
		this.insert_link_node = false;
		this.has_selected_text = false;
		var sel = this.getCurrent (),
			url = '', text = '', target = '',
			page_matched = false,
			base = window.location.origin
				? window.location.origin
				: window.location.protocol + '//' + window.location.host;

		if (sel && sel.anchorNode && sel.anchorNode.parentNode.tagName === 'A') {
			this.insert_link_node = sel.anchorNode.parentNode;
			url = sel.anchorNode.parentNode.href;
			text = sel.anchorNode.parentNode.text;
			target = sel.anchorNode.parentNode.target;
		} else if (sel.tagName && sel.tagName === 'A') {
			this.insert_link_node = sel;
			url = sel.href;
			text = sel.text;
			target = sel.target;
		} else {
			var parent = this.getParent ();
			if (parent.nodeName === 'A') {
				this.insert_link_node = $(parent);
				text = this.insert_link_node.text ();
				url = this.insert_link_node.attr ('href');
				target = this.insert_link_node.attr ('target');
			} else {
				text = this.getSelectionText ();
			}
		}
		
		if (text.length !== 0) {
			this.has_selected_text = true;
		}
		
		if (url.search (base) === 0) {
			url = url.replace (base, '');
		}

		this.selectionSave ();

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
					'<input type="text" id="links-url" size="65" placeholder="http://" />' +
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

		if (this._links) {
			var sel = $('#links-page');
			sel.append (
				$('<option>')
					.attr ('value', '')
					.text ($.i18n ('- select -'))
			);
			for (var i in this._links) {
				if (url === this._links[i].url) {
					sel.append (
						$('<option>')
							.attr ('value', this._links[i].url)
							.text (this._links[i].title)
							.attr ('selected', 'selected')
					);
					page_matched = true;
				} else {
					sel.append (
						$('<option>')
							.attr ('value', this._links[i].url)
							.text (this._links[i].title)
					);
				}
			}
		}

		if (page_matched) {
			this.show_pages ();
		} else if (url.search ('mailto:') === 0) {
			$('#links-email').val (url.replace ('mailto:', ''));
			this.show_email ();
		} else if (url.length !== 0) {
			$('#links-url').val (url);
			this.show_url ();
		} else {
			this.show_pages ();
		}

		$('#links-text').val (text);

		if (target && target.length !== 0) {
			$('#links-tab').attr ('checked', 'checked');
		}

		$('#links-page-btn').click ($.proxy (this.show_pages, this));
		$('#links-url-btn').click ($.proxy (this.show_url, this));
		$('#links-email-btn').click ($.proxy (this.show_email, this));
		$('#links-cancel').click (function () { $.close_dialog (); });
		$('#links-submit').click ($.proxy (this.handle, this));
	},

	// Insert or update the link
	handle: function () {
		this.selectionRestore ();
		
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
			this.insertHtml (html);
		}

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