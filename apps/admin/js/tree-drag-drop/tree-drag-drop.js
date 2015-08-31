/*global window, $, jQuery*/

if (typeof String.prototype.trim !== 'function') {
	String.prototype.trim = function () {
		"use strict";
		return this.replace(/^\s+|\s+$/g, '');
	};
}

(function ($, undef) {
	"use strict";

	$.treeDragDrop = {
		
		defaults: {			
			selectedClass: "tdd-selected",
			collapsedClass: "tdd-collapsed",
			expandedClass: "tdd-expanded",
			beforeClass: "tdd-before", 
			afterClass: "tdd-after",
			cursorGrabbingUrl: null,
			inFolderThreshhold: 100,
			cursorAt: {left: 10, top: -40}, 
			dragContainer: $('<div class="tdd-dragContainer" />'),			
			marker: $('<div />'),
			attributes: ["id", "class"],
			getUrl: null,
			updateUrl: null		
		}		
	};
	
	// helpers
	
	function debug(txt) {
		if (window.console && window.console.log) {
			window.console.log(txt);
		}
	}
	
	function getContext(el) {
		return $(el).closest(".treeDragDrop");
	}
	
	function getOptions(el) {
		return $(el).closest(".treeDragDrop").data("options");
	}
	
	function serializeTree(el) {
		var tool, tools, obj, data = [];
		el.find('.section').each(function (index, node) {
			obj = {};
			tools = [];
			obj.name = node.getAttribute('data-section');
			$(node).find('.tool').each(function (index, subnode) {
				tool = {};
				tool.name = subnode.getAttribute('data-name');
				tool.handler = subnode.getAttribute('data-handler');
				tools.push(tool);
			});
			obj.tools = tools;
			data.push(obj);
		});
		return data;
	}
		
	function sendTree(data, updateUrl) {
		if (updateUrl !== null) {
			$.post(updateUrl, {
				data: serializeTree(data), 
				autofill: data.find('.special').length
			}, function (res) {
				console.log (res);
				//TODO: error handling
				return true;
			});
		}	
	}
	// handlers	
		
	$.treeDragDrop.handlers = {
		
		handleDraggableStart: function (e, o) {
			var options = getOptions($(e.target));			
			$(e.target).addClass(getOptions($(e.target)).selectedClass);
			document.onmousemove = function () {
				return false;
			};
			$("body").css("cursor", "url(" + options.cursorGrabbingUrl + ") , move").addClass("cursorGrabbing");
		},
		
		handleDraggableDrag: function (e, o) {				
		},
		
		handleDraggableStop: function (e, o) {
			
			var ctx = getContext($(e.target)),
				options = getOptions($(e.target)),		
				tree = $(".tdd-tree", ctx);		
			
			// remove the mousemove Listener			
			$("li, .tdd-tree", ctx).unbind("mousemove");
			
			// remove sections from trashbin
			$(".tdd-trashbin .section:not(.special)").remove();
			
			// build the array and post the ajax
			sendTree(tree, options.updateUrl);
			
			$("body").removeClass("cursorGrabbing").css("cursor", "auto");
		},
		
		handleDroppableOut: function (e, o) {
			$(e.target).unbind("mousemove");						
		},
		
		handleDroppableOver: function (e, o) {
			var	options = getOptions($(e.target)),
				selectedClass = options.selectedClass,
				beforeClass = options.beforeClass,
				afterClass = options.afterClass,
				draggable = $(o.draggable),
				dropable = $(e.target),
				marker = options.marker;
			if (dropable.is('li')) {
				// bind MouseMove to the item to check if the draggable should be appended or placed before or after the item 
				dropable.bind('mousemove', function (mme) {
					
					var target = $(mme.target),						
						x = mme.pageX - $(mme.target).offset().left,
						y = mme.pageY - $(mme.target).offset().top,
						threshhold = options.inFolderThreshhold;
					
					// threshhold for apending or placing before/ater
					// will grow according to the deepness of nesting
					
					if (target.find('ul').length !== 0) {
						threshhold = Math.min(options.inFolderThreshhold * (target.find('ul').length + 1), target.width() * 0.75);
					}
					
					marker.removeClass(beforeClass, afterClass);
					
					if (target.parents('.tdd-trashbin').length !== 0) {						
						target.parents('.tdd-trashbin').append(marker);
					} else if (target.hasClass('section') && draggable.hasClass('tool')) {
						marker.addClass(afterClass);
						if (target.children('.tools').length === 0) {
							target.append('<ul class="tools"></ul>');
						}
						target.children('.tools').prepend(marker);
					} else if (draggable.hasClass('special')) {
						marker.addClass(beforeClass);
						target.parents('.tdd-tree').append(marker);
					} else if (draggable.hasClass('section') && target.hasClass('special')) {
						marker.addClass(beforeClass);
						target.before(marker);
					} else {
						if (target.parent().hasClass('tools') && !draggable.hasClass('tool')) return;
						if (target.parent().hasClass('tdd-tree') && draggable.hasClass('tool')) return;
						// append to item
						if (y < target.height() / 2) {
							marker.addClass(beforeClass);
							target.before(marker);
						// place after item
						} else {
							marker.addClass(afterClass);
							target.after(marker);
						}
					}
				});
				
			// if tree is empty items may be put in the ul 
			} else if (dropable.hasClass("tdd-tree") && draggable.hasClass('section')) {
				marker.removeClass(beforeClass, afterClass);
				marker.addClass(beforeClass);
				dropable.append(marker);
				if (dropable.children('.special').length !== 0) dropable.append(dropable.children('.special'));
			} else if (dropable.hasClass("tdd-trashbin")) {
				dropable.append(marker);
			}
		},
		
		handleDroppableDrop: function (e, o) {
			
			var	draggable = $(o.draggable),
				dropable = $(e.target),
				marker = $.treeDragDrop.defaults.marker,
				ctx = draggable.data("tddCtx");
				if (!ctx) return;
				var options = ctx.data("options");
				
			// remove selection	
			draggable.removeClass(options.selectedClass);
			
			// if its the trashbin put them all next to each other (no nesting)
			if (dropable.parents(".tdd-trashbin").length !== 0 || dropable.hasClass("tdd-trashbin")) {
				$(".tdd-trashbin").append(draggable);	
				$("li", draggable).each(function (index, value) {
					$(".tdd-trashbin").append(value);
				});
				
			} else if (draggable.hasClass('tool') && dropable.hasClass('tdd-tree')) {
				return;
			// put the item directly in the tree ul if it contains no other element	
			} else if (dropable.hasClass("tdd-tree") && $(".tdd-tree").children().length === 0) {
				$(".tdd-tree").append(draggable);
			// otherwise put it before the marker, which will be detached asap
			} else {				
				marker.before(draggable);
			}			
			marker.detach();
			//clean up empty uls if its not the tree or trashbin
			$("ul", ctx).not(".tdd-trashbin, .tdd-tree").each(function () {
				if ($(this).children().length === 0) {
					$(this).remove();					
				}
			});	
		},
		
		handleOpenModal: function() {
			var title = '', html = '<form id="treeModal" \
				onsubmit="if($.treeDragDrop.handlers.handleAddCategory()) { $.close_dialog (); } return false;">\
				<span class="caption error"></span>\
				<label for="add-category">Category Name<br><input type="text" id="add-category" style="width: 95%" /></label><br><br>\
				<input type="submit" value="Add" style="float:right" />\
			</form>';
			$.open_dialog('New Catagory',html,{width:250,height:200});
			$('#treeModal #add-category')[0].focus();
		},
		
		handleAddCategory: function () {
			var modal = $('#treeModal'), name = modal.find('input#add-category');
			if (name.val() == '') {
				modal.find('.error').text('Category name must be specified.');
				return false;
			}
			var id = 'cat_'+ name.val().toLowerCase().replace(/ /g,'_');
			if ($('.treeDragDrop #'+ id).length) {
				modal.find('.error').text('Category name already in use.');
				return false;
			}
			modal.find('.error').text('');
			var node = document.createElement("LI");
			node.id = id;
			node.setAttribute('data-section', name.val());
			node.setAttribute('class', 'section');
			node.innerHTML = '<b>'+ name.val() +'</b><ul class="tools"></ul>';
			$(".tree .tdd-tree").append(node);
			if (node.previousElementSibling && /special/.test(node.previousElementSibling.className)) {
				$(node.previousElementSibling).before(node);
			}
			$(node).data('tddCtx',$(node).closest('.treeDragDrop'));
			name.val("");
			
			$('.treeDragDrop').treeDragDrop({ // rebind drag/drop for new node.
				updateUrl: "/admin/api/toolbar",
				cursorGrabbingUrl: (window.is_msie) ? "/apps/admin/js/tree-drag-drop/css/closedhand.cur" : "/apps/admin/js/tree-drag-drop/css/cursorGrabbing.png"
			}, '#'+ id); 
			return true
		}
	};
	
	
	// the Prototype
		
	$.fn.treeDragDrop = function (options, node) {
		
		//extend the global default with the options for the element
		options = $.extend({}, $.treeDragDrop.defaults, options);
						
		return this.each(function () {
			var ctx = $(this),
				data = ctx.data('treeDragDrop');
			
			node = (node)?node:"li";
			
			// init the element(s)
			if (!data || node !== "li") {	
				$(node, ctx).draggable({ 
					addClasses: false,
					cursorAt:  $.treeDragDrop.defaults.cursorAt,
					helper: "clone",
					appendTo: "body",
					opacity: 0.2,
					delay: 10,
					start: $.treeDragDrop.handlers.handleDraggableStart,
					stop: $.treeDragDrop.handlers.handleDraggableStop
				}).droppable({
					addClasses: false,
					greedy: false,
					tolerance: "pointer",
					drop: $.treeDragDrop.handlers.handleDroppableDrop,
					over: $.treeDragDrop.handlers.handleDroppableOver,
					out: $.treeDragDrop.handlers.handleDroppableOut
					
				}).bind("onselectstart", function () { 
					return false;
				}).attr("unselectable", "on").data("tddCtx", ctx);
			}
			// init the tree(s)
			if (!data) {
				$(".tdd-tree, .tdd-trashbin", ctx).droppable({
					addClasses: false,				
					tolerance: "pointer",
					drop: $.treeDragDrop.handlers.handleDroppableDrop,
					over: $.treeDragDrop.handlers.handleDroppableOver,
					out: $.treeDragDrop.handlers.handleDroppableOut				
				}).bind("onselectstart", function () {return false; }).attr("unselectable", "on");
				
				
				$.treeDragDrop.defaults.marker.bind("mousemove", function () { return false; });
				$.treeDragDrop.defaults.marker.bind("mouseover", function () { return false; });
				
			
				ctx.data('options',  options);											
				ctx.data('treeDragDrop', {inited: true});				
			}				
		});
	};
	
}(jQuery));

// Cheap test for MSIE
window.is_msie = (window.navigator.userAgent.indexOf ('MSIE ') > -1);

$('.treeDragDrop').treeDragDrop({
	updateUrl: "/admin/api/toolbar",
	cursorGrabbingUrl: (window.is_msie) ? "/apps/admin/js/tree-drag-drop/css/closedhand.cur" : "/apps/admin/js/tree-drag-drop/css/cursorGrabbing.png"
}); 

