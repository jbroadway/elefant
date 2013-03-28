if (typeof RedactorPlugins === 'undefined') var RedactorPlugins = {};

RedactorPlugins.fontsize = {

	init: function()
	{
		var fonts = [10, 11, 12, 14, 16, 18, 20, 24, 28, 30];
		var that = this;
		var dropdown = {};

		$.each(fonts, function(i,s)
		{
			dropdown['s' + i] = { title: s + 'px', callback: function() { that.setFontsize(s); } };
		});

		dropdown['remove'] = { title: 'Remove font size', callback: function() { that.resetFontsize(); } };


		this.button.add.call(this, 'fontsize', 'Change font size', false, dropdown);
	},
	setFontsize: function(size)
	{
		this.inline.setStyle.call(this, 'font-size', size + 'px');
	},
	resetFontsize: function()
	{
		this.inline.removeStyle.call(this, 'font-size');
	}
}