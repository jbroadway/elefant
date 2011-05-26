(function($){
    if(!$.Indextank){
        $.Indextank = new Object();
    };
    
    $.Indextank.AjaxSearch = function(el, options){
        // To avoid scope issues, use 'base' instead of 'this'
        // to reference this class from internal events and functions.
        var base = this;
        
        // Access to jQuery and DOM versions of element
        base.$el = $(el);
        base.el = el;
        
        // Add a reverse reference to the DOM object
        base.$el.data("Indextank.AjaxSearch", base);
        
        base.init = function(){
            
            base.options = $.extend({},$.Indextank.AjaxSearch.defaultOptions, options);
            base.xhr = undefined;
            
            
            // TODO: make sure ize is an Indextank.Ize element somehow
            base.ize = $(base.el.form).data("Indextank.Ize");
            base.ize.$el.submit(function(e){
                // make sure the form is not submitted
                e.preventDefault();
                base.runQuery();
            });


            // make it possible for other to trigger an ajax search
            base.$el.bind( "Indextank.AjaxSearch.runQuery", function (event, term ) {
                base.runQuery(term);
            });
        };
        
        // Sample Function, Uncomment to use
        // base.functionName = function(paramaters){
        // 
        // };

            base.runQuery = function( term ) {
                // don't run a query twice
                var query = base.options.rewriteQuery( term || base.el.value );
                if (base.query == query) {
                    return;
                } 
                
                // if we are running a query, an old one makes no sense.
                if (base.xhr != undefined ) {
                    base.xhr.abort();
                }
               

                // remember the current running query
                base.query = query;

                base.options.listeners.trigger("Indextank.AjaxSearch.searching");
                base.$el.trigger("Indextank.AjaxSearch.searching");


                // run the query, with ajax
                base.xhr = $.ajax( {
                    url: base.ize.apiurl + "/v1/indexes/" + base.ize.indexName + "/search",
                    dataType: "jsonp",
                    data: { 
                            "q": query, 
                            "fetch": base.options.fields, 
                            "snippet": base.options.snippets, 
                            "function": base.options.scoringFunction 
                          },
                    success: function( data ) { 
                                // Indextank API does not send the query.
                                // I'll save the current query inside 'data',
                                // so our listeners can use it.
                                data.query = query; 
                                base.options.listeners.trigger("Indextank.AjaxSearch.success", data);
                                }
                } );
            } 
        
        // Run initializer
        base.init();
    };
    
    $.Indextank.AjaxSearch.defaultOptions = {
        // default fields to fetch .. 
        fields : "name,title,image,url,link",
        // fields to make snippets for
        snippets : "text",
        // no one listening .. sad
        listeners: [],
        // scoring function to use
        scoringFunction: 0,
        // the default query re-writer is identity
        rewriteQuery: function(q) {return q}
    };
    
    $.fn.indextank_AjaxSearch = function(options){
        return this.each(function(){
            (new $.Indextank.AjaxSearch(this, options));
        });
    };
    
})(jQuery);
