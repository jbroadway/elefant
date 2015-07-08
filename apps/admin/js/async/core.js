window.async = (function($){
	var self = {};
	self.REGEX = new RegExp('^https?://'+ document.domain +'(/.*)', 'i');
	self.container = '#content'; // CSS selector for where to put the returned HTML data
	self.debug = false;
	self.compile = true; // auto include scripts into HTML?
	self._loading = false; // current URI being loaded, internal use
	self.ignore = [
		"/blog/edit",
		"/blog/add",
		"/events/add",
		"/user/logout"
	];
	self.bind = function(){
		function catch_link(e){
			e.preventDefault();
			e.stopImmediatePropagation();
			return async.link(this.href, false);
		}
		$('a:not([class*="editable"]):not(.noasync):not([href^="'+ self.ignore.join('"]):not([href^="') +'"])').on('click', catch_link);
		$('#admin-bar a').off('click', catch_link);
		$('#preview-bar a').off('click', catch_link);
	};
	self.link = function(target, pop_state){
		if (!self.REGEX.test(target) && !pop_state) {
			if (self.debug) console.log('Caught external link.');
			window.open(target,'_blank');
			return false;
		}
		if (self.debug) console.log('Caught internal link.');
		var uri, container = document.querySelector(self.container), $document = $(document);
		if (pop_state) { uri = target; }
		else { uri = self.REGEX.exec(target)[1]; }
		if (self.debug) console.log('URI: '+ uri);
		if (uri != container.dataset['path']){
			self._loading = uri.split('?')[0];
			$document.trigger('async_start');
			$.getJSON('/admin/api/async?page='+ encodeURIComponent(uri)).success(function(res){
				if (self.debug) console.log(res);
				if (res.success) {
					if (self._loading !== res.data.path) return false;
					$document.trigger({type:'async_pre', data:res.data});
					container.innerHTML = (self.compile?res.data.page.head:'') + res.data.html + (self.compile?res.data.page.tail:'');
					$document.trigger({type:'async_post', data:res.data});
					if (self.debug) console.log('Async load successful');
					document.cookie = 'elefant_last_page='+ res.data.path;
					self.bind();
					container.dataset['path'] = uri;
					if (!pop_state) {
						history.pushState({data:uri}, res.data.page.title, uri);
					}
					$('head title').text(res.data.page.title);
					$document.trigger('async_success');
					self._loading = false;
				} else {
					if (self.debug) console.log(res);
					$document.trigger({type:'async_error', data:res});
				}
			}).fail(function(){
				if (self.debug) console.log('Async load unsuccessful');
				$document.trigger('async_fail');
			}).always(function(){
				$document.trigger('async_end');
			});
		}
		return false;
	};
	console.log('Async wrapper loaded.');
	self.bind();
	return self;
}(jQuery));

window.onpopstate = function(e){
	if (e.state) window.async.link(e.state.data, true);
};