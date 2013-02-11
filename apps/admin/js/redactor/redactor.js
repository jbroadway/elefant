/*
	Redactor v9.0 beta
	Updated: January 31, 2013

	http://redactorjs.com/

	Copyright (c) 2009-2013, Imperavi Inc.
	License: http://imperavi.com/redactor/license/

	Usage: $('#content').redactor();
*/


var rwindow, rdocument;

if (typeof RELANG === 'undefined')
{
	var RELANG = {};
}

var RLANG = {
	html: 'HTML',
	video: 'Insert Video',
	image: 'Insert Image',
	table: 'Table',
	link: 'Link',
	link_insert: 'Insert link',
	unlink: 'Unlink',
	formatting: 'Formatting',
	paragraph: 'Paragraph',
	quote: 'Quote',
	code: 'Code',
	header1: 'Header 1',
	header2: 'Header 2',
	header3: 'Header 3',
	header4: 'Header 4',
	bold:  'Bold',
	italic: 'Italic',
	fontcolor: 'Font Color',
	backcolor: 'Back Color',
	unorderedlist: 'Unordered List',
	orderedlist: 'Ordered List',
	outdent: 'Outdent',
	indent: 'Indent',
	cancel: 'Cancel',
	insert: 'Insert',
	save: 'Save',
	_delete: 'Delete',
	insert_table: 'Insert Table',
	insert_row_above: 'Add Row Above',
	insert_row_below: 'Add Row Below',
	insert_column_left: 'Add Column Left',
	insert_column_right: 'Add Column Right',
	delete_column: 'Delete Column',
	delete_row: 'Delete Row',
	delete_table: 'Delete Table',
	rows: 'Rows',
	columns: 'Columns',
	add_head: 'Add Head',
	delete_head: 'Delete Head',
	title: 'Title',
	image_position: 'Position',
	none: 'None',
	left: 'Left',
	right: 'Right',
	image_web_link: 'Image Web Link',
	text: 'Text',
	mailto: 'Email',
	web: 'URL',
	video_html_code: 'Video Embed Code',
	file: 'Insert File',
	upload: 'Upload',
	download: 'Download',
	choose: 'Choose',
	or_choose: 'Or choose',
	drop_file_here: 'Drop file here',
	align_left:	'Align text to the left',
	align_center: 'Center text',
	align_right: 'Align text to the right',
	align_justify: 'Justify text',
	horizontalrule: 'Insert Horizontal Rule',
	deleted: 'Deleted',
	anchor: 'Anchor',
	link_new_tab: 'Open link in new tab',
	underline: 'Underline',
	alignment: 'Alignment',
	filename: 'Name (optional)'
};

