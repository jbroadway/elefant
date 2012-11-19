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
			appendClass: "tdd-append", 
			beforeClass: "tdd-before", 
			afterClass: "tdd-after",
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
		return el.closest(".treeDragDrop");
	}
	
	function getOptions(el) {
		return el.closest(".treeDragDrop").data("options");
	}
	
	function serializeTree(el, ctx) {
		var data = [],
			intestingAttr = getOptions(el).attributes;
		
		el.children("li").each(function (index, value) {			
			var obj = {},
				attr = {};
				
			obj.data = $(value).clone().children().remove().end().text().trim();
			$.each(intestingAttr, function (index, attribute) {
				if ($(value).attr(attribute) !== undef) {
					if (attribute === "class") {
						attr.classname = $(value).attr(attribute);
					} else {
						attr[attribute] = $(value).attr(attribute);
					}
				}
			});			
			obj.attr = attr;								
			if ($(value).children("ul").length > 0) {
				obj.children = serializeTree($(value).children("ul"));
			}			
			data.push(obj);
		});	
		
		return data;
	}
		
	function sendTree(tree, updateUrl) {
		var ajaxData, 
			treeData = serializeTree(tree);
		
		if (updateUrl !== null) {
			ajaxData = {tree: treeData};
			debug(ajaxData);
			$.post(updateUrl, ajaxData, function (res) {
				//console.log (res.data.msg);
				//TODO: error handling
				return true;
			});
		}	
	}
		
	// handlers	
		
	$.treeDragDrop.handlers = {
				
		handleDraggableStart: function (e, o) {			
			debug("handleDraggableStart");	
			$(e.target).addClass(getOptions($(e.target)).selectedClass);
			document.onmousemove = function () {
				return false;
			};	
		},
		
		handleDraggableDrag: function (e, o) {
			debug("handleDraggableDrag");						
		},
		
		handleDraggableStop: function (e, o) {
			debug("handleDraggableStop");
			
			var ctx = getContext($(e.target)),
				options = getOptions($(e.target)),			
				tree = $(".tdd-tree", ctx);			
			// remove the mousemove Listener			
			$("li, .tdd-tree", ctx).unbind("mousemove").removeClass(options.selectedClass);
			
			// build the array and post the ajax
			debug("handleDraggableStop: sendTree");
			sendTree(tree, options.updateUrl);
			
			
		},
		
		handleDroppableOut: function (e, o) {
			$(e.target).unbind("mousemove");						
		},
		
		handleDroppableOver: function (e, o) {
			debug("handleDroppableOver");
			var	options = getOptions($(e.target)),
				selectedClass = options.selectedClass,
				appendClass = options.appendClass, 
				beforeClass = options.beforeClass,
				afterClass = options.afterClass,
				marker = options.marker;		
			
			if ($(e.target).is("li")) {
				// bind MouseMove to the item to check if the draggable should be appended or placed before or after the item 
				$(e.target).bind("mousemove", function (mme) {
					
					var target = $(mme.target),						
						x = mme.pageX - $(mme.target).offset().left,
						y = mme.pageY - $(mme.target).offset().top,
						threshhold = options.inFolderThreshhold;
					
					// threshhold for apending or placing before/ater
					// will grow according to the deepness of nesting
					
					if (target.find("ul").length !== 0) {
						threshhold = Math.min(options.inFolderThreshhold * (target.find("ul").length + 1), target.width() * 0.75);
					}
					
					marker.removeClass(appendClass, beforeClass, afterClass);
										
					// prevent dropping items in itself
					if (target.hasClass(selectedClass) || target.parents("." + selectedClass).length !== 0 || target.parents(".tdd-trashbin").length !== 0) {
						marker.detach();
					} else {
						// append to item
						if (x > threshhold) {							
							// append to ul if there is one
							if (target.is("li") && target.children("ul").length !== 0) {
								target.children("ul").append(marker);
							} else if (target.is("li")) {
								target.append(marker);
							}
						// place before item	
						} else if (y < target.height() / 2) {
							marker.addClass(beforeClass);
							target.before(marker);
						// place after item
						} else {
							marker.addClass(afterClass);
							target.after(marker);
						}
					}
							
					//e.stopImmediatePropagation();
				});
							
			// if tree is empty items may be put in the ul 
			//} else if ($(e.target).hasClass("tdd-tree")/* && $(".tdd-tree").children().length === 0 */) {
			} else if ($(e.target).hasClass("tdd-tree")) {
				debug("tree");
				marker.removeClass(appendClass, beforeClass, afterClass);
				marker.addClass(beforeClass);
				$(e.target).append(marker);
			} else if ($(e.target).hasClass("tdd-trashbin")) {
				debug("trashbin");
				marker.detach();
			}
			
			//e.stopImmediatePropagation();		
		},
		
		handleDroppableDrop: function (e, o) {
			debug("handleDroppableDrop");
			
			var	draggable = $(o.draggable),
				dropable = $(e.target),
				marker = $.treeDragDrop.defaults.marker,
				ctx = draggable.data("tddCtx"),
				options = ctx.data("options");
				
			// remove selection	
			draggable.removeClass(options.selectedClass);
			
			// if its the trashbin put them all next to each other (no nesting)
			if (dropable.parents(".tdd-trashbin").length !== 0 || dropable.hasClass("tdd-trashbin")) {
				$(".tdd-trashbin").append(draggable);	
				$("li", draggable).each(function (index, value) {
					$(".tdd-trashbin").append(value);
				});				
				
			// put the item directly in the tree ul if it contains no other element	
			} else if (dropable.hasClass("tdd-tree") && $(".tdd-tree").children().length === 0) {
				$(".tdd-tree").append(draggable);
			// otherwise put it before the marker, which will be detached asap
			} else {				
				marker.before(draggable);				
				if (draggable.parent().is("li")) {					
					draggable.wrap("<ul></ul>");				
				}
			}			
			marker.detach();
			//clean up empty uls if its not the tree or trashbin
			$("ul", ctx).not(".tdd-trashbin, .tdd-tree").each(function () {
				if ($(this).children().length === 0) {
					debug($(this));
					$(this).remove();					
				}
			});	
			// adjust expand/collapse icons 
			$("li", ctx).has("ul").not("li." + options.collapsedClass).addClass(options.expandedClass);
			$("li", ctx).not("li:has(ul)").removeClass(options.expandedClass).removeClass(options.collapsedClass);							
		},
		
		
		// toggle expand/collapse
		handleClick: function (e) {
			
			var target = $(e.target),
				ctx = getContext($(e.target)),
				tree = $(".tdd-tree", ctx),
				options = getOptions($(e.target)),
				collapsed = options.collapsedClass,
				expanded = options.expandedClass;
			
			
			if (target.children("ul").length === 0) {				
				return false;				
			} else {			
				if (target.hasClass(collapsed)) {
					target.removeClass(collapsed).addClass(expanded);					
					target.children("ul").show();					
				} else {
					target.removeClass(expanded).addClass(collapsed);	
					target.children("ul").hide();
				}
			}	
			
			debug("handleClick: sendTree");
			sendTree(tree, options.updateUrl);	
					
			e.stopImmediatePropagation();
		}
	};
	
	
	// the Prototype
		
	$.fn.treeDragDrop = function (options) {
		
		//extend the global default with the options for the element
		options = $.extend({}, $.treeDragDrop.defaults, options);
		
		
		
		return this.each(function () {
			var ctx = $(this),
				data = ctx.data('treeDragDrop');
			
			// init the element
			if (!data) {	
				$("li", ctx).draggable({ 
					addClasses: false,
					cursorAt:  $.treeDragDrop.defaults.cursorAt,
					helper: "clone",
					appendTo: "body",
					opacity: 0.2,
					delay: 10,
					//create: $.treeDragDrop.handlers.handleDraggableCreate,
					start: $.treeDragDrop.handlers.handleDraggableStart,
					//drag: $.treeDragDrop.handlers.handleDraggableDrag,
					stop: $.treeDragDrop.handlers.handleDraggableStop
				}).droppable({
					addClasses: false,
					greedy: false,
					tolerance: "pointer",
					drop: $.treeDragDrop.handlers.handleDroppableDrop,
					over: $.treeDragDrop.handlers.handleDroppableOver,
					out: $.treeDragDrop.handlers.handleDroppableOut
					
				}).bind(
					"click", 
					$.treeDragDrop.handlers.handleClick
				).bind("onselectstart", function () { 
					return false;
				}).attr("unselectable", "on").data("tddCtx", ctx).has("ul").addClass(options.expandedClass);
				
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

$('.treeDragDrop').treeDragDrop({
	collapsedClass: "icon-folder-close", 
	expandedClass: "icon-folder-open", 
	updateUrl: "/navigation/api/update"
}); 

