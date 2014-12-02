/**
 * See `admin/util/search` helper.
 *
 * Usage:
 *
 *     $.search_init ({
 *         form: '#search-form',     // form selector
 *         query: '#search-query',   // query field selector
 *         links: '.search-for',     // selector to modify search via links
 *         options: '.search-option' // selector to modify search via select boxes
 *     });
 *
 *     // add to query and submit
 *     $.search_for ('some value');
 */
(function ($) {
	var opts = {
			form: '#search-form',
			query: '#search-query',
			links: '.search-for',
			options: '.search-option'
		},
		$form = null,
		$query = null;

	if (!String.prototype.trim) {
		String.prototype.trim = function () {
			return this.replace (/^\s+|\s+$/g, '');
		};
	}
	
	$.search_init = function (settings) {
		opts = $.extend (opts, settings);

		$form = $(opts.form);
		$query = $(opts.query);

		$(opts.links).click (function (e) {
			e.preventDefault ();
			
			var search = $(this).data ('search');
			
			$.search_for (search);
		});
		
		$(opts.options).each (function () {
			var $this = $(this),
				prefix = $this.data ('prefix'),
				regex = new RegExp (prefix + ':("[a-z0-9\'\. _-]+"|[a-z0-9\'\._-]+)', 'i'),
				q = $query.val (),
				match = regex.exec (q);

			if (match != null) {
				$this.val (match[1].replace (/(^")|("$)/g, ''));
			}
		});
		
		$(opts.options).change (function (e) {
			e.preventDefault ();
			
			var $this = $(this),
				prefix = $this.data ('prefix'),
				val = $this.find (':selected').val ();

			if (val === '' || val.match (/^[a-z0-9\'\._-]+$/i)) {
				$.search_for (prefix + ':' + val);
			} else {
				$.search_for (prefix + ':"' + val + '"');
			}
		});
	};
	
	$.search_for = function (search) {
		var prefix = search.match (':') ? search.split (':')[0] : false,
			remove_prefix = (search === prefix + ':') ? true : false,
			regex = new RegExp (prefix + ':("[a-z0-9\'\. _-]+"|[a-z0-9\'\._-]+)', 'i'),
			q = $query.val ();

		if (q.length === 0) {
			$query.val (search.trim ()); // first term added
		} else if (! remove_prefix && q.match (search)) {
			return; // already matches
		} else if (prefix) { // searching for exact match
			if (q.match (prefix + ':')) {
				// remove 'prefix:' searches from the query
				search = (remove_prefix) ? '' : search;

				$query.val (q.replace (regex, search).trim ());
			} else {
				$query.val ((q + ' ' + search).trim ());
			}
		} else {
			$query.val ((q + ' ' + search).trim ());
		}
		
		$form.submit ();
	};
})(jQuery);
