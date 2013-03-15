if (typeof RedactorPlugins === 'undefined') var RedactorPlugins = {};

RedactorPlugins.fullscreen = {

	init: function()
	{
		this.fullscreen = false;
		this.button.add.call(this, 'fullscreen', 'Fullscreen', $.proxy(function()
		{
			this.toggleFullscreen();
		}, this));

		this.button.setRight.call(this, 'fullscreen');
	},
	toggleFullscreen: function()
	{
		var html;

		if (this.fullscreen === false)
		{
			this.button.changeIcon.call(this, 'fullscreen', 'normalscreen');
			this.button.active.call(this, 'fullscreen');
			this.fullscreen = true;

			if (this.opts.toolbarExternal)
			{
				this.toolcss = {};
				this.boxcss = {};
				this.toolcss.width = this.$toolbar.css('width');
				this.toolcss.top = this.$toolbar.css('top');
				this.toolcss.position = this.$toolbar.css('position');
				this.boxcss.top = this.$box.css('top');
			}

			this.fsheight = this.$editor.height();

			if (this.opts.iframe) var html = this.getCode();

			this.tmpspan = $('<span></span>');
			this.$box.addClass('redactor_box_fullscreen').after(this.tmpspan);

			$('body, html').css('overflow', 'hidden');
			$('body').prepend(this.$box);

			if (this.opts.iframe) this.setCode(html);

			this.fullScreenResize();
			$(window).resize($.proxy(this.fullScreenResize, this));
			$(document).scrollTop(0,0);

			this.$editor.focus();
			this.observe.start.call(this);
		}
		else
		{
			this.button.removeIcon.call(this, 'fullscreen', 'normalscreen');
			this.button.inactive.call(this, 'fullscreen');
			this.fullscreen = false;

			$(window).unbind('resize', $.proxy(this.fullScreenResize, this));
			$('body, html').css('overflow', '');

			this.$box.removeClass('redactor_box_fullscreen').css({ width: 'auto', height: 'auto' });
			this.tmpspan.after(this.$box).remove();

			this.sync();

			var height = this.fsheight;
			if (this.opts.autoresize)
			{
				height = 'auto';
			}

			if (this.opts.toolbarExternal)
			{
				this.$box.css('top', this.boxcss.top);
				this.$toolbar.css({ 'width': this.toolcss.width, 'top': this.toolcss.top, 'position': this.toolcss.position });
			}

			if (this.opts.iframe === false)
			{
				this.$editor.css('height', height)
			}
			else
			{
				this.$frame.css('height', height)
			}

			this.$el.css('height', height);
			this.$editor.focus();
			this.observe.start.call(this);
		}
	},
	fullScreenResize: function()
	{
		if (this.fullscreen === false)
		{
			return false;
		}

		var toolbarHeight = this.$toolbar.height();

		var pad = this.$editor.css('padding-top').replace('px', '');
		var height = $(window).height() - toolbarHeight;
		this.$box.width($(window).width() - 2).height(height+toolbarHeight);

		if (this.opts.toolbarExternal)
		{
			this.$toolbar.css({ 'top': '0px', 'position': 'absolute', 'width': '100%'});
			this.$box.css('top', toolbarHeight + 'px');
		}

		if (this.opts.iframe === false)
		{
			this.$editor.height(height-(pad*2));
		}
		else
		{
			setTimeout($.proxy(function()
			{
				this.$frame.height(height);
			}, this), 1);
		}

		this.$el.height(height);
	}
}