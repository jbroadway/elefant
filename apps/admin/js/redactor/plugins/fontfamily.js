if (typeof RedactorPlugins === 'undefined') var RedactorPlugins = {};

RedactorPlugins.fontfamily = {

	init: function()
	{
		var fonts = [
			'Arial',
			'Helvetica',
			'Georgia',
			'Times New Roman',
			'Monospace'
		];
		var that = this;
		var dropdown = {};

		$.each(fonts, function(i,s)
		{
			dropdown['s' + i] = { title: s, callback: function() { that.setFontfamily(s); } };
		});

		dropdown['remove'] = { title: 'Remove font', callback: function() { that.resetFontfamily(); } };

		this.button.add.call(this, 'fontfamily', 'Change font family', false, dropdown);
	},
	setFontfamily: function(value)
	{
		this.inline.setStyle.call(this, 'font-family', value);
	},
	resetFontfamily: function()
	{
		this.inline.removeStyle.call(this, 'font-family');
	}
}