//-------------------------------------------------
//		Quick Pager jquery plugin
//		Created by dan and emanuel @geckonm.com
//		www.geckonewmedia.com
// 
//		v1.1
//		18/09/09 * bug fix by John V - http://blog.geekyjohn.com/
//-------------------------------------------------

(function($) {
	    
	$.fn.quickPager = function(options) {
	
		var defaults = {
			pageSize: 10,
			currentPage: 1,
			holder: null,
			pagerLocation: "after",
			pageCounter: 1
		};
		
		var options = $.extend(defaults, options);
		
		
		return this.each(function() {
	
						
			var selector = $(this);	
			options.pageCounter = 1;
			
			selector.wrap("<div class='simplePagerContainer'></div>");
			
			selector.children().each(function(i){ 
					
				if(i < options.pageCounter*options.pageSize && i >= (options.pageCounter-1)*options.pageSize) {
				$(this).addClass("simplePagerPage"+options.pageCounter);
				}
				else {
					$(this).addClass("simplePagerPage"+(options.pageCounter+1));
					options.pageCounter ++;
				}	
				
			});
			
			// show/hide the appropriate regions 
			selector.children().hide();
			selector.children(".simplePagerPage"+options.currentPage).show();
			
			if(options.pageCounter <= 1) {
				return;
			}
			
			//Build pager navigation
			var pageNav = "<ul class='simplePagerNav'>";
			pageNav += "<li class='simplePageNavFirst'><a rel='1' href='#'>&laquo;</a></li>";
			for (i=1;i<=options.pageCounter;i++){
				if (i==options.currentPage) {
					pageNav += "<li class='currentPage simplePageNav"+i+"'><a rel='"+i+"' href='#'>"+i+"</a></li>";	
				}
				else {
					pageNav += "<li class='simplePageNav"+i+"'><a rel='"+i+"' href='#'>"+i+"</a></li>";
				}
			}
			pageNav += "<li class='simplePageNavLast'><a rel='" + options.pageCounter + "' href='#'>&raquo;</a></li>";
			pageNav += "</ul>";
			
			if(!options.holder) {
				switch(options.pagerLocation)
				{
				case "before":
					selector.before(pageNav);
				break;
				case "both":
					selector.before(pageNav);
					selector.after(pageNav);
				break;
				default:
					selector.after(pageNav);
				}
			}
			else {
				$(options.holder).append(pageNav);
			}
			
			//pager navigation behaviour
			selector.parent().find(".simplePagerNav a").click(function() {
					
				//grab the REL attribute 
				var clickedLink = $(this).attr("rel");
				options.currentPage = clickedLink;
				
				if(options.holder) {
					$(this).parent("li").parent("ul").parent(options.holder).find("li.currentPage").removeClass("currentPage");
					$(this).parent("li").parent("ul").parent(options.holder).find("a[rel='"+clickedLink+"']").parent("li").addClass("currentPage");
				}
				else {
					//remove current current (!) page
					$(this).parent("li").parent("ul").parent(".simplePagerContainer").find("li.currentPage").removeClass("currentPage");
					//Add current page highlighting
					$(this).parent("li").parent("ul").parent(".simplePagerContainer").find("a[rel='"+clickedLink+"']").parent("li").addClass("currentPage");
				}
				
				//hide and show relevant links
				selector.children().hide();			
				selector.find(".simplePagerPage"+clickedLink).show();
				
				update_visible_pager_links (options);
				
				return false;
			});
			
			update_visible_pager_links (options);
		});
	}
	
	function update_visible_pager_links (options) {
		var cur = parseInt (options.currentPage);
		var start = (cur - 2 > 0) ? cur - 2 : 1;
		var end = (cur + 2 < options.pageCounter) ? cur + 2 : options.pageCounter;
		
		for (i = 1; i <= options.pageCounter; i++) {
			if (i < start || i > end) {
				$('.simplePageNav' + i).hide ();
			} else {
				$('.simplePageNav' + i).show ();
			}
		}
	}
	

})(jQuery);