'use strict';
(function($){

	// Plugin
	$.fn.redactor = function(options)
	{
		var val = [];
		var args = Array.prototype.slice.call(arguments, 1);

		if (typeof options === 'string')
		{
			this.each(function()
			{
				var instance = $.data(this, 'redactor');

				if (typeof instance !== 'undefined')
				{
					var arr = options.split('.');
					var func = arr.length == 1 ? instance[options] : instance[arr[0]][arr[1]];

					if ($.isFunction(func))
					{
						var methodVal = func.call(instance, args);
						if (methodVal !== undefined && methodVal !== instance)
						{
							val.push(methodVal);
						}
					}
				}
			});
		}
		else
		{
			this.each(function()
			{
				if (!$.data(this, 'redactor'))
				{
					$.data(this, 'redactor', new Redactor(this, options));
				}
			});
		}

		if (val.length == 0)
		{
			return this;
		}
		else if (val.length == 1)
		{
			return val[0];
		}
		else
		{
			return val;
		}

	};

	// Initialization
	var Redactor = function(el, options)
	{
		this.$element = this.$el = $(el);

		// Lang
		if (typeof options !== 'undefined' && typeof options.lang !== 'undefined' && typeof RELANG[options.lang] !== 'undefined')
		{
			RLANG = $.extend({}, RLANG, RELANG[options.lang]);
		}

		// Options
		this.opts = $.extend({

			iframe: false,
			fullpage: false,
			css: false, // url

			lang: 'en',
			direction: 'ltr', // ltr or rtl

			callback: false, // function
			keyupCallback: false, // function
			keydownCallback: false, // function
			execCommandCallback: false,  // function
			syncCallback: false,  // function

			focus: false,
			tabindex: false,
			autoresize: true,
			minHeight: false,

			plugins: false, // array

			air: false,
			mobile: true,
			wym: false,
			cleanup: true,
			source: true,
			shortcuts: true,

			visual: true,

			placeholder: false,

			linebreaks: false,
			paragraphy: true,
			convertDivs: false,
			convertLinks: true,
			formattingPre: false,

			autosave: false, // false or url
			autosaveCallback: false, // function
			interval: 60, // seconds

			fixed: false,
			fixedTop: 0, // pixels
			fixedBox: false,
			toolbarExternal: false, // ID selector

			linkAnchor: false,
			linkEmail: false,

			imageGetJson: false, // url (ex. /folder/images.json ) or false

			imageUpload: false, // url
			imageUploadCallback: false, // function
			imageUploadErrorCallback: false, // function

			fileUpload: false, // url
			fileUploadCallback: false, // function
			fileUploadErrorCallback: false, // function

			uploadCrossDomain: false,
			uploadFields: false,

			observeImages: true,
			overlay: true, // modal overlay

			allowedTags: false,
			deniedTags: false,
			clearTags: ['script', 'html', 'head', 'title', 'link', 'body', 'style', 'meta'],

			boldTag: 'strong',
			italicTag: 'em',

			buttonsCustom: {},
			buttonsAdd: [],
			buttons: ['html', '|', 'formatting', '|', 'bold', 'italic', 'deleted', 'underline', '|', 'unorderedlist', 'orderedlist', 'outdent', 'indent', '|',
					'image', 'video', 'file', 'table', 'link', '|',
					'fontcolor', 'backcolor', '|', 'alignment', '|', 'horizontalrule'], // 'underline', 'alignleft', 'aligncenter', 'alignright', 'justify'

			airButtons: ['formatting', '|', 'bold', 'italic', 'deleted', '|', 'unorderedlist', 'orderedlist', 'outdent', 'indent', '|', 'fontcolor', 'backcolor'],

			formattingTags: ['p', 'blockquote', 'pre', 'h1', 'h2', 'h3', 'h4'],

			activeButtons: ['deleted', 'italic', 'bold', 'underline', 'unorderedlist', 'orderedlist', 'table'], // 'alignleft', 'aligncenter', 'alignright', 'justify'
			activeButtonsStates: {
				b: 'bold',
				strong: 'bold',
				i: 'italic',
				em: 'italic',
				del: 'deleted',
				strike: 'deleted',
				ul: 'unorderedlist',
				ol: 'orderedlist',
				u: 'underline',
				table: 'table'
			},

			colors: [
				'#ffffff', '#000000', '#eeece1', '#1f497d', '#4f81bd', '#c0504d', '#9bbb59', '#8064a2', '#4bacc6', '#f79646', '#ffff00',
				'#f2f2f2', '#7f7f7f', '#ddd9c3', '#c6d9f0', '#dbe5f1', '#f2dcdb', '#ebf1dd', '#e5e0ec', '#dbeef3', '#fdeada', '#fff2ca',
				'#d8d8d8', '#595959', '#c4bd97', '#8db3e2', '#b8cce4', '#e5b9b7', '#d7e3bc', '#ccc1d9', '#b7dde8', '#fbd5b5', '#ffe694',
				'#bfbfbf', '#3f3f3f', '#938953', '#548dd4', '#95b3d7', '#d99694', '#c3d69b', '#b2a2c7', '#b7dde8', '#fac08f', '#f2c314',
				'#a5a5a5', '#262626', '#494429', '#17365d', '#366092', '#953734', '#76923c', '#5f497a', '#92cddc', '#e36c09', '#c09100',
				'#7f7f7f', '#0c0c0c', '#1d1b10', '#0f243e', '#244061', '#632423', '#4f6128', '#3f3151', '#31859b', '#974806', '#7f6000'],

			// private
			textareamode: false,
			buffer: false,
			emptyHtml: '<p>&#x200b;</p>',
			invisibleSpace: '&#x200b;',
			alignmentTags: ['H1', 'H2', 'H3', 'H4', 'H5', 'H6', 'P', 'TD', 'DIV', 'BLOCKQUOTE'],

			// modal windows container
			modal_file: String() +
				'<div id="redactor_modal_content">' +
				'<form id="redactorUploadFileForm" method="post" action="" enctype="multipart/form-data">' +
					'<label>' + RLANG.filename + '</label>' +
					'<input type="text" id="redactor_filename" class="redactor_input" />' +
					'<div style="margin-top: 7px;">' +
						'<input type="file" id="redactor_file" name="file" />' +
					'</div>' +
				'</form><br>' +
				'</div>',

			modal_image_edit: String() +
				'<div id="redactor_modal_content">' +
				'<label>' + RLANG.title + '</label>' +
				'<input id="redactor_file_alt" class="redactor_input" />' +
				'<label>' + RLANG.link + '</label>' +
				'<input id="redactor_file_link" class="redactor_input" />' +
				'<label>' + RLANG.image_position + '</label>' +
				'<select id="redactor_form_image_align">' +
					'<option value="none">' + RLANG.none + '</option>' +
					'<option value="left">' + RLANG.left + '</option>' +
					'<option value="right">' + RLANG.right + '</option>' +
				'</select>' +
				'</div>' +
				'<div id="redactor_modal_footer">' +
					'<a href="javascript:void(null);" id="redactor_image_delete_btn" class="redactor_modal_btn">' + RLANG._delete + '</a>&nbsp;&nbsp;&nbsp;' +
					'<a href="javascript:void(null);" class="redactor_modal_btn redactor_btn_modal_close">' + RLANG.cancel + '</a>' +
					'<input type="button" name="save" class="redactor_modal_btn" id="redactorSaveBtn" value="' + RLANG.save + '" />' +
				'</div>',

			modal_image: String() +
				'<div id="redactor_modal_content">' +
				'<div id="redactor_tabs">' +
					'<a href="javascript:void(null);" class="redactor_tabs_act">' + RLANG.upload + '</a>' +
					'<a href="javascript:void(null);">' + RLANG.choose + '</a>' +
					'<a href="javascript:void(null);">' + RLANG.link + '</a>' +
				'</div>' +
				'<form id="redactorInsertImageForm" method="post" action="" enctype="multipart/form-data">' +
					'<div id="redactor_tab1" class="redactor_tab">' +
						'<input type="file" id="redactor_file" name="file" />' +
					'</div>' +
					'<div id="redactor_tab2" class="redactor_tab" style="display: none;">' +
						'<div id="redactor_image_box"></div>' +
					'</div>' +
				'</form>' +
				'<div id="redactor_tab3" class="redactor_tab" style="display: none;">' +
					'<label>' + RLANG.image_web_link + '</label>' +
					'<input type="text" name="redactor_file_link" id="redactor_file_link" class="redactor_input"  />' +
				'</div>' +
				'</div>' +
				'<div id="redactor_modal_footer">' +
					'<a href="javascript:void(null);" class="redactor_modal_btn redactor_btn_modal_close">' + RLANG.cancel + '</a>' +
					'<input type="button" name="upload" class="redactor_modal_btn" id="redactor_upload_btn" value="' + RLANG.insert + '" />' +
				'</div>',

			modal_link: String() +
				'<div id="redactor_modal_content">' +
				'<form id="redactorInsertLinkForm" method="post" action="">' +
					'<div id="redactor_tabs">' +
						'<a href="javascript:void(null);" class="redactor_tabs_act">URL</a>' +
						'<a href="javascript:void(null);">Email</a>' +
						'<a href="javascript:void(null);">' + RLANG.anchor + '</a>' +
					'</div>' +
					'<input type="hidden" id="redactor_tab_selected" value="1" />' +
					'<div class="redactor_tab" id="redactor_tab1">' +
						'<label>URL</label><input type="text" id="redactor_link_url" class="redactor_input"  />' +
						'<label>' + RLANG.text + '</label><input type="text" class="redactor_input redactor_link_text" id="redactor_link_url_text" />' +
						'<label><input type="checkbox" id="redactor_link_blank"> ' + RLANG.link_new_tab + '</label>' +
					'</div>' +
					'<div class="redactor_tab" id="redactor_tab2" style="display: none;">' +
						'<label>Email</label><input type="text" id="redactor_link_mailto" class="redactor_input" />' +
						'<label>' + RLANG.text + '</label><input type="text" class="redactor_input redactor_link_text" id="redactor_link_mailto_text" />' +
					'</div>' +
					'<div class="redactor_tab" id="redactor_tab3" style="display: none;">' +
						'<label>' + RLANG.anchor + '</label><input type="text" class="redactor_input" id="redactor_link_anchor"  />' +
						'<label>' + RLANG.text + '</label><input type="text" class="redactor_input redactor_link_text" id="redactor_link_anchor_text" />' +
					'</div>' +
				'</form>' +
				'</div>' +
				'<div id="redactor_modal_footer">' +
					'<a href="javascript:void(null);" class="redactor_modal_btn redactor_btn_modal_close">' + RLANG.cancel + '</a>' +
					'<input type="button" class="redactor_modal_btn" id="redactor_insert_link_btn" value="' + RLANG.insert + '" />' +
				'</div>',

			modal_table: String() +
				'<div id="redactor_modal_content">' +
					'<label>' + RLANG.rows + '</label>' +
					'<input type="text" size="5" value="2" id="redactor_table_rows" />' +
					'<label>' + RLANG.columns + '</label>' +
					'<input type="text" size="5" value="3" id="redactor_table_columns" />' +
				'</div>' +
				'<div id="redactor_modal_footer">' +
					'<a href="javascript:void(null);" class="redactor_modal_btn redactor_btn_modal_close">' + RLANG.cancel + '</a>' +
					'<input type="button" name="upload" class="redactor_modal_btn" id="redactor_insert_table_btn" value="' + RLANG.insert + '" />' +
				'</div>',

			modal_video: String() +
				'<div id="redactor_modal_content">' +
				'<form id="redactorInsertVideoForm">' +
					'<label>' + RLANG.video_html_code + '</label>' +
					'<textarea id="redactor_insert_video_area" style="width: 99%; height: 160px;"></textarea>' +
				'</form>' +
				'</div>'+
				'<div id="redactor_modal_footer">' +
					'<a href="javascript:void(null);" class="redactor_modal_btn redactor_btn_modal_close">' + RLANG.cancel + '</a>' +
					'<input type="button" class="redactor_modal_btn" id="redactor_insert_video_btn" value="' + RLANG.insert + '" />' +
				'</div>',



			toolbar: {
				html:
				{
					title: RLANG.html,
					func: 'toggle'
				},
				formatting:
				{
					title: RLANG.formatting,
					func: 'show',
					dropdown:
					{
						p:
						{
							title: RLANG.paragraph,
							func: 'format.blocks'
						},
						blockquote:
						{
							title: RLANG.quote,
							func: 'quote.toggle',
							className: 'redactor_format_blockquote'
						},
						pre:
						{
							title: RLANG.code,
							func: 'format.blocks',
							className: 'redactor_format_pre'
						},
						h1:
						{
							title: RLANG.header1,
							func: 'format.blocks',
							className: 'redactor_format_h1'
						},
						h2:
						{
							title: RLANG.header2,
							func: 'format.blocks',
							className: 'redactor_format_h2'
						},
						h3:
						{
							title: RLANG.header3,
							func: 'format.blocks',
							className: 'redactor_format_h3'
						},
						h4:
						{
							title: RLANG.header4,
							func: 'format.blocks',
							className: 'redactor_format_h4'
						}
					}
				},
				bold:
				{
					title: RLANG.bold,
					exec: 'bold'
				},
				italic:
				{
					title: RLANG.italic,
					exec: 'italic'
				},
				deleted:
				{
					title: RLANG.deleted,
					exec: 'strikethrough'
				},
				underline:
				{
					title: RLANG.underline,
					exec: 'underline'
				},
				unorderedlist:
				{
					title: '&bull; ' + RLANG.unorderedlist,
					exec: 'insertunorderedlist'
				},
				orderedlist:
				{
					title: '1. ' + RLANG.orderedlist,
					exec: 'insertorderedlist'
				},
				outdent:
				{
					title: '< ' + RLANG.outdent,
					func: 'indenting.outdent'
				},
				indent:
				{
					title: '> ' + RLANG.indent,
					func: 'indenting.indent'
				},
				image:
				{
					title: RLANG.image,
					func: 'image.show'
				},
				video:
				{
					title: RLANG.video,
					func: 'video.show'
				},
				file:
				{
					title: RLANG.file,
					func: 'file.show'
				},
				table:
				{
					title: RLANG.table,
					func: 'show',
					dropdown:
					{
						insert_table:
						{
							title: RLANG.insert_table,
							func: 'table.show'
						},
						separator_drop1:
						{
							name: 'separator'
						},
						insert_row_above:
						{
							title: RLANG.insert_row_above,
							func: 'table.insertRowAbove'
						},
						insert_row_below:
						{
							title: RLANG.insert_row_below,
							func: 'table.insertRowBelow'
						},
						insert_column_left:
						{
							title: RLANG.insert_column_left,
							func: 'table.insertColumnLeft'
						},
						insert_column_right:
						{
							title: RLANG.insert_column_right,
							func: 'table.insertColumnRight'
						},
						separator_drop2:
						{
							name: 'separator'
						},
						add_head:
						{
							title: RLANG.add_head,
							func: 'table.addHead'
						},
						delete_head:
						{
							title: RLANG.delete_head,
							func: 'table.deleteHead'
						},
						separator_drop3:
						{
							name: 'separator'
						},
						delete_column:
						{
							title: RLANG.delete_column,
							func: 'table.deleteColumn'
						},
						delete_row:
						{
							title: RLANG.delete_row,
							func: 'table.deleteRow'
						},
						delete_table:
						{
							title: RLANG.delete_table,
							func: 'table.deleteTable'
						}
					}
				},
				link:
				{
					title: RLANG.link,
					func: 'show',
					dropdown:
					{
						link:
						{
							title: RLANG.link_insert,
							func: 'link.show'
						},
						unlink:
						{
							title: RLANG.unlink,
							exec: 'unlink'
						}
					}
				},
				fontcolor:
				{
					title: RLANG.fontcolor,
					func: 'show'
				},
				backcolor:
				{
					title: RLANG.backcolor,
					func: 'show'
				},
				alignment:
				{
					title: RLANG.alignment,
					func: 'show',
					dropdown:
					{
						alignleft:
						{
							title: RLANG.align_left,
							func: 'alignment.left'
						},
						aligncenter:
						{
							title: RLANG.align_center,
							func: 'alignment.center'
						},
						alignright:
						{
							title: RLANG.align_right,
							func: 'alignment.right'
						},
						justify:
						{
							title: RLANG.align_justify,
							func: 'alignment.justify'
						}
					}
				},
				alignleft:
				{
					title: RLANG.align_left,
					func: 'alignment.left'
				},
				aligncenter:
				{
					title: RLANG.align_center,
					func: 'alignment.center'
				},
				alignright:
				{
					title: RLANG.align_right,
					func: 'alignment.right'
				},
				justify:
				{
					title: RLANG.align_justify,
					func: 'alignment.justify'
				},
				horizontalrule:
				{
					exec: 'inserthorizontalrule',
					title: RLANG.horizontalrule
				}
			}

		}, options, this.$el.data());

		this.dropdowns = [];

		// Init
		this.init();
	};

	// Functionality
	Redactor.prototype = {

		// Initialization
		init: function()
		{
			// get dimensions
			this.height = this.$el.css('height');
			this.width = this.$el.css('width');

			this.codeactions = false;

			rdocument = this.document = document;
			rwindow = this.window = window;

			if (this.opts.fullpage) this.opts.iframe = true;
			if (this.opts.iframe) this.opts.autoresize = false;

			// setup formatting permissions
			if (this.opts.linebreaks === false)
			{
				if (this.opts.allowedTags !== false && $.inArray('p', this.opts.allowedTags) == '-1')
				{
					this.opts.allowedTags.push('p');
				}

				if (this.opts.deniedTags !== false)
				{
					var pos = $.inArray('p', this.opts.deniedTags);
					if (pos != '-1')
					{
						this.opts.deniedTags.splice(pos, pos);
					}
				}
			}

			// Build
			this.build.start.call(this);

		},

		afterBuild: function()
		{
			// extend buttons
			if (this.opts.air)
			{
				this.opts.buttons = this.opts.airButtons;
			}
			else if (this.opts.toolbar !== false)
			{
				if (this.opts.source === false)
				{
					var index = this.opts.buttons.indexOf('html');
					var next = this.opts.buttons[index+1];
					this.opts.buttons.splice(index, 1);
					if (typeof next !== 'undefined' && next === '|')
					{
						this.opts.buttons.splice(index, 1);
					}
				}

				$.extend(this.opts.toolbar, this.opts.buttonsCustom);
				$.each(this.opts.buttonsAdd, $.proxy(function(i,s)
				{
					this.opts.buttons.push(s);

				}, this));
			}

			// formatting tags
			if (this.opts.toolbar !== false)
			{
				if (this.opts.linebreaks)
				{
					var num = $.inArray('p', this.opts.formattingTags);
					if (num === 0)
					{
						delete this.opts.formattingTags[0];
					}
				}

				$.each(this.opts.toolbar.formatting.dropdown, $.proxy(function(i,s)
				{
					if ($.inArray(i, this.opts.formattingTags) == '-1')
					{
						delete this.opts.toolbar.formatting.dropdown[i];
					}

				}, this));
			}

			// air enable
			this.air.enable.call(this);

			// toolbar
			this.toolbar.build.call(this);

			// PLUGINS
			if (typeof this.opts.plugins === 'object')
			{
				$.each(this.opts.plugins, $.proxy(function(i,s)
				{
					if (typeof RedactorPlugins[s] !== 'undefined')
					{
						$.extend(this, RedactorPlugins[s]);

						if (typeof RedactorPlugins[s].init !== 'undefined')
						{
							this.init();
						}
					}

				}, this));
			}

			// buttons response
			if (this.opts.activeButtons !== false && this.opts.toolbar !== false)
			{
				this.$editor.on('click.redactor keyup.redactor', $.proxy(this.observe.formatting, this));
			}

			// events
			if (this.opts.linebreaks === true)
			{
				this.$editor.on("keyup.redactor mouseup.redactor", $.proxy(function()
				{
					var that = this.$editor[0];
					if (this.utils.oldIE.call(this) === false && (!that.lastChild || that.lastChild.nodeName.toLowerCase() != "br"))
					{
						that.appendChild(document.createElement("br"));
					}
				}, this));
			}

			// paste
			var oldsafari = false;
			if (this.utils.browser.call(this, 'webkit') && navigator.userAgent.indexOf('Chrome') === -1)
			{
				var arr = this.utils.browser.call(this, 'version').split('.');
				if (arr[0] < 536)
				{
					oldsafari = true;
				}
			}

			if (this.utils.isMobile.call(this, true) === false && oldsafari === false)
			{
				this.$editor.bind('paste.redactor', $.proxy(function(e)
				{
					if (this.opts.cleanup === false)
					{
						return true;
					}

					this.pasteRunning = true;

					this.selection.save.call(this);

					if (this.opts.autoresize === true)
					{
						this.$editor.height(this.$editor.height());
						this.saveScroll = this.document.body.scrollTop;
					}
					else
					{
						this.saveScroll = this.$editor.scrollTop();
					}

					var frag = this.utils.extractContent.call(this);

					setTimeout($.proxy(function()
					{
						var pastedFrag = this.utils.extractContent.call(this);
						this.$editor.append(frag);

						this.selection.restore.call(this);

						var html = this.utils.getFragmentHtml.call(this, pastedFrag);
						this.paste.clean.call(this, html);
						this.pasteRunning = false;

						if (this.opts.autoresize === true)
						{
							this.$editor.css('height', 'auto');
						}

					}, this), 1);

				}, this));
			}

			// formatting
			this.$editor.on('keydown.redactor', $.proxy(function(e)
			{
				var key = e.which;
				var parent = this.selection.getParent.call(this);
				var current = this.selection.getElement.call(this);
				var pre = false;
				var ctrl = e.ctrlKey || e.metaKey;


				// callback keydown
				if (typeof this.opts.keydownCallback === 'function')
				{
					this.opts.keydownCallback(this, e);
				}

				//if (this.opts.linebreaks === false and this.utils.
				//console.log(parent);

				// down
				if ((parent && $(parent).get(0).tagName === 'PRE') || (current && $(current).get(0).tagName === 'PRE'))
				{
					pre = true;

					if (key === 40)
					{
						this.format.insertAfterLastElement.call(this, current);
					}

				}

				if (parent && $(parent).get(0).tagName === 'BLOCKQUOTE')
				{
					if (key === 40)
					{
						this.format.insertAfterLastElement.call(this, parent);
					}
				}

				if (current && $(current).get(0).tagName === 'BLOCKQUOTE')
				{
					if (key === 40)
					{
						this.format.insertAfterLastElement.call(this, current);
					}
				}

				// Enter pre
				if (pre === true && key === 13)
				{
					this.buffer.set.call(this);
					e.preventDefault();

					var html = $(current).parent().text();
					this.insert.nodeAtCaret.call(this, document.createTextNode('\n'));
					if (html.search(/\s$/) == -1)
					{
						this.insert.nodeAtCaret.call(this, document.createTextNode('\n'));
					}

					this.sync();

					return false;
				}
				else if (key === 13 && !e.shiftKey && !e.ctrlKey && !e.metaKey) // Enter
				{
					this.buffer.set.call(this);

					var element = this.selection.getNode.call(this);

					// Inserting br on Enter
					if ($(element).closest('h1, h2, h3, h4, h5, h6, ol, ul, li, p, td', this.$editor[0]).size() == 0 && !this.utils.browser.call(this, 'mozilla'))
					{
						e.preventDefault();
						this.format.insertLineBreak.call(this);
						return false;
					}
					else
					{
						// Native line break for blocks elements h1, h2, h3, h4, h5, h6, ol, ul, li, p, td
						setTimeout($.proxy(this.format.newLine, this), 1);
					}
				}
				else if (key === 13 && (e.ctrlKey || e.shiftKey)) // Shift+Enter or Ctrl+Enter
				{
					this.buffer.set.call(this);

					e.preventDefault();
					this.format.insertLineBreak.call(this);
				}

				// SHORCTCUTS
				if (ctrl && this.opts.shortcuts)
				{
					this.shortcuts.set.call(this, e, key);
				}

				// Tab
				if (key === 9 && this.opts.shortcuts)
				{
					e.preventDefault();

					if (pre === true && !e.shiftKey)
					{
						this.buffer.set.call(this);
						this.insert.nodeAtCaret.call(this, document.createTextNode('\t'));
						this.sync();
						return false;
					}
					else
					{
						if (!e.shiftKey)
						{
							this.indenting.indent.call(this);
							return false;
						}
						else
						{
							this.indenting.outdent.call(this);
							return false;
						}
					}
				}

			}, this));

			this.$editor.on('keyup.redactor', $.proxy(function(e)
			{
				var key = e.which;

				// convert links
				if (this.opts.convertLinks && key === 13)
				{
					this.$editor.linkify();
				}

				// callback as you type
				if (typeof this.opts.keyupCallback === 'function')
				{
					this.opts.keyupCallback(this, e);
				}

				// if empty
				if (this.opts.linebreaks === false && (key === 8 || key === 46))
				{
					return this.format.empty.call(this, e);
				}

				this.sync();

			}, this));

			// autosave
			if (this.opts.autosave !== false)
			{
				this.autosave();
			}

			// observers
			setTimeout($.proxy(this.observe.start, this), 1);

			// FF fix
			if (this.utils.browser.call(this, 'mozilla'))
			{
				try
				{
					this.document.execCommand('enableObjectResizing', false, false);
					this.document.execCommand('enableInlineTableEditing', false, false);
				}
				catch (e) {}
			}

			// focus
			if (this.opts.focus)
			{
				setTimeout($.proxy(this.focus.set, this), 100);
			}

			// fixed
			if (this.opts.fixed)
			{
				this.observe.scroll.call(this);
				$(document).scroll($.proxy(this.observe.scroll, this));
			}

			// code mode
			if (this.opts.visual === false)
			{
				setTimeout($.proxy(function()
				{
					this.opts.visual = true;
					this.toggle();

				}, this), 200);
			}

			// callback
			if (typeof this.opts.callback === 'function')
			{
				this.opts.callback(this);
			}

			if (this.opts.toolbar !== false)
			{
				this.$toolbar.find('a').attr('tabindex', '-1');
			}

		},

		// SHORTCUTS
		shortcuts:
		{
			set: function(e, key)
			{
				if (key === 90)
				{
					if (this.opts.buffer !== false)
					{
						e.preventDefault();
						this.buffer.get.call(this);
					}
					else if (e.shiftKey) this.shortcuts.load.call(this, e, 'redo'); // Ctrl + Shift + z
					else this.shortcuts.load.call(this, e, 'undo'); // Ctrl + z
				}
				else if (key === 77) this.shortcuts.load.call(this, e, 'removeFormat'); // Ctrl + m
				else if (key === 66) this.shortcuts.load.call(this, e, 'bold'); // Ctrl + b
				else if (key === 73) this.shortcuts.load.call(this, e, 'italic'); // Ctrl + i
				else if (key === 74) this.shortcuts.load.call(this, e, 'insertunorderedlist'); // Ctrl + j
				else if (key === 75) this.shortcuts.load.call(this, e, 'insertorderedlist'); // Ctrl + k
				else if (key === 76) this.shortcuts.load.call(this, e, 'superscript'); // Ctrl + l
				else if (key === 72) this.shortcuts.load.call(this, e, 'subscript'); // Ctrl + h

			},
			load: function(e, cmd)
			{
				e.preventDefault();
				this.exec.command.call(this, cmd, false);
			}
		},

		// BUILD
		build:
		{
			start: function()
			{
				// content
				this.content = '';

				// container
				this.$box = $('<div class="redactor_box"></div>');

				// air box
				if (this.opts.air)
				{
					this.$air = $('<div class="redactor_air" style="display: none;"></div>');
				}

				// mobile
				if (this.utils.oldIE.call(this) || (this.opts.mobile === false && this.utils.isMobile.call(this) === true))
				{
					if (this.$el.get(0).tagName === 'TEXTAREA')
					{
						this.$box.insertAfter(this.$el).append(this.$el);
					}
					else
					{
						this.$editor = this.$el;
						this.$el = $('<textarea name="' + this.$editor.attr('id') + '"></textarea>').css('height', this.height).val($.trim(this.$editor.html()));
						this.$editor.hide();
						this.$box.insertAfter(this.$editor).append(this.$el);
					}

					return true;
				}

				// build
				if (this.opts.iframe)
				{
					this.iframe.start.call(this);
				}
				else if (this.$el.get(0).tagName === 'TEXTAREA')
				{
					this.opts.textrareamode = true;
					this.build.textarea.call(this);
				}
				else
				{
					this.build.element.call(this);
				}

				if (!this.opts.iframe)
				{
					this.build.options.call(this);
					this.afterBuild.call(this);
				}
			},
			textarea: function()
			{
				this.$editor = $('<div></div>');
				this.build.addClasses.call(this, this.$editor);
				this.content = $.trim(this.$el.val());
				this.$el.attr('dir', this.opts.direction);
				this.$box.insertAfter(this.$el).append(this.$editor).append(this.$el);

				this.build.enable.call(this);
			},
			element: function()
			{
				this.$editor = this.$el;
				this.$el = $('<textarea name="' + this.$editor.attr('id') + '"></textarea>').attr('dir', this.opts.direction).css('height', this.height);
				this.content = $.trim(this.$editor.html());
				this.$box.insertAfter(this.$editor).append(this.$editor).append(this.$el);

				this.build.enable.call(this);
			},
			addClasses: function(el)
			{
				var classlist = this.$el.get(0).className.split(/\s+/);
				$.each(classlist, function(i,s)
				{
					el.addClass('redactor_' + s);
				});
			},
			enable: function()
			{
				this.$editor.addClass('redactor_editor').attr('contenteditable', true).attr('dir', this.opts.direction);
				this.$el.hide();

				this.build.clean.call(this);
				this.placeholder.start.call(this);
				this.code.set.call(this, this.content, false);
			},
			options: function()
			{
				var $el = this.$editor;

				if (this.opts.iframe) $el = this.$frame;
				if (this.opts.tabindex) $el.attr('tabindex', this.opts.tabindex);
				if (this.opts.minHeight) $el.css('min-height', this.opts.minHeight + 'px');
				if (this.opts.wym) this.$editor.addClass('redactor_editor_wym');
				if (!this.opts.autoresize) $el.css('height', this.height);
			},
			clean: function(html)
			{
				if (typeof html === 'undefined') html = this.content;

				html = this.clean.savePreCode.call(this, html);

				if (!this.opts.fullpage) html = this.clean.stripTags.call(this, html, false);
				if (this.opts.paragraphy) html = this.clean.paragraphy.call(this, html);
				if (this.opts.linebreaks) html = html.replace(/\n/g, '<br>');

				return this.content = html;
			}
		},

		// IFRAME
		iframe:
		{
			start: function()
			{
				this.iframe.create.call(this);

				if (this.$el.get(0).tagName === 'TEXTAREA')
				{
					this.opts.textrareamode = true;
					this.content = $.trim(this.$el.val());
					this.$el.attr('dir', this.opts.direction);
					this.iframe.append.call(this, this.$el);
				}
				else
				{
					this.$elold = this.$el.hide();
					this.$el = $('<textarea name="' + this.$elold.attr('id') + '"></textarea>').attr('dir', this.opts.direction).css('height', this.height);
					this.content = $.trim(this.$elold.html());
					this.iframe.append.call(this, this.$elold);
				}

				if (this.opts.fullpage)
				{
					this.iframe.page.call(this);
					if (this.content === '') this.content = this.opts.emptyHtml;
					this.$frame.contents()[0].write(this.content);
				}

				this.iframe.load.call(this, true);
			},
			append: function(el)
			{
				this.$box.insertAfter(el).append(this.$frame).append(this.$el);
				this.$el.hide();
			},
			create: function()
			{
				this.$frame = null;
				this.initbuild = true;
				var that = this;
				this.$frame = $('<iframe style="width: 100%;" frameborder="0">').load(function()
				{
					that.iframe.load.call(that);
				});
			},
			page: function()
			{
				var frame = this.$frame[0];
				var doc = frame.contentDocument || frame.contentWindow.document;
				if (doc.documentElement) doc.removeChild(doc.documentElement);

				return doc;
			},
			load: function(afterbuild)
			{
				this.$editor = this.$frame.contents().find("body").attr('contenteditable', true).attr('dir', this.opts.direction);
				if (this.$editor[0])
				{
					rdocument = this.document = this.$editor[0].ownerDocument;
					rwindow = this.window = this.document.defaultView || window;
				}

				var time = 1;
				if (this.utils.browser.call(this, 'msie')) time = 10;

				// iframe css
				if (this.opts.css !== false)
				{
					setTimeout($.proxy(function()
					{
						this.$frame.contents().find('head').append('<link rel="stylesheet" href="' + this.opts.css + '" />');
					}, this), time);
				}

				if (this.opts.fullpage)
				{
					setTimeout($.proxy(function()
					{
						this.content = this.build.clean.call(this, this.$editor.html());
						this.placeholder.start.call(this);
						this.$editor.html(this.content);
						this.sync();

					}, this), 10);
				}
				else
				{
					if (this.initbuild) this.build.clean.call(this);
					this.placeholder.start.call(this);
					this.code.set.call(this, this.content, false);
				}

				setTimeout($.proxy(this.build.options, this), time);
				this.initbuild = false;

				if (afterbuild === true) this.afterBuild.call(this);
			}
		},

		// PLACEHOLDER
		placeholder:
		{
			start: function()
			{
				if (this.$element.attr('placeholder'))
				{
					this.opts.placeholder = this.$element.attr('placeholder');
				}

				var html = this.content.replace(/&#x200b;|<br>|<br \/>/gi, '');

				if (html !== '' && html !== '<p></p>') this.opts.placeholder = false;
				if (this.opts.placeholder === false || this.opts.placeholder === '') return false;

				this.opts.focus = false;
				this.content = $('<span class="redactor_placeholder">').attr('contenteditable', false).text(this.opts.placeholder);

				if (this.opts.linebreaks === false)
				{
					this.content = $('<p>').append(this.content);
				}

				this.content = this.utils.outerHtml.call(this, this.content);

				this.$editor.one('focus.redactor_placeholder', $.proxy(this.placeholder.focus, this));
				this.$editor.one('blur.redactor_placeholder', $.proxy(this.placeholder.blur, this));
			},
			focus: function()
			{
				var ph = this.$editor.find('.redactor_placeholder')
				if (this.opts.linebreaks === false && ph.size() != 0)
				{
					var parent = ph.parent();
					parent.html($('<br>'));
					this.$editor.focus();
					this.selection.start.call(this, parent);
				}
				else
				{
					this.format.replaceLineBreak.call(this, ph);
				}

				this.sync();

				this.$editor.one('blur.redactor_placeholder', $.proxy(this.placeholder.blur, this));
				this.$editor.off('focus.redactor_placeholder');
			},
			blur: function()
			{
				var html = this.code.get.call(this);

				html = html.replace(/<br>|<br \/>/gi, '');
				if (html === '' || html === '<p></p>')
				{
					var ph = $('<span class="redactor_placeholder">').attr('contenteditable', false).text(this.opts.placeholder);

					if (this.opts.linebreaks === false) ph = $('<p>').append(ph);

					this.code.set.call(this, this.utils.outerHtml.call(this, ph));
					this.$editor.one('focus.redactor_placeholder', $.proxy(this.placeholder.focus, this));
					this.$editor.off('blur.redactor_placeholder');
				}
			},
			remove: function(html, mode, events)
			{
				if (this.opts.placeholder !== false)
				{
					if (events !== false)
					{
						this.$editor.off('focus.redactor_placeholder');
						this.$editor.off('blur.redactor_placeholder');
					}

					var ph = '<span class="redactor_placeholder" contenteditable="false">' + this.opts.placeholder + '</span>';
					if (this.opts.linebreaks === false && html == '<p>' + ph + '</p>')
					{
						if (mode !== false) html = this.opts.emptyHtml;
						else html = '';

					}
					else if (html == ph)
					{
						if (mode !== false) html = '<br />';
						else html = '';
					}
				}

				return html;
			}
		},

		// DESTROY
		destroy: function()
		{
			if (!this.$editor) return false;

			$(this.window).unbind('.redactor');
			this.$editor.unbind('.redactor');
			this.$editor.unbind('.redactor_placeholder');
			this.$editor.removeData('redactor');

			var html = this.code.get.call(this);

			html = this.placeholder.remove.call(this, html, false);

			if (this.opts.textrareamode)
			{
				this.$box.after(this.$el);
				this.$box.remove();
				this.$el.height(this.height).val(html).show();
			}
			else
			{
				this.$box.after(this.$editor);
				this.$box.remove();
				this.$editor.removeClass('redactor_editor').removeClass('redactor_editor_wym').attr('contenteditable', false).html(html).show();
			}

			if (this.opts.toolbarExternal)
			{
				$(this.opts.toolbarExternal).empty();
			}

			$('.redactor_air').remove();
			if (typeof this.$frame !== 'undefined')
			{
				this.$frame.remove();
			}

			for (var i = 0; i < this.dropdowns.length; i++)
			{
				this.dropdowns[i].remove();
				delete(this.dropdowns[i]);
			}

			if (this.opts.autosave !== false)
			{
				clearInterval(this.autosaveInterval);
			}

			this.$editor = null;

		},

		// BUFFER
		buffer:
		{
			set: function()
			{
				this.selection.saveDynamic.call(this);
				this.opts.buffer = this.$editor.html();
			},
			get: function()
			{
				if (this.opts.buffer === false) return false;
				this.$editor.html(this.opts.buffer);
				this.selection.restoreDynamic.call(this);
				setTimeout($.proxy(this.observe.start, this), 1);
				this.opts.buffer = false;
			}
		},

		// OBSERVERS
		observe:
		{
			start: function()
			{
				this.observe.images.call(this);
				this.observe.tables.call(this);
			},
			formatting: function()
			{
				var parent = this.selection.getElement.call(this);

				this.button.inactiveAll.call(this);

				$.each(this.opts.activeButtonsStates, $.proxy(function(i,s)
				{
					if ($(parent).closest(i, this.$editor[0]).length != 0)
					{
						this.button.active.call(this, s);
					}

				}, this));

				var tag = $(parent).closest(['p', 'div', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'blockquote', 'td']);

				if (typeof tag[0] !== 'undefined' && typeof tag[0].elem !== 'undefined' && $(tag[0].elem).size() != 0)
				{
					var align = $(tag[0].elem).css('text-align');

					switch (align)
					{
						case 'right':
							this.button.active.call(this, 'alignright');
						break;
						case 'center':
							this.button.active.call(this, 'aligncenter');
						break;
						case 'justify':
							this.button.active.call(this, 'justify');
						break;
						default:
							this.button.active.call(this, 'alignleft');
						break;
					}
				}
			},
			images: function()
			{
				if (this.opts.observeImages === false)
				{
					return false;
				}

				this.$editor.find('img').each($.proxy(function(i,s)
				{
					if (this.utils.browser.call(this, 'msie'))
					{
						$(s).attr('unselectable', 'on');
					}

					this.image.resize.call(this, s);

				}, this));

			},
			tables: function()
			{
				this.$editor.find('table').click($.proxy(this.table.observer, this));
			},
			scroll: function()
			{
				var scrolltop = $(this.document).scrollTop();
				var boxtop = this.$box.offset().top;
				var left = 0;

				if (scrolltop > boxtop)
				{
					var width = '100%';
					if (this.opts.fixedBox)
					{
						left = this.$box.offset().left;
						width = this.$box.innerWidth();
					}

					this.fixed = true;
					this.$toolbar.css({ position: 'fixed', width: width, zIndex: 1005, top: this.opts.fixedTop + 'px', left: left });
				}
				else
				{
					this.fixed = false;
					this.$toolbar.css({ position: 'relative', width: 'auto', zIndex: 1, top: 0, left: left });
				}
			}
		},

		// AUTOSAVE
		autosave: function()
		{
			this.autosaveInterval = setInterval($.proxy(function()
			{
				$.ajax({
					url: this.opts.autosave,
					type: 'post',
					data: this.$el.attr('name') + '=' + escape(encodeURIComponent(this.code.get.call(this))),
					success: $.proxy(function(data)
					{
						// callback
						if (typeof this.opts.autosaveCallback === 'function')
						{
							this.opts.autosaveCallback(data, this);
						}

					}, this)
				});


			}, this), this.opts.interval*1000);
		},

		// AIR
		air:
		{
			enable: function()
			{
				if (this.opts.air === false)
				{
					return false;
				}

				this.$air.hide();

				this.$editor.on('textselect.redactor', $.proxy(function(e)
				{
					this.air.show.call(this, e);

				}, this));

				this.$editor.on('textunselect.redactor', $.proxy(function()
				{
					this.$air.hide();

				}, this));

			},
			show: function(e)
			{
				$('.redactor_air').hide();

				var width = this.$air.innerWidth();
				var left = e.clientX;

				if ($(this.document).width() < (left + width))
				{
					left = left - width;
				}

				var top = e.clientY + $(this.document).scrollTop() + 14;
				if (this.opts.iframe === true)
				{
					top = top + this.$box.position().top;
					left = left + this.$box.position().left;
				}

				this.$air.css({ left: left + 'px', top: top + 'px' }).show();
			}
		},

		// TOOLBAR
		toolbar:
		{
			build: function()
			{
				if (this.opts.toolbar === false) return false;

				this.$toolbar = $('<ul>').addClass('redactor_toolbar');

				if (this.opts.air)
				{
					$(this.$air).append(this.$toolbar);
					$('body').append(this.$air);
				}
				else
				{
					if (this.opts.toolbarExternal === false) this.$box.prepend(this.$toolbar);
					else $(this.opts.toolbarExternal).html(this.$toolbar);
				}

				$.each(this.opts.buttons, $.proxy(function(i,key)
				{

					if (key !== '|' && typeof this.opts.toolbar[key] !== 'undefined')
					{
						var s = this.opts.toolbar[key];

						if (this.opts.fileUpload === false && key === 'file')
						{
							return true;
						}

						this.$toolbar.append($('<li>').append(this.button.build.call(this, key, s)));
					}

					// add separator
					if (key === '|') this.$toolbar.append($('<li class="redactor_separator"></li>'));

				}, this));
			}
		},

		// COLORPICKER
		picker:
		{
			build: function(dropdown, key)
			{
				$(dropdown).width(210);

				var rule = 'color';
				if (key === 'backcolor')
				{
					rule = 'background-color';
				}

				var len = this.opts.colors.length;
				var _self = this;
				for (var i = 0; i < len; ++i)
				{
					var color = this.opts.colors[i];

					var swatch = $('<a rel="' + color + '" href="javascript:void(null);" class="redactor_color_link"></a>').css({ 'backgroundColor': color });
					$(dropdown).append(swatch);

					$(swatch).on('click', function()
					{
						var type = $(this).attr('rel');
						if (key === 'backcolor')
						{
							type = $(this).css('background-color');
						}

						_self.picker.set.call(_self, rule, type);

					});
				}

				var elnone = $('<a href="javascript:void(null);" class="redactor_color_none"></a>').html(RLANG.none).on('click', function() { _self.setPickerData(rule, false); });
				$(dropdown).append(elnone);

				return dropdown;
			},
			set: function(rule, type)
			{
				this.$editor.focus();
				this.inline.removeStyle.call(this, rule);

				if (type !== false)
				{
					this.inline.setStyle.call(this, rule, type);
				}

				this.sync();
			}
		},

		// DROPDOWNS
		dropdown:
		{
			build: function(dropdown, obj)
			{
				$.each(obj, $.proxy(
					function (x, d)
					{
						if (typeof(d.className) === 'undefined')
						{
							d.className = '';
						}

						var drop_a;
						if (typeof d.name !== 'undefined' && d.name === 'separator')
						{
							drop_a = $('<a class="redactor_separator_drop">');
						}
						else
						{
							drop_a = $('<a href="javascript:void(null);" class="' + d.className + '">' + d.title + '</a>');

							if (typeof(d.callback) === 'function')
							{
								$(drop_a).click($.proxy(function(e) { d.callback(this, e, x); }, this));
							}
							else if (typeof(d.func) === 'undefined')
							{
								$(drop_a).click($.proxy(function() { this.exec.command.call(this, d.exec, x); }, this));
							}
							else
							{
								$(drop_a).click($.proxy(function(e) {

									var arr = d.func.split('.');
									if (arr.length == 1) this[s.func](x);
									else this[arr[0]][arr[1]].call(this, x);

								}, this));
							}
						}

						$(dropdown).append(drop_a);

					}, this)
				);

				return dropdown;

			},
			show: function(e, dropdown, key)
			{
				if (this.button.get.call(this, key).hasClass('dropact'))
				{
					this.dropdown.hideAll.call(this);
				}
				else
				{
					this.dropdown.hideAll.call(this);

					this.button.active.call(this, key);
					this.button.get.call(this, key).addClass('dropact');

					var left = this.button.get.call(this, key).offset().left;

					if (this.opts.air)
					{
						var air_top = this.$air.offset().top;

						$(dropdown).css({ position: 'absolute', left: left + 'px', top: air_top + 29 + 'px' }).show();
					}
					else if (this.opts.fixed && this.fixed)
					{
						$(dropdown).css({ position: 'fixed', left: left + 'px', top: '29px' }).show();
					}
					else
					{
						var top = this.$toolbar.offset().top + 29;
						$(dropdown).css({ position: 'absolute', left: left + 'px', top: top + 'px' }).show();
					}
				}

				var hdlHideDropDown = $.proxy(function(e) { this.dropdown.hide.call(this, e, dropdown, key); }, this);

				$(document).one('click', hdlHideDropDown);
				this.$editor.one('click', hdlHideDropDown);

				e.stopPropagation();

			},
			hideAll: function()
			{
				this.$toolbar.find('a.dropact').removeClass('redactor_act').removeClass('dropact');
				$('.redactor_dropdown').hide();
			},
			hide: function(e, dropdown, key)
			{
				if (!$(e.target).hasClass('dropact'))
				{
					$(dropdown).removeClass('dropact');
					this.showedDropDown = false;
					this.dropdown.hideAll.call(this);
				}
			}
		},

		// BUTTONS
		button:
		{
			build: function(key, s)
			{
				var button = $('<a href="javascript:void(null);" title="' + s.title + '" class="redactor_btn_' + key + '"></a>');

				if (typeof s.func === 'undefined')
				{
					button.click($.proxy(function()
					{
						if ($.inArray(key, this.opts.activeButtons) != -1)
						{
							this.button.inactive.call(this);

							if (!button.hasClass('redactor_act')) this.button.active.call(this, key);
							else this.button.inactive.call(this, key);
						}

						if (this.utils.browser.call(this, 'mozilla')) this.$editor.focus();

						this.exec.command.call(this, s.exec, key);

					}, this));
				}
				else if (s.func !== 'show')
				{
					button.click($.proxy(function(e) {

						var arr = s.func.split('.');
						if (arr.length == 1) this[s.func](e);
						else this[arr[0]][arr[1]].call(this, e);

					}, this));
				}

				if (typeof s.callback !== 'undefined' && s.callback !== false)
				{
					button.click($.proxy(function(e) { s.callback(this, e, key); }, this));
				}

				// dropdown
				if (key === 'backcolor' || key === 'fontcolor' || typeof(s.dropdown) !== 'undefined')
				{
					var dropdown = $('<div class="redactor_dropdown" style="display: none;">');

					if (key === 'backcolor' || key === 'fontcolor')
					{
						dropdown = this.picker.build.call(this, dropdown, key);
					}
					else
					{
						dropdown = this.dropdown.build.call(this, dropdown, s.dropdown);
					}

					this.dropdowns.push(dropdown.appendTo($(document.body)));

					// observing dropdown
					this.hdlShowDropDown = $.proxy(function(e) { this.dropdown.show.call(this, e, dropdown, key); }, this);

					button.click(this.hdlShowDropDown);
				}

				return button;
			},
			get: function(key)
			{
				if (this.opts.toolbar === false) return false;

				return $(this.$toolbar.find('a.redactor_btn_' + key));
			},
			active: function(key)
			{
				this.button.get.call(this, key).addClass('redactor_act');
			},
			inactive: function(key)
			{
				this.button.get.call(this, key).removeClass('redactor_act');
			},
			inactiveAll: function()
			{
				$.each(this.opts.activeButtons, $.proxy(function(i,s)
				{
					this.button.inactive.call(this, s);

				}, this));
			},
			changeIcon: function(key, classname)
			{
				this.button.get.call(this, key).addClass('redactor_btn_' + classname);
			},
			removeIcon: function(key, classname)
			{
				this.button.get.call(this, key).removeClass('redactor_btn_' + classname);
			},

			addSeparator: function()
			{
				this.$toolbar.append($('<li class="redactor_separator"></li>'));
			},
			addSeparatorAfter: function(key)
			{
				var $btn = this.button.get.call(this, key);
				$btn.parent().after($('<li class="redactor_separator"></li>'));
			},
			addSeparatorBefore: function(key)
			{
				var $btn = this.button.get.call(this, key);
				$btn.parent().before($('<li class="redactor_separator"></li>'));
			},
			removeSeparatorAfter: function(key)
			{
				var $btn = this.button.get.call(this, key);
				$btn.parent().next().remove();
			},
			removeSeparatorBefore: function(key)
			{
				var $btn = this.button.get.call(this, key);
				$btn.parent().prev().remove();
			},
			setRight: function(key)
			{
				if (this.opts.toolbar === false) return false;
				this.button.get.call(this, key).parent().addClass('redactor_btn_right');
			},
			setLeft: function(key)
			{
				if (this.opts.toolbar === false) return false;
				this.button.get.call(this, key).parent().removeClass('redactor_btn_right');
			},
			add: function(key, title, callback, dropdown)
			{
				if (this.opts.toolbar === false) return false;
				var btn = this.button.build.call(this, key, { title: title, callback: callback, dropdown: dropdown });
				this.$toolbar.append($('<li>').append(btn));
			},
			addFirst: function(key, title, callback, dropdown)
			{
				if (this.opts.toolbar === false) return false;
				var btn = this.button.build.call(this, key, { title: title, callback: callback, dropdown: dropdown });
				this.$toolbar.prepend($('<li>').append(btn));
			},
			addAfter: function(afterkey, key, title, callback, dropdown)
			{
				if (this.opts.toolbar === false) return false;
				var btn = this.button.build.call(this, key, { title: title, callback: callback, dropdown: dropdown });
				var $btn = this.button.get.call(this, afterkey);
				$btn.parent().after($('<li>').append(btn));
			},
			addBefore: function(beforekey, key, title, callback, dropdown)
			{
				if (this.opts.toolbar === false) return false;
				var btn = this.button.build.call(this, key, { title: title, callback: callback, dropdown: dropdown });
				var $btn = this.button.get.call(this, beforekey);
				$btn.parent().before($('<li>').append(btn));
			},
			remove: function(key, separator)
			{
				var $btn = this.button.get.call(this, key);

				if (separator === true) $btn.parent().next().remove();

				$btn.parent().removeClass('redactor_btn_right');
				$btn.remove();
			}
		},

		// FOCUS
		focus:
		{
			set: function()
			{
				if (this.opts.iframe && this.opts.linebreaks === false)
				{
					var contents = this.$editor.contents();
					var el = contents.eq(0);
					this.$editor.focus();

					if (el.size() !== 0 && el[0].tagName === 'HR')
					{
						el = contents.eq(1);
					}

					if (el.size() !== 0)
					{
						this.selection.start.call(this, el);
						return true;
					}
				}

				this.$editor.focus();

			},
			end: function()
			{
			    var range, selection;
			    if (this.document.createRange)
			    {
			        range = this.document.createRange();
			        range.selectNodeContents(this.$editor[0]);
			        range.collapse(false);
			        selection = this.window.getSelection();
			        selection.removeAllRanges();
			        selection.addRange(range);
			    }
			    else if (this.document.selection)
			    {
			        range = this.document.body.createTextRange();
			        range.moveToElementText(this.$editor[0]);
			        range.collapse(false);
			        range.select();
			    }
			}
		},

		// TOGGLE
		toggle: function()
		{
			var html;

			if (this.opts.visual)
			{
				this.selection.save.call(this);

				var height = null;
				if (this.opts.iframe)
				{
					height = this.$frame.height();
					if (this.opts.fullpage) this.$editor.removeAttr('contenteditable');
					this.$frame.hide();
				}
				else
				{
					height = this.$editor.innerHeight();
					this.$editor.hide();
				}

				html = this.code.get.call(this);
				html = $.trim(this.clean.start.call(this, html));

				this.codeactions = html;

				this.$el.removeAttr('placeholder').height(height).val(html).show().focus();

				// indenting
				this.$el.on('keydown.redactor-textarea', function(e)
				{
					if (e.keyCode == 9)
					{
						var start = $(this).get(0).selectionStart;
						$(this).val($(this).val().substring(0, start) + "\t" + $(this).val().substring($(this).get(0).selectionEnd));
						$(this).get(0).selectionStart = $(this).get(0).selectionEnd = start + 1;
						return false;
					}
				});

				this.button.inactiveAll.call(this);
				this.button.active.call(this, 'html');
				this.opts.visual = false;
			}
			else
			{
				var html = this.$el.val();
				this.$el.hide();

				this.codeactions = this.clean.removeSpaces.call(this, this.codeactions) == this.clean.removeSpaces.call(this, html);

				if (this.codeactions === false)
				{
					// clean up
					html = this.clean.savePreCode.call(this, html);

					if (this.opts.fullpage === false)
					{
						html = this.clean.stripTags.call(this, html);
						html = this.clean.paragraphy.call(this, html);
					}

					if (this.opts.linebreaks === false)
					{
						if (html === '') html = this.opts.emptyHtml;
						else if (html.search(/^<hr\s?\/?>$/gi) !== -1) html = '<hr>' + this.opts.emptyHtml;
					}

					this.code.set.call(this, html, false);
				}

				if (this.opts.iframe) this.$frame.show()
				else this.$editor.show();

				this.$el.off('keydown.redactor-textarea');

				if (this.codeactions === false)
				{
					setTimeout($.proxy(this.focus.set, this), 100);
				}
				else
				{
					setTimeout($.proxy(function()
					{
						this.$editor.focus();
						this.selection.restore.call(this);
					}, this), 100);
				}

				this.observe.start.call(this);
				this.button.inactive.call(this, 'html');
				this.opts.visual = true;
			}
		},

		// SYNC
		sync: function()
		{
			var html = this.code.get.call(this);
			this.$el.val(html);

			if (typeof this.opts.syncCallback === 'function')
			{
				this.opts.syncCallback(this, html);
			}

		},

		// GET & SET CODE
		code:
		{
			getIframe: function()
			{
				this.$editor.removeAttr('contenteditable').removeAttr('dir');
				var html = this.utils.outerHtml.call(this, this.$frame.contents().children());
				this.$editor.attr('contenteditable', true).attr('dir', this.opts.direction);
				return html;
			},
			get: function()
			{
				var html = '';

				if (this.opts.fullpage)
				{
					html = $.trim(this.code.getIframe.call(this));
				}
				else
				{
					html = this.clean.stripTags.call(this, $.trim(this.$editor.html()));
				}

				// remove placeholder
				html = this.placeholder.remove.call(this, html, true, false);

				// remove space
				html = html.replace(/&#x200b;/gi, '');

				// php code fix
				html = html.replace('<!--?php', '<?php');
				html = html.replace('?-->', '?>');

				// bold, italic, del
				if (this.opts.boldTag === 'strong') html = html.replace(/<b>([\w\W]*?)<\/b>/gi, '<strong>$1</strong>');
				else html = html.replace(/<strong>([\w\W]*?)<\/strong>/gi, '<b>$1</b>');

				if (this.opts.italicTag === 'em') html = html.replace(/<i>([\w\W]*?)<\/i>/gi, '<em>$1</em>');
				else html = html.replace(/<em>([\w\W]*?)<\/em>/gi, '<i>$1</i>');

				html = html.replace(/<strike>([\w\W]*?)<\/strike>/gi, '<del>$1</del>');

				html = this.clean.convertInlineTags.call(this, html);

				return html.replace(/<span id="(buffer|insert)-marker(.*?)">(.*?)<\/span>/gi, '');
			},
			setIframe: function(html)
			{
				var doc = this.iframe.page.call(this);
				this.$frame[0].src = "about:blank"

				html = this.clean.removeSpaces.call(this, html);

				doc.open();
				doc.write(html);
				doc.close();

				this.sync();
			},
			setEditor: function(html, strip)
			{
				if (strip !== false)
				{
					html = this.clean.stripTags.call(this, html);
					if (this.opts.linebreaks === false)
					{
						html = this.clean.paragraphy.call(this, html);
					}
					else
					{
						html = html.replace(/<p(.*?)>([\w\W]*?)<\/p>/gi, '$2<br>');
					}
				}

				this.$editor.html(html);
				this.sync();
			},
			set: function(html, strip)
			{
				if (this.opts.fullpage) this.code.setIframe.call(this, html);
				else this.code.setEditor.call(this, html, strip);
			}
		},

		// INSERT
		insert:
		{
			html: function(html, sync)
			{
				this.$editor.focus();

				if (this.opts.linebreaks === false)
				{
					var current = this.selection.getBlock.call(this);
					var blockhtml = $.trim(current.innerHTML.replace(/<br\s?\/?>/gi, ''));
				}

				if (this.opts.linebreaks === false && current.tagName === 'P' && blockhtml == '')
				{
					var tmphtml = $(html);
					$(current).replaceWith(tmphtml);
					this.selection.end.call(this, tmphtml.last());

				}
				else
				{
					//this.document.execCommand('inserthtml', false, html);

					var sel, range;
					if (this.window.getSelection)
					{
						sel = this.window.getSelection();
						if (sel.getRangeAt && sel.rangeCount)
						{
							range = sel.getRangeAt(0);
							range.deleteContents();

							var el = this.document.createElement("div");
							el.innerHTML = html;
							var frag = this.document.createDocumentFragment(), node, lastNode;
							while ((node = el.firstChild))
							{
								lastNode = frag.appendChild(node);
							}

							range.insertNode(frag);

							if (lastNode)
							{
								range = range.cloneRange();
								range.setStartAfter(lastNode);
								range.collapse(true);
								sel.removeAllRanges();
								sel.addRange(range);
							}
						}
					}
					else if (this.document.selection && this.document.selection.type != "Control") // IE < 9
					{
						this.document.selection.createRange().pasteHTML(html);
					}
				}

				this.observe.start.call(this);

				if (sync !== false)
				{
					this.sync();
				}

			},
			force: function(html)
			{
				this.$editor.focus();

				if (this.opts.linebreaks === false && /<\/(P|H[1-6]|UL|OL|DIV|TABLE|BLOCKQUOTE|PRE|ADDRESS|SECTION|HEADER|FOOTER|ASIDE|ARTICLE)>/gi.test(html))
				{
					var current = this.selection.getBlock.call(this);
					var extract = this.utils.extractBlockContentsFromCaret.call(this);

					$(current).after($('<p id="redactor-insert-id">'));

					html = $(html).after('<' + current.tagName + '>' + this.utils.getFragmentHtml.call(this, extract) + '</' + current.tagName + '>');

					this.$editor.find('p#redactor-insert-id').replaceWith(html);

					if ($.trim($(current).html()) == '')
					{
						$(current).remove();
					}


					this.selection.end.call(this, html);
					this.observe.start.call(this)
					this.sync();
				}
				else
				{
					this.insert.html.call(this, html);
				}

			},

			// without delete contents
			beforeCaret: function(node)
			{
			    var sel, range;
				if (this.window.getSelection)
				{
					sel = this.window.getSelection();
					if (sel.getRangeAt && sel.rangeCount)
					{
						range = sel.getRangeAt(0);
						range.insertNode(node);
					}
				}
			},
			afterCaret: function(node)
			{
				if (this.window.getSelection)
				{
					var sel = this.window.getSelection();
					if (sel.rangeCount)
					{
						var range = sel.getRangeAt(0);
						range.collapse(false);
						range.insertNode(node);
						range = range.cloneRange();
						range.selectNodeContents(node);
						range.collapse(false);
						sel.removeAllRanges();
						sel.addRange(range);
					}
				}
			},

			// with delete contents
			nodeAtCaret: function(node)
			{
				var sel;
				if (this.window.getSelection)
				{
					sel = this.selection.get.call(this);
					if (sel.getRangeAt && sel.rangeCount)
					{
						range = sel.getRangeAt(0);
						range.deleteContents();
						range.insertNode(node);
						range.setEndAfter(node);
						range.setStartAfter(node);
						sel.removeAllRanges();
						sel.addRange(range);
					}
				}
			}
		},

		// CODE CLEAN UP
		clean:
		{
			start: function(html)
			{
				html = this.clean.removeSpaces.call(this, html);
				html = this.clean.removeEmptyTags.call(this, html);
				html = this.clean.addBefore.call(this, html);
				html = this.clean.addAfter.call(this, html);
				html = this.clean.setTabulation.call(this, html);

				return html;
			},
			removeSpaces: function(html)
			{
				// save pre
				var prebuffer = [];
				var pre = html.match(/<pre(.*?)>([\w\W]*?)<\/pre>/gi);
				if (pre !== null)
				{
					$.each(pre, function(i,s)
					{
						html = html.replace(s, 'prebuffer_' + i);
						prebuffer.push(s);
					});
				}

				html = html.replace(/\s{2,}/g, ' ');
				html = html.replace(/\n/g, ' ');
				html = html.replace(/[\t]*/g, '');
				html = html.replace(/\n\s*\n/g, "\n");
				html = html.replace(/^[\s\n]*/g, '');
				html = html.replace(/[\s\n]*$/g, '');
				html = html.replace(/>\s+</g, '><');

				if (prebuffer)
				{
					$.each(prebuffer, function(i,s)
					{
						html = html.replace('prebuffer_' + i, s);
					});

					prebuffer = [];
				}

				html = html.replace(/\n\n/g, "\n");

				return html;
			},
			removeEmptyTags: function(html)
			{
				html = html.replace(/<span>([\w\W]*?)<\/span>/gi, '$1');

				var etags = ["<pre></pre>","<blockquote>\\s*</blockquote>","<em>\\s*</em>","<ul></ul>","<ol></ol>","<li></li>","<table></table>","<tr></tr>","<span>\\s*<span>", "<span>&nbsp;<span>", "<b>\\s*</b>", "<b>&nbsp;</b>", "<p>\\s*</p>", "<p>&nbsp;</p>",  "<p>\\s*<br>\\s*</p>", "<div>\\s*</div>", "<div>\\s*<br>\\s*</div>"];
				for (var i = 0; i < etags.length; ++i)
				{
					var bbb = etags[i];
					html = html.replace(new RegExp(bbb,'gi'), "");
				}

				return html;
			},
			addBefore: function(html)
			{
				var lb = '\r\n';
				var btags = ["<p", "<form","</ul>", '</ol>', "<fieldset","<legend","<object","<embed","<select","<option","<input","<textarea","<pre","<blockquote","<ul","<ol","<li","<dl","<dt","<dd","<table", "<thead","<tbody","<caption","</caption>","<th","<tr","<td","<figure"];
				for (var i = 0; i < btags.length; ++i)
				{
					var eee = btags[i];
					html = html.replace(new RegExp(eee,'gi'),lb+eee);
				}

				return html;
			},
			addAfter: function(html)
			{
				var lb = '\r\n';
				var atags = ['</p>', '</div>', '</h1>', '</h2>', '</h3>', '</h4>', '</h5>', '</h6>', '<br>', '<br />', '</dl>', '</dt>', '</dd>', '</form>', '</blockquote>', '</pre>', '</legend>', '</fieldset>', '</object>', '</embed>', '</textarea>', '</select>', '</option>', '</table>', '</thead>', '</tbody>', '</tr>', '</td>', '</th>', '</figure>'];
				for (var i = 0; i < atags.length; ++i)
				{
					var aaa = atags[i];
					html = html.replace(new RegExp(aaa,'gi'),aaa+lb);
				}

				return html;
			},
			setTabulation: function(html)
			{
				html = html.replace(/<li/g, "\t<li");
				html = html.replace(/<tr/g, "\t<tr");
				html = html.replace(/<td/g, "\t\t<td");
				html = html.replace(/<\/tr>/g, "\t</tr>");

				return html;
			},
			paragraphy: function(html)
			{
				html = $.trim(html);

				if (this.opts.linebreaks === true) return html;
				if (html === '' || html === '<p></p>') return this.opts.emptyHtml;

				// convert div to p
				if (this.opts.convertDivs)
				{
					html = html.replace(/<div(.*?)>([\w\W]*?)<\/div>/gi, '<p>$2</p>');
				}

				html = html + "\n";

				var safes = [];
				var z = 0;

				if (html.search(/<(table|div|pre|object)/gi) !== -1)
				{
					$.each(html.match(/<(table|div|pre|object)(.*?)>([\w\W]*?)<\/(table|div|pre|object)>/gi), function(i,s)
					{
						z++;
						safes[z] = s;
						html = html.replace(s, '{replace' + z + '}\n');
					});
				}

				html = html.replace(/<br \/>\s*<br \/>/gi, "\n\n");

				function R(str, mod, r)
				{
					return html.replace(new RegExp(str, mod), r);
				}

				var blocks = '(html|body|head|title|meta|style|script|link|table|thead|tfoot|caption|col|colgroup|tbody|tr|td|th|div|dl|dd|dt|ul|ol|li|pre|select|option|form|map|area|blockquote|address|math|style|p|h[1-6]|hr|fieldset|legend|section|article|aside|hgroup|header|footer|nav|figure|figcaption|details|menu|summary)';

				html = R('(<' + blocks + '[^>]*>)', 'gi', "\n$1");
				html = R('(</' + blocks + '>)', 'gi', "$1\n\n");
				html = R("\r\n", 'g', "\n");
				html = R("\r", 'g', "\n");
				html = R("/\n\n+/", 'g', "\n\n");

				var htmls = html.split(new RegExp("\n\s*\n", 'g'), -1);
				html = '';

				for (i in htmls)
				{
					if ( htmls[i].search('{replace') === -1) html += '<p>' + htmls[i].replace(/^\n+|\n+$/g, "") + "</p>";
					else html += htmls[i];
				}

				html = R('<p>\s*</p>', 'gi', '');
				html = R('<p>([^<]+)</(div|address|form)>', 'gi', "<p>$1</p></$2>");
				html = R('<p>\s*(</?' + blocks + '[^>]*>)\s*</p>', 'gi', "$1");
				html = R("<p>(<li.+?)</p>", 'gi', "$1");
				html = R('<p><blockquote([^>]*)>', 'gi', "<blockquote$1><p>");
				html = R('</blockquote></p>', 'gi', '</p></blockquote>');
				html = R('<p>\s*(</?' + blocks + '[^>]*>)', 'gi', "$1");

				html = R('(</?' + blocks + '[^>]*>)\s*</p>', 'gi', "$1");
				html = R('(</?' + blocks + '[^>]*>)\s*<br />', 'gi', "$1");
				html = R('<br />(\s*</?(?:p|li|div|dl|dd|dt|th|pre|td|ul|ol)[^>]*>)', 'gi', '$1');
				html = R("\n</p>", 'gi', '</p>');

				html = R('</li><p>', 'gi', '</li>');
				html = R('</ul><p>(.*?)</li>', 'gi', '</ul></li>');
				html = R('</ol><p>', 'gi', '</ol>');

				html = this.clean.convertInlineTags.call(this, html);

				$.each(safes, function(i,s)
				{
					html = html.replace('{replace' + i + '}', s);
				});

				return $.trim(html);

			},
			convertInlineTags: function(html)
			{
				var boldTag = 'strong';
				if (this.opts.boldTag === 'b') boldTag = 'b';

				var italicTag = 'em';
				if (this.opts.italicTag === 'i') italicTag = 'i';

				html = html.replace(/<span style="font-style: italic;">([\w\W]*?)<\/span>/gi, '<' + italicTag + '>$1</' + italicTag + '>');
				html = html.replace(/<span style="font-weight: bold;">([\w\W]*?)<\/span>/gi, '<' + boldTag + '>$1</' + boldTag + '>');

				return html;
			},
			stripTags: function(html)
			{
				var allowed = false;
				if (this.opts.allowedTags !== false)
				{
					allowed = true;
				}

				var arr = allowed === true ? this.opts.allowedTags : this.opts.deniedTags;
				var cleartags = this.opts.clearTags;

				var tags = /<\/?([a-z][a-z0-9]*)\b[^>]*>/gi;
				html = html.replace(tags, function ($0, $1)
				{
					return $.inArray($1.toLowerCase(), cleartags) > '-1' ? '' : $0;
				});

				html = html.replace(tags, function ($0, $1)
				{
					if (allowed === true)
					{
						return $.inArray($1.toLowerCase(), arr) > '-1' ? $0 : '';
					}
					else
					{
						return $.inArray($1.toLowerCase(), arr) > '-1' ? '' : $0;
					}
				});

				return html;
			},
			savePreCode: function(html, encode)
			{
				var pre = html.match(/<pre(.*?)>([\w\W]*?)<\/pre>/gi);
				if (pre !== null)
				{
					$.each(pre, $.proxy(function(i,s)
					{
						var arr = s.match(/<pre(.*?)>([\w\W]*?)<\/pre>/i);
						arr[2] = arr[2].replace(/&nbsp;/g, ' ');
						arr[2] = arr[2].replace(/(<br>|<br \/>)/gi, '\n');

						if (encode !== false)
						{
							arr[2] = this.clean.encodeEntities.call(this, arr[2]);
						}

						html = html.replace(s, '<pre' + arr[1] + '>' + arr[2] + '</pre>');

					}, this));
				}

				return html;
			},
			encodeEntities: function(str)
			{
				str = String(str).replace(/&amp;/g, '&').replace(/&lt;/g, '<').replace(/&gt;/g, '>').replace(/&quot;/g, '"');
				return String(str).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
			}
		},

		// PASTE
		paste:
		{
			clean: function(html)
			{
				var parent = this.selection.getParent.call(this);

				// clean up pre
				if (parent && $(parent).get(0).tagName === 'PRE')
				{
					html = this.paste.pre.call(this, html);
					this.paste.insert.call(this, html);
					return true;
				}

				// remove comments and php tags
				html = html.replace(/<!--[\s\S]*?-->|<\?(?:php)?[\s\S]*?\?>/gi, '');

				// remove nbsp
				html = html.replace(/(&nbsp;){2,}/gi, '&nbsp;');

				// remove google docs marker
				html = html.replace(/<b\sid="internal-source-marker(.*?)">([\w\W]*?)<\/b>/gi, "$2");

				// strip tags
				html = this.clean.stripTags.call(this, html);

				// prevert
				html = html.replace(/<td><\/td>/gi, '[td]');
				html = html.replace(/<td>&nbsp;<\/td>/gi, '[td]');
				html = html.replace(/<td><br><\/td>/gi, '[td]');
				html = html.replace(/<a(.*?)href="(.*?)"(.*?)>([\w\W]*?)<\/a>/gi, '[a href="$2"]$4[/a]');
				html = html.replace(/<iframe(.*?)>([\w\W]*?)<\/iframe>/gi, '[iframe$1]$2[/iframe]');
				html = html.replace(/<video(.*?)>([\w\W]*?)<\/video>/gi, '[video$1]$2[/video]');
				html = html.replace(/<audio(.*?)>([\w\W]*?)<\/audio>/gi, '[audio$1]$2[/audio]');
				html = html.replace(/<embed(.*?)>([\w\W]*?)<\/embed>/gi, '[embed$1]$2[/embed]');
				html = html.replace(/<object(.*?)>([\w\W]*?)<\/object>/gi, '[object$1]$2[/object]');
				html = html.replace(/<param(.*?)>/gi, '[param$1]');
				html = html.replace(/<img(.*?)style="(.*?)"(.*?)>/gi, '[img$1$3]');

				// remove attributes
				html = html.replace(/<(\w+)([\w\W]*?)>/gi, '<$1>');

				// remove empty
				html = html.replace(/<[^\/>][^>]*>(\s*|\t*|\n*|&nbsp;|<br>)<\/[^>]+>/gi, '');
				html = html.replace(/<[^\/>][^>]*>(\s*|\t*|\n*|&nbsp;|<br>)<\/[^>]+>/gi, '');

				// revert
				html = html.replace(/\[td\]/gi, '<td>&nbsp;</td>');
				html = html.replace(/\[a href="(.*?)"\]([\w\W]*?)\[\/a\]/gi, '<a href="$1">$2</a>');
				html = html.replace(/\[iframe(.*?)\]([\w\W]*?)\[\/iframe\]/gi, '<iframe$1>$2</iframe>');
				html = html.replace(/\[video(.*?)\]([\w\W]*?)\[\/video\]/gi, '<video$1>$2</video>');
				html = html.replace(/\[audio(.*?)\]([\w\W]*?)\[\/audio\]/gi, '<audio$1>$2</audio>');
				html = html.replace(/\[embed(.*?)\]([\w\W]*?)\[\/embed\]/gi, '<embed$1>$2</embed>');
				html = html.replace(/\[object(.*?)\]([\w\W]*?)\[\/object\]/gi, '<object$1>$2</object>');
				html = html.replace(/\[param(.*?)\]/gi, '<param$1>');
				html = html.replace(/\[img(.*?)\]/gi, '<img$1>');


				// convert div to p
				if (this.opts.convertDivs)
				{
					html = html.replace(/<div(.*?)>([\w\W]*?)<\/div>/gi, '<p>$2</p>');
				}

				// remove span
				html = html.replace(/<span>([\w\W]*?)<\/span>/gi, '$1');

				html = html.replace(/\n{3,}/gi, '\n');

				// remove dirty p
				html = html.replace(/<p><p>/gi, '<p>');
				html = html.replace(/<\/p><\/p>/gi, '</p>');

				if (this.opts.linebreaks === true)
				{
					html = html.replace(/<p(.*?)>([\w\W]*?)<\/p>/gi, '$2<br>');
				}

				// FF fix
				if (this.utils.browser.call(this, 'mozilla'))
				{
					html = html.replace(/<br>$/gi, '');
				}

				this.paste.insert.call(this, html);

			},
			pre: function(s)
			{
				s = s.replace(/<br>/gi, '\n');
				s = s.replace(/<\/p>/gi, '\n');
				s = s.replace(/<\/div>/gi, '\n');

				var tmp = this.document.createElement("div");
				tmp.innerHTML = s;
				return tmp.textContent||tmp.innerText;

			},
			insert: function(html)
			{
				this.insert.html.call(this, html);

				if (this.opts.autoresize === true) $(this.document.body).scrollTop(this.saveScroll);
				else this.$editor.scrollTop(this.saveScroll);
			}
		},

		// ALIGNMENT
		alignment:
		{
			left: function()
			{
				this.alignment.set.call(this, '', 'JustifyLeft');
			},
			right: function()
			{
				this.alignment.set.call(this, 'right', 'JustifyRight');
			},
			center: function()
			{
				this.alignment.set.call(this, 'center', 'JustifyCenter');
			},
			justify: function()
			{
				this.alignment.set.call(this, 'justify', 'JustifyFull');
			},
			set: function(type, cmd)
			{
				// buffer
				this.buffer.set.call(this);

				if (this.utils.oldIE.call(this))
				{
					this.document.execCommand(cmd, false, false);
					return true;
				}

				this.selection.save.call(this);

				var elements = this.selection.getBlocks.call(this);
				var element = this.selection.getBlock.call(this);
				if (elements.length === 0)
				{
					elements.push(element);
				}

				$.each(elements, $.proxy(function(i,s)
				{
					var $el = false;
					if ($.inArray(s.tagName, this.opts.alignmentTags) !== -1)
					{
						$el = $(s);
					}
					else
					{
						$el = $(s).closest(this.opts.alignmentTags.toString().toLowerCase(), this.$editor[0]);
					}

					if ($el)
					{
						$el.css('text-align', type);
						this.utils.removeEmptyStyleAttr.call(this, $el);
					}

				}, this));

				this.selection.restore.call(this);
				this.sync();
			}
		},

		// INDENTING
		indenting:
		{
			indent: function()
			{
				this.indenting.start.call(this, 'indent');
			},
			outdent: function()
			{
				this.indenting.start.call(this, 'outdent');
			},
			start: function(cmd)
			{
				var elements = this.selection.getBlocks.call(this);
				var element = this.selection.getBlock.call(this);

				if (!this.utils.isParentRedactor.call(this, element))
				{
					return false;
				}

				// buffer
				this.buffer.set.call(this);

				var parent = $(element).closest('h1, h2, h3, h4, h5, h6, p, li, div, blockquote');

				if (parent[0].tagName == 'LI')
				{
					this.document.execCommand(cmd, false, false);
				}
				else
				{
					if (elements.length === 0)
					{
						elements.push(element);
					}

					// indent block tags
					$.each(elements, $.proxy(function(i,s)
					{
						this.indenting.process.call(this, s, cmd);

					}, this));
				}
			},
			process: function(element, cmd)
			{

				if (!this.utils.isParentRedactor.call(this, element))
				{
					return false;
				}

				var parent = $(element).closest('h1, h2, h3, h4, h5, h6, p, li, div, blockquote');

				// blockquote
				if (parent[0].tagName == 'P' && parent.parent()[0].tagName == 'BLOCKQUOTE')
				{
					parent = parent.parent();
				}

				// others
				if (parent.size() != 0 && !parent.hasClass('redactor_editor'))
				{
					// increase
					if (cmd === 'indent')
					{
						parent.css('margin-left', (this.utils.normalize.call(this, parent.css('margin-left')) + 20) + 'px');
					}
					// decrease
					else
					{
						var marginLeft = this.utils.normalize.call(this, parent.css('margin-left')) - 20;
						if (marginLeft <= 0)
						{
							parent.css('margin-left', '');
							if (parent.attr('style') == '')
							{
								parent.removeAttr('style');
							}
						}
						else
						{
							parent.css('margin-left', marginLeft + 'px');
						}

					}
				}

				this.sync();
			}
		},

		// EXECCOMMAND
		exec:
		{
			command: function(cmd, param)
			{
				if (this.opts.visual == false)
				{
					this.$el.focus();
					return false;
				}

				try
				{
					var parentel = this.selection.getParent.call(this);
					var currentel = this.selection.getElement.call(this);
					var pre = false;

					if ((parentel && $(parentel).get(0).tagName === 'PRE') || (currentel && $(currentel).get(0).tagName === 'PRE'))
					{
						pre = true;
					}

					var parent;
					if (cmd === 'inserthtml')
					{
						this.insert.html.call(this, param, false);
					}
					else if (cmd === 'unlink')
					{
						parent = this.selection.getElement.call(this);
						if ($(parent).get(0).tagName === 'A') $(parent).replaceWith($(parent).text());
						else this.exec.run.call(this, cmd, param);
					}
					else
					{
						if (cmd === 'inserthorizontalrule' && this.utils.browser.call(this, 'msie')) this.$editor.focus();
						if (cmd === 'formatblock' && this.utils.browser.call(this, 'mozilla')) this.$editor.focus();

						if (pre && this.opts.formattingPre) return false;

						this.exec.run.call(this, cmd, param);
					}

					if (cmd === 'inserthorizontalrule')
					{
						this.$editor.find('hr').removeAttr('id');

						if (this.opts.linebreaks === false)
						{
							this.$editor.focus();
							var p = $('<p>' + this.opts.invisibleSpace + '</p>');
							this.insert.nodeAtCaret.call(this, p[0]);

							this.$editor.focus();
							this.selection.start.call(this, p, 0);
						}
					}

					if (pre && this.opts.formattingPre && (cmd === 'italic' || cmd === 'bold' || cmd === 'strikethrough'))
					{
						return false;
					}

					setTimeout($.proxy(this.sync, this), 10);

					if (typeof this.opts.execCommandCallback === 'function')
					{
						this.opts.execCommandCallback(this, cmd);
					}

					if (this.opts.air)
					{
						this.$air.hide();
					}
				}
				catch (e) { }
			},
			run: function(cmd, param)
			{
				if (cmd === 'formatblock' && this.utils.browser.call(this, 'msie'))
				{
					param = '<' + param + '>';
				}

				this.document.execCommand(cmd, false, param);
			}
		},

		// QUOTE
		quote:
		{
			toggle: function()
			{
				this.buffer.set.call(this);

				var html = false;
				var nodes = this.selection.getBlocks.call(this);

				if (nodes.length === 0)
				{
					nodes = [this.selection.getBlock.call(this)];
				}

				if (!nodes)
				{
					this.exec.command.call(this, 'formatblock', 'blockquote');
				}

				$.each(nodes, $.proxy(function(i, node)
				{
					if (node === false || typeof node.tagName === 'undefined')
					{
						this.exec.command.call(this, 'formatblock', 'blockquote');
					}
					else if (node.tagName === 'BLOCKQUOTE')
					{
						if (this.opts.linebreaks === true)
						{
							this.selection.save.call(this);
							var node1 = $('<span id="insert-marker-1">')[0];
							var node2 = $('<span id="insert-marker-2">')[0];
							this.insert.beforeCaret.call(this, node1);
							this.insert.afterCaret.call(this, node2);
							this.selection.restore.call(this);
						}

						html = this.quote.html.call(this, node);
					}
					else if (node.tagName === 'PRE')
					{
						html = '<blockquote>' + this.quote.html.call(this, node) + '</blockquote>';
					}
					else
					{
						var parent = $(node).parent();
						if (parent[0].tagName === 'BLOCKQUOTE')
						{
							html = $(parent).html();
							node = parent;
						}
						else
						{
							html = $('<blockquote>' + this.utils.outerHtml.call(this, node) + '</blockquote>');
						}
					}

					if (html !== false)
					{
						if (this.opts.linebreaks === false)
						{
							var el = $(html);
							$(node).replaceWith(el);
							this.selection.start.call(this, el);
						}
						else
						{
							$(node).replaceWith(html);

							var node1 = $(this.$editor.find('span#insert-marker-1'));
							var node2 = $(this.$editor.find('span#insert-marker-2'));

							if (node1.size() !== 0 && node2.size() !== 0)
							{
								this.selection.set.call(this, node1[0], 0, node2[0], 0);
								node1.remove();
								node2.remove();
							}
							else
							{
								this.focus.set.call(this);
							}

						}
					}


				}, this));

				this.sync();
			},
			html: function(node)
			{
				var html = $(node).html();
				if (this.opts.linebreaks === false)
				{
					html = '<p>' + html + '</p>';
				}

				return html;
			}
		},

		// FORMAT
		format:
		{
			empty: function(e)
			{
				var html = $.trim(this.$editor.html());

				if (this.utils.browser.call(this, 'mozilla'))
				{
					html = html.replace(/<br\s\/?>/i, '');
				}

				var thtml = html.replace(/<(?:.|\n)*?>/gm, '');

				if (html === '' || thtml === '')
				{
					e.preventDefault();

					var node = $(this.opts.emptyHtml).get(0);
					this.$editor.html(node);
					this.selection.set.call(this, node, 0, node, 0);

					this.sync();
					return false;
				}
				else
				{
					this.sync();
				}
			},
			newLine: function()
			{
				var parent = this.selection.getElement.call(this);

				if (parent === false || (this.opts.iframe && parent.tagName === 'BODY') || (this.opts.iframe === false &&  parent.tagName === 'DIV' && $(parent).hasClass('redactor_editor')))
				{
					var element = $(this.selection.getNode.call(this));

					// Replace div to p
					if (this.opts.linebreaks === false)
					{
						if ((element.get(0).tagName === 'DIV' || element.get(0).tagName === 'H6') && (element.html() === '' || element.html() === '<br>'))
						{
							// replace with paragraph
							var newElement = $('<p>').append(element.clone().get(0).childNodes);
							element.replaceWith(newElement);
							this.selection.start.call(this, newElement);
						}
					}
					else
					{
						// Replace div, p to br
						if (element.size() != 0 && (element[0].tagName === 'DIV' || element[0].tagName === 'P') && (element.html() === '' || element.html() === '<br>'))
						{
							this.format.replaceLineBreak.call(this, element);
						}
					}
				}

				if (parent.tagName === 'P' && this.opts.linebreaks === true)
				{
					this.format.replaceLineBreak.call(this, parent);
				}

				this.sync();

			},
			insertAfterLastElement: function(element)
			{
				if (this.utils.isEndOfElement.call(this) && this.opts.linebreaks === false)
				{
					if (this.$editor.contents().last()[0] !== element)
					{
						return false;
					}

					this.buffer.set.call(this);

					var node = $(this.opts.emptyHtml);
					$(element).after(node);
					this.selection.start.call(this, node);
				}
			},
			insertLineBreak: function()
			{
				var br = $('<br>' + this.opts.invisibleSpace);
				this.insert.nodeAtCaret.call(this, br[0]);
			},
			replaceLineBreak: function(element)
			{
				var node = this.document.createTextNode('\uFEFF');
				$(element).replaceWith(node);
				this.selection.start.call(this, $(node));
			},
			blocks: function(tag)
			{
				this.buffer.set.call(this);

				var nodes = this.selection.getBlocks.call(this);
				var last;

				if (nodes.length === 0)
				{
					this.format.block.call(this, tag, false, true);
				}
				else
				{
					$.each(nodes, $.proxy(function(i, node)
					{
						last = this.format.block.call(this, tag, node, false);

					}, this));

					this.selection.end.call(this, last);
				}

				this.sync();
			},
			block: function(tag, block, offset)
			{
				if (block === false)
				{
					block = this.selection.getBlock.call(this);
				}

				if (block === false)
				{
					if (this.opts.linebreaks === true)
					{
						this.exec.command.call(this, 'formatblock', tag);
					}

					return true;
				}

				if (offset !== false)
				{
					var offset = this.selection.caretOffset.call(this, block);
				}

				var contents = '';
				if (tag !== 'pre')
				{
					contents = $(block).contents();
				}
				else
				{
					contents = this.clean.encodeEntities.call(this, $(block).text());
				}

				if (this.opts.linebreaks === true && tag === 'p')
				{
					$(block).replaceWith(contents);

					return $(contents);
				}
				else
				{
					var node = $('<' + tag + '>').append(contents);
					$(block).replaceWith(node);

					if (offset !== false)
					{
						this.selection.caret.call(this, node[0], offset);
					}
				}

				return node;
			},
			changeTag: function(from, to)
			{
				this.selection.save.call(this);

				var elements = this.$editor.find(from);
				$.each(elements, $.proxy(function(i,s)
				{
					$(s).replaceWith(function()
					{
						return $('<' + to + '/>').append($(this).contents());
					});
				}, this));

				setTimeout($.proxy(this.selection.restore, this), 10);

			}
		},

		// BLOCK CLASS AND STYLE
		block:
		{
			removeAttr: function(attr)
			{
				var nodes = this.selection.getBlocks.call(this);

				if (nodes.length === 0)
				{
					$(this.selection.getBlock.call(this)).removeAttr(attr);
				}
				else
				{
					$(nodes).removeAttr(attr);
				}
			},
			setAttr: function(attr, value)
			{
				var nodes = this.selection.getBlocks.call(this);

				if (nodes.length === 0)
				{
					$(this.selection.getBlock.call(this)).attr(attr, value);
				}
				else
				{
					$.each(nodes, function(i,s)
					{
						$(s).attr(attr, value);
					});
				}

			},
			removeStyle: function(rule)
			{
				var nodes = this.selection.getBlocks.call(this);

				if (nodes.length === 0)
				{
					var $block = $(this.selection.getBlock.call(this));
					$block.css(rule, '');
					this.utils.removeEmptyStyleAttr.call(this, $block);
				}
				else
				{
					$(nodes).css(rule, '');
					$.each(nodes, $.proxy(function(i,s)
					{
						this.utils.removeEmptyStyleAttr.call(this, s);
					}, this));
				}
			},
			setStyle: function(rule, value)
			{
				var nodes = this.selection.getBlocks.call(this);

				if (nodes.length === 0)
				{
					$(this.selection.getBlock.call(this)).css(rule, value);
				}
				else
				{
					$.each(nodes, function(i,s)
					{
						$(s).css(rule, value);
					});
				}

			},
			removeClass: function(className)
			{
				var nodes = this.selection.getBlocks.call(this);

				if (nodes.length === 0)
				{
					$(this.selection.getBlock.call(this)).removeClass(className);
				}
				else
				{
					$(nodes).removeClass(className);
				}
			},
			setClass: function(className)
			{
				var nodes = this.selection.getBlocks.call(this);

				if (nodes.length === 0)
				{
					$(this.selection.getBlock.call(this)).addClass(className);
				}
				else
				{
					$.each(nodes, function(i,s)
					{
						$(block).addClass(className);
					});
				}

			}
		},

		// INLINE CLASS AND STYLE
		inline:
		{
			removeClass: function(className)
			{
				var nodes = this.selection.getNodes.call(this);
				nodes.push(this.selection.getElement.call(this));

				$.each(nodes, function(i,s)
				{
					if ($(s).hasClass(className))
					{
						$(s).removeClass(className);
					}
				});

			},
			setClass: function(className)
			{
				var current = this.selection.getElement.call(this);
				if ($(current).hasClass(className))
				{
					return true;
				}

				this.inline.methods.call(this, 'addClass', className);
			},
			removeStyle: function(rule)
			{
				var nodes = this.selection.getNodes.call(this);
				nodes.push(this.selection.getElement.call(this));

				$.each(nodes, $.proxy(function(i,s)
				{
					if (s.tagName === 'SPAN')
					{
						$(s).css(rule, '')
						this.utils.removeEmptyStyleAttr.call(this, s);
					}
				}, this));

			},
			setStyle: function(rule, value)
			{
				this.inline.methods.call(this, 'css', rule, value);
			},
			removeAttr: function(attr)
			{
				var nodes = this.selection.getNodes.call(this);
				nodes.push(this.selection.getElement.call(this));

				$.each(nodes, function(i,s)
				{
					if ($(s).attr(attr))
					{
						$(s).removeAttr(attr);
					}
				});

			},
			setAttr: function(attr, value)
			{
				this.inline.methods.call(this, 'attr', attr, value);
			},
			methods: function(type, attr, value)
			{
				this.document.execCommand("fontSize", false, 4);

				var fonts = this.$editor.find('font');
				var last;
				$.each(fonts, $.proxy(function(i,s)
				{
					last = this.inline.setMethods.call(this, type, s, attr, value);
				}, this));

				if (last) this.selection.end.call(this, last);
			},
			setMethods: function(type, s, attr, value)
			{
				var parent = $(s).parent();
				if (parent && parent[0].tagName === 'SPAN')
				{
					var el = parent;
					$(s).replaceWith($(s).html());
				}
				else
				{
					var el = $('<span/>').append($(s).contents());
					$(s).replaceWith(el);
				}

				if (type === 'addClass') $(el).addClass(attr);
				else if (type === 'css') $(el).css(attr, value);
				else if (type === 'attr') $(el).attr(attr, value);

				return el;
			},
			format: function(tag)
			{
				this.document.execCommand("fontSize", false, 4);

				var fonts = this.$editor.find('font');
				var last;
				$.each(fonts, function(i,s)
				{
					var el = $('<' + tag + '/>').append($(s).contents());
					$(s).replaceWith(el);
					last = el;
				});

				if (last) this.selection.end.call(this, last);
			},
			removeFormat: function(tag)
			{
				var utag = tag.toUpperCase();
				var nodes = this.selection.getNodes.call(this);
				nodes.push(this.selection.getElement.call(this));

				$.each(nodes, function(i,s)
				{
					if (s.tagName === utag)
					{
						$(s).replaceWith($(s).contents());
					}
				});
			}
		},

		// SELECTION
		selection:
		{
			save: function()
			{
				this.$editor.focus();

				this.savedSel = this.selection.origin.call(this);
				this.savedSelObj = this.selection.focus.call(this);
			},
			restore: function()
			{
				if (typeof this.savedSel !== 'undefined' && this.savedSel !== null && this.savedSelObj !== null && this.savedSel[0].tagName !== 'BODY')
				{

					if (this.opts.iframe === false && $(this.savedSel[0]).closest('.redactor_editor').size() == 0)
					{
						this.focus.set.call(this);
					}
					else
					{
						if (this.utils.browser.call(this, 'opera')) this.$editor.focus();

						this.selection.set.call(this, this.savedSel[0], this.savedSel[1], this.savedSelObj[0], this.savedSelObj[1]);

						if (this.utils.browser.call(this, 'mozilla')) this.$editor.focus();
					}
				}
				else
				{
					this.focus.set.call(this);
				}
			},
			saveDynamic: function()
			{
				var id = 'buffer-marker-';

				$(this.$editor.find('span#' + id + '1')).remove();
				$(this.$editor.find('span#' + id + '2')).remove();

				this.selection.save.call(this);
				var node1 = $('<span id="' + id + '1">')[0];
				var node2 = $('<span id="' + id + '2">')[0];
				this.insert.beforeCaret.call(this, node1);
				this.insert.afterCaret.call(this, node2);
				this.selection.restore.call(this);
			},
			restoreDynamic: function()
			{
				var id = 'buffer-marker-';
				this.$editor.focus();

				var node1 = $(this.$editor.find('span#' + id + '1'));
				var node2 = $(this.$editor.find('span#' + id + '2'));

				if (node1.size() !== 0 && node2.size() !== 0)
				{
					this.selection.set.call(this, node1[0], 0, node2[0], 0);
					node1.remove();
					node2.remove();
				}
				else
				{
					this.focus.set.call(this);
				}
			},
			all: function()
			{
				var sel, range;
				if (this.window.getSelection && this.document.createRange)
				{
					range = this.document.createRange();
					range.selectNodeContents(this.$editor[0]);
					sel = this.window.getSelection();
					sel.removeAllRanges();
					sel.addRange(range);
				}
				else if (this.document.body.createTextRange)
				{
					range = this.document.body.createTextRange();
					range.moveToElementText(this.$editor[0]);
					range.select();
				}
			},
			get: function()
			{
				var doc = this.document;

				if (this.window.getSelection)
				{
					return this.window.getSelection();
				}
				else if (doc.getSelection)
				{
					return doc.getSelection();
				}
				else // IE
				{
					return doc.selection.createRange();
				}

				return false;
			},
			origin: function()
			{
				var sel;
				if (!((sel = this.selection.get.call(this)) && (sel.anchorNode != null)))
				{
					return null;
				}

				return [sel.anchorNode, sel.anchorOffset];
			},
			focus: function()
			{
				var sel;
				if (!((sel = this.selection.get.call(this)) && (sel.focusNode != null)))
				{
					return null;
				}

				return [sel.focusNode, sel.focusOffset];
			},
			getTextNodesIn: function(node)
			{
				var textNodes = [];

				if (node.nodeType == 3)
				{
					textNodes.push(node);
				}
				else
				{
					var children = node.childNodes;
					for (var i = 0, len = children.length; i < len; ++i)
					{
						textNodes.push.apply(textNodes, this.selection.getTextNodesIn.call(this, children[i]));
					}
				}

				return textNodes;
			},
			caretOffset: function(element)
			{
				var caretOffset = 0;
				if (typeof this.window.getSelection != "undefined")
				{
					var range = this.window.getSelection().getRangeAt(0);
					var preCaretRange = range.cloneRange();
					preCaretRange.selectNodeContents(element);
					preCaretRange.setEnd(range.endContainer, range.endOffset);
					caretOffset = $.trim(preCaretRange.toString()).length;
				}
				else if (typeof this.document.selection != "undefined" && this.document.selection.type != "Control")
				{
					var textRange = this.document.selection.createRange();
					var preCaretTextRange = this.document.body.createTextRange();
					preCaretTextRange.moveToElementText(element);
					preCaretTextRange.setEndPoint("EndToEnd", textRange);
					caretOffset = $.trim(preCaretTextRange.text).length;
				}

				return caretOffset;
			},
			caret: function (el, start, end)
			{
				if (typeof end === 'undefined')
				{
					end = start;
				}

				if (this.document.createRange && this.window.getSelection)
				{
					var range = this.document.createRange();
					range.selectNodeContents(el);
					var textNodes = this.getTextNodesIn(el);
					var foundStart = false;
					var charCount = 0, endCharCount;

					for (var i = 0, textNode; textNode = textNodes[i++];)
					{
						endCharCount = charCount + textNode.length;
						if (!foundStart && start >= charCount && (start < endCharCount || (start == endCharCount && i < textNodes.length)))
						{
							range.setStart(textNode, start - charCount);
							foundStart = true;
						}

						if (foundStart && end <= endCharCount)
						{
							range.setEnd(textNode, end - charCount);
							break;
						}

						charCount = endCharCount;
					}

					var sel = this.window.getSelection();
					sel.removeAllRanges();
					sel.addRange(range);
				}
				else if (this.document.selection && this.document.body.createTextRange)
				{
					var textRange = this.document.body.createTextRange();
					textRange.moveToElementText(el);
					textRange.collapse(true);
					textRange.moveEnd("character", end);
					textRange.moveStart("character", start);
					textRange.select();
				}
			},
			element: function(node)
			{
				this.selection.set.call(this, node[0], 1, node[0], 0);
			},
			start: function(node)
			{
				this.selection.set.call(this, node[0], 0, node[0], 0);
			},
			end: function(node)
			{
				this.selection.set.call(this, node[0], 1, node[0], 1);
			},
			set: function (orgn, orgo, focn, foco)
			{
				if (focn == null)
				{
					focn = orgn;
				}

				if (foco == null)
				{
					foco = orgo;
				}

				var sel = this.selection.get.call(this);
				if (!sel)
				{
					return;
				}

				if (sel.collapse && sel.extend)
				{
					sel.collapse(orgn, orgo);
					sel.extend(focn, foco);
				}
				else // IE9
				{
					r = this.document.createRange();
					r.setStart(orgn, orgo);
					r.setEnd(focn, foco);

					try
					{
						sel.removeAllRanges();
					}
					catch (e) {}

					sel.addRange(r);
				}
			},

			getNode: function()
			{
				var el = false;
				if (typeof this.window.getSelection !== 'undefined')
				{
					var s = this.window.getSelection();
					if (s.rangeCount > 0)
					{
						el = this.selection.get.call(this).getRangeAt(0).startContainer;
					}
				}
				else if (typeof this.document.selection !== 'undefined')
				{
					el = this.selection.get.call(this);
				}

				return this.utils.isParentRedactor.call(this, el);
			},
			getElement: function()
			{
				var el = false;
				if (typeof this.window.getSelection !== 'undefined')
				{
					var s = this.window.getSelection();
					if (s.rangeCount > 0)
					{
						el = s.getRangeAt(0).startContainer.parentNode;
					}
				}
				else if (typeof this.document.selection !== 'undefined')
				{
					el = this.selection.get.call(this).parentElement();
				}

				return this.utils.isParentRedactor.call(this, el);

			},
			getParent: function()
			{
				var el = this.selection.getElement.call(this);
				if (el)
				{
					return this.utils.isParentRedactor.call(this, $(el).parent()[0]);
				}
				else
				{
					return false;
				}
			},
			text: function()
			{
				var text = '';

				if (this.window.getSelection)
				{
					text = this.window.getSelection().toString();
				}
				else if (this.document.selection && this.document.selection.type != "Control")
				{
					text = this.document.selection.createRange().text;
				}

				return text;
			},
			html: function()
			{
				var html = '';

				if (this.window.getSelection)
				{
					var sel = this.window.getSelection();
					if (sel.rangeCount)
					{
						var container = this.document.createElement("div");
						for (var i = 0, len = sel.rangeCount; i < len; ++i)
						{
							container.appendChild(sel.getRangeAt(i).cloneContents());
						}

						html = container.innerHTML;
					}
				}
				else if (this.document.selection)
				{
					if (this.document.selection.type === "Text")
					{
						html = this.document.selection.createRange().htmlText;
					}
				}

				return html;
			},
			nodeTestBlocks: function(node)
			{
				return node.nodeType == 1 && /^(P|H[1-6]|DIV|LI|BLOCKQUOTE|PRE|ADDRESS|SECTION|HEADER|FOOTER|ASIDE|ARTICLE)$/i.test(node.nodeName);
			},
			getBlock: function(node)
			{
				if (typeof node === 'undefined')
				{
					node = this.selection.getNode.call(this);
				}

				while (node)
				{
					if (this.selection.nodeTestBlocks.call(this, node))
					{

						if ($(node).hasClass('redactor_editor'))
						{
							return false;
						}

						return node;
					}


					node = node.parentNode;
				}

				return false;
			},

			nextNode: function(node)
			{
				if (node.hasChildNodes())
				{
					return node.firstChild;
				}
				else
				{
					while (node && !node.nextSibling)
					{
						node = node.parentNode;
					}

					if (!node)
					{
						return null;
					}

					return node.nextSibling;
				}
			},
			getRangeSelectedNodes: function(range)
			{
				var node = range.startContainer;
				var endNode = range.endContainer;

				if (node == endNode)
				{
					return [node];
				}

				var rangeNodes = [];
				while (node && node != endNode)
				{
					rangeNodes.push( node = this.selection.nextNode.call(this, node) );
				}

				node = range.startContainer;
				while (node && node != range.commonAncestorContainer)
				{
					rangeNodes.unshift(node);
					node = node.parentNode;
				}

				return rangeNodes;
			},
			getNodes: function()
			{
				var nodes = [];
				var finalnodes = [];
				if (this.window.getSelection)
				{
					var sel = this.window.getSelection();
					if (!sel.isCollapsed)
					{
						nodes = this.selection.getRangeSelectedNodes.call(this, sel.getRangeAt(0));
					}
				}

				$.each(nodes, $.proxy(function(i,node)
				{
					if (this.opts.iframe === false && $(node).parents('div.redactor_editor').size() == 0)
					{
						return false;
					}

					finalnodes.push(node);

				}, this));

				return finalnodes;
			},
			getBlocks: function()
			{
				var nodes = this.selection.getNodes.call(this);
				var newnodes = [];

				$.each(nodes, $.proxy(function(i,node)
				{
					if (this.opts.iframe === false && $(node).parents('div.redactor_editor').size() == 0)
					{
						return false;
					}

					if (this.selection.nodeTestBlocks.call(this, node))
					{
						newnodes.push(node);
					}

				}, this));

				return newnodes;
			},

			remove: function()
			{
				if (this.window.getSelection)
				{
					this.window.getSelection().removeAllRanges();
				}
				else if (document.selection)
				{
					this.document.selection.empty();
				}
			}
		},

		// TABLE
		table:
		{
			show: function()
			{
				this.selection.save.call(this);

				this.modal.init.call(this, RLANG.table, this.opts.modal_table, 300, $.proxy(function()
					{
						$('#redactor_insert_table_btn').click($.proxy(this.table.insert, this));

						setTimeout(function()
						{
							$('#redactor_table_rows').focus();
						}, 200);

					}, this)
				);
			},
			insert: function()
			{
				var rows = $('#redactor_table_rows').val();
				var columns = $('#redactor_table_columns').val();

				var table_box = $('<div></div>');

				var tableid = Math.floor(Math.random() * 99999);
				var table = $('<table id="table' + tableid + '"><tbody></tbody></table>');

				for (var i = 0; i < rows; i++)
				{
					var row = $('<tr></tr>');
					for (var z = 0; z < columns; z++)
					{
						var column = $('<td>' + this.opts.invisibleSpace + '</td>');
						$(row).append(column);
					}
					$(table).append(row);
				}

				$(table_box).append(table);
				var html = $(table_box).html();

				this.selection.restore.call(this);
				this.insert.force.call(this, html);
				this.modal.close.call(this);
				this.observe.tables.call(this);

			},
			observer: function(e)
			{
				this.$table = $(e.target).closest('table');

				this.$table_tr = this.$table.find('tr');
				this.$table_td = this.$table.find('td');

				this.$tbody = $(e.target).closest('tbody');
				this.$thead = $(this.$table).find('thead');

				this.$current_td = $(e.target);
				this.$current_tr = $(e.target).closest('tr');
			},
			deleteTable: function()
			{
				$(this.$table).remove();
				this.$table = false;
				this.sync();
			},
			deleteRow: function()
			{
				$(this.$current_tr).remove();
				this.sync();
			},
			deleteColumn: function()
			{
				var index = $(this.$current_td).get(0).cellIndex;

				$(this.$table).find('tr').each(function()
				{
					$(this).find('td').eq(index).remove();
				});

				this.sync();
			},
			addHead: function()
			{
				if ($(this.$table).find('thead').size() !== 0)
				{
					this.table.deleteHead.call(this);
				}
				else
				{
					var tr = $(this.$table).find('tr').first().clone();
					tr.find('td').html(this.opts.invisibleSpace);
					this.$thead = $('<thead></thead>');
					this.$thead.append(tr);
					$(this.$table).prepend(this.$thead);
					this.sync();
				}
			},
			deleteHead: function()
			{
				$(this.$thead).remove();
				this.$thead = false;
				this.sync();
			},
			insertRowAbove: function()
			{
				this.table.insertRow.call(this, 'before');
			},
			insertRowBelow: function()
			{
				this.table.insertRow.call(this, 'after');
			},
			insertColumnLeft: function()
			{
				this.table.insertColumn.call(this, 'before');
			},
			insertColumnRight: function()
			{
				this.table.insertColumn.call(this, 'after');
			},
			insertRow: function(type)
			{
				var new_tr = $(this.$current_tr).clone();
				new_tr.find('td').html(this.opts.invisibleSpace);
				if (type === 'after')
				{
					$(this.$current_tr).after(new_tr);
				}
				else
				{
					$(this.$current_tr).before(new_tr);
				}

				this.sync();
			},
			insertColumn: function(type)
			{
				var index = 0;

				this.$current_tr.find('td').each($.proxy(function(i,s)
				{
					if ($(s)[0] === this.$current_td[0])
					{
						index = i;
					}
				}, this));

				this.$table_tr.each($.proxy(function(i,s)
				{
					var current = $(s).find('td').eq(index);

					var td = current.clone();
					td.html(this.opts.invisibleSpace);

					if (type === 'after') $(current).after(td);
					else $(current).before(td);

				}, this));

				this.sync();
			}
		},

		// VIDEO
		video:
		{
			show: function()
			{
				this.selection.save.call(this);
				this.modal.init.call(this, RLANG.video, this.opts.modal_video, 600, $.proxy(function()
					{
						$('#redactor_insert_video_btn').click($.proxy(this.video.insert, this));

						setTimeout(function()
						{
							$('#redactor_insert_video_area').focus();
						}, 200);

					}, this)
				);
			},
			insert: function()
			{
				var data = $('#redactor_insert_video_area').val();
				data = this.clean.stripTags.call(this, data);

				this.selection.restore.call(this);
				this.exec.command.call(this, 'inserthtml', data);
				this.modal.close.call(this);
			}
		},

		// IMAGE
		image:
		{
			show: function()
			{
				this.selection.save.call(this);

				var callback = $.proxy(function()
				{
					// json
					if (this.opts.imageGetJson !== false)
					{
						$.getJSON(this.opts.imageGetJson, $.proxy(function(data) {

							var folders = {};
							var z = 0;

							// folders
							$.each(data, $.proxy(function(key, val)
							{
								if (typeof val.folder !== 'undefined')
								{
									z++;
									folders[val.folder] = z;
								}

							}, this));

							var folderclass = false;
							$.each(data, $.proxy(function(key, val)
							{
								// title
								var thumbtitle = '';
								if (typeof val.title !== 'undefined')
								{
									thumbtitle = val.title;
								}

								var folderkey = 0;
								if (!$.isEmptyObject(folders) && typeof val.folder !== 'undefined')
								{
									folderkey = folders[val.folder];
									if (folderclass === false)
									{
										folderclass = '.redactorfolder' + folderkey;
									}
								}

								var img = $('<img src="' + val.thumb + '" class="redactorfolder redactorfolder' + folderkey + '" rel="' + val.image + '" title="' + thumbtitle + '" />');
								$('#redactor_image_box').append(img);
								$(img).click($.proxy(this.image.thumb, this));


							}, this));

							// folders
							if (!$.isEmptyObject(folders))
							{
								$('.redactorfolder').hide();
								$(folderclass).show();

								var onchangeFunc = function(e)
								{
									$('.redactorfolder').hide();
									$('.redactorfolder' + $(e.target).val()).show();
								}

								var select = $('<select id="redactor_image_box_select">');
								$.each(folders, function(k,v)
								{
									select.append($('<option value="' + v + '">' + k + '</option>'));
								});

								$('#redactor_image_box').before(select);
								select.change(onchangeFunc);
							}

						}, this));
					}
					else
					{
						$('#redactor_tabs a').eq(1).remove();
					}

					if (this.opts.imageUpload !== false)
					{

						// dragupload
						if (this.opts.uploadCrossDomain === false && this.utils.isMobile.call(this) === false)
						{
							if ($('#redactor_file').size() !== 0)
							{
								this.dragupload.init.call(this, '#redactor_file',
								{
									url: this.opts.imageUpload,
									uploadFields: this.opts.uploadFields,
									success: $.proxy(this.image.callback, this),
									error: $.proxy(this.opts.imageUploadErrorCallback, this)
								});
							}
						}

						// ajax upload
						this.upload.init.call(this, 'redactor_file',
						{
							auto: true,
							url: this.opts.imageUpload,
							success: $.proxy(this.image.callback, this),
							error: $.proxy(this.opts.imageUploadErrorCallback, this)
						});
					}
					else
					{
						$('.redactor_tab').hide();
						if (this.opts.imageGetJson === false)
						{
							$('#redactor_tabs').remove();
							$('#redactor_tab3').show();
						}
						else
						{
							var tabs = $('#redactor_tabs a');
							tabs.eq(0).remove();
							tabs.eq(1).addClass('redactor_tabs_act');
							$('#redactor_tab2').show();
						}
					}

					$('#redactor_upload_btn').click($.proxy(this.image.callbackLink, this));

					if (this.opts.imageUpload === false && this.opts.imageGetJson === false)
					{
						setTimeout(function()
						{
							$('#redactor_file_link').focus();
						}, 200);

					}

				}, this);

				this.modal.init.call(this, RLANG.image, this.opts.modal_image, 610, callback);

			},
			edit: function(e)
			{
				var $el = $(e.target);
				var parent = $el.parent();

				var callback = $.proxy(function()
				{
					$('#redactor_file_alt').val($el.attr('alt'));
					$('#redactor_image_edit_src').attr('href', $el.attr('src'));
					$('#redactor_form_image_align').val($el.css('float'));

					if ($(parent).get(0).tagName === 'A')
					{
						$('#redactor_file_link').val($(parent).attr('href'));
					}

					$('#redactor_image_delete_btn').click($.proxy(function() { this.image.remove.call(this, $el); }, this));
					$('#redactorSaveBtn').click($.proxy(function() { this.image.save.call(this, $el); }, this));

				}, this);

				this.modal.init.call(this, RLANG.image, this.opts.modal_image_edit, 380, callback);

			},
			remove: function(el)
			{
				$(el).remove();
				this.modal.close.call(this);
				this.sync();
			},
			save: function(el)
			{
				var parent = $(el).parent();

				$(el).attr('alt', $('#redactor_file_alt').val());

				var floating = $('#redactor_form_image_align').val();

				if (floating === 'left')
				{
					$(el).css({ 'float': 'left', margin: '0 10px 10px 0' });
				}
				else if (floating === 'right')
				{
					$(el).css({ 'float': 'right', margin: '0 0 10px 10px' });
				}
				else
				{
					$(el).css({ 'float': 'none', margin: '0' });
				}

				// as link
				var link = $.trim($('#redactor_file_link').val());
				if (link !== '')
				{
					if ($(parent).get(0).tagName !== 'A')
					{
						$(el).replaceWith('<a href="' + link + '">' + this.utils.outerHtml.call(this, el) + '</a>');
					}
					else
					{
						$(parent).attr('href', link);
					}
				}
				else
				{
					if ($(parent).get(0).tagName === 'A')
					{
						$(parent).replaceWith(this.utils.outerHtml.call(this, el));
					}
				}

				this.modal.close.call(this);
				this.observe.images.call(this);
				this.sync();

			},
			resize: function(resize)
			{
				var clicked = false;
				var clicker = false;
				var start_x;
				var start_y;
				var ratio = $(resize).width()/$(resize).height();
				var min_w = 10;
				var min_h = 10;

				$(resize).off('hover mousedown mouseup click mousemove');
	 			$(resize).hover(function() { $(resize).css('cursor', 'nw-resize'); }, function() { $(resize).css('cursor',''); clicked = false; });

				$(resize).mousedown(function(e)
				{
					e.preventDefault();

					ratio = $(resize).width()/$(resize).height();

					clicked = true;
					clicker = true;

					start_x = Math.round(e.pageX - $(resize).eq(0).offset().left);
					start_y = Math.round(e.pageY - $(resize).eq(0).offset().top);
				});

				$(resize).mouseup($.proxy(function(e)
				{
					clicked = false;
					$(resize).css('cursor','');
					this.sync();

				}, this));

				$(resize).click($.proxy(function(e)
				{
					if (clicker)
					{
						this.image.edit.call(this, e);
					}

				}, this));

				$(resize).mousemove(function(e)
				{
					if (clicked)
					{
						clicker = false;

						var mouse_x = Math.round(e.pageX - $(this).eq(0).offset().left) - start_x;
						var mouse_y = Math.round(e.pageY - $(this).eq(0).offset().top) - start_y;

						var div_h = $(resize).height();

						var new_h = parseInt(div_h, 10) + mouse_y;
						var new_w = new_h*ratio;

						if (new_w > min_w)
						{
							$(resize).width(new_w);
						}

						//if (new_h > min_h) $(resize).height(new_h);

						start_x = Math.round(e.pageX - $(this).eq(0).offset().left);
						start_y = Math.round(e.pageY - $(this).eq(0).offset().top);
					}
				});
			},
			thumb: function(e)
			{
				var img = '<img id="image-marker" src="' + $(e.target).attr('rel') + '" alt="' + $(e.target).attr('title') + '" />';

				if (this.opts.linebreaks === false)
				{
					img = '<p>' + img + '</p>';
				}

				this.image.insert.call(this, img, true);
			},
			callbackLink: function()
			{
				if ($('#redactor_file_link').val() !== '')
				{
					var data = '<img id="image-marker" src="' + $('#redactor_file_link').val() + '" />';
					if (this.opts.linebreaks === false)
					{
						data = '<p>' + data + '</p>';
					}

					this.image.insert.call(this, data, true);
				}
				else
				{
					this.modal.close.call(this);
				}
			},
			callback: function(data)
			{
				this.image.insert.call(this, data);
			},
			insert: function(json, link)
			{
				this.selection.restore.call(this);

				if (json !== false)
				{
					var html = '';
					if (link !== true)
					{
						html = '<img id="image-marker" src="' + json.filelink + '" />';
						if (this.opts.linebreaks === false)
						{
							html = '<p>' + html + '</p>';
						}
					}
					else
					{
						html = json;
					}

					this.exec.command.call(this, 'inserthtml', html);
					var image = $(this.$editor.find('img#image-marker'));

					if (image.size() != 0) image.removeAttr('id');
					else image = false;

					// upload image callback
					if (link !== true && typeof this.opts.imageUploadCallback === 'function')
					{
						this.opts.imageUploadCallback(this, image, json);
					}
				}

				this.modal.close.call(this);
				this.observe.images.call(this);
			},
		},

		// LINK
		link:
		{
			show: function()
			{
				this.selection.save.call(this);

				var callback = $.proxy(function()
				{
					this.insert_link_node = false;
					var sel = this.selection.get.call(this);
					var url = '', text = '', target = '';

					if (this.utils.browser.call(this, 'msie'))
					{
						var parent = this.selection.getElement.call(this);
						if (parent.nodeName === 'A')
						{
							this.insert_link_node = $(parent);
							text = this.insert_link_node.text();
							url = this.insert_link_node.attr('href');
							target = this.insert_link_node.attr('target');
						}
						else
						{
							if (this.utils.oldIE.call(this)) text = sel.text;
							else text = sel.toString();
						}
					}
					else
					{
						if (sel && sel.anchorNode && sel.anchorNode.parentNode.tagName === 'A')
						{
							url = sel.anchorNode.parentNode.href;
							text = sel.anchorNode.parentNode.text;
							target = sel.anchorNode.parentNode.target;

							if (sel.toString() === '')
							{
								this.insert_link_node = sel.anchorNode.parentNode;
							}
						}
						else
						{
							text = sel.toString();
						}
					}

					$('.redactor_link_text').val(text);

					var thref = self.location.href.replace(/\/$/i, '');
					var turl = url.replace(thref, '');

					var tabs = $('#redactor_tabs a');
					if (this.opts.linkEmail === false)
					{
						tabs.eq(1).remove();
					}

					if (this.opts.linkAnchor === false)
					{
						tabs.eq(2).remove();
					}

					if (this.opts.linkEmail === false && this.opts.linkAnchor === false)
					{
						$('#redactor_tabs').remove();
						$('#redactor_link_url').val(turl);
					}
					else
					{
						if (url.search('mailto:') === 0)
						{
							this.modal.setTab.call(this, 2);

							$('#redactor_tab_selected').val(2);
							$('#redactor_link_mailto').val(url.replace('mailto:', ''));
						}
						else if (turl.search(/^#/gi) === 0)
						{
							this.modal.setTab.call(this, 3);

							$('#redactor_tab_selected').val(3);
							$('#redactor_link_anchor').val(turl.replace(/^#/gi, ''));
						}
						else
						{
							$('#redactor_link_url').val(turl);
						}
					}

					if (target === '_blank')
					{
						$('#redactor_link_blank').attr('checked', true);
					}

					$('#redactor_insert_link_btn').click($.proxy(this.link.process, this));

					setTimeout(function()
					{
						$('#redactor_link_url').focus();
					}, 200);

				}, this);

				this.modal.init.call(this, RLANG.link, this.opts.modal_link, 460, callback);

			},
			process: function()
			{
				var tab_selected = $('#redactor_tab_selected').val();
				var link = '', text = '', target = '';

				if (tab_selected === '1') // url
				{
					link = $('#redactor_link_url').val();
					text = $('#redactor_link_url_text').val();

					if ($('#redactor_link_blank').attr('checked'))
					{
						target = ' target="_blank"';
					}

					// test url
					var pattern = '/(\w+:{0,1}\w*@)?(\S+)(:[0-9]+)?(\/|\/([\w#!:.?+=&%@!\-\/]))?/';
					//var pattern = '((xn--)?[a-z0-9]+(-[a-z0-9]+)*\.)+[a-z]{2,}';
					var re = new RegExp('^(http|ftp|https)://' + pattern,'i');
					var re2 = new RegExp('^' + pattern,'i');
					if (link.search(re) == -1 && link.search(re2) == 0 && this.opts.protocol !== false)
					{
						link = this.opts.protocol + link;
					}

				}
				else if (tab_selected === '2') // mailto
				{
					link = 'mailto:' + $('#redactor_link_mailto').val();
					text = $('#redactor_link_mailto_text').val();
				}
				else if (tab_selected === '3') // anchor
				{
					link = '#' + $('#redactor_link_anchor').val();
					text = $('#redactor_link_anchor_text').val();
				}

				this.link.insert.call(this, '<a href="' + link + '"' + target + '>' +  text + '</a>', $.trim(text), link, target);

			},
			insert: function(a, text, link, target)
			{
				this.selection.restore.call(this);

				if (text !== '')
				{
					if (this.insert_link_node)
					{
						$(this.insert_link_node).text(text);
						$(this.insert_link_node).attr('href', link);
						if (target !== '')
						{
							$(this.insert_link_node).attr('target', target);
						}
						else
						{
							$(this.insert_link_node).removeAttr('target');
						}

						this.sync();
					}
					else
					{
						this.exec.command.call(this, 'inserthtml', a);
					}
				}

				this.modal.close.call(this);
			}
		},

		// FILE
		file:
		{
			show: function()
			{
				this.selection.save.call(this);

				var callback = $.proxy(function()
				{
					var sel = this.selection.get.call(this);

					var text = '';

					if (this.utils.oldIE.call(this))
					{
						text = sel.text;
					}
					else
					{
						text = sel.toString();
					}

					$('#redactor_filename').val(text);

					// dragupload
					if (this.opts.uploadCrossDomain === false && this.utils.isMobile.call(this) === false)
					{
						this.dragupload.init.call(this, '#redactor_file',
						{
							url: this.opts.fileUpload,
							uploadFields: this.opts.uploadFields,
							success: $.proxy(this.file.callback, this),
							error: $.proxy(this.opts.fileUploadErrorCallback, this)
						});
					}

					this.upload.init.call(this, 'redactor_file',
					{
						auto: true,
						url: this.opts.fileUpload,
						success: $.proxy(this.file.callback, this),
						error: $.proxy(this.opts.fileUploadErrorCallback, this)
					});

				}, this);

				this.modal.init.call(this, RLANG.file, this.opts.modal_file, 500, callback);
			},
			callback: function(json)
			{
				this.selection.restore.call(this);

				if (json !== false)
				{
					var text = $('#redactor_filename').val();

					if (text === '')
					{
						text = json.filename;
					}

					var link = '<a href="' + json.filelink + '" id="filelink-marker">' + text + '</a>';

					// chrome fix
					if (this.utils.browser.call(this, 'webkit') && !!this.window.chrome)
					{
						link = link + '&nbsp;';
					}

					this.exec.command.call(this, 'inserthtml', link);
					var linkmarker = $(this.$editor.find('a#filelink-marker'));
					if (linkmarker.size() != 0)
					{
						linkmarker.removeAttr('id');
					}
					else
					{
						linkmarker = false;
					}

					// file upload callback
					if (typeof this.opts.fileUploadCallback === 'function')
					{
						this.opts.fileUploadCallback(this, linkmarker, json);
					}
				}

				this.modal.close.call(this);
			}
		},

		// MODAL
		modal:
		{
			init: function(title, content, width, callback)
			{
				// modal overlay
				if ($('#redactor_modal_overlay').size() === 0)
				{
					this.overlay = $('<div id="redactor_modal_overlay" style="display: none;"></div>');
					$('body').prepend(this.overlay);
				}

				if (this.opts.overlay)
				{
					$('#redactor_modal_overlay').show();
					$('#redactor_modal_overlay').click($.proxy(this.modal.close, this));
				}

				if ($('#redactor_modal').size() === 0)
				{
					this.$modal = $('<div id="redactor_modal" style="display: none;"><div id="redactor_modal_close">&times;</div><div id="redactor_modal_header"></div><div id="redactor_modal_inner"></div></div>');
					$('body').append(this.$modal);
				}

				$('#redactor_modal_close').click($.proxy(this.modal.close, this));

				this.hdlModalClose = $.proxy(function(e) { if ( e.keyCode === 27) { this.modal.close(); return false; } }, this);

				$(document).keyup(this.hdlModalClose);
				this.$editor.keyup(this.hdlModalClose);

				// set content
				if (content.indexOf('#') == 0)
				{
					$('#redactor_modal_inner').empty().append($(content).html());
				}
				else
				{
					$('#redactor_modal_inner').empty().append(content);
				}


				$('#redactor_modal_header').html(title);

				// draggable
				if (typeof $.fn.draggable !== 'undefined')
				{
					$('#redactor_modal').draggable({ handle: '#redactor_modal_header' });
					$('#redactor_modal_header').css('cursor', 'move');
				}

				// tabs
				if ($('#redactor_tabs').size() !== 0)
				{
					var that = this;
					$('#redactor_tabs a').each(function(i,s)
					{
						i++;
						$(s).click(function()
						{
							$('#redactor_tabs a').removeClass('redactor_tabs_act');
							$(this).addClass('redactor_tabs_act');
							$('.redactor_tab').hide();
							$('#redactor_tab' + i).show();
							$('#redactor_tab_selected').val(i);

							if (that.utils.isMobile.call(that) === false)
							{
								var height = $('#redactor_modal').outerHeight();
								$('#redactor_modal').css('margin-top', '-' + (height+10)/2 + 'px');
							}
						});
					});
				}

				$('#redactor_modal .redactor_btn_modal_close').click($.proxy(this.modal.close, this));

				if (this.utils.isMobile.call(this) === false)
				{
					$('#redactor_modal').css({ position: 'fixed', top: '-2000px', left: '50%', width: width + 'px', marginLeft: '-' + (width+60)/2 + 'px' }).show();

					this.modalSaveBodyOveflow = $(document.body).css('overflow');
					$(document.body).css('overflow', 'hidden');
				}
				else
				{
					$('#redactor_modal').css({ position: 'fixed', width: '100%', height: '100%', top: '0', left: '0', margin: '0', minHeight: '300px' }).show();
				}

				// callback
				if (typeof callback === 'function')
				{
					callback();
				}

				if (this.utils.isMobile.call(this) === false)
				{
					setTimeout(function()
					{
						var height = $('#redactor_modal').outerHeight();
						$('#redactor_modal').css({ top: '50%', height: 'auto', minHeight: 'auto', marginTop: '-' + (height+10)/2 + 'px' });

					}, 20);
				}

			},
			close: function()
			{
				$('#redactor_modal_close').unbind('click', this.modal.close);
				$('#redactor_modal').fadeOut('fast', $.proxy(function()
				{
					$('#redactor_modal_inner').html('');

					if (this.opts.overlay)
					{
						$('#redactor_modal_overlay').hide();
						$('#redactor_modal_overlay').unbind('click', this.modal.close);
					}

					$(document).unbind('keyup', this.hdlModalClose);
					this.$editor.unbind('keyup', this.hdlModalClose);

				}, this));


				if (this.utils.isMobile.call(this) === false)
				{
					$(document.body).css('overflow', this.modalSaveBodyOveflow ? this.modalSaveBodyOveflow : 'visible');
				}

				return false;

			},
			setTab: function(num)
			{
				$('.redactor_tab').hide();
				var tabs = $('#redactor_tabs a');
				tabs.removeClass('redactor_tabs_act');
				tabs.eq(num-1).addClass('redactor_tabs_act');
				$('#redactor_tab' + num).show();
			}
		},

		// UPLOAD
		upload:
		{
			init: function(el, options)
			{
				this.uploadOptions = {
					url: false,
					success: false,
					error: false,
					start: false,
					trigger: false,
					auto: false,
					input: false
				};

				$.extend(this.uploadOptions, options);

				// Test input or form
				if ($('#' + el).size() !== 0 && $('#' + el).get(0).tagName === 'INPUT')
				{
					this.uploadOptions.input = $('#' + el);
					this.el = $($('#' + el).get(0).form);
				}
				else
				{
					this.el = $('#' + el);
				}

				this.element_action = this.el.attr('action');

				// Auto or trigger
				if (this.uploadOptions.auto)
				{
					$(this.uploadOptions.input).change($.proxy(function()
					{
						this.el.submit(function(e) { return false; });
						this.upload.submit.call(this);
					}, this));

				}
				else if (this.uploadOptions.trigger)
				{
					$('#' + this.uploadOptions.trigger).click($.proxy(this.upload.submit, this));
				}
			},
			submit : function()
			{
				this.upload.form.call(this, this.element, this.upload.frame.call(this));
			},
			frame : function()
			{
				this.id = 'f' + Math.floor(Math.random() * 99999);

				var d = this.document.createElement('div');
				var iframe = '<iframe style="display:none" id="'+this.id+'" name="'+this.id+'"></iframe>';
				d.innerHTML = iframe;
				$(d).appendTo("body");

				// Start
				if (this.uploadOptions.start)
				{
					this.uploadOptions.start();
				}

				$('#' + this.id).load($.proxy(this.upload.loaded, this));

				return this.id;
			},
			form : function(f, name)
			{
				if (this.uploadOptions.input)
				{
					var formId = 'redactorUploadForm' + this.id;
					var fileId = 'redactorUploadFile' + this.id;
					this.form = $('<form  action="' + this.uploadOptions.url + '" method="POST" target="' + name + '" name="' + formId + '" id="' + formId + '" enctype="multipart/form-data"></form>');

					// append hidden fields
					if (this.opts.uploadFields !== false && typeof this.opts.uploadFields === 'object')
					{
						$.each(this.opts.uploadFields, $.proxy(function(k,v)
						{
							if (v.toString().indexOf('#') === 0)
							{
								v = $(v).val();
							}

							var hidden = $('<input/>', {'type': "hidden", 'name': k, 'value': v});
							$(this.form).append(hidden);

						}, this));
					}

					var oldElement = this.uploadOptions.input;
					var newElement = $(oldElement).clone();
					$(oldElement).attr('id', fileId);
					$(oldElement).before(newElement);
					$(oldElement).appendTo(this.form);
					$(this.form).css('position', 'absolute');
					$(this.form).css('top', '-2000px');
					$(this.form).css('left', '-2000px');
					$(this.form).appendTo('body');

					this.form.submit();
				}
				else
				{
					f.attr('target', name);
					f.attr('method', 'POST');
					f.attr('enctype', 'multipart/form-data');
					f.attr('action', this.uploadOptions.url);

					this.element.submit();
				}

			},
			loaded : function()
			{
				var i = $('#' + this.id)[0];
				var d;

				if (i.contentDocument) d = i.contentDocument;
				else if (i.contentWindow) d = i.contentWindow.document;
				else d = window.frames[this.id].document;

				// Success
				if (this.uploadOptions.success)
				{
					if (typeof d !== 'undefined')
					{
						// Remove bizarre <pre> tag wrappers around our json data:
						var rawString = d.body.innerHTML;
						var jsonString = rawString.match(/\{(.|\n)*\}/)[0];
						jsonString = jsonString.replace(/^\[/, '');
						jsonString = jsonString.replace(/\]$/, '');
						var json = $.parseJSON(jsonString);

						if (typeof json.error == 'undefined')
						{
							this.uploadOptions.success(json);
						}
						else
						{
							this.uploadOptions.error(this, json);
							this.modal.close.call(this);
						}
					}
					else
					{
						this.modal.close.call(this);
						alert('Upload failed!');
					}
				}

				this.element.attr('action', this.element_action);
				this.element.attr('target', '');

			}
		},

		// DRAGUPLOAD
		dragupload:
		{
			init: function(el, options)
			{
				this.draguploadOptions = $.extend({

					url: false,
					success: false,
					error: false,
					preview: false,
					uploadFields: false,

					text: RLANG.drop_file_here,
					atext: RLANG.or_choose

				}, options);

				if (window.FormData === undefined)
				{
					return false;
				}

				this.droparea = $('<div class="redactor_droparea"></div>');
				this.dropareabox = $('<div class="redactor_dropareabox">' + this.draguploadOptions.text + '</div>');
				this.dropalternative = $('<div class="redactor_dropalternative">' + this.draguploadOptions.atext + '</div>');

				this.droparea.append(this.dropareabox);

				$(el).before(this.droparea);
				$(el).before(this.dropalternative);

				// drag over
				this.dropareabox.bind('dragover', $.proxy(function() { return this.dragupload.ondrag.call(this); }, this));

				// drag leave
				this.dropareabox.bind('dragleave', $.proxy(function() { return this.dragupload.ondragleave.call(this); }, this));

				var uploadProgress = $.proxy(function(e)
				{
					var percent = parseInt(e.loaded / e.total * 100, 10);
					this.dropareabox.text('Loading ' + percent + '%');

				}, this);

				var xhr = jQuery.ajaxSettings.xhr();

				if (xhr.upload)
				{
					xhr.upload.addEventListener('progress', uploadProgress, false);
				}

				var provider = function () { return xhr; };

				// drop
				this.dropareabox.get(0).ondrop = $.proxy(function(e)
				{
					e.preventDefault();

					this.dropareabox.removeClass('hover').addClass('drop');

					var file = e.dataTransfer.files[0];
					var fd = new FormData();

					// append hidden fields
					if (this.draguploadOptions.uploadFields !== false && typeof this.draguploadOptions.uploadFields === 'object')
					{
						$.each(this.draguploadOptions.uploadFields, $.proxy(function(k,v)
						{
							if (v.toString().indexOf('#') === 0)
							{
								v = $(v).val();
							}

							fd.append(k, v);

						}, this));
					}

					// append file data
					fd.append('file', file);

					$.ajax({
						url: this.draguploadOptions.url,
						dataType: 'html',
						data: fd,
						xhr: provider,
						cache: false,
						contentType: false,
						processData: false,
						type: 'POST',
						success: $.proxy(function(data)
						{
							data = data.replace(/^\[/, '');
							data = data.replace(/\]$/, '');

							var json = $.parseJSON(data);

							if (typeof json.error == 'undefined')
							{
								this.draguploadOptions.success(json);
							}
							else
							{
								this.draguploadOptions.error(this, json);
								this.draguploadOptions.success(false);
							}

						}, this)
					});


				}, this);

			},
			ondrag: function()
			{
				this.dropareabox.addClass('hover');
				return false;
			},
			ondragleave: function()
			{
				this.dropareabox.removeClass('hover');
				return false;
			}
		},

		// UTILITIES
		utils:
		{
			browser: function(browser)
			{
				var ua = navigator.userAgent.toLowerCase();
				var match = /(chrome)[ \/]([\w.]+)/.exec(ua) || /(webkit)[ \/]([\w.]+)/.exec(ua) || /(opera)(?:.*version|)[ \/]([\w.]+)/.exec(ua) || /(msie) ([\w.]+)/.exec(ua) || ua.indexOf("compatible") < 0 && /(mozilla)(?:.*? rv:([\w.]+)|)/.exec(ua) || [];

				if (browser == 'version')
				{
					return match[2];
				}

				if (browser == 'webkit')
				{
					return (match[1] == 'chrome' || match[1] == 'webkit');
				}

				return match[1] == browser;
			},
			outerHtml: function(s)
			{
				return $('<div>').append($(s).eq(0).clone()).html();
			},
			oldIE: function()
			{
				if (this.utils.browser.call(this, 'msie') && parseInt(this.utils.browser.call(this, 'version'), 10) < 9)
				{
					return true;
				}

				return false;
			},
			normalize: function(str)
			{
				return parseInt(str.replace('px',''), 10);
			},
			removeEmptyStyleAttr: function(el)
			{
				if ($(el).attr('style') == '') $(el).removeAttr('style');
			},
			extractBlockContentsFromCaret: function()
			{
				var sel = this.window.getSelection();
				if (sel.rangeCount)
				{
					var selRange = sel.getRangeAt(0);
					var blockEl = this.selection.getBlock.call(this, selRange.endContainer);
					if (blockEl)
					{
						var range = selRange.cloneRange();
						range.selectNodeContents(blockEl);
						range.setStart(selRange.endContainer, selRange.endOffset);
						return range.extractContents();
					}
				}
			},
			getFragmentHtml: function (fragment)
			{
				var cloned = fragment.cloneNode(true);
				var div = this.document.createElement('div');
				div.appendChild(cloned);
				return div.innerHTML;
			},
			extractContent: function()
			{
				var node = this.$editor.get(0);
				var frag = this.document.createDocumentFragment(), child;
				while ((child = node.firstChild))
				{
					frag.appendChild(child);
				}

				return frag;
			},
			isParentRedactor: function(el)
			{
				if (!el) return false;
				if (this.opts.iframe) return el;

				if ($(el).parents('div.redactor_editor').size() == 0 || $(el).hasClass('redactor_editor')) return false;
				else return el;
			},
			isEndOfElement: function()
			{
				var current = this.selection.getBlock.call(this);

				var offset = this.selection.caretOffset.call(this, current);
				var text = $.trim($(current).text()).replace(/\n\r\n/g, '');

				var len = text.length;

				if (offset == len) return true;
				else return false;
			},

			// Mobile
			isMobile: function(ipad)
			{
				if (ipad === true && /(iPhone|iPod|iPad|BlackBerry|Android)/.test(navigator.userAgent))
				{
					return true;
				}
				else if (/(iPhone|iPod|BlackBerry|Android)/.test(navigator.userAgent))
				{
					return true;
				}
				else
				{
					return false;
				}
			}
		}

	};

})(jQuery);

(function($) {
	"use strict";
	var protocol = 'http://';
	var url1 = /(^|&lt;|\s)(www\..+?\..+?)(\s|&gt;|$)/g, url2 = /(^|&lt;|\s)(((https?|ftp):\/\/|mailto:).+?)(\s|&gt;|$)/g,
	linkifyThis = function ()
	{
		var childNodes = this.childNodes, i = childNodes.length;
		while (i--)
		{
			var n = childNodes[i];
			if (n.nodeType === 3)
			{
				var html = n.nodeValue;

				if (html && (html.match(url1) || html.match(url2)))
				{
					html = html.replace(/&/g, '&amp;')
								.replace(/</g, '&lt;')
								.replace(/>/g, '&gt;')
								.replace(url1, '$1<a href="' + protocol + '$2">$2</a>$3')
								.replace(url2, '$1<a href="$2">$2</a>$5');

					$(n).after(html).remove();
				}
			}
			else if (n.nodeType === 1  &&  !/^(a|button|textarea)$/i.test(n.tagName))
			{
				linkifyThis.call(n);
			}
		}
	};

	$.fn.linkify = function ()
	{
		this.each(linkifyThis);
	};

})(jQuery);



/* jQuery plugin textselect
 * version: 0.9
 * author: Josef Moravec, josef.moravec@gmail.com
 * updated: Imperavi Inc.
 *
 */
(function ($) {
    $.event.special.textselect = {
        setup: function (data, namespaces) {
            $(this).data("textselected", false);
            $(this).data("ttt", data);
            $(this).bind('mouseup', $.event.special.textselect.handler)
        },
        teardown: function (data) {
            $(this).unbind('mouseup', $.event.special.textselect.handler)
        },
        handler: function (event) {
            var data = $(this).data("ttt");
            var text = $.event.special.textselect.getSelectedText(data).toString();
            if (text != '') {
                $(this).data("textselected", true);
                event.type = "textselect";
                event.text = text;
               $.event.dispatch.apply(this, arguments)
            }
        },
        getSelectedText: function (data) {
            var text = '';
            if (rwindow.getSelection) {
                text = rwindow.getSelection()
            } else if (rdocument.getSelection) {
                text = rdocument.getSelection()
            } else if (rdocument.selection) {
                text = rdocument.selection.createRange().text
            }
            return text
        }
    };
    $.event.special.textunselect = {
        setup: function (data, namespaces) {
            $(this).data("rttt", data);
            $(this).data("textselected", false);
            $(this).bind('mouseup', $.event.special.textunselect.handler);
            $(this).bind('keyup', $.event.special.textunselect.handlerKey)
        },
        teardown: function (data) {
            $(this).unbind('mouseup', $.event.special.textunselect.handler)
        },
        handler: function (event) {
            if ($(this).data("textselected")) {
                var data = $(this).data("rttt");
                var text = $.event.special.textselect.getSelectedText(data).toString();
                if (text == '') {
                    $(this).data("textselected", false);
                    event.type = "textunselect";
                    $.event.dispatch.apply(this, arguments)
                }
            }
        },
        handlerKey: function (event) {
            if ($(this).data("textselected")) {
                var data = $(this).data("rttt");
                var text = $.event.special.textselect.getSelectedText(data).toString();
                if ((event.keyCode = 27) && (text == '')) {
                    $(this).data("textselected", false);
                    event.type = "textunselect";
                    $.event.dispatch.apply(this, arguments)
                }
            }
        }
    }
})(jQuery);