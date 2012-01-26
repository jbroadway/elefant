; <?php /*

[Blog]

; This is the title of your blog index page (/blog).

title = Blog

; This is the layout to use for blog listing pages.

layout = default

; This is the layout to use for the blog post page.

post_layout = default

; This is the service to use for blog comments. Currently
; only facebook is supported. Set it to Off to disable
; comments.

comments = facebook

[Twitter]

; Set these to your twitter credentials to have blog posts automatically
; tweeted to your twitter account.

username = ""
password = ""

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

; */ ?>