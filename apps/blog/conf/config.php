; <?php /*

[Blog]

; This is the title of your blog index page (/blog).

title = Blog

; This is the layout to use for blog listing pages.

layout = default

; This is the layout to use for the blog post page.

post_layout = default

; Here you can choose a service to use for blog comments.
; Supported platforms are Facebook and Disqus. Set it
; to Off to disable comments. For Disqus, you also need
; to register at disqus.com and enter your account's
; "shortname" value into the disqus_shortname setting.

comments = facebook
;comments = disqus

disqus_shortname = ""

[Social Buttons]

; Here you can enable/disable different social buttons
; that will be displayed in the footer of each blog
; post. To turn one off, set it to `Off`. To enable,
; set it to `On`.

twitter = On
facebook = On
google = On

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