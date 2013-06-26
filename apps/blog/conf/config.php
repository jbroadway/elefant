; <?php /*

[Blog]

; This is the title of your blog index page (/blog).

title = Blog

; This is the layout to use for blog listing pages.

layout = default

; This is the layout to use for the blog post page.

post_layout = default

; Whether to include this app in the list of pages
; available to the Tools > Navigation tree.

include_in_nav = On

; Limit the number of characters to include in the blog post previews.

preview_chars = Off

; The format posts are written in. The default, 'html', will use
; Elefant's wysiwyg editor, whereas 'markdown' will instead use
; the Markdown syntax for rendering the post body.

post_format = html

; Here you can choose a service to use for blog comments.
; Supported platforms are Facebook and Disqus, or a custom
; handler name that implements an alternate comment system.
; Set it to Off to disable comments. For Disqus, you also need
; to register at disqus.com and enter your account's
; "shortname" value into the disqus_shortname setting.
; For an example of a working custom comments provider, see
; the comments/embed handler from the comments app at
; https://github.com/jbroadway/comments

comments = facebook
;comments = disqus
;comments = comments/embed

disqus_shortname = ""

[Social Buttons]

; Here you can enable/disable different social buttons
; that will be displayed in the footer of each blog
; post. To turn one off, set it to `Off`. To enable,
; set it to `On`.

twitter = On
facebook = On
google = On

[Custom Handlers]

; You can override some of the built-in handlers with your own
; by changing them here. You can also disable any of them by setting
; them to Off.

blog/index = blog/index
blog/post = blog/post

[Admin]

handler = blog/admin
name = Blog Posts
install = blog/upgrade
upgrade = blog/upgrade
version = 1.1.3-stable
sitemap = "blog\Post::sitemap"

; */ ?>
