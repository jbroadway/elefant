# README

indextank-jquery is a bunch of little building blocks to make your search UX better.

## Quick usage

If you just want to see it working on your index, just:

1. Download a tarball -> https://github.com/flaptor/indextank-jquery/tarball/master
2. Uncompress it
3. cd to the extracted directory
4. Find test.html, and add there your API URL, and INDEX NAME
5. Point your browser to test.html, and type a query

## I like it, how do I use it on my site? 

I assume you have a search template, and it has:

- a FORM on it, with an id.
- an INPUT element with type=text, and an id. This element is a child of the FORM element above.
- a DIV element with an id, that we can use to display results.

If you met these requirements, follow these steps:

1. Copy the **&lt;head&gt;** element of test.html on the **&lt;head&gt;** element of your site's template. If you didn't go through the **Quick usage** section above, place your indextank credentials on it.
2. Edit the script, so that:
    - the indextank_Ize()'d element matches the FORM id.
    - the indextank_Autocomplete()'d element matches the INPUT id.
    - the indextank_Renderer()'d element matches the DIV id.
3. Write a query on your search box

## OK, but I want results rendered differently. Can I do that?

Sure you can!
You'll need to write a **format** function that takes a result as argument and returns the rendered HTML. 

Example

    var userFormat = function(item) {
        var r = $("<div></div>").addClass("result").addClass("user")
                .append( $("<img></img>").attr("src", item.avatar ) )
                .append( $("<a></a>").attr("href", item.url).html(item.snippet_name || item.name) )
                .append( $("<span></span>").addClass("location").text(item.hometown || "far far away") );

        // some items need a slightly different rendering
        if (item.is_staff == "true") {
                r.addClass("staff");
        } 

        return r;
    };

And then tell your renderer to use it

    $("#results").indextank_Renderer({format: userFormat});


## Dependencies

indextank-jquery relies on

- [jquery](https://github.com/jquery/jquery) > 1.4.3 
- [jquery-ui](https://github.com/jquery/jquery-ui) > 1.8.
 
You can hotlink them from github, download them and serve them yourself, or use google ajax apis.
Just make sure you import them in order, before importing indextank-jquery files.
