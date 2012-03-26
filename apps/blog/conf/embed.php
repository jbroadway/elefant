; <?php /*

[blog/headlines]

label = "Blog: Latest Posts"

tag[label] = "Tag (optional)"
tag[type] = select
tag[require] = "apps/blog/lib/Functions.php"
tag[callback] = "blog_get_tags"

[blog/tags]

label = "Blog: Tag Cloud"

[blog/rssviewer]

label = "Blog: RSS Viewer"

url[label] = RSS Link
url[type] = text
url[not empty] = 1
url[regex] = "|^http://.+$|"
url[message] = Please enter a valid URL.

; */ ?>