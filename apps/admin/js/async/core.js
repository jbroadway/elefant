// Copywrite for this file is inherited from ELefantCMS.

window.async = (function($){
	var self = {};
	self.REGEX = new RegExp('^https?://'+ document.domain +'(/.*)', 'i');
	self.container = '#content'; // CSS selector for where to put the returned HTML data
	self.debug = false;
	self.compile = true; // auto include scripts into HTML?
	// current URI being loaded
	self.load = false; // set to false during the async_start or async_pre events to cancel the page request or DOM injection respectively.
	self.ignore = [
		"/blog/edit",
		"/blog/add",
		"/events/add",
		"/user/logout"
	];
	self.bind = function(container){
		function catch_link(e){
			e.preventDefault();
			e.stopImmediatePropagation();
			return async.link(this.href, false);
		}
		if (!container) css = document;
		$(container).find('a:not([class*="editable"]):not(.noasync):not([href^="'+ self.ignore.join('"]):not([href^="') +'"])').on('click', catch_link);
		$('#admin-bar a').off('click', catch_link);
		$('#preview-bar a').off('click', catch_link);
	};
	self.link = function(target, pop_state){
		var uri, container, $document = $(document);
		if (!self.REGEX.test(target) && !pop_state) {
			$document.trigger('async_out');
			if (self.debug) console.log('Caught external link.');
			window.open(target,'_blank');
			return false;
		}
		if (self.debug) console.log('Caught internal link.');
		$document.trigger('async_in');
		container = document.querySelector(self.container);
		if (pop_state) { uri = target; }
		else { uri = self.REGEX.exec(target)[1]; }
		if (self.debug) console.log('URI: '+ uri);
		if (uri != container.dataset['path']){
			self.load = uri.split('?')[0];
			$document.trigger('async_start');
			if (self.load !== uri.split('?')[0]) return false; // check to make sure the request hasn't been stopped
			$.getJSON('/admin/api/async?page='+ encodeURIComponent(uri)).success(function(res){
				if (self.debug) console.log(res);
				if (res.success) {
					$document.trigger('async_pre', res.data);
					if (self.load !== res.data.path) return false;
					container.innerHTML = (self.compile?res.data.page.head:'') + res.data.html + (self.compile?res.data.page.tail:'');
					var title = res.data.page.window_title || res.data.page._window_title || res.data.page.title;
					document.querySelector('head title').innerText = title;
					$document.trigger('async_post', res.data);
					if (self.debug) console.log('Async load successful');
					document.cookie = 'elefant_last_page='+ res.data.path;
					self.bind(container);
					container.dataset['path'] = uri;
					if (!pop_state) {
						history.pushState({data:uri}, document.querySelector('head title').innerText, uri);
					}
					$document.trigger('async_success');
					self._loading = false;
				} else {
					if (self.debug) console.log(res);
					$document.trigger('async_error', res.error);
				}
			}).fail(function(){
				if (self.debug) console.log('Async load unsuccessful');
				$document.trigger('async_fail');
			}).always(function(){
				$document.trigger('async_end');
			});
			return true; // request was sent successfully
		}
		return false;
	};
	console.log('Async wrapper loaded.');
	$(self.bind);
	return self;
}(jQuery));

window.onpopstate = function(e){
	if (e.state) window.async.link(e.state.data, true);
};