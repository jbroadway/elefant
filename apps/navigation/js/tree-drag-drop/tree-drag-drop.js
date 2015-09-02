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
		return $(".treeDragDrop").first ();
	}
	
	function getOptions(el) {
		return $(".treeDragDrop").first ().data("options");
	}
	
	function serializeTree(el, ctx) {
		var data = [],
			intestingAttr = getOptions(el).attributes;
		
		el.children("li").each(function (index, value) {			
			var obj = {},
				attr = {};
						
			obj.data = $(value).clone().children().remove().end().text().trim();
			$.each(intestingAttr, function (index, attribute) {
				
				if ($(value).attr(attribute) !== undef || attribute === "class") {
					if (attribute === "class") {						
						if ($(value).children("i").attr(attribute) !== undef) {
							attr.classname = $(value).children("i").attr(attribute);
							debug("serializeTree: attr.classname " + attr.classname);
						}						
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
		
		debug(treeData);		
		if (updateUrl !== null) {
			ajaxData = {tree: treeData};
			$.post(updateUrl, ajaxData, function (res) {
				//debug (res.data.msg);
				//TODO: error handling
				return true;
			});
		}	
	}
	
	function updateFolderIcons(ctx, options) {
		debug("updateFolderIcons:");
		
		//debug($("li:not(>ul)", ctx).length);
		//debug($("li>ul", ctx).length);
		//debug($("li", ctx).length);
		
		$("li:not(:has(ul))", ctx).children("i").removeClass(options.expandedClass).removeClass(options.collapsedClass).removeClass ('fa');
		$("li>ul", ctx).siblings("i:not(." + options.collapsedClass + ")").addClass ('fa').addClass(options.expandedClass);
	}
	// handlers	
		
	$.treeDragDrop.handlers = {
				
		handleDraggableStart: function (e, o) {			
			debug("handleDraggableStart");

			var options = getOptions($(e.target));			

			// Add selected class to element being dragged
			$(e.target).addClass(getOptions($(e.target)).selectedClass);

			document.onmousemove = function () {
				return false;
			};

			// Set cursor to grabbing			
			$("body").css("cursor", "url(" + options.cursorGrabbingUrl + ") , move").addClass("cursorGrabbing");
		},
		
		handleDraggableDrag: function (e, o) {
			debug("handleDraggableDrag");						
		},
		
		handleDraggableStop: function (e, o) {
			debug("handleDraggableStop");
			
			var ctx = getContext($(e.target)),
				options = getOptions($(e.target)),			
				tree = $(".tdd-tree", ctx);

			// Remove the mousemove listener and selected class
			$("li, .tdd-tree", ctx).unbind("mousemove").removeClass(options.selectedClass);
			
			// Build the array and post the ajax
			debug("handleDraggableStop: sendTree");
			sendTree(tree, options.updateUrl);

			// Reset cursor to default
			$("body").removeClass("cursorGrabbing").css("cursor", "auto");
		},
		
		handleDroppableOut: function (e, o) {
			$(e.target).unbind("mousemove");						
		},
		
		handleDroppableOver: function (e, o) {
			debug("handleDroppableOver");
			var	options = getOptions($(e.target)),
				selectedClass = options.selectedClass,
				beforeClass = options.beforeClass,
				afterClass = options.afterClass,
				marker = options.marker;

			marker.show ();
			
			if ($(e.target).is("li")) {
				// Bind mousemove to the item to check if the draggable should be appended or placed before or after the item 
				$(e.target).bind("mousemove", function (mme) {
					
					var target = $(mme.target),
						x = mme.pageX - $(mme.target).offset().left,
						y = mme.pageY - $(mme.target).offset().top,
						threshhold = options.inFolderThreshhold;
					
					// Threshhold for apending or placing before/after
					// will grow according to the deepness of nesting
					if (target.find("ul").length !== 0) {
						threshhold = Math.min(options.inFolderThreshhold * (target.find("ul").length + 1), target.width() * 0.75);
					}
					
					marker.removeClass(beforeClass, afterClass);
										
					// Prevent dropping an item in itself
					if (target.hasClass(selectedClass) || target.parents("." + selectedClass).length !== 0) {
						marker.detach();

					// Add to trashbin
					} else if (target.parents(".tdd-trashbin").length !== 0) {						
						target.parents(".tdd-trashbin").append(marker);						

					// Add to tree
					} else {
						// Append to item
						if (x > threshhold) {							
							// Append to ul if there is one
							if (target.is("li") && target.children("ul").length !== 0) {
								target.children("ul").append(marker);
							} else if (target.is("li")) {
								target.append(marker);
							}
						// Place before item	
						} else if (y < target.height() / 2) {
							marker.addClass(beforeClass);
							target.before(marker);
						// Place after item
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
				marker.removeClass(beforeClass, afterClass);
				marker.addClass(beforeClass);
				$(e.target).append(marker);
			} else if ($(e.target).hasClass("tdd-trashbin")) {
				debug("trashbin");
				$(e.target).append(marker);
			}
			
			//e.stopImmediatePropagation();		
		},
		
		handleDroppableDrop: function (e, o) {
			debug("handleDroppableDrop");
			
			var	draggable = $(o.draggable),
				droppable = $(e.target),
				marker = $.treeDragDrop.defaults.marker,
				ctx = draggable.data("tddCtx"),
				options = ctx.data("options");
				
			// Remove selected class
			draggable.removeClass(options.selectedClass);

			// If it's in the trashbin, put them all next to each other (no nesting)
			if (droppable.parents(".tdd-trashbin").length !== 0 || droppable.hasClass("tdd-trashbin")) {
				$(".tdd-trashbin").append(draggable);	
				$("li", draggable).each(function (index, value) {
					$(".tdd-trashbin").append(value);
				});				
				
			// Put the item directly in the tree ul if it contains no other element	
			} else if (droppable.hasClass("tdd-tree") && $(".tdd-tree").children().length === 0) {
				$(".tdd-tree").append(draggable);

			// Otherwise put it before the marker, which will be detached asap
			} else {
				marker.before(draggable);				
				if (draggable.parent().is("li")) {					
					draggable.wrap("<ul></ul>");				
				}
			}

			marker.hide();

			// Clean up empty ul's if it's not the tree or trashbin
			$("ul", ctx).not(".tdd-trashbin, .tdd-tree").each(function () {
				if ($(this).children().length === 0) {
					debug($(this));
					$(this).remove();					
				}
			});

			// Adjust expand/collapse icons
			updateFolderIcons(ctx, options);
		},
				
		// toggle expand/collapse
		handleClick: function (e) {
			
			if ($("body").hasClass("cursorGrabbing")) {
				e.stopImmediatePropagation();
				return false;
			}
			
			var target = $(e.target),
				ctx = getContext($(e.target)),
				tree = $(".tdd-tree", ctx),
				options = getOptions($(e.target)),
				collapsed = options.collapsedClass,
				expanded = options.expandedClass;
			
			
			if (target.siblings("ul").length === 0) {				
				return false;				
			} else {			
				if (target.hasClass(collapsed)) {
					target.removeClass(collapsed).addClass(expanded);					
					target.siblings("ul").show();					
				} else {
					target.removeClass(expanded).addClass(collapsed);	
					target.siblings("ul").hide();
				}
			}	
			
			//debug("handleClick: target " +target);
			//debug(target);
			//debug("handleClick: sendTree ");
			sendTree(tree, options.updateUrl);						
			e.stopImmediatePropagation();
		}
	};
	
	
	// The prototype
	$.fn.treeDragDrop = function (options) {
		
		// Extend the global default with the options for the element
		options = $.extend({}, $.treeDragDrop.defaults, options);
						
		return this.each(function () {
			var ctx = $(this),
				data = ctx.data('treeDragDrop');
			
			// Initialize the element
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
					
				}).bind("onselectstart", function () { 
					return false;
				//}).attr("unselectable", "on").data("tddCtx", ctx).has("ul").children("i").addClass(options.expandedClass);
				}).attr("unselectable", "on").data("tddCtx", ctx);
				
				$(".tdd-tree, .tdd-trashbin", ctx).droppable({
					addClasses: false,				
					tolerance: "pointer",
					drop: $.treeDragDrop.handlers.handleDroppableDrop,
					over: $.treeDragDrop.handlers.handleDroppableOver,
					out: $.treeDragDrop.handlers.handleDroppableOut				
				}).bind("onselectstart", function () {return false; }).attr("unselectable", "on");
				
				$(".tdd-tree i", ctx).bind("click", $.treeDragDrop.handlers.handleClick);
				updateFolderIcons(ctx, options);
				$(".tdd-tree i." + options.collapsedClass, ctx).siblings("ul").hide();
				
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
	collapsedClass: "fa-folder", 
	expandedClass: "fa-folder-open", 
	updateUrl: "/navigation/api/update",
	cursorGrabbingUrl: (window.is_msie) ? "/apps/navigation/js/tree-drag-drop/css/closedhand.cur" : "/apps/navigation/js/tree-drag-drop/css/cursorGrabbing.png"
}); 

